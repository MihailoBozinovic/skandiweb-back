<?php

namespace App\Schemas;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class CategoryType extends ObjectType
{
    public function __construct(ProductType $productType) {
        parent::__construct([
            'name' => 'Category',
            'fields' => [
                'name' => Type::string(),
                'products' => Type::listOf($productType),
            ],
        ]);
    }
}