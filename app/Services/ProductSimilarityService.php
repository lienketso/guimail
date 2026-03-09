<?php

namespace App\Services;

class ProductSimilarityService
{
    public function checkDuplicate($name, $existingProducts, $threshold = 99, $taxCode = null)
    {
        foreach ($existingProducts as $product) {

            $productName = $product;
            $productTaxCode = null;

            if (is_array($product)) {
                $productName = $product['product_name'] ?? ($product['name'] ?? $productName);
                $productTaxCode = $product['tax_code'] ?? null;
            } elseif (is_object($product)) {
                $productName = $product->product_name ?? $product->name ?? $productName;
                $productTaxCode = $product->tax_code ?? null;
            }

            if ($taxCode !== null && $productTaxCode !== null && $productTaxCode !== $taxCode) {
                continue;
            }

            similar_text(
                mb_strtolower($name),
                mb_strtolower($productName),
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
    public function findDuplicateProduct($name, $products, $threshold = 99, $taxCode = null)
    {
        foreach ($products as $product) {

            if ($taxCode !== null && isset($product->tax_code) && $product->tax_code !== $taxCode) {
                continue;
            }

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
