<?php

namespace App\Core;

use App\Core\Database;
use PDO;

abstract class Model
{
    protected PDO $db;
    protected string $table;
    protected array $fillable = [];

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function all(): array
    {
        $stmt = $this->db->query("SELECT * FROM {$this->table} ORDER BY id DESC");
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function create(array $data): int
    {
        $fields = array_intersect_key($data, array_flip($this->fillable));
        $columns = implode(', ', array_keys($fields));
        $placeholders = ':' . implode(', :', array_keys($fields));
        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
        $stmt = $this->db->prepare($sql);
        /*var_dump($data);
        exit;*/
        $stmt->execute($fields);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $fields = array_intersect_key($data, array_flip($this->fillable));
        $set = implode(', ', array_map(fn($column) => "{$column} = :{$column}", array_keys($fields)));
        $fields['id'] = $id;
        $sql = "UPDATE {$this->table} SET {$set} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($fields);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}