<?php

namespace App\Repositories;

use App\Database\Connection;
use Exception;
use RuntimeException;

class ProductRepository
{
    private \PDO $db;

    public function __construct() {
        $this->db = Connection::getInstance();
    }
    public function getAllProducts(): array {
        $query = "
            SELECT 
                p.id_product AS id,
                p.name,
                p.in_stock AS inStock,
                p.description,
                p.category,
                p.brand,
                g.url AS gallery
            FROM 
                products p
            LEFT JOIN 
                gallery g ON p.id_product = g.id_product;
        ";
        $stmt = $this->db->query($query);
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Group gallery images for each product
        $groupedProducts = [];
        foreach ($results as $row) {
            if (!isset($groupedProducts[$row['id']])) {
                $groupedProducts[$row['id']] = [
                    'id' => $row['id'],
                    'name' => $row['name'],
                    'inStock' => (bool)$row['inStock'],
                    'description' => $row['description'],
                    'category' => $row['category'],
                    'brand' => $row['brand'],
                    'gallery' => [],
                ];
            }
            if (!is_null($row['gallery'])) {
                $groupedProducts[$row['id']]['gallery'][] = $row['gallery'];
            }
        }

        return array_values($groupedProducts);
    }

    public function getProductsByCategory(string $category): array
    {
        $query = "
            SELECT 
                p.id,
                p.id_product,
                p.name,
                p.in_stock,
                p.description,
                p.category,
                p.brand,
                g.url AS gallery_url,
                pr.amount AS price,
                pr.currency AS currency,
                c.symbol AS symbol,
            FROM 
                products p
            LEFT JOIN 
                gallery g ON p.id_product = g.id_product
            LEFT JOIN
                prices pr ON p.id_product = pr.id_product
            LEFT JOIN
                currencies c ON c.label = pr.currency
            WHERE 
                p.category = :category;
        ";

        $results = $this->db->query($query, ['category' => $category]);

        // Group gallery URLs for each product
        $groupedProducts = [];
        foreach ($results as $row) {
            if (!isset($groupedProducts[$row['id_product']])) {
                $groupedProducts[$row['id_product']] = [
                    'id' => $row['id_product'],
                    'name' => $row['name'],
                    'inStock' => (bool)$row['in_stock'],
                    'description' => $row['description'],
                    'category' => $row['category'],
                    'brand' => $row['brand'],
                    'gallery' => []
                ];
            }
            $groupedProducts[$row['id_product']]['gallery'][] = $row['gallery_url'];
        }

        return array_values($groupedProducts);
    }

    public function getAttributesForProduct(string $productId): array {
        $query = "
            SELECT 
                a.id_attribute,
                a.name AS attribute_name,
                a.type AS attribute_type,
                ai.id_attribute_item,
                ai.display_value,
                ai.value
            FROM 
                attributes a
            LEFT JOIN 
                attribute_items ai ON a.id_attribute = ai.id_attribute
            WHERE 
                a.id_product = :productId
            AND 
                ai.id_product = :productId
        ";

        $stmt = $this->db->prepare($query);
        $stmt->execute(['productId' => $productId]);
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Group attributes and their items
        $groupedAttributes = [];
        foreach ($results as $row) {
            if (!isset($groupedAttributes[$row['id_attribute']])) {
                $groupedAttributes[$row['id_attribute']] = [
                    'id' => $row['id_attribute'],
                    'name' => $row['attribute_name'],
                    'type' => $row['attribute_type'],
                    'items' => []
                ];
            }
            $groupedAttributes[$row['id_attribute']]['items'][] = [
                'id' => $row['id_attribute_item'],
                'displayValue' => $row['display_value'],
                'value' => $row['value']
            ];
        }

        return array_values($groupedAttributes);
    }

    public function getProductById(string $id): array {
        $query = "SELECT * FROM products WHERE id = :id";
        return json_decode($this->db->query($query, ['id' => $id]));
    }

    public function getInStockProducts(): array {
        $query = "SELECT * FROM products WHERE in_stock = 1";
        return json_decode($this->db->query($query));
    }

    public function createOrder(string $orderId, array $productIds, float $totalAmount, string $currency): void {
        // Start database transaction
        $this->db->beginTransaction();

        try {
            // Insert order into 'orders' table
            $this->db->query(
                "INSERT INTO orders (id, total_amount, currency) VALUES (:id, :total_amount, :currency)",
                [
                    'id' => $orderId,
                    'total_amount' => $totalAmount,
                    'currency' => $currency
                ]
            );

            // Insert products into 'order_products' table
            foreach ($productIds as $productId) {
                $this->db->query(
                    "INSERT INTO order_products (order_id, product_id) VALUES (:order_id, :product_id)",
                    [
                        'order_id' => $orderId,
                        'product_id' => $productId
                    ]
                );
            }

            // Commit transaction
            $this->db->commit();
        } catch (Exception $e) {
            // Rollback transaction on failure
            $this->db->rollBack();
            throw new RuntimeException('Failed to create order: ' . $e->getMessage());
        }
    }
}