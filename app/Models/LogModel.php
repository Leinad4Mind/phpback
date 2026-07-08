<?php

namespace App\Models;

use CodeIgniter\Model;

class LogModel extends Model
{
    protected $table         = 'logs';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = false;
    protected $allowedFields = ['content', 'date', 'type', 'toid', 'idea_id'];

    public function add(string $content, string $type, int $toid, ?int $ideaId = null): void
    {
        $this->insert([
            'content' => $content,
            'date'    => date('d/m/y H:i'),
            'type'    => $type,
            'toid'    => $toid,
            'idea_id' => $ideaId,
        ]);
    }

    public function forTarget(string $type, int $toid, int $limit = 0): array
    {
        $builder = $this->select('logs.*, users.name as user_name, ideas.title as idea_title')
                        ->join('users', 'users.id = logs.toid', 'left')
                        ->join('ideas', 'ideas.id = logs.idea_id', 'left')
                        ->where('logs.type', $type)
                        ->where('logs.toid', $toid)
                        ->orderBy('logs.id', 'DESC');

        return $limit > 0 ? $builder->findAll($limit) : $builder->findAll();
    }

    public function latest(int $limit = 30): array
    {
        return $this->select('logs.content, logs.date, logs.idea_id, logs.toid, users.name as user_name, ideas.title as idea_title')
                    ->join('users', 'users.id = logs.toid', 'left')
                    ->join('ideas', 'ideas.id = logs.idea_id', 'left')
                    ->orderBy('logs.id', 'DESC')
                    ->findAll($limit);
    }
}
