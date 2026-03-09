<?php

namespace App\Services;

class ProductSimilarityService
{
    public function checkDuplicate($name, $existingProducts, $threshold = 85)
    {
        foreach ($existingProducts as $product) {

            similar_text(
                mb_strtolower($name),
                mb_strtolower($product),
                $percent
            );

            if ($percent >= $threshold) {
                return [
                    'duplicate' => true,
                    'matched_product' => $product,
                    'similarity' => round($percent, 2)
                ];
            }
        }

        return [
            'duplicate' => false
        ];
    }
    public function findDuplicateProduct($name, $products, $threshold = 85)
    {
        foreach ($products as $product) {

            similar_text(
                mb_strtolower($name),
                mb_strtolower($product->product_name),
                $percent
            );

            if ($percent >= $threshold) {
                return [
                    'matched' => true,
                    'product' => $product,
                    'similarity' => $percent
                ];
            }
        }

        return [
            'matched' => false
        ];
    }
}
