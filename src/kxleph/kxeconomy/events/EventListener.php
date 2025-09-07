<?php
declare(strict_types=1);

namespace kxleph\kxeconomy\events;

use kxleph\kxeconomy\KXEconomy;
use kxleph\kxeconomy\EconomyAccount;
use kxleph\kxeconomy\managers\EconomyManager;

use kxleph\kxeconomy\provider\MySQLProvider;

use pocketmine\player\Player;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;

use libs\SOFe\AwaitGenerator\Await;

class EventListener implements Listener {
    
    private KXEconomy $plugin;

    public function __construct(KXEconomy $plugin) {
        $this->plugin = $plugin;
    }

    public function onJoin(PlayerJoinEvent $event): void {
        $player = $event->getPlayer();
        $economy = EconomyManager::getInstance();
        
        if ($economy->getAccount($player) === null) $economy->createAccount($player);
    }

    public function onQuit(PlayerQuitEvent $event): void {
        $player = $event->getPlayer();
        $economy = EconomyManager::getInstance();
        $account = $economy->getAccount($player);
        
        if ($account !== null) {
            Await::f2c(function () use ($account) {
                yield from $this->savePlayerData($account);
            });
            
            Await::f2c(function () use ($account, $economy) {
                yield from $economy->getTransactionLogger()->logTransaction(
                    $account->getUniqueId(),
                    "LOGOUT",
                    "system",
                    0,
                    "Player logged out"
                );
            });
            
            $this->cleanupPlayerData($player);
        }
    }

    /**
     * @param EconomyAccount $account
     * @return Generator
     */
    private function savePlayerData(EconomyAccount $account): \Generator {
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
                function () use ($resolve, $account) {
                    $this->plugin->getLogger()->debug("Saved economy data for " . $account->getUsername());
                    $resolve(null);
                },
                function (\Throwable $error) use ($reject, $account) {
                    $this->plugin->getLogger()->error("Failed to save economy data for " . $account->getUsername() . ": " . $error->getMessage());
                    $reject($error);
                }
            );
        });
    }

    /**
     * @param Player $player
     */
    private function cleanupPlayerData(Player $player): void { // will implement soon
        $uniqueid = $player->getUniqueId()->toString();
        $this->plugin->getLogger()->debug("Cleaned up memory data for " . $player->getName());
    }
}