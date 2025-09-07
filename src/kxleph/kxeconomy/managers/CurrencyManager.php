<?php
declare(strict_types=1);

namespace kxleph\kxeconomy\managers;

use kxleph\kxeconomy\CurrencyData;

class CurrencyManager {
    
    /** @var array<string, CurrencyData> */
    private array $currencies = [];

    public function __construct() {
        $this->initializeCurrencies();
    }

    private function initializeCurrencies(): void {
        $this->currencies["gold"] = new CurrencyData("gold", "", "Gold", 1000, "§g");
        $this->currencies["gems"] = new CurrencyData("gems", "", "Gems", 0, "§a");
        $this->currencies["tokens"] = new CurrencyData("tokens", "", "Tokens", 0, "§b");
    }

    public function isValidCurrency(string $currency): bool {
        return isset($this->currencies[$currency]);
    }

    public function getCurrencies(): array {
        return array_keys($this->currencies);
    }

    public function getCurrencyData(string $currency): ?CurrencyData {
        return $this->currencies[$currency] ?? null;
    }

    public function formatCurrency(string $currency, int $amount): string {
        $data = $this->getCurrencyData($currency);
        if ($data === null) return "$" . number_format($amount);
        return $data->getSymbol() . number_format($amount);
    }

    public function getStartingAmount(string $currency): int {
        $data = $this->getCurrencyData($currency);
        return $data ? $data->getStartingAmount() : 0;
    }

    public function getCurrencyName(string $currency): string {
        $data = $this->getCurrencyData($currency);
        return $data ? $data->getName() : ucfirst($currency);
    }

    public function getCurrencySymbol(string $currency): string {
        $data = $this->getCurrencyData($currency);
        return $data ? $data->getSymbol() : "";
    }

    public function getCurrencyColor(string $currency): string {
        $data = $this->getCurrencyData($currency);
        return $data ? $data->getColor() : "§g";
    }
}