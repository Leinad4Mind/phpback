<?php

namespace App\Models;

use CodeIgniter\Model;

class AttachmentModel extends Model
{
    protected $table         = 'attachments';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = false;
    protected $allowedFields = ['idea_id', 'file_name', 'file_path', 'file_type', 'file_size', 'created_at'];

    /**
     * @return list<object>
     */
    public function forIdea(int $ideaId): array
    {
        return $this->where('idea_id', $ideaId)->findAll();
    }
}
