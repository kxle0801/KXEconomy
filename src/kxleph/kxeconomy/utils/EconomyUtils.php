<?php
declare(strict_types=1);

namespace kxleph\kxeconomy\utils;

use kxleph\kxeconomy\managers\EconomyManager;

use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class EconomyUtils {
    
    public static function formatBalance(Player $player): string {
        $economy = EconomyManager::getInstance();
        $account = $economy->getAccount($player);
        
        if ($account === null) return TextFormat::RED . "No account found";
        
        $currencyManager = $economy->getCurrencyManager();
        $balances = $account->getAllBalances();
        
        $formatted = [];
        foreach ($balances as $currency => $amount) $formatted[] = $currencyManager->formatCurrency($currency, $amount);
        
        return implode(" | ", $formatted);
    }
    
    public static function sendBalanceMessage(Player $player): void {
        $balance = self::formatBalance($player);
        $player->sendMessage(TextFormat::GOLD . "Balance: " . TextFormat::WHITE . $balance);
    }
    
    public static function createTopList(string $currency, int $limit = 10): array {
        $economy = EconomyManager::getInstance();
        $topPlayers = $economy->getTopPlayers($currency, $limit);
        $currencyManager = $economy->getCurrencyManager();
        
        $list = [];
        $position = 1;
        
        foreach ($topPlayers as $account) {
            $list[] = [
                'position' => $position,
                'username' => $account->getUsername(),
                'amount' => $account->getCurrency($currency),
                'formatted' => $currencyManager->formatCurrency($currency, $account->getCurrency($currency))
            ];
            $position++;
        }
        
        return $list;
    }
    
    public static function isValidAmount(int $amount): bool {
        return $amount > 0 && $amount <= 1000000000; // 1 billion max for now ;)
    }
    
    public static function parseAmount(string $input): int {
        $input = strtolower(trim($input)); // Handle suffixes like 1k, 1m, 1b
        
        if (preg_match('/^(\d+(?:\.\d+)?)([kmb])$/', $input, $matches)) {
            $number = (float) $matches[1];
            $suffix = $matches[2];
            
            switch ($suffix) {
                case 'k':
                    return (int) ($number * 1000);
                case 'm':
                    return (int) ($number * 1000000);
                case 'b':
                    return (int) ($number * 1000000000);
            }
        }
        
        return (int) $input;
    }
}