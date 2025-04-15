<?php

namespace App\Model;

use App\Database\Connection;

abstract class Model
{
    protected \PDO $pdo;
    protected string $table;

    public function __construct() {
        $this->pdo = Connection::getInstance();

        if (!isset($this->table)) {
            $this->table = (new \ReflectionClass($this))->getShortName() . 's';
        }
    }

    public function all(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM " . $this->table);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function find(string $value, ?string $column = 'id'): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM " . $this->table . " WHERE " .  $column . " = ?");
        $stmt->execute([$value]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
}