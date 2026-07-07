<?php

namespace App\Controllers;

use App\Models\AttachmentModel;
use App\Models\CategoryModel;
use App\Models\CommentModel;
use App\Models\FlagModel;
use App\Models\IdeaModel;
use App\Models\LogModel;
use App\Models\SettingModel;
use App\Models\UserModel;

class Adminaction extends BaseController
{
    /**
     * Admin login (public POST). Rejects non-admins; the privileged routes
     * are additionally gated by the admin filter.
     */
    public function login()
    {
        if (! service('throttler')->check(md5('adminlogin_' . $this->request->getIPAddress()), 8, MINUTE)) {
            return redirect()->to('admin')->with('error', 'toomany');
        }

        $email = trim((string) $this->request->getPost('email'));
        $pass  = (string) $this->request->getPost('password');

        $user = model(UserModel::class)->attemptLogin($email, $pass);
        if ($user === null) {
            return redirect()->to('admin/index/error');
        }
        if ((int) $user->isadmin < 1) {
            return redirect()->to('admin/index/noadmin');
        }

        phpback_login($user);

        return redirect()->to('admin/dashboard');
    }

    public function banuser()
    {
        $id   = (int) $this->request->getPost('id');
        $days = (int) $this->request->getPost('days');

        $bannedValue = $days === 0 ? -1 : (int) date('Ymd', strtotime("+{$days} days"));
        model(UserModel::class)->setBan($id, $bannedValue);

        $this->log(str_replace(['%s1', '%s2'], ["#{$id}", (string) $bannedValue], (string) $this->lang('log_user_banned')));
        $this->log(str_replace('%s', "#{$id}", (string) $this->lang('log_user_was_banned')), 'user', $id);

        return redirect()->to('admin/users');
    }

    public function unban()
    {
        $id = (int) $this->request->getPost('id');
        model(UserModel::class)->setBan($id, 0);
        $this->log(str_replace('%s', "#{$id}", (string) $this->lang('log_user_unbanned')));

        return redirect()->to('admin/users');
    }

    public function deletecomment()
    {
        $id       = (int) $this->request->getPost('id');
        $comments = model(CommentModel::class);
        $comment  = $comments->find($id);

        if ($comment !== null) {
            model(IdeaModel::class)->adjustComments((int) $comment->ideaid, -1);
            $comments->delete($id);
            model(FlagModel::class)->deleteForComment($id);
            $this->log(str_replace('%s', "#{$id}", (string) $this->lang('log_comment_deleted')));
        }

        return redirect()->to('admin/ideas');
    }

    public function deleteidea()
    {
        $id = (int) $this->request->getPost('id');

        // Remove attachment files from disk before the DB rows cascade away.
        foreach (model(AttachmentModel::class)->forIdea($id) as $attachment) {
            $path = WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR . $attachment->file_path;
            if (is_file($path)) {
                @unlink($path);
            }
        }

        model(IdeaModel::class)->deleteIdea($id);
        $this->log(str_replace('%s', "#{$id}", (string) $this->lang('log_idea_deleted')));

        return redirect()->to('home');
    }

    public function approveidea()
    {
        $id = (int) $this->request->getPost('id');
        model(IdeaModel::class)->approve($id);
        $this->log(str_replace('%s', "#{$id}", (string) $this->lang('log_idea_approved')));

        return redirect()->to('home/idea/' . $id);
    }

    public function ideastatus()
    {
        $id     = (int) $this->request->getPost('id');
        $status = (string) $this->request->getPost('status');

        if (! in_array($status, IdeaModel::STATUSES, true)) {
            return redirect()->to('home/idea/' . $id);
        }

        model(IdeaModel::class)->changeStatus($id, $status);
        $this->log(str_replace(['%s1', '%s2'], ["#{$id}", $status], (string) $this->lang('log_idea_status')));

        return redirect()->to('home/idea/' . $id);
    }

    public function editsettings()
    {
        $settings = model(SettingModel::class);
        foreach ($settings->all() as $setting) {
            $value = $this->request->getPost('setting-' . $setting->id);
            if ($value !== null) {
                $settings->updateValue((int) $setting->id, (string) $value);
            }
        }
        $this->log((string) $this->lang('log_settings'), 'system');

        return redirect()->to('admin/system');
    }

    public function editadmin()
    {
        $id    = (int) $this->request->getPost('id');
        $level = (int) $this->request->getPost('level');

        if (current_user_id() !== $id && model(UserModel::class)->setAdminLevel($id, $level)) {
            $this->log((string) $this->lang('log_user_admin'), 'user', $id);
        }

        return redirect()->to('admin/system');
    }

    public function addcategory()
    {
        $name        = trim((string) $this->request->getPost('name'));
        $description = (string) $this->request->getPost('description');
        if ($name === '') {
            return redirect()->to('admin/system');
        }

        $categories = model(CategoryModel::class);
        $existingId = $categories->idByName($name);
        if ($existingId > 0) {
            $categories->update($existingId, ['description' => $description]);
            $this->log("'{$name}'" . $this->lang('log_category_description'));
        } else {
            $categories->addCategory($name, $description);
            $this->log("'{$name}'" . $this->lang('log_category_created'));
        }

        return redirect()->to('admin/system');
    }

    public function updatecategories()
    {
        $categories = model(CategoryModel::class);
        foreach ($categories->getAllKeyed() as $cat) {
            $new = trim((string) $this->request->getPost('category-' . $cat->id));
            if ($new !== '' && $new !== $cat->name) {
                $categories->update($cat->id, ['name' => $new]);
                $this->log(str_replace(['%s1', '%s2'], [$cat->name, $new], (string) $this->lang('log_category_changed')));
            }
        }

        return redirect()->to('admin/system');
    }

    public function deletecategory()
    {
        $id = (int) $this->request->getPost('catid');

        if ($this->request->getPost('ideas')) {
            $ideas = model(IdeaModel::class);
            foreach ($ideas->allByCategory($id) as $idea) {
                $ideas->deleteIdea((int) $idea->id);
            }
        }

        model(CategoryModel::class)->delete($id);
        $this->log(str_replace('%s', "#{$id}", (string) $this->lang('log_category_deleted')));

        return redirect()->to('admin/system');
    }

    /* ------------------------------------------------------------------ */

    private function log(string $content, string $type = 'user', ?int $toid = null): void
    {
        model(LogModel::class)->add($content, $type, $toid ?? (current_user_id() ?? 0));
    }

    private function lang(string $key): string
    {
        $lang = $this->langArray();

        return $lang[$key] ?? '';
    }
}
