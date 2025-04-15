<?php

namespace App\Controller;

use App\Registry\TypeRegistry;
use App\Repositories\CategoryRepository;
use App\Repositories\ProductRepository;
use App\Resolvers\CategoryResolver;
use App\Resolvers\ProductResolver;
use App\Schemas\AttributeType;
use App\Schemas\CategoryType;
use App\Schemas\ProductType;
use GraphQL\Error\DebugFlag;
use GraphQL\GraphQL as GraphQLBase;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;
use GraphQL\Type\SchemaConfig;
use RuntimeException;
use Throwable;

class GraphQL {
    static public function handle() {
        try {
            // Repositories
            $productRepository = new ProductRepository();
            $categoryRepository = new CategoryRepository();

            // Resolvers
            $productResolver = new ProductResolver($productRepository);
            $categoryResolver = new CategoryResolver($categoryRepository);

            $productType = new ProductType();
            $categoryType = new CategoryType($productType);
            $attributeType = new AttributeType();

            $queryType = new ObjectType([
                'name' => 'Query',
                'fields' => [
                    'categories' => [
                        'type' => Type::listOf($categoryType),
                        'resolve' => [$categoryResolver, 'resolve'],
                    ],
                    'all_products' => [
                        'type' => Type::listOf($productType),
                        'resolve' => static function ($root, $args) use ($productRepository) {
                            return $productRepository->getAllProducts();
                        },
                    ],
                    'attributes' => [
                        'type' => Type::listOf($attributeType),
                        'args' => [
                            'productId' => ['type' => Type::string()],
                        ],
                        'resolve' => static function ($root, $args) use ($productRepository) {
                            return $productRepository->getAttributesForProduct($args['productId']);
                        },
                    ],
                    'echo' => [
                        'type' => Type::string(),
                        'args' => [
                            'message' => ['type' => Type::string()],
                        ],
                        'resolve' => static fn ($rootValue, array $args): string => $rootValue['prefix'] . $args['message'],
                    ],
                ],
            ]);
        
            $mutationType = new ObjectType([
                'name' => 'Mutation',
                'fields' => [
                    'createOrder' => [
                        'type' => Type::string(), // Returns the order ID
                        'args' => [
                            'products' => ['type' => Type::listOf(Type::string())], // Product IDs
                            'totalAmount' => ['type' => Type::float()],
                            'currency' => ['type' => Type::string()],
                        ],
                        'resolve' => static function ($root, $args) use ($productRepository) {
                            // Example logic to handle order creation
                            $orderId = uniqid(); // Generate a unique order ID
                            $productRepository->createOrder($orderId, $args['products'], $args['totalAmount'], $args['currency']);
                            return $orderId;
                        },
                    ],
                    'sum' => [
                        'type' => Type::int(),
                        'args' => [
                            'x' => ['type' => Type::int()],
                            'y' => ['type' => Type::int()],
                        ],
                        'resolve' => static fn ($calc, array $args): int => $args['x'] + $args['y'],
                    ],
                ],
            ]);
        
            // See docs on schema options:
            // https://webonyx.github.io/graphql-php/schema-definition/#configuration-options
            $schema = new Schema(
                (new SchemaConfig())
                ->setQuery($queryType)
                ->setMutation($mutationType)
            );
        
            $rawInput = file_get_contents('php://input');
            if ($rawInput === false) {
                throw new RuntimeException('Failed to get php://input');
            }
        
            $input = json_decode($rawInput, true);
            $query = $input['query'];
            $variableValues = $input['variables'] ?? null;
        
            $rootValue = ['prefix' => 'You said: '];
            $result = GraphQLBase::executeQuery($schema, $query, $rootValue, null, $variableValues);
            $output = $result->toArray(DebugFlag::INCLUDE_DEBUG_MESSAGE | DebugFlag::INCLUDE_TRACE);
        } catch (Throwable $e) {
            $output = [
                'error' => [
                    'message' => $e->getMessage(),
                ],
            ];
        }

        header('Content-Type: application/json; charset=UTF-8');
        return json_encode($output);
    }
}