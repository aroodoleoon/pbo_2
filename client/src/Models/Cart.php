<?php
declare(strict_types=1);

namespace PosClient\Models;

use PosClient\Config;
use PosClient\Session;

class Cart
{
    private const SESSION_KEY = 'pos_cart_items';
    private const CUSTOMER_KEY = 'pos_cart_customer';

    public static function getItems(): array
    {
        return Session::get(self::SESSION_KEY, []);
    }

    public static function getCustomer(): ?array
    {
        return Session::get(self::CUSTOMER_KEY, null);
    }

    public static function setCustomer(?array $customer): void
    {
        Session::set(self::CUSTOMER_KEY, $customer);
    }

    public static function addItem(Product $product, int $quantity = 1): void
    {
        $items = self::getItems();
        $productId = $product->getId();

        if (isset($items[$productId])) {
            $items[$productId]['quantity'] += $quantity;
            $items[$productId]['subtotal'] = $items[$productId]['quantity'] * $items[$productId]['sellPrice'];
        } else {
            $items[$productId] = [
                'id' => $product->getId(),
                'barcode' => $product->getBarcode(),
                'name' => $product->getName(),
                'sellPrice' => $product->getSellPrice(),
                'quantity' => $quantity,
                'subtotal' => $quantity * $product->getSellPrice(),
                'stock' => $product->getStock(),
            ];
        }

        Session::set(self::SESSION_KEY, $items);
    }

    public static function updateQuantity(string $productId, int $quantity): void
    {
        $items = self::getItems();
        if ($quantity <= 0) {
            unset($items[$productId]);
        } elseif (isset($items[$productId])) {
            $items[$productId]['quantity'] = $quantity;
            $items[$productId]['subtotal'] = $quantity * $items[$productId]['sellPrice'];
        }
        Session::set(self::SESSION_KEY, $items);
    }

    public static function removeItem(string $productId): void
    {
        $items = self::getItems();
        unset($items[$productId]);
        Session::set(self::SESSION_KEY, $items);
    }

    public static function clear(): void
    {
        Session::remove(self::SESSION_KEY);
        Session::remove(self::CUSTOMER_KEY);
    }

    public static function calculateSummary(): array
    {
        $items = self::getItems();
        $customerData = self::getCustomer();
        $customer = $customerData ? new Customer($customerData) : null;

        $totalItemCount = 0;
        $subtotal = 0;

        foreach ($items as $item) {
            $totalItemCount += $item['quantity'];
            $subtotal += $item['subtotal'];
        }

        $discountPercent = $customer ? $customer->getDiscountPercent() : 0.0;
        $discountAmount = (int) round($subtotal * $discountPercent);
        $taxableAmount = max(0, $subtotal - $discountAmount);
        $taxAmount = (int) round($taxableAmount * Config::TAX_RATE);
        $total = $taxableAmount + $taxAmount;

        return [
            'itemCount' => $totalItemCount,
            'subtotal' => $subtotal,
            'discountPercent' => $discountPercent,
            'discountAmount' => $discountAmount,
            'discountLabel' => $customer ? $customer->getDiscountLabel() : 'Non-Member',
            'taxAmount' => $taxAmount,
            'taxPercent' => (int)(Config::TAX_RATE * 100),
            'total' => $total,
            'customer' => $customer ? $customer->toArray() : null,
            'items' => array_values($items),
        ];
    }
}
