<?php

namespace App\Controllers;

use App\Models\LogModel;
use App\Models\SettingModel;
use App\Models\UserModel;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Google OAuth2 "Sign in with Google" (authorization-code flow + PKCE).
 *
 * Both endpoints are GET and therefore outside the global CSRF filter; the
 * one-time `state` value bound to the session provides the CSRF protection
 * for this flow, and PKCE protects the authorization code itself.
 */
class Auth extends BaseController
{
    private const AUTH_ENDPOINT     = 'https://accounts.google.com/o/oauth2/v2/auth';
    private const TOKEN_ENDPOINT    = 'https://oauth2.googleapis.com/token';
    private const USERINFO_ENDPOINT = 'https://openidconnect.googleapis.com/v1/userinfo';

    public function google()
    {
        if (is_logged_in()) {
            return redirect()->to('home');
        }

        [$clientId, $clientSecret] = $this->credentials();
        if ($clientId === '' || $clientSecret === '') {
            return redirect()->to('home/login');
        }

        if (! service('throttler')->check(md5('google_' . $this->request->getIPAddress()), 8, MINUTE)) {
            return redirect()->to('home/login/toomany');
        }

        $state    = bin2hex(random_bytes(16));
        $verifier = bin2hex(random_bytes(32));
        session()->set(['oauth2_state' => $state, 'oauth2_verifier' => $verifier]);

        return redirect()->to(self::AUTH_ENDPOINT . '?' . http_build_query([
            'client_id'             => $clientId,
            'redirect_uri'          => base_url('auth/google/callback'),
            'response_type'         => 'code',
            'scope'                 => 'openid email profile',
            'state'                 => $state,
            'code_challenge'        => rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '='),
            'code_challenge_method' => 'S256',
        ]));
    }

    public function googleCallback()
    {
        if (is_logged_in()) {
            return redirect()->to('home');
        }

        if (! service('throttler')->check(md5('google_' . $this->request->getIPAddress()), 8, MINUTE)) {
            return redirect()->to('home/login/toomany');
        }

        // Requires Config\Cookie::$samesite = 'Lax': 'Strict' would drop the
        // session cookie on Google's cross-site redirect and void the state.
        $session  = session();
        $expected = (string) $session->get('oauth2_state');
        $verifier = (string) $session->get('oauth2_verifier');
        $session->remove(['oauth2_state', 'oauth2_verifier']); // one-time use

        if ($this->request->getGet('error') !== null) {
            return $this->fail('provider returned error: ' . (string) $this->request->getGet('error'));
        }

        $state = (string) $this->request->getGet('state');
        $code  = (string) $this->request->getGet('code');
        if ($expected === '' || $state === '' || ! hash_equals($expected, $state) || $code === '') {
            return $this->fail('missing or mismatched state/code');
        }

        [$clientId, $clientSecret] = $this->credentials();
        if ($clientId === '' || $clientSecret === '') {
            return $this->fail('credentials not configured');
        }

        $token = $this->exchangeCode($code, $verifier, $clientId, $clientSecret);
        if ($token === null) {
            return $this->fail('token exchange failed');
        }

        $info = $this->fetchUserinfo($token);
        if ($info === null) {
            return $this->fail('userinfo fetch failed');
        }

        $sub      = (string) ($info['sub'] ?? '');
        $email    = mb_strtolower(trim((string) ($info['email'] ?? '')));
        $verified = ($info['email_verified'] ?? false) === true || ($info['email_verified'] ?? '') === 'true';
        if ($sub === '' || $email === '' || ! $verified) {
            return $this->fail('missing sub/email or email not verified');
        }

        $users = model(UserModel::class);
        $user  = $users->findByGoogleId($sub);

        if ($user === null) {
            $existing = $users->findByEmail($email);
            if ($existing !== null) {
                // Google verified ownership of this address: link the accounts.
                $users->linkGoogle((int) $existing->id, $sub);
                $user = $existing;
            } else {
                $user = $this->registerGoogleUser($sub, $email, (string) ($info['name'] ?? ''));
                if ($user === null) {
                    return $this->fail('auto-registration failed');
                }
            }
        }

        if ($response = $this->rejectIfBanned($user)) {
            return $response;
        }

        phpback_login($user);

        return redirect()->to('home');
    }

    /**
     * @return array{0: string, 1: string} [client id, client secret]
     */
    private function credentials(): array
    {
        $settings = model(SettingModel::class);

        return [
            trim((string) $settings->get('google_client_id')),
            trim((string) $settings->get('google_client_secret')),
        ];
    }

    private function exchangeCode(string $code, string $verifier, string $clientId, string $clientSecret): ?string
    {
        try {
            $client   = \Config\Services::curlrequest();
            $response = $client->post(self::TOKEN_ENDPOINT, [
                'form_params' => [
                    'code'          => $code,
                    'client_id'     => $clientId,
                    'client_secret' => $clientSecret,
                    'redirect_uri'  => base_url('auth/google/callback'),
                    'grant_type'    => 'authorization_code',
                    'code_verifier' => $verifier,
                ],
                'http_errors' => false,
            ]);
            $body = json_decode($response->getBody(), true);

            $token = is_array($body) ? ($body['access_token'] ?? null) : null;

            return is_string($token) && $token !== '' ? $token : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    private function fetchUserinfo(string $accessToken): ?array
    {
        try {
            $client   = \Config\Services::curlrequest();
            $response = $client->get(self::USERINFO_ENDPOINT, [
                'headers'     => ['Authorization' => 'Bearer ' . $accessToken],
                'http_errors' => false,
            ]);
            $body = json_decode($response->getBody(), true);

            return is_array($body) ? $body : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Mirrors Action::register() defaults; the random password keeps password
     * login effectively disabled for the account until it is reset.
     */
    private function registerGoogleUser(string $sub, string $email, string $name): ?object
    {
        $settings = model(SettingModel::class);
        $users    = model(UserModel::class);

        $name  = trim($name) !== '' ? trim($name) : (string) strstr($email, '@', true);
        $votes = (int) ($settings->get('maxvotes') ?: 10);

        $newId = $users->createUser($name, $email, bin2hex(random_bytes(32)), $votes, 0);
        if ($newId === false) {
            return null;
        }
        $users->linkGoogle((int) $newId, $sub);

        $lang = $this->langArray();
        model(LogModel::class)->add((($lang['log_user_registered'] ?? '') ?: 'New user') . ": {$name}({$email})", 'general', 0);

        $title   = (string) ($settings->get('title') ?: 'PHPBack');
        $message = "Welcome to our feedback: {$title}\n\nYour account has been created for {$email}.\n\nPlease log in here: " . base_url('home/login') . "\n";
        $this->sendMail($message, "New account - {$title}", $email);

        return $users->findUser((int) $newId);
    }

    /**
     * Same semantics as Home::checkBan(), but runs before the session exists:
     * expired bans are cleared, active ones redirect to the ban notice.
     */
    private function rejectIfBanned(object $user): ?ResponseInterface
    {
        $ban = (int) $user->banned;
        if ($ban === 0) {
            return null;
        }

        if ($ban !== -1 && $ban <= (int) date('Ymd')) {
            model(UserModel::class)->setBan((int) $user->id, 0);

            return null;
        }

        $days = -1;
        if ($ban !== -1) {
            $end  = \DateTime::createFromFormat('Ymd', (string) $ban);
            $days = $end ? max(0, (int) (new \DateTime('today'))->diff($end)->days) : 0;
        }

        return redirect()->to('home/login/banned/' . $days);
    }

    private function fail(string $detail): ResponseInterface
    {
        log_message('error', 'Google OAuth: ' . $detail);

        return redirect()->to('home/login/googlefail');
    }
}
