<?php

namespace App\Models;

use CodeIgniter\Model;

class FlagModel extends Model
{
    protected $table         = 'flags';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = false;
    protected $allowedFields = ['toflagid', 'userid', 'date'];

    /**
     * Flags a comment once per user. Returns false if already flagged.
     */
    public function flag(int $commentId, int $userId): bool
    {
        if ($commentId < 1 || $userId < 1) {
            return false;
        }

        $already = $this->where('userid', $userId)->where('toflagid', $commentId)->countAllResults() > 0;
        if ($already) {
            return false;
        }

        $this->insert([
            'toflagid' => $commentId,
            'userid'   => $userId,
            'date'     => date('d/m/y H:i'),
        ]);

        return true;
    }

    public function deleteForComment(int $commentId): void
    {
        $this->where('toflagid', $commentId)->delete();
    }

    /**
     * Flagged comments enriched with their content/author and flag count.
     * Each row exposes: id, content, userid, ideaid, votes.
     *
     * @return list<object>
     */
    public function flaggedComments(): array
    {
        return $this->db->table('flags')
            ->select('flags.toflagid AS id, comments.content, comments.userid, comments.ideaid, COUNT(*) AS votes')
            ->join('comments', 'comments.id = flags.toflagid')
            ->groupBy('flags.toflagid, comments.content, comments.userid, comments.ideaid')
            ->orderBy('votes', 'DESC')
            ->get()->getResult();
    }
}
