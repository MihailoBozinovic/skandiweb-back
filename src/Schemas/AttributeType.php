<?php

namespace App\Schemas;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class AttributeType extends ObjectType {
    public function __construct() {
        parent::__construct([
            'name' => 'Attribute',
            'fields' => [
                'id' => Type::string(),
                'name' => Type::string(),
                'type' => Type::string(),
                'items' => Type::listOf(new ObjectType([
                    'name' => 'AttributeItem',
                    'fields' => [
                        'id' => Type::string(),
                        'displayValue' => Type::string(),
                        'value' => Type::string()
                    ]
                ])),
            ],
        ]);
    }
}