<?php

namespace App\Resolvers;

use App\Repositories\ProductRepository;
use GraphQL\Type\Definition\ResolveInfo;

class ProductResolver
{
    private ProductRepository $productRepository;

    public function __construct(ProductRepository $productRepository) {
        $this->productRepository = $productRepository;
    }

    public function resolve($root, $args, $context, ResolveInfo $info) {
        if (isset($args['category'])) {
            return $this->productRepository->getProductsByCategory($args['category']);
        }
        return $this->productRepository->getAllProducts();
    }
}