<?php

namespace App\Registry;

use App\Schemas\AttributeType;
use App\Schemas\CategoryType;
use App\Schemas\ProductType;

class TypeRegistry {
    private static array $types = [];

    public static function getProductType(): ProductType {
        if (!isset(self::$types['Product'])) {
            self::$types['Product'] = new ProductType();
        }
        return self::$types['Product'];
    }

    public static function getCategoryType(): CategoryType {
        if (!isset(self::$types['Category'])) {
            self::$types['Category'] = new CategoryType();
        }
        return self::$types['Category'];
    }

    public static function getAttributeType(): AttributeType {
        if (!isset(self::$types['Attribute'])) {
            self::$types['Attribute'] = new AttributeType();
        }
        return self::$types['Attribute'];
    }
}