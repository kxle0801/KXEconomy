<?php
declare(strict_types=1);

namespace kxleph\kxeconomy\managers;

use kxleph\kxeconomy\EconomyAccount;
use kxleph\kxeconomy\transactions\TransactionLogs;
use kxleph\kxeconomy\managers\CurrencyManager;

use kxleph\kxeconomy\provider\MySQLProvider;

use pocketmine\player\Player;
use pocketmine\utils\SingletonTrait;
use libs\SOFe\AwaitGenerator\Await;
use Generator;
use Throwable;

class EconomyManager {
    
    use SingletonTrait;

    /** @var EconomyAccount[] */
    private static array $accounts = [];

    private TransactionLogs $transactionLogger;
    private CurrencyManager $currencyManager;

    public function __construct() {
        $this->transactionLogger = new TransactionLogs();
        $this->currencyManager = new CurrencyManager();
    }

    public static function init(): void {
        self::setInstance(new self());
        Await::f2c(function () {
            yield from self::getInstance()->loadAccountsAsync();
        });
    }

    private function loadAccountsAsync(): Generator {
        return yield from Await::promise(function ($resolve, $reject) {
            MySQLProvider::getInstance()->get()->executeSelect(
                "economy.all", [],
                function (array $rows) use ($resolve) {
                    foreach ($rows as $row) {
                        self::$accounts[$row["uniqueid"]] = new EconomyAccount(
                            $row['uniqueid'],
                            $row['username'],
                            (int) $row['gold'],
                            (int) $row['gems'],
                            (int) $row['tokens']
                        );
                    }
                    $resolve(null);
                },
                function (Throwable $error) use ($reject): void {
                    $reject($error);
                }
            );
        });
    }

    public function createAccount(Player $player): void {
        Await::f2c(function () use ($player) {
            yield from $this->createAccountAsync($player);
        });
    }

    private function createAccountAsync(Player $player): Generator {
        $uniqueid = $player->getUniqueId()->toString();
        $username = $player->getName();

        if ($this->getAccount($player) === null) {
            yield from Await::promise(function ($resolve, $reject) use ($uniqueid, $username) {
                MySQLProvider::getInstance()->get()->executeInsert("economy.create", [
                        "uniqueid" => $uniqueid,
                        "username" => $username,
                        "gold" => $this->currencyManager->getStartingAmount("gold"),
                        "gems" => $this->currencyManager->getStartingAmount("gems"),
                        "tokens" => $this->currencyManager->getStartingAmount("tokens")
                    ],
                    function () use ($resolve, $uniqueid, $username) {
                        self::$accounts[$uniqueid] = new EconomyAccount(
                            $uniqueid,
                            $username,
                            $this->currencyManager->getStartingAmount("gold"),
                            $this->currencyManager->getStartingAmount("gems"),
                            $this->currencyManager->getStartingAmount("tokens")
                        );
                        $resolve(null);
                    },
                    function (Throwable $error) use ($reject) {
                        $reject($error);
                    }
                );
            });
        }
    }

    public function getAccount(Player $player): ?EconomyAccount {
        return $this->getAccountById($player->getUniqueId()->toString());
    }

    public function getAccountById(string $uniqueid): ?EconomyAccount {
        return self::$accounts[$uniqueid] ?? null;
    }

    public function addMoney(Player $player, string $currency, int $amount, string $reason = "Unknown"): void {
        if (!$this->currencyManager->isValidCurrency($currency) || $amount <= 0) return;
        
        Await::f2c(function () use ($player, $currency, $amount, $reason) {
            $account = $this->getAccount($player);
            if ($account !== null) {
                $account->addCurrency($currency, $amount);
                yield from $this->updateAccountAsync($account);
                yield from $this->transactionLogger->logTransaction(
                    $account->getUniqueId(), "ADD", $currency, $amount, $reason
                );
            }
        });
    }

    public function removeMoney(Player $player, string $currency, int $amount, string $reason = "Unknown"): bool {
        if (!$this->currencyManager->isValidCurrency($currency) || $amount <= 0) return false;
        
        $account = $this->getAccount($player);
        if ($account === null || !$account->hasCurrency($currency, $amount)) {
            return false;
        }

        Await::f2c(function () use ($account, $currency, $amount, $reason) {
            $account->removeCurrency($currency, $amount);
            yield from $this->updateAccountAsync($account);
            yield from $this->transactionLogger->logTransaction(
                $account->getUniqueId(), "REMOVE", $currency, $amount, $reason
            );
        });

        return true;
    }

    public function transferMoney(Player $from, Player $to, string $currency, int $amount, string $reason = "Transfer"): bool {
        if (!$this->currencyManager->isValidCurrency($currency) || $amount <= 0) return false;
        
        $fromAccount = $this->getAccount($from);
        $toAccount = $this->getAccount($to);
        
        if ($fromAccount === null || $toAccount === null || !$fromAccount->hasCurrency($currency, $amount)) {
            return false;
        }

        Await::f2c(function () use ($fromAccount, $toAccount, $currency, $amount, $reason) {
            $fromAccount->removeCurrency($currency, $amount);
            $toAccount->addCurrency($currency, $amount);
            
            yield from $this->updateAccountAsync($fromAccount);
            yield from $this->updateAccountAsync($toAccount);
            
            yield from $this->transactionLogger->logTransaction(
                $fromAccount->getUniqueId(), "TRANSFER_OUT", $currency, $amount, 
                $reason . " to " . $toAccount->getUsername()
            );
            yield from $this->transactionLogger->logTransaction(
                $toAccount->getUniqueId(), "TRANSFER_IN", $currency, $amount, 
                $reason . " from " . $fromAccount->getUsername()
            );
        });

        return true;
    }

    public function getBalance(Player $player, string $currency): int {
        $account = $this->getAccount($player);
        return $account ? $account->getCurrency($currency) : 0;
    }

    public function hasEnoughMoney(Player $player, string $currency, int $amount): bool {
        $account = $this->getAccount($player);
        return $account ? $account->hasCurrency($currency, $amount) : false;
    }

    public function getTopPlayers(string $currency, int $limit = 10): array {
        if (!$this->currencyManager->isValidCurrency($currency)) return [];
        
        $accounts = self::$accounts;
        
        usort($accounts, function(EconomyAccount $a, EconomyAccount $b) use ($currency) {
            return $b->getCurrency($currency) <=> $a->getCurrency($currency);
        });
        
        return array_slice($accounts, 0, $limit);
    }

    public function getCurrencyManager(): CurrencyManager {
        return $this->currencyManager;
    }

    public function getTransactionLogger(): TransactionLogs {
        return $this->transactionLogger;
    }

    private function updateAccountAsync(EconomyAccount $account): Generator {
        return yield from Await::promise(function ($resolve, $reject) use ($account) {
            MySQLProvider::getInstance()->get()->executeChange(
                "economy.update",
                [
                    "uniqueid" => $account->getUniqueId(),
                    "username" => $account->getUsername(),
                    "gold" => $account->getGold(),
                    "gems" => $account->getGems(),
                    "tokens" => $account->getTokens()
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
}