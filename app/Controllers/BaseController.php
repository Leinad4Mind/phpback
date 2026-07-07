<?php

namespace App\Controllers;

use App\Models\CategoryModel;
use App\Models\RememberTokenModel;
use App\Models\SettingModel;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 *
 * Extend this class in any new controllers:
 * ```
 *     class Home extends BaseController
 * ```
 *
 * For security, be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /**
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */

    // protected $session;

    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all other controllers that extend BaseController.
     *
     * @var list<string>
     */
    protected $helpers = ['url', 'form', 'text', 'html', 'common', 'vite'];

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Load here all helpers you want to be available in your controllers that extend BaseController.
        // Caution: Do not put the this below the parent::initController() call below.
        // $this->helpers = ['form', 'url'];

        // Caution: Do not edit this line.
        parent::initController($request, $response, $logger);

        // Preload any models, libraries, etc, here.
        // $this->session = service('session');
    }

    /**
     * Translation array for the configured language, merged over English so
     * keys missing from a language pack fall back to the English string.
     *
     * @return array<string, string>
     */
    protected function langArray(): array
    {
        static $cache = null;
        if ($cache !== null) {
            return $cache;
        }

        $language = (string) model(SettingModel::class)->get('language') ?: 'english';
        $base     = APPPATH . 'Language' . DIRECTORY_SEPARATOR . 'phpback' . DIRECTORY_SEPARATOR;

        $english = is_file($base . 'english.php') ? require $base . 'english.php' : [];

        $file = $base . preg_replace('/[^a-z\-]/', '', strtolower($language)) . '.php';
        if (! is_file($file) || $file === $base . 'english.php') {
            return $cache = $english;
        }

        return $cache = array_merge($english, require $file);
    }

    /**
     * Language packs available in app/Language/phpback, as slug => native name.
     *
     * @return array<string, string>
     */
    protected function availableLanguages(): array
    {
        $languages = [];
        foreach (glob(APPPATH . 'Language/phpback/*.php') ?: [] as $file) {
            $pack = require $file;
            $slug = basename($file, '.php');
            $languages[$slug] = (string) ($pack['lang_name'] ?? ucfirst($slug));
        }
        ksort($languages);

        return $languages;
    }

    /**
     * Data shared by every public page (site title, categories, translations).
     *
     * @return array<string, mixed>
     */
    protected function defaultData(): array
    {
        $lang = $this->langArray();

        return [
            'title'      => (string) model(SettingModel::class)->get('title'),
            'categories' => model(CategoryModel::class)->getAllKeyed(),
            'lang'       => $lang,
            'langCode'   => (string) ($lang['lang_code'] ?? 'en'),
            'langDir'    => (string) ($lang['lang_dir'] ?? 'ltr'),
        ];
    }

    /**
     * Renders a public page wrapped in the shared header/menu/footer template.
     *
     * @param array<string, mixed> $data
     */
    protected function render(string $view, array $data): string
    {
        return view($view, $data);
    }

    /**
     * Issues (or rotates) the "remember me" cookie for a user.
     */
    protected function issueRememberCookie(int $userId, ?string $oldCookie = null): void
    {
        $tokens = model(RememberTokenModel::class);
        if ($oldCookie !== null && $oldCookie !== '') {
            $tokens->clearCookie($oldCookie);
        }

        $value = $tokens->issue($userId, 30);
        $this->response->setCookie([
            'name'     => 'phpback_remember',
            'value'    => $value,
            'expire'   => 86400 * 30,
            'path'     => '/',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    protected function clearRememberCookie(): void
    {
        $cookie = $this->request->getCookie('phpback_remember');
        if (is_string($cookie) && $cookie !== '') {
            model(RememberTokenModel::class)->clearCookie($cookie);
        }
        $this->response->deleteCookie('phpback_remember', '', '/');
    }
}
