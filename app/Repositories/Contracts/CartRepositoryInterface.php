<?php

namespace App\Repositories\Contracts;

interface CartRepositoryInterface
{   
    /**
     * Get all items in cart.
     *
     * @return void
     */
    public function getCartItems();
    /**
     * Add an item to the cart.
     *
     * @param int $id
     * @param string $name
     * @param int $quantity
     * @param float $price
     * @return void
     */
    public function addToCart(int $id, string $name, int $quantity, float $price): void;

    /**
     * Adjust the quantity of a cart item.
     *
     * @param string $rowId
     * @param string $action
     * @return void
     */
    public function adjustQuantity(string $rowId, string $action): void;

    /**
     * Remove an item from the cart.
     *
     * @param string $rowId
     * @return void
     */
    public function removeItem(string $rowId): void;

    /**
     * Empty all items from the cart.
     *
     * @return void
     */
    public function emptyCart(): void;

    /**
     * Get the subtotal of the cart.
     *
     * @return float
     */
    public function getCartSubtotal();

    /**
     * Get the tax of the cart.
     *
     * @return float
     */
    public function getCartTax();

     /**
     * Get the total of the cart.
     *
     * @return float
     */
    public function getCartTotal();
}
