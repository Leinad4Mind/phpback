<?php

namespace App\Models;

use CodeIgniter\Model;

class CommentModel extends Model
{
    protected $table         = 'comments';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = false;
    protected $allowedFields = ['content', 'ideaid', 'userid', 'date'];

    /**
     * @return list<object>
     */
    public function forIdea(int $ideaId): array
    {
        return $this->where('ideaid', $ideaId)->findAll();
    }

    public function add(int $ideaId, string $content, int $userId): int
    {
        $this->insert([
            'content' => $content,
            'ideaid'  => $ideaId,
            'userid'  => $userId,
            'date'    => date('d/m/y H:i'),
        ]);

        return $this->getInsertID();
    }

    /**
     * @return list<object>
     */
    public function forUser(int $userId, int $limit): array
    {
        return $this->where('userid', $userId)->orderBy('id', 'DESC')->findAll($limit);
    }

    public function countForIdea(int $ideaId): int
    {
        return $this->where('ideaid', $ideaId)->countAllResults();
    }
}
