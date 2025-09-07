<?php
declare(strict_types=1);

namespace kxleph\kxeconomy\commands\subcommands;

use kxleph\kxeconomy\KXEconomy;
use kxleph\kxeconomy\managers\EconomyManager;

use kxleph\elysium\commands\utils\PermissionUtils;

use libs\CortexPE\Commando\BaseSubCommand;
use libs\CortexPE\Commando\args\RawStringArgument;

use pocketmine\plugin\Plugin;

use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class BalanceSubCommand extends BaseSubCommand {

    public function __construct(private Plugin $plugin) {
        parent::__construct("balance", "Check your balance", ["bal", "money"]);
        $this->plugin = $plugin;
    }

    public function prepare(): void {
        $this->registerArgument(0, new RawStringArgument("player", true));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
        if (!$this->plugin instanceof KXEconomy) return;
        
        if (!$sender instanceof Player && !isset($args["player"])) {
            $sender->sendMessage(TextFormat::RED . "Please specify a player name!");
            return;
        }

        $targetPlayer = !isset($args["player"]) ? $sender : $this->plugin->getServer()->getPlayerExact($args["player"]);
        
        if ($targetPlayer === null) {
            $sender->sendMessage(TextFormat::RED . "Player not found!");
            return;
        }

        if ($targetPlayer !== $sender && !$sender->hasPermission("economy.admin")) {
            $sender->sendMessage(TextFormat::RED . "You don't have permission to check other players' balances!");
            return;
        }

        $economy = EconomyManager::getInstance();
        $account = $economy->getAccount($targetPlayer);
        
        if ($account === null) {
            $sender->sendMessage(TextFormat::RED . "Economy account not found!");
            return;
        }

        $currencyManager = $economy->getCurrencyManager();
        $balances = $account->getAllBalances();
        
        $message = TextFormat::GOLD . "=== " . $targetPlayer->getName() . "'s Balance ===\n";
        foreach ($balances as $currency => $amount) {
            $message .= TextFormat::YELLOW . ucfirst($currency) . ": " . TextFormat::WHITE . $currencyManager->formatCurrency($currency, $amount) . "\n";
        }
        $sender->sendMessage($message);
    }
}