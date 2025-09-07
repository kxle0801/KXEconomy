<?php
declare(strict_types=1);

namespace kxleph\kxeconomy\transactions;

use kxleph\kxeconomy\provider\MySQLProvider;
use libs\SOFe\AwaitGenerator\Await;

use Generator;
use Throwable;

class TransactionLogs {

    public function logTransaction(string $uniqueid, string $type, string $currency, int $amount, string $reason): Generator {
        return yield from Await::promise(function ($resolve, $reject) use ($uniqueid, $type, $currency, $amount, $reason) {
            MySQLProvider::getInstance()->get()->executeInsert(
                "economy.log",
                [
                    "uniqueid" => $uniqueid,
                    "type" => $type,
                    "currency" => $currency,
                    "amount" => $amount,
                    "reason" => $reason,
                    "timestamp" => time()
                ],
                function () use ($resolve) {
                    $resolve(null);
                },
                function (Throwable $error) use ($reject) {
                    $reject($error);
                }
            );
        });
    }

    public function getPlayerTransactions(string $uniqueid, int $limit = 50): Generator {
        return yield from Await::promise(function ($resolve, $reject) use ($uniqueid, $limit) {
            MySQLProvider::getInstance()->get()->executeSelect(
                "economy.transactions.player",
                ["uniqueid" => $uniqueid, "limit" => $limit],
                function (array $rows) use ($resolve) {
                    $transactions = [];
                    foreach ($rows as $row) {
                        $transactions[] = new Transaction(
                            (int) $row['id'],
                            $row['uniqueid'],
                            $row['type'],
                            $row['currency'],
                            (int) $row['amount'],
                            $row['reason'],
                            (int) $row['timestamp']
                        );
                    }
                    $resolve($transactions);
                },
                function (Throwable $error) use ($reject) {
                    $reject($error);
                }
            );
        });
    }
}