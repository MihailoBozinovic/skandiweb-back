<?php

namespace App\Resolvers;

use App\Repositories\CategoryRepository;

class CategoryResolver
{
    private CategoryRepository $categoryRepository;

    public function __construct(CategoryRepository $categoryRepository) {
        $this->categoryRepository = $categoryRepository;
    }

    public function resolve($root, $args, $context) {
        return $this->categoryRepository->getAllCategories();
    }
}