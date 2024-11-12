<?php

namespace App\Repositories\Contracts;

use Illuminate\Http\Request;

interface ProductRepositoryInterface
{
    /**
     * Get all product items, paginated.
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getAll();

    /**
     * Find a record item by its ID.
     *
     * @param int $id
     * @return \App\Models\Product|null
     */
    public function find($id);

    /**
     * Get products for the shop with filters, sorting, and pagination.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function getProductsForShop(Request $request);

    /**
     * Get a product by its slug.
     *
     * @param string $product_slug
     * @return \App\Models\Product|null
     */
    public function getRelatedProducts($product_slug);

    /**
     * Get related products, excluding the current product.
     *
     * @param string $product_slug
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getProductBySlug($product_slug);
}
