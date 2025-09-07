<?php
declare(strict_types=1);

namespace kxleph\kxeconomy;

class EconomyAccount {

    public function __construct(
        private string $uniqueid,
        private string $username,
        private int $gold,
        private int $gems,
        private int $tokens
        ) {
        $this->uniqueid = $uniqueid;
        $this->username = $username;
        $this->gold = max(0, $gold);
        $this->gems = max(0, $gems);
        $this->tokens = max(0, $tokens);
    }

    public function getUniqueId(): string {
        return $this->uniqueid;
    }

    public function getUsername(): string {
        return $this->username;
    }

    public function setUsername(string $username): void {
        $this->username = $username;
    }

    public function getGold(): int {
        return $this->gold;
    }

    public function getGems(): int {
        return $this->gems;
    }

    public function getTokens(): int {
        return $this->tokens;
    }

    public function getCurrency(string $currency): int {
        return match($currency) {
            "gold" => $this->gold,
            "gems" => $this->gems,
            "tokens" => $this->tokens,
            default => 0
        };
    }

    public function addCurrency(string $currency, int $amount): void {
        if ($amount <= 0) return;
        
        match($currency) {
            "gold" => $this->gold += $amount,
            "gems" => $this->gems += $amount,
            "tokens" => $this->tokens += $amount
        };
    }

    public function removeCurrency(string $currency, int $amount): bool {
        if ($amount <= 0 || !$this->hasCurrency($currency, $amount)) return false;
        
        match($currency) {
            "gold" => $this->gold -= $amount,
            "gems" => $this->gems -= $amount,
            "tokens" => $this->tokens -= $amount
        };
        
        return true;
    }

    public function hasCurrency(string $currency, int $amount): bool {
        return $this->getCurrency($currency) >= $amount;
    }

    public function setCurrency(string $currency, int $amount): void {
        $amount = max(0, $amount);
        
        match($currency) {
            "gold" => $this->gold = $amount,
            "gems" => $this->gems = $amount,
            "tokens" => $this->tokens = $amount
        };
    }

    public function getAllBalances(): array {
        return [
            "gold" => $this->gold,
            "gems" => $this->gems,
            "tokens" => $this->tokens
        ];
    }

    public function getTotalWorth(): int {
        return $this->gold + ($this->gems * 10) + ($this->tokens * 5);
    }
}