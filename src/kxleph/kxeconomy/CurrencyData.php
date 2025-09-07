<?php
declare(strict_types=1);

namespace kxleph\kxeconomy;

class CurrencyData {

    public function __construct(
        private string $id, 
        private string $symbol, 
        private string $name, 
        private int $startingAmount, 
        private string $color
        ) {
        $this->id = $id;
        $this->symbol = $symbol;
        $this->name = $name;
        $this->startingAmount = $startingAmount;
        $this->color = $color;
    }

    public function getId(): string {
        return $this->id;
    }

    public function getSymbol(): string {
        return $this->symbol;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getStartingAmount(): int {
        return $this->startingAmount;
    }

    public function getColor(): string {
        return $this->color;
    }
}