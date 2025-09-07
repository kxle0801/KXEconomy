<?php
declare(strict_types=1);

namespace kxleph\kxeconomy\events;

use kxleph\kxeconomy\EconomyAccount;
use pocketmine\event\Event;

class EconomyTransactionEvent extends Event {
    private bool $cancelled = false;

    public function __construct(
        private EconomyAccount $account, 
        private string $type, 
        private string $currency, 
        private int $amount, 
        private string $reason
        ) {
        $this->account = $account;
        $this->type = $type;
        $this->currency = $currency;
        $this->amount = $amount;
        $this->reason = $reason;
    }

    public function getAccount(): EconomyAccount {
        return $this->account;
    }

    public function getType(): string {
        return $this->type;
    }

    public function getCurrency(): string {
        return $this->currency;
    }

    public function getAmount(): int {
        return $this->amount;
    }

    public function setAmount(int $amount): void {
        $this->amount = $amount;
    }

    public function getReason(): string {
        return $this->reason;
    }

    public function setReason(string $reason): void {
        $this->reason = $reason;
    }

    public function isCancelled(): bool {
        return $this->cancelled;
    }

    public function setCancelled($cancelled = true): void {
        $this->cancelled = $cancelled;
    }
}