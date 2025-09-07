<?php
declare(strict_types=1);

namespace kxleph\kxeconomy\transactions;

class Transaction {

    public function __construct(
        private int $id, 
        private string $uniqueid, 
        private string $type, 
        private string $currency, 
        private int $amount, 
        private string $reason, 
        private int $timestamp
        ) {
        $this->id = $id;
        $this->uniqueid = $uniqueid;
        $this->type = $type;
        $this->currency = $currency;
        $this->amount = $amount;
        $this->reason = $reason;
        $this->timestamp = $timestamp;
    }

    public function getId(): int {
        return $this->id;
    }

    public function getUniqueId(): string {
        return $this->uniqueid;
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

    public function getReason(): string {
        return $this->reason;
    }

    public function getTimestamp(): int {
        return $this->timestamp;
    }

    public function getFormattedDate(): string {
        return date('Y-m-d H:i:s', $this->timestamp);
    }

    public function isPositive(): bool {
        return in_array($this->type, ['ADD', 'TRANSFER_IN']);
    }

    public function isNegative(): bool {
        return in_array($this->type, ['REMOVE', 'TRANSFER_OUT']);
    }
}