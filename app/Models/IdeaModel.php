<?php

namespace App\Models;

use CodeIgniter\Model;

class IdeaModel extends Model
{
    protected $table         = 'ideas';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = false;
    protected $allowedFields = ['title', 'content', 'authorid', 'date', 'votes', 'comments', 'status', 'categoryid', 'created_at'];

    public const STATUSES = ['new', 'considered', 'planned', 'started', 'completed', 'declined'];

    /**
     * Adds the derived `parsedTitle` slug and public `url` to an idea object.
     */
    public function decorate(?object $idea): ?object
    {
        if ($idea === null) {
            return null;
        }

        $idea->parsedTitle = url_title($idea->title, '-', true);
        $idea->url         = base_url('home/idea/' . $idea->id . '/' . $idea->parsedTitle);

        return $idea;
    }

    /**
     * @param list<object> $ideas
     * @return list<object>
     */
    public function decorateMany(array $ideas): array
    {
        foreach ($ideas as $idea) {
            $this->decorate($idea);
        }

        return $ideas;
    }

    public function getIdea(int $id): ?object
    {
        return $this->decorate($this->find($id));
    }

    public function getLastIdea(): ?object
    {
        return $this->decorate($this->orderBy('id', 'DESC')->first());
    }

    /**
     * Admin idea listing with status/category filters.
     *
     * @param list<string> $statuses
     * @param list<int>    $categories
     * @return list<object>
     */
    public function getIdeas(string $orderby, bool $isDesc, int $from, int $limit, array $statuses = [], array $categories = []): array
    {
        $orderby = in_array($orderby, ['id', 'title', 'votes', 'date', 'comments'], true) ? $orderby : 'votes';

        $builder = $this->db->table('ideas')->select('ideas.*');

        if ($categories !== []) {
            $builder->whereIn('categoryid', array_map('intval', $categories));
        }
        if ($statuses !== []) {
            $builder->whereIn('status', $statuses);
        }

        $builder->orderBy($orderby, $isDesc ? 'DESC' : 'ASC')->limit($limit, $from);

        return $this->decorateMany($builder->get()->getResult());
    }

    /**
     * Public category listing (excludes unapproved 'new' ideas).
     * Supports an optional status filter (ported from upstream PR #164).
     *
     * @return list<object>
     */
    public function getByCategory(int $categoryId, string $order = 'votes', string $type = 'desc', int $page = 1, ?string $status = null, int $perPage = 20): array
    {
        $orderColumn = in_array($order, ['id', 'title', 'votes'], true) ? $order : 'votes';
        $direction   = strtolower($type) === 'asc' ? 'ASC' : 'DESC';

        $builder = $this->db->table('ideas')->where('categoryid', $categoryId);

        if ($status !== null && in_array($status, self::STATUSES, true) && $status !== 'new') {
            $builder->where('status', $status);
        } else {
            $builder->where('status !=', 'new');
        }

        $builder->orderBy($orderColumn, $direction);

        if ($page > 0) {
            $builder->limit($perPage, ($page - 1) * $perPage);
        }

        return $this->decorateMany($builder->get()->getResult());
    }

    public function countApproved(int $categoryId): int
    {
        return $this->where('categoryid', $categoryId)->where('status !=', 'new')->countAllResults();
    }

    /**
     * Every idea in a category, regardless of status (used when deleting a
     * whole category).
     *
     * @return list<object>
     */
    public function allByCategory(int $categoryId): array
    {
        return $this->where('categoryid', $categoryId)->findAll();
    }

    /**
     * Homepage filters: category / status / tag / sort / pagination.
     *
     * @param array<string, mixed> $filters
     * @return list<object>
     */
    public function getFiltered(array $filters): array
    {
        $builder = $this->db->table('ideas')->select('ideas.*');

        if (! empty($filters['tag'])) {
            $builder->join('idea_tags', 'ideas.id = idea_tags.idea_id')
                ->where('idea_tags.tag_id', (int) $filters['tag']);
        }
        if (! empty($filters['category'])) {
            $builder->where('ideas.categoryid', (int) $filters['category']);
        }
        if (! empty($filters['status'])) {
            if (is_array($filters['status'])) {
                $builder->whereIn('ideas.status', $filters['status']);
            } else {
                $builder->where('ideas.status', $filters['status']);
            }
        }
        $builder->where('ideas.status !=', 'new');

        if (($filters['sort'] ?? '') === 'votes') {
            $builder->orderBy('ideas.votes', 'DESC');
        } else {
            $builder->orderBy('ideas.id', 'DESC');
        }

        $limit  = max(1, (int) ($filters['limit'] ?? 10));
        $page   = max(1, (int) ($filters['page'] ?? 1));
        $builder->limit($limit, ($page - 1) * $limit);

        return $this->decorateMany($builder->get()->getResult());
    }

    /**
     * @return list<object>
     */
    public function search(string $query): array
    {
        $keywords = array_filter(array_map('trim', explode(' ', $query)));
        if ($keywords === []) {
            return [];
        }

        $builder = $this->db->table('ideas')->groupStart();
        foreach ($keywords as $keyword) {
            $builder->orLike('title', $keyword);
        }
        $builder->groupEnd()->orderBy('votes', 'DESC');

        return $this->decorateMany($builder->get()->getResult());
    }

    /**
     * @return list<object>
     */
    public function forUser(int $userId): array
    {
        return $this->decorateMany($this->where('authorid', $userId)->findAll());
    }

    /**
     * @return list<object>
     */
    public function newIdeas(int $limit): array
    {
        return $this->decorateMany($this->where('status', 'new')->orderBy('id', 'DESC')->findAll($limit));
    }

    public function newIdeasCount(): int
    {
        return $this->where('status', 'new')->countAllResults();
    }

    public function addIdea(string $title, string $content, int $authorId, int $categoryId): int|false
    {
        if ($authorId < 1 || $categoryId < 1) {
            return false;
        }

        $this->insert([
            'title'      => $title,
            'content'    => $content,
            'authorid'   => $authorId,
            'date'       => date('d/m/y H:i'),
            'created_at' => date('Y-m-d H:i:s'),
            'votes'      => 0,
            'comments'   => 0,
            'status'     => 'new',
            'categoryid' => $categoryId,
        ]);

        return $this->getInsertID();
    }

    public function adjustVotes(int $id, int $delta): void
    {
        $idea = $this->find($id);
        if ($idea !== null) {
            $this->update($id, ['votes' => (int) $idea->votes + $delta]);
        }
    }

    public function adjustComments(int $id, int $delta): void
    {
        $idea = $this->find($id);
        if ($idea !== null) {
            $this->update($id, ['comments' => max(0, (int) $idea->comments + $delta)]);
        }
    }

    /**
     * Approves a 'new' idea (-> considered) and bumps the category counter.
     */
    public function approve(int $id): void
    {
        $idea = $this->find($id);
        if ($idea === null) {
            return;
        }

        $this->changeStatus($id, 'considered');
        model(CategoryModel::class)->adjustCount((int) $idea->categoryid, +1);
    }

    /**
     * Changes an idea's status, restoring spent votes when it becomes
     * completed/declined and keeping the category counter in sync.
     */
    public function changeStatus(int $id, string $status): void
    {
        if (! in_array($status, self::STATUSES, true)) {
            return;
        }

        $idea = $this->find($id);
        if ($idea === null) {
            return;
        }

        if ($status === 'completed' || $status === 'declined') {
            $this->restoreVotes($id);

            if ($status === 'declined' && $idea->status !== 'new') {
                model(CategoryModel::class)->adjustCount((int) $idea->categoryid, -1);
            }
        }

        $this->update($id, ['status' => $status]);
    }

    /**
     * Deletes an idea and all dependent rows (comments, flags, votes),
     * restoring spent votes and fixing the category counter.
     */
    public function deleteIdea(int $id): void
    {
        $idea = $this->find($id);
        if ($idea === null) {
            return;
        }

        $comments = model(CommentModel::class);
        $flags    = model(FlagModel::class);
        foreach ($comments->forIdea($id) as $comment) {
            $flags->deleteForComment((int) $comment->id);
        }
        $comments->where('ideaid', $id)->delete();

        $this->restoreVotes($id);

        if ($idea->status !== 'new' && $idea->status !== 'declined') {
            model(CategoryModel::class)->adjustCount((int) $idea->categoryid, -1);
        }

        $this->delete($id); // FK cascade removes idea_tags + attachments rows
    }

    /**
     * Returns spent votes to their owners and clears the idea's vote rows.
     */
    private function restoreVotes(int $ideaId): void
    {
        $votes = model(VoteModel::class);
        $users = model(UserModel::class);

        foreach ($votes->forIdea($ideaId) as $vote) {
            $users->addVotes((int) $vote->userid, (int) $vote->number);
        }
        $votes->deleteForIdea($ideaId);
    }
}
