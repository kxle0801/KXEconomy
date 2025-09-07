<?php
declare(strict_types=1);

namespace kxleph\kxeconomy\commands\subcommands;

use kxleph\kxeconomy\KXEconomy;
use kxleph\kxeconomy\managers\EconomyManager;

use kxleph\elysium\commands\utils\PermissionUtils;

use pocketmine\plugin\Plugin;

use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

use libs\CortexPE\Commando\BaseSubCommand;
use libs\CortexPE\Commando\args\RawStringArgument;
use libs\CortexPE\Commando\args\IntegerArgument;

class PaySubCommand extends BaseSubCommand {

    public function __construct(private Plugin $plugin) {
        parent::__construct("pay", "Pay money to another player");
        $this->plugin = $plugin;
    }

    public function prepare(): void {
        $this->registerArgument(0, new RawStringArgument("player", false));
        $this->registerArgument(1, new RawStringArgument("currency", false));
        $this->registerArgument(2, new IntegerArgument("amount", false));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
        if (!$this->plugin instanceof KXEconomy) return;
        
        if (!$sender instanceof Player) {
            $sender->sendMessage(TextFormat::RED . "This command can only be used by players!");
            return;
        }

        $targetName = $args["player"];
        $currency = strtolower($args["currency"]);
        $amount = $args["amount"];

        if ($amount <= 0) {
            $sender->sendMessage(TextFormat::RED . "Amount must be greater than 0!");
            return;
        }

        $target = $this->plugin->getServer()->getPlayerExact($targetName);
        if ($target === null) {
            $sender->sendMessage(TextFormat::RED . "Player not found!");
            return;
        }

        if ($target === $sender) {
            $sender->sendMessage(TextFormat::RED . "You cannot pay yourself!");
            return;
        }

        $economy = EconomyManager::getInstance();
        $currencyManager = $economy->getCurrencyManager();

        if (!$currencyManager->isValidCurrency($currency)) {
            $sender->sendMessage(TextFormat::RED . "Invalid currency! Available: " . implode(", ", $currencyManager->getCurrencies()));
            return;
        }

        if (!$economy->hasEnoughMoney($sender, $currency, $amount)) {
            $sender->sendMessage(TextFormat::RED . "You don't have enough " . $currency . "!");
            return;
        }
        
        $economy->transferMoney($sender, $target, $currency, $amount, "Player payment");
        
        $formattedAmount = $currencyManager->formatCurrency($currency, $amount);
        $sender->sendMessage(TextFormat::GREEN . "You paid " . $formattedAmount . " to " . $target->getName());
        $target->sendMessage(TextFormat::GREEN . "You received " . $formattedAmount . " from " . $sender->getName());
    }
}