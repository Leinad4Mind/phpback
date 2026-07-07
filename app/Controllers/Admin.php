<?php

namespace App\Controllers;

use App\Models\CategoryModel;
use App\Models\FlagModel;
use App\Models\IdeaModel;
use App\Models\LogModel;
use App\Models\SettingModel;
use App\Models\UserModel;

class Admin extends BaseController
{
    public const VERSION = '2.0.0';

    /**
     * Admin login screen (public). Authenticated admins are sent to the
     * dashboard; the actual privilege gate lives in the route filters.
     */
    public function index($error = '')
    {
        if (is_admin(1)) {
            return redirect()->to('admin/dashboard');
        }

        $data          = ['lang' => $this->langArray(), 'title' => (string) model(SettingModel::class)->get('title')];
        $data['error'] = $error !== '' ? $error : (session('error') ?? '');

        return view('admin/login', $data);
    }

    public function dashboard()
    {
        $data         = $this->adminData();
        $data['logs'] = model(LogModel::class)->latest();
        $data['active'] = 'dashboard';

        return view('admin/dashboard/index', $data);
    }

    public function ideas()
    {
        $ideas = model(IdeaModel::class);
        $data  = $this->adminData();
        $data['active'] = 'ideas';

        $data['newideas']     = $ideas->newIdeas(150);
        $data['newideas_num'] = $ideas->newIdeasCount();
        $data['flags']        = model(FlagModel::class)->flaggedComments();
        $data['categories']   = model(CategoryModel::class)->getAllKeyed();

        $statuses = [];
        if (strtolower($this->request->getMethod()) !== 'post') {
            $form = [
                'status-completed'  => 0,
                'status-started'    => 0,
                'status-planned'    => 1,
                'status-considered' => 1,
                'status-declined'   => 0,
                'orderby'           => 'votes',
                'isdesc'            => 1,
            ];
            $categoryIds = [];
            foreach ($data['categories'] as $cat) {
                $categoryIds[]                    = $cat->id;
                $form['category-' . $cat->id]     = 1;
            }
            $statuses  = ['considered', 'planned'];
            $data['toall'] = 0;
        } else {
            $form = [
                'orderby' => $this->request->getPost('orderby') ?: 'votes',
                'isdesc'  => $this->request->getPost('isdesc') ? 1 : 0,
            ];
            foreach (['completed', 'started', 'planned', 'considered', 'declined'] as $status) {
                $on                          = (bool) $this->request->getPost('status-' . $status);
                $form['status-' . $status]   = $on ? 1 : 0;
                if ($on) {
                    $statuses[] = $status;
                }
            }
            $categoryIds = [];
            foreach ($data['categories'] as $cat) {
                $on                            = (bool) $this->request->getPost('category-' . $cat->id);
                $form['category-' . $cat->id]  = $on ? 1 : 0;
                if ($on) {
                    $categoryIds[] = (int) $cat->id;
                }
            }
            $data['toall'] = 1;
        }

        $data['form']  = $form;
        $data['ideas'] = $ideas->getIdeas((string) $form['orderby'], (bool) $form['isdesc'], 0, 150, $statuses, $categoryIds);

        return view('admin/dashboard/ideas', $data);
    }

    public function users($idban = 0)
    {
        $users = model(UserModel::class);
        $data  = $this->adminData();
        $data['active'] = 'users';

        $data['users']  = $users->listActive('id', 200);
        $data['banned'] = $users->listBanned(100);
        if ((int) $idban > 0) {
            $data['idban'] = (int) $idban;
        }

        return view('admin/dashboard/users', $data);
    }

    public function system()
    {
        $settings = model(SettingModel::class);
        $data     = $this->adminData();
        $data['active'] = 'system';

        $data['settings']    = $settings->all();
        $data['adminusers']  = model(UserModel::class)->getAdmins();
        $data['categories']  = model(CategoryModel::class)->getAllKeyed();
        $data['version']     = self::VERSION;

        return view('admin/dashboard/system', $data);
    }

    /**
     * @return array<string, mixed>
     */
    private function adminData(): array
    {
        return [
            'lang'  => $this->langArray(),
            'title' => (string) model(SettingModel::class)->get('title'),
        ];
    }
}
