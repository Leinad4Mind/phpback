<?php

namespace App\Models;

use CodeIgniter\Model;

class CategoryModel extends Model
{
    protected $table         = 'categories';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = false;
    protected $allowedFields = ['name', 'description', 'ideas'];

    /**
     * Returns categories keyed by id, each decorated with a public URL.
     *
     * @return array<int, object>
     */
    public function getAllKeyed(): array
    {
        $list = [];
        foreach ($this->orderBy('name', 'ASC')->findAll() as $category) {
            $category->url = base_url('category/' . $category->id . '/' . url_title($category->name, '-', true));
            $list[$category->id] = $category;
        }

        return $list;
    }

    public function exists(int $id): bool
    {
        return $this->where('id', $id)->countAllResults() > 0;
    }

    public function idByName(string $name): int
    {
        $row = $this->where('name', $name)->first();

        return $row->id ?? 0;
    }

    public function addCategory(string $name, ?string $description): int
    {
        $this->insert(['name' => $name, 'description' => $description, 'ideas' => 0]);

        return $this->getInsertID();
    }

    public function adjustCount(int $id, int $delta): bool
    {
        $cat = $this->find($id);
        if ($cat === null) {
            return false;
        }

        return $this->update($id, ['ideas' => (int) $cat->ideas + $delta]);
    }
}
