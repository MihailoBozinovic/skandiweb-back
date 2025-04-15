<?php

namespace App\Schemas;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class ProductType extends ObjectType {
    public function __construct() {
        parent::__construct([
            'name' => 'Product',
            'fields' => [
                'id' => Type::string(),
                'name' => Type::string(),
                'inStock' => Type::boolean(),
                'description' => Type::string(),
                'category' => Type::string(),
                'brand' => Type::string(),
                'gallery' => Type::listOf(Type::string()),
            ],
        ]);
    }
}