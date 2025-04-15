<?php

namespace App\Repositories;

use App\Database\Connection;

class CategoryRepository
{
    private \PDO $db;

    public function __construct() {
        $this->db = Connection::getInstance();
    }

    public function getAllCategories(): array {
        $query = "SELECT * FROM categories";
        return json_decode($this->db->query($query));
    }

    public function getCategoryByName(string $name): array {
        $query = "SELECT * FROM categories WHERE name = :name";
        return json_decode($this->db->query($query, ['name' => $name]));
    }
}