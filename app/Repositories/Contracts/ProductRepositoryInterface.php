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

    /**
     * Retrieve a random selection of sale products.
     *
     * @return \Illuminate\Database\Eloquent\Collection 
     */
    public function getSaleProducts();

    /**
     * Retrieve a selection of featured products.
     *
     * @return \Illuminate\Database\Eloquent\Collection 
     */
    public function getFeaturedProducts();

    /**
     * Store a new product in the database.
     *
     * @param array $data
     * @return \App\Models\Product
     */
    public function storeProduct(array $data);

    /**
     * Update an existing product.
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateProduct(int $id, array $data): bool;

    /**
     * Delete a product by ID.
     *
     * @param int $id
     * @return bool
     */
    public function deleteProduct(int $id): bool;

    /**
     * Save the brand image to the 'products' folder using the ImageUploadTrait.
     *
     * @param \Illuminate\Http\UploadedFile $image
     * @return string
     */
    public function saveProductImage($image): string;

    /**
     * Save gallery images for a product to the specified folder and return their filenames as a comma-separated string.
     *
     * @param array $images 
     * @return string 
     * @throws \Exception
     */
    public function saveGalleryImages($images);
}
