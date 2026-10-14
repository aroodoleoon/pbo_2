<?php
declare(strict_types=1);

namespace PosClient\Models;

class Customer
{
    private string $id;
    private string $name;
    private string $contact;
    private bool $isMember;
    private ?string $memberNumber;
    private int $points;
    private string $tier; // REGULER, SILVER, GOLD, PLATINUM

    public function __construct(array $data)
    {
        $this->id = (string)($data['id'] ?? '');
        $this->name = (string)($data['name'] ?? '');
        $this->contact = (string)($data['contact'] ?? '');
        $this->isMember = (bool)($data['isMember'] ?? false);
        $this->memberNumber = isset($data['memberNumber']) ? (string)$data['memberNumber'] : null;
        $this->points = (int)($data['points'] ?? 0);
        $this->tier = strtoupper((string)($data['tier'] ?? 'REGULER'));
    }

    public function getId(): string { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getContact(): string { return $this->contact; }
    public function isMember(): bool { return $this->isMember; }
    public function getMemberNumber(): ?string { return $this->memberNumber; }
    public function getPoints(): int { return $this->points; }
    public function getTier(): string { return $this->tier; }

    public function getDiscountPercent(): float
    {
        if (!$this->isMember) {
            return 0.0;
        }

        return match ($this->tier) {
            'PLATINUM' => 0.12,
            'GOLD' => 0.10,
            'SILVER' => 0.08,
            default => 0.05, // REGULER
        };
    }

    public function getDiscountLabel(): string
    {
        if (!$this->isMember) {
            return 'Non-Member (0%)';
        }
        $pct = (int)($this->getDiscountPercent() * 100);
        return "Member {$this->tier} ({$pct}%)";
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'contact' => $this->contact,
            'isMember' => $this->isMember,
            'memberNumber' => $this->memberNumber,
            'points' => $this->points,
            'tier' => $this->tier,
            'discountPercent' => $this->getDiscountPercent(),
            'discountLabel' => $this->getDiscountLabel(),
        ];
    }
}

