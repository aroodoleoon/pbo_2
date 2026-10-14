<?php
declare(strict_types=1);

namespace PosClient\Models;

class Product
{
    private string $id;
    private string $barcode;
    private string $name;
    private ?string $categoryId;
    private int $buyPrice;
    private int $sellPrice;
    private int $stock;
    private int $minStock;
    private bool $active;

    public function __construct(array $data)
    {
        $this->id = (string)($data['id'] ?? '');
        $this->barcode = (string)($data['barcode'] ?? '');
        $this->name = (string)($data['name'] ?? '');
        $this->categoryId = isset($data['categoryId']) ? (string)$data['categoryId'] : null;
        $this->buyPrice = (int)($data['buyPrice'] ?? 0);
        $this->sellPrice = (int)($data['sellPrice'] ?? 0);
        $this->stock = (int)($data['stock'] ?? 0);
        $this->minStock = (int)($data['minStock'] ?? 5);
        $this->active = (bool)($data['active'] ?? true);
    }

    public function getId(): string { return $this->id; }
    public function getBarcode(): string { return $this->barcode; }
    public function getName(): string { return $this->name; }
    public function getCategoryId(): ?string { return $this->categoryId; }
    public function getBuyPrice(): int { return $this->buyPrice; }
    public function getSellPrice(): int { return $this->sellPrice; }
    public function getStock(): int { return $this->stock; }
    public function getMinStock(): int { return $this->minStock; }
    public function isActive(): bool { return $this->active; }

    public function isLowStock(): bool
    {
        return $this->stock <= $this->minStock;
    }

    public function isOutOfStock(): bool
    {
        return $this->stock <= 0;
    }

    public function getFormattedSellPrice(): string
    {
        return 'Rp ' . number_format($this->sellPrice, 0, ',', '.');
    }

    public function getFormattedBuyPrice(): string
    {
        return 'Rp ' . number_format($this->buyPrice, 0, ',', '.');
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'barcode' => $this->barcode,
            'name' => $this->name,
            'categoryId' => $this->categoryId,
            'buyPrice' => $this->buyPrice,
            'sellPrice' => $this->sellPrice,
            'stock' => $this->stock,
            'minStock' => $this->minStock,
            'active' => $this->active,
            'formattedSellPrice' => $this->getFormattedSellPrice(),
            'isLowStock' => $this->isLowStock(),
        ];
    }
}
