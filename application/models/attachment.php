<?php
class Attachment extends CI_Model {
    protected $table = 'attachments';
    protected $primaryKey = 'id';
    protected $allowedFields = ['idea_id', 'file_name', 'file_path', 'file_type', 'created_at'];
    protected $useTimestamps = false;
}
