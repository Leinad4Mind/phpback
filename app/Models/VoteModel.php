<?php

namespace App\Models;

use CodeIgniter\Model;

class VoteModel extends Model
{
    protected $table         = 'votes';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = false;
    protected $allowedFields = ['ideaid', 'userid', 'number'];

    public function forUserAndIdea(int $userId, int $ideaId): ?object
    {
        return $this->where('userid', $userId)->where('ideaid', $ideaId)->first();
    }

    /**
     * @return list<object>
     */
    public function forIdea(int $ideaId): array
    {
        return $this->where('ideaid', $ideaId)->findAll();
    }

    /**
     * @return list<object>
     */
    public function forUser(int $userId): array
    {
        return $this->where('userid', $userId)->findAll();
    }

    public function deleteForIdea(int $ideaId): void
    {
        $this->where('ideaid', $ideaId)->delete();
    }

    /**
     * Casts (or updates) a user's vote on an idea, moving vote credits between
     * the user's balance and the idea's tally. Returns true on success.
     */
    public function castVote(int $ideaId, int $userId, int $votes): bool
    {
        if ($ideaId < 1 || $userId < 1 || $votes < 1 || $votes > 3) {
            return false;
        }

        $users = model(UserModel::class);
        $ideas = model(IdeaModel::class);

        $user = $users->find($userId);
        $idea = $ideas->find($ideaId);
        if ($user === null || $idea === null) {
            return false;
        }
        if ($idea->status === 'completed' || $idea->status === 'declined') {
            return false;
        }

        $existing = $this->forUserAndIdea($userId, $ideaId);

        if ($existing === null) {
            if ($votes > (int) $user->votes) {
                return false;
            }
            $this->insert(['ideaid' => $ideaId, 'userid' => $userId, 'number' => $votes]);
            $users->addVotes($userId, -$votes);
            $ideas->adjustVotes($ideaId, +$votes);

            return true;
        }

        $delta = $votes - (int) $existing->number;
        if ((int) $user->votes < $delta) {
            return false;
        }
        $this->update($existing->id, ['number' => $votes]);
        $users->addVotes($userId, -$delta);
        $ideas->adjustVotes($ideaId, +$delta);

        return true;
    }
}
