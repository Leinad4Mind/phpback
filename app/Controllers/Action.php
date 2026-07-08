<?php

namespace App\Controllers;

use App\Models\AttachmentModel;
use App\Models\CommentModel;
use App\Models\IdeaModel;
use App\Models\LogModel;
use App\Models\SettingModel;
use App\Models\TagModel;
use App\Models\UserModel;
use App\Models\VoteModel;
use CodeIgniter\HTTP\Files\UploadedFile;

class Action extends BaseController
{
    /** Allowed attachment extensions and their permitted MIME types. */
    private const ALLOWED_EXT  = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx'];
    private const ALLOWED_MIME = [
        'image/jpeg', 'image/png', 'image/gif', 'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    ];
    private const MAX_UPLOAD_BYTES = 5 * 1024 * 1024; // 5 MB

    public function register()
    {
        if (is_logged_in()) {
            return redirect()->to('home');
        }

        // Throttle registrations per IP.
        if (! service('throttler')->check(md5('register_' . $this->request->getIPAddress()), 5, MINUTE)) {
            return redirect()->to('home/register')->with('error', 'toomany');
        }

        $settings = model(SettingModel::class);
        $email    = trim((string) $this->request->getPost('email'));
        $pass     = (string) $this->request->getPost('password');
        $pass2    = (string) $this->request->getPost('password2');
        $name     = trim((string) $this->request->getPost('name'));

        if ((string) $settings->get('recaptchapublic') !== '' && ! $this->verifyRecaptcha()) {
            return redirect()->to('home/register/recaptcha');
        }
        if (mb_strlen($name) < 3) {
            return redirect()->to('home/register/name');
        }
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return redirect()->to('home/register/email');
        }
        if (mb_strlen($pass) < 6) {
            return redirect()->to('home/register/pass');
        }
        if ($pass !== $pass2) {
            return redirect()->to('home/register/pass2');
        }

        $votes = (int) ($settings->get('maxvotes') ?: 10);
        $newId = model(UserModel::class)->createUser($name, $email, $pass, $votes, 0);
        if ($newId === false) {
            return redirect()->to('home/register/exists');
        }

        model(LogModel::class)->add(($this->lang('log_user_registered') ?: 'New user') . ": {$name}({$email})", 'general', 0);

        $title   = (string) ($settings->get('title') ?: 'PHPBack');
        $message = "Welcome to our feedback: {$title}\n\nYour account has been created for {$email}.\n\nPlease log in here: " . base_url('home/login') . "\n";
        $this->sendMail($message, "New account - {$title}", $email);

        return redirect()->to('home/login/register');
    }

    public function login()
    {
        // Throttle login attempts per IP to slow brute-force.
        if (! service('throttler')->check(md5('login_' . $this->request->getIPAddress()), 8, MINUTE)) {
            return redirect()->to('home/login/toomany');
        }

        $email = trim((string) $this->request->getPost('email'));
        $pass  = (string) $this->request->getPost('password');

        $user = model(UserModel::class)->attemptLogin($email, $pass);
        if ($user === null) {
            return redirect()->to('home/login/errorlogin');
        }

        phpback_login($user);

        if ($this->request->getPost('rememberme')) {
            $this->issueRememberCookie((int) $user->id);
        }

        return redirect()->to('home');
    }

    public function logout()
    {
        $this->clearRememberCookie();
        phpback_logout();

        return redirect()->to('home');
    }

    public function vote()
    {
        $ideaId = (int) $this->request->getPost('ideaid');
        $votes  = (int) $this->request->getPost('votes');
        $userId = current_user_id();

        if (model(VoteModel::class)->castVote($ideaId, $userId, $votes)) {
            model(LogModel::class)->add(
                str_replace(['%s1', '%s2', '%s3'], ["#{$ideaId}", (string) $votes, current_username()], (string) $this->lang('log_idea_voted')),
                'user',
                $userId,
                $ideaId
            );
            $success = true;
        } else {
            $success = false;
        }

        if ($this->request->isAJAX() || $this->request->getHeaderLine('Accept') === 'application/json') {
            $newVote = model(VoteModel::class)->forUserAndIdea($userId, $ideaId);
            $idea = model(IdeaModel::class)->getIdea($ideaId);
            return $this->response->setJSON([
                'success' => $success, 
                'ideaId' => $ideaId, 
                'votes' => $votes,
                'totalVotes' => $idea ? (int) $idea->votes : 0,
                'userVoteId' => $newVote ? (int) $newVote->id : null,
                'csrfHash' => csrf_hash()
            ]);
        }

        $idea = model(IdeaModel::class)->getIdea($ideaId);
        return redirect()->to($idea?->url ?? base_url('home'));
    }

    public function unvote()
    {
        $id     = (int) $this->request->getPost('id');
        $userId = current_user_id();
        $success = false;

        $votes = model(VoteModel::class);
        $vote  = $votes->find($id);

        if ($vote !== null && (int) $vote->userid === $userId) {
            model(IdeaModel::class)->adjustVotes((int) $vote->ideaid, -(int) $vote->number);
            model(UserModel::class)->addVotes($userId, (int) $vote->number);
            $votes->delete($id);
            $success = true;
        }

        if ($this->request->isAJAX() || $this->request->getHeaderLine('Accept') === 'application/json') {
            $idea = $vote ? model(IdeaModel::class)->getIdea((int) $vote->ideaid) : null;
            return $this->response->setJSON([
                'success' => $success,
                'totalVotes' => $idea ? (int) $idea->votes : 0,
                'csrfHash' => csrf_hash()
            ]);
        }

        return redirect()->to(base_url('home/profile/' . $userId));
    }

    public function changepassword()
    {
        $userId = current_user_id();
        $old    = (string) $this->request->getPost('old');
        $new    = (string) $this->request->getPost('new');
        $rnew   = (string) $this->request->getPost('rnew');
        $back   = base_url('home/profile/' . $userId);

        if (mb_strlen($new) < 6) {
            return redirect()->to($back)->with('error', 3);
        }
        if ($new !== $rnew) {
            return redirect()->to($back)->with('error', 1);
        }

        $users = model(UserModel::class);
        $user  = $users->find($userId);
        if ($user === null || ! password_verify($old, $user->pass)) {
            return redirect()->to($back)->with('error', 2);
        }

        $users->updatePassword($userId, $new);

        // Notify the user WITHOUT ever emailing the password itself.
        $title = (string) (model(SettingModel::class)->get('title') ?: 'PHPBack');
        $body  = ($this->lang('message_password_changed') ?: 'Your password has been changed.') . "\n";
        $this->sendMail($body, ($this->lang('label_change_password') ?: 'Password changed') . " - {$title}", $user->email);

        return redirect()->to($back)->with('message', 'passwordchanged');
    }

    public function newidea()
    {
        $userId = current_user_id();
        $title  = trim((string) $this->request->getPost('title'));
        $desc   = trim((string) $this->request->getPost('description'));
        $catId  = (int) $this->request->getPost('category');

        if ($catId === 0) {
            return redirect()->to('home/postidea/errorcat')->withInput();
        }
        if (mb_strlen($title) < 9) {
            return redirect()->to('home/postidea/errortitle')->withInput();
        }
        if (mb_strlen(strip_tags($desc)) < 20) {
            return redirect()->to('home/postidea/errordesc')->withInput();
        }

        // Validate any attachment BEFORE creating the idea, so a bad file never
        // leaves a half-created idea behind.
        $file      = $this->request->getFile('attachment');
        $hasUpload = $file !== null && $file->isValid() && $file->getError() !== UPLOAD_ERR_NO_FILE;
        if ($hasUpload && ($uploadError = $this->validateAttachment($file)) !== null) {
            return redirect()->to('home/postidea/' . $uploadError)->withInput();
        }

        $ideas  = model(IdeaModel::class);
        $ideaId = $ideas->addIdea($title, $desc, $userId, $catId);
        if ($ideaId === false) {
            return redirect()->to('home/postidea/errorcat')->withInput();
        }

        model(LogModel::class)->add(($this->lang('log_new_idea') ?: 'New idea') . ": {$title}", 'user', $userId);

        // Tags (comma-separated)
        $tagsRaw = trim((string) $this->request->getPost('tags'));
        if ($tagsRaw !== '') {
            $tagModel = model(TagModel::class);
            $tagIds   = $tagModel->getOrCreate(explode(',', $tagsRaw));
            $tagModel->attachToIdea($ideaId, $tagIds);
        }

        if ($hasUpload) {
            $this->storeAttachment($ideaId, $file);
        }

        $this->notifyAdminsOfNewIdea($ideas->getIdea($ideaId));

        return redirect()->to(base_url('home/profile/' . $userId));
    }

    public function comment()
    {
        $ideaId  = (int) $this->request->getPost('idea_id');
        $content = trim((string) $this->request->getPost('content'));
        $userId  = current_user_id();

        if (trim(strip_tags($content)) === '' || mb_strlen(strip_tags($content)) > 2000) {
            if ($this->request->isAJAX() || $this->request->getHeaderLine('Accept') === 'application/json') {
                return $this->response->setJSON(['success' => false, 'error' => 'Invalid or empty content', 'csrfHash' => csrf_hash()]);
            }
            $idea = model(IdeaModel::class)->getIdea($ideaId);
            return redirect()->to($idea?->url ?? base_url('home'));
        }

        $id = model(CommentModel::class)->add($ideaId, $content, $userId);
        model(IdeaModel::class)->adjustComments($ideaId, +1);
        model(LogModel::class)->add(
            str_replace('%s', '#' . $ideaId, (string) $this->lang('log_commented')),
            'user',
            $userId,
            $ideaId
        );

        $this->notifyCommentParticipants($ideaId);

        if ($this->request->isAJAX() || $this->request->getHeaderLine('Accept') === 'application/json') {
            $user = model(UserModel::class)->find($userId);
            return $this->response->setJSON([
                'success' => true,
                'comment' => [
                    'id' => $id,
                    'user' => $user->name,
                    'userid' => $user->id,
                    'date' => date('Y-m-d H:i:s'),
                    'content' => purify_html($content)
                ],
                'csrfHash' => csrf_hash()
            ]);
        }

        $idea = model(IdeaModel::class)->getIdea($ideaId);
        return redirect()->to($idea?->url ?? base_url('home'));
    }

    public function flag()
    {
        $commentId = (int) $this->request->getPost('cid');
        $ideaId    = (int) $this->request->getPost('idea_id');

        model(\App\Models\FlagModel::class)->flag($commentId, current_user_id());

        if ($this->request->isAJAX() || $this->request->getHeaderLine('Accept') === 'application/json') {
            return $this->response->setJSON(['success' => true, 'csrfHash' => csrf_hash()]);
        }

        $idea = model(IdeaModel::class)->getIdea($ideaId);
        return redirect()->to($idea?->url ?? base_url('home'));
    }

    /* ------------------------------------------------------------------ */

    /**
     * Validates an uploaded attachment against the size, extension and detected
     * MIME allowlists. Returns an error code string, or null when acceptable.
     */
    private function validateAttachment(UploadedFile $file): ?string
    {
        if ($file->getSize() > self::MAX_UPLOAD_BYTES) {
            return 'errorsize';
        }

        $ext  = strtolower((string) $file->getClientExtension());
        $mime = (string) $file->getMimeType(); // detected from file content (finfo)

        if (! in_array($ext, self::ALLOWED_EXT, true) || ! in_array($mime, self::ALLOWED_MIME, true)) {
            return 'errorfile';
        }

        return null;
    }

    /**
     * Moves a validated upload into writable/uploads (outside the web root) and
     * records it. The stored path uses a random name; the original name is kept
     * only for display/download.
     */
    private function storeAttachment(int $ideaId, UploadedFile $file): void
    {
        $dir = WRITEPATH . 'uploads';
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $mime     = (string) $file->getMimeType();
        $origName = basename((string) $file->getClientName());
        $size     = (int) $file->getSize();
        $newName  = $file->getRandomName();
        $file->move($dir, $newName);

        model(AttachmentModel::class)->insert([
            'idea_id'    => $ideaId,
            'file_name'  => $origName,
            'file_path'  => $newName,
            'file_type'  => $mime,
            'file_size'  => $size,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    private function verifyRecaptcha(): bool
    {
        $settings = model(SettingModel::class);
        $secret = (string) $settings->get('recaptchaprivate');
        $provider = (string) ($settings->get('captcha_provider') ?? 'recaptcha_v2');
        
        if ($secret === '') {
            return true;
        }

        if ($provider === 'turnstile') {
            try {
                $client = \Config\Services::curlrequest();
                $response = $client->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                    'form_params' => [
                        'secret'   => $secret,
                        'response' => (string) $this->request->getPost('cf-turnstile-response'),
                        'remoteip' => $this->request->getIPAddress(),
                    ],
                    'http_errors' => false,
                ]);
                $body = json_decode($response->getBody(), true);
                return isset($body['success']) && $body['success'] === true;
            } catch (\Exception $e) {
                return false;
            }
        }

        if (! class_exists(\ReCaptcha\ReCaptcha::class)) {
            return true;
        }

        $recaptcha = new \ReCaptcha\ReCaptcha($secret);
        
        if ($provider === 'recaptcha_v3') {
            $recaptcha->setScoreThreshold(0.5);
        }

        $response  = $recaptcha->verify(
            (string) $this->request->getPost('g-recaptcha-response'),
            $this->request->getIPAddress()
        );

        return $response->isSuccess();
    }

    private function notifyAdminsOfNewIdea(?object $idea): void
    {
        if ($idea === null) {
            return;
        }

        $emails = array_map(static fn ($admin) => $admin->email, model(UserModel::class)->getAdmins());
        if ($emails === []) {
            return;
        }

        $title   = (string) (model(SettingModel::class)->get('title') ?: 'PHPBack');
        $template = (string) $this->lang('log_new_idea_mail_content');
        $message  = $template !== '' ? sprintf($template, $title, $idea->url) : "A new idea was posted on {$title}: {$idea->url}";

        $this->sendMail($message, ($this->lang('log_new_idea') ?: 'New idea') . " : {$title}", implode(', ', $emails));
    }

    private function notifyCommentParticipants(int $ideaId): void
    {
        $users  = model(UserModel::class);
        $emails = [];
        foreach (model(CommentModel::class)->forIdea($ideaId) as $comment) {
            $user = $users->find((int) $comment->userid);
            if ($user !== null) {
                $emails[$user->email] = true;
            }
        }
        if ($emails === []) {
            return;
        }

        $idea    = model(IdeaModel::class)->getIdea($ideaId);
        $title   = (string) (model(SettingModel::class)->get('title') ?: 'PHPBack');
        $template = (string) $this->lang('log_new_comment_mail_content');
        $message  = $template !== '' ? sprintf($template, $title, $idea?->url) : "A new comment was posted on {$title}: " . ($idea?->url ?? '');

        $this->sendMail($message, sprintf((string) ($this->lang('log_commented') ?: 'New comment on #%s'), $ideaId), implode(', ', array_keys($emails)));
    }

    private function lang(string $key): string
    {
        $lang = $this->langArray();

        return $lang[$key] ?? '';
    }
}
