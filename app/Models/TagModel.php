<?php

namespace App\Models;

use CodeIgniter\Model;

class TagModel extends Model
{
    protected $table         = 'tags';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = false;
    protected $allowedFields = ['name'];

    /**
     * Resolves a list of tag names to ids, creating missing tags.
     *
     * @param list<string> $names
     * @return list<int>
     */
    public function getOrCreate(array $names): array
    {
        $ids = [];
        foreach ($names as $name) {
            $name = trim(strtolower($name));
            if ($name === '') {
                continue;
            }

            $tag = $this->where('name', $name)->first();
            if ($tag !== null) {
                $ids[] = (int) $tag->id;
            } else {
                $this->insert(['name' => $name]);
                $ids[] = $this->getInsertID();
            }
        }

        return array_values(array_unique($ids));
    }

    /**
     * @param list<int> $tagIds
     */
    public function attachToIdea(int $ideaId, array $tagIds): void
    {
        $pivot = $this->db->table('idea_tags');
        foreach ($tagIds as $tagId) {
            $exists = $this->db->table('idea_tags')
                ->where('idea_id', $ideaId)->where('tag_id', (int) $tagId)
                ->countAllResults() > 0;
            if (! $exists) {
                $pivot->insert(['idea_id' => $ideaId, 'tag_id' => (int) $tagId]);
            }
        }
    }

    /**
     * @return list<object>
     */
    public function forIdea(int $ideaId): array
    {
        return $this->db->table('tags')
            ->select('tags.*')
            ->join('idea_tags', 'tags.id = idea_tags.tag_id')
            ->where('idea_tags.idea_id', $ideaId)
            ->get()->getResult();
    }

    /**
     * @return list<object>
     */
    public function allTags(): array
    {
        return $this->orderBy('name', 'ASC')->findAll();
    }
}
