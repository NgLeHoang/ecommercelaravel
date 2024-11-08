<?php

namespace App\Repositories\Contracts;

use Illuminate\Http\Request;

interface ProductRepositoryInterface
{
    public function getAll();
    public function find($id);
    public function getProductsForShop(Request $request);
    public function getRelatedProducts($product_slug);
    public function getProductBySlug($product_slug);
    // public function create(array $data);
    // public function update($id, array $data);
    // public function delete($id);
}
