<?php

namespace App\Controllers;

use App\Models\AttachmentModel;
use App\Models\CategoryModel;
use App\Models\CommentModel;
use App\Models\IdeaModel;
use App\Models\LogModel;
use App\Models\RememberTokenModel;
use App\Models\SettingModel;
use App\Models\TagModel;
use App\Models\UserModel;
use App\Models\VoteModel;
use CodeIgniter\HTTP\ResponseInterface;

class Home extends BaseController
{
    public function index()
    {
        if ($response = $this->checkBan()) {
            return $response;
        }
        if ($response = $this->autoLoginByCookie()) {
            return $response;
        }

        $settings = model(SettingModel::class);
        $ideas    = model(IdeaModel::class);

        $data                       = $this->defaultData();
        
        $welcomeTitle = (string) $settings->get('welcometext-title');
        $data['welcomeTitle'] = ($welcomeTitle === 'Welcome to our feedback') 
            ? ($data['lang']['text_welcome_title'] ?? $welcomeTitle) 
            : $welcomeTitle;

        $welcomeDescription = (string) $settings->get('welcometext-description');
        $data['welcomeDescription'] = ($welcomeDescription === 'Share your ideas and vote for the ones you like the most.') 
            ? ($data['lang']['text_welcome_description'] ?? $welcomeDescription) 
            : $welcomeDescription;

        $filters = [
            'category' => $this->request->getGet('category'),
            'status'   => $this->request->getGet('status'),
            'tag'      => $this->request->getGet('tag'),
            'sort'     => $this->request->getGet('sort'),
            'limit'    => 10,
            'page'     => $this->request->getGet('page') ?? 1,
        ];

        $hasFilter              = ! empty($filters['status']) || ! empty($filters['tag']) || ! empty($filters['category']);
        $data['filters']        = $filters;
        $data['hasFilter']      = $hasFilter;
        $data['ideas_filtered'] = $hasFilter ? $ideas->getFiltered($filters) : [];
        $data['ideas']          = [
            'completed'  => $ideas->getIdeas('id', true, 0, 10, ['completed']),
            'started'    => $ideas->getIdeas('id', true, 0, 10, ['started']),
            'planned'    => $ideas->getIdeas('id', true, 0, 10, ['planned']),
            'considered' => $ideas->getIdeas('id', true, 0, 10, ['considered']),
        ];

        return $this->render('home/index', $data);
    }

    public function category($id, $name = '', $status = '', $order = 'votes', $type = 'desc', $page = '1')
    {
        $id = (int) $id;
        if (! model(CategoryModel::class)->exists($id)) {
            return redirect()->to('home');
        }

        $ideas   = model(IdeaModel::class);
        $perPage = (int) (model(SettingModel::class)->get('max_results') ?: 20);
        $page    = (int) $page;

        $data                = $this->defaultData();
        $data['category']    = $data['categories'][$id];
        $data['ideas']       = $ideas->getByCategory($id, $order, $type, $page, $status ?: null, $perPage);
        $total               = $ideas->countApproved($id);
        $data['max_results'] = $perPage;
        $data['page']        = $page;
        $data['pages']       = (int) ceil($total / max(1, $perPage));
        $data['type']        = $type;
        $data['order']       = $order;
        $data['idea_status'] = $status;

        return $this->render('home/category_ideas', $data);
    }

    public function search()
    {
        $data          = $this->defaultData();
        $query         = (string) $this->request->getPost('query');
        $data['query'] = $query;
        $data['ideas'] = model(IdeaModel::class)->search($query);

        return $this->render('home/search_results', $data);
    }

    public function idea($id)
    {
        if ($response = $this->checkBan()) {
            return $response;
        }

        $ideas = model(IdeaModel::class);
        $idea  = $ideas->getIdea((int) $id);
        if ($idea === null) {
            return redirect()->to('home');
        }

        $users      = model(UserModel::class);
        $author     = $users->find((int) $idea->authorid);
        $idea->user = $author->name ?? '';

        $comments = model(CommentModel::class)->forIdea((int) $id);
        foreach ($comments as $comment) {
            $commentUser   = $users->find((int) $comment->userid);
            $comment->user = $commentUser->name ?? '';
        }

        $data                = $this->defaultData();
        $data['idea']        = $idea;
        $data['comments']    = $comments;
        $data['tags']        = model(TagModel::class)->forIdea((int) $id);
        $data['attachments'] = model(AttachmentModel::class)->forIdea((int) $id);
        $data['userVote']    = is_logged_in() ? model(VoteModel::class)->forUserAndIdea(current_user_id(), (int) $id) : null;

        return $this->render('home/view_idea', $data);
    }

    public function profile($id, $error = 0)
    {
        if ($response = $this->checkBan()) {
            return $response;
        }

        $users = model(UserModel::class);
        $user  = $users->find((int) $id);
        if ($user === null) {
            return redirect()->to('home');
        }

        $comments = [];
        foreach (model(CommentModel::class)->forUser((int) $id, 20) as $comment) {
            $comments[] = [
                'idea' => model(IdeaModel::class)->getIdea((int) $comment->ideaid),
                'id'   => $comment->id,
                'date' => $comment->date,
            ];
        }

        $data             = $this->defaultData();
        $data['user']     = $user;
        $data['logs']     = model(LogModel::class)->forTarget('user', (int) $id);
        $data['comments'] = $comments;
        $data['ideas']    = model(IdeaModel::class)->forUser((int) $id);
        $data['error']    = $error;

        if (is_logged_in() && (int) $user->id === current_user_id()) {
            $data['votes'] = $this->userVotes((int) $user->id);
        }

        return $this->render('home/user', $data);
    }

    public function login($error = '', $ban = 0)
    {
        if (! is_logged_in()) {
            if ($response = $this->autoLoginByCookie()) {
                return $response;
            }
        }
        if (is_logged_in()) {
            return redirect()->to('home');
        }

        $data          = $this->defaultData();
        $data['error'] = $error;
        $data['ban']   = (int) $ban;

        return $this->render('home/login', $data);
    }

    public function postidea($error = 'none')
    {
        if ($response = $this->checkBan()) {
            return $response;
        }
        if (! is_logged_in()) {
            return redirect()->to('home/login');
        }

        $data            = $this->defaultData();
        $data['error']   = $error;
        $data['POST']    = [
            'title' => old('title', ''),
            'catid' => old('catid', ''),
            'desc'  => old('desc', ''),
        ];
        $data['tagsAll'] = model(TagModel::class)->allTags();

        return $this->render('home/post_idea', $data);
    }

    public function register($error = '')
    {
        if (is_logged_in()) {
            return redirect()->to('home');
        }

        $data                    = $this->defaultData();
        $data['recaptchapublic'] = (string) model(SettingModel::class)->get('recaptchapublic');
        $data['error']           = $error;

        return $this->render('home/register', $data);
    }

    /**
     * Restores the votes-with-idea list for the profile page.
     *
     * @return list<array{idea: ?object, number: int, id: int}>
     */
    private function userVotes(int $userId): array
    {
        $list = [];
        foreach (model(VoteModel::class)->forUser($userId) as $vote) {
            $list[] = [
                'idea'   => model(IdeaModel::class)->getIdea((int) $vote->ideaid),
                'number' => (int) $vote->number,
                'id'     => (int) $vote->id,
            ];
        }

        return $list;
    }

    /**
     * Logs the user in automatically from a valid remember-me cookie.
     */
    private function autoLoginByCookie(): ?ResponseInterface
    {
        if (is_logged_in()) {
            return null;
        }

        $cookie = $this->request->getCookie('phpback_remember');
        if (! is_string($cookie) || $cookie === '') {
            return null;
        }

        $userId = model(RememberTokenModel::class)->verifyCookie($cookie);
        if ($userId === null) {
            $this->clearRememberCookie();

            return null;
        }

        $user = model(UserModel::class)->find($userId);
        if ($user === null) {
            $this->clearRememberCookie();

            return null;
        }

        phpback_login($user);
        $this->issueRememberCookie($userId, $cookie);

        return redirect()->to('home');
    }

    /**
     * Enforces active bans: expired bans are cleared, active ones log the user
     * out and redirect to the ban notice.
     */
    private function checkBan(): ?ResponseInterface
    {
        if (! is_logged_in()) {
            return null;
        }

        $users = model(UserModel::class);
        $user  = $users->find(current_user_id());
        if ($user === null) {
            return null;
        }

        $ban = (int) $user->banned;
        if ($ban === 0) {
            return null;
        }

        if ($ban !== -1 && $ban <= (int) date('Ymd')) {
            $users->setBan((int) $user->id, 0);

            return null;
        }

        phpback_logout();
        $this->clearRememberCookie();

        $days = -1;
        if ($ban !== -1) {
            $end  = \DateTime::createFromFormat('Ymd', (string) $ban);
            $days = $end ? max(0, (int) (new \DateTime('today'))->diff($end)->days) : 0;
        }

        return redirect()->to('home/login/banned/' . $days);
    }
}
