<?php

namespace App\Models;

use CodeIgniter\Model;

class SettingModel extends Model
{
    protected $table         = 'settings';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = false;
    protected $allowedFields = ['name', 'value'];

    /** @var array<string, mixed> */
    private array $cache = [];

    /**
     * Returns a setting value by name, or false when not set.
     */
    public function get(string $name)
    {
        if (array_key_exists($name, $this->cache)) {
            return $this->cache[$name];
        }

        $row = $this->where('name', $name)->first();

        return $this->cache[$name] = $row->value ?? false;
    }

    /**
     * @return list<object>
     */
    public function all(): array
    {
        return $this->orderBy('id', 'ASC')->findAll();
    }

    public function updateValue(int $id, ?string $value): bool
    {
        $this->cache = [];

        return $this->update($id, ['value' => $value]);
    }

    /**
     * Builds a CodeIgniter 4 email configuration array from stored settings.
     *
     * @return array<string, mixed>
     */
    public function emailConfig(): array
    {
        return [
            'protocol'    => 'smtp',
            'SMTPHost'    => (string) $this->get('smtp-host'),
            'SMTPPort'    => (int) ($this->get('smtp-port') ?: 25),
            'SMTPUser'    => (string) $this->get('smtp-user'),
            'SMTPPass'    => (string) $this->get('smtp-pass'),
            'SMTPTimeout' => 7,
            'charset'     => 'utf-8',
            'newline'     => "\r\n",
            'mailType'    => 'text',
            'wordWrap'    => true,
        ];
    }
}
