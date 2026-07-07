<?php

namespace App\Models;

use CodeIgniter\Model;

class LogModel extends Model
{
    protected $table         = 'logs';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = false;
    protected $allowedFields = ['content', 'date', 'type', 'toid'];

    public function add(string $content, string $type, int $toid): void
    {
        $this->insert([
            'content' => $content,
            'date'    => date('d/m/y H:i'),
            'type'    => $type,
            'toid'    => $toid,
        ]);
    }

    /**
     * @return list<object>
     */
    public function forTarget(string $type, int $toid, int $limit = 0): array
    {
        $builder = $this->where('type', $type)->where('toid', $toid)->orderBy('id', 'DESC');

        return $limit > 0 ? $builder->findAll($limit) : $builder->findAll();
    }

    /**
     * @return list<object>
     */
    public function latest(int $limit = 30): array
    {
        return $this->select('content, date')->orderBy('id', 'DESC')->findAll($limit);
    }
}
