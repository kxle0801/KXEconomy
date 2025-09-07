<?php
declare(strict_types=1);

namespace kxleph\kxeconomy\commands\subcommands;

use kxleph\kxeconomy\KXEconomy;
use kxleph\kxeconomy\managers\EconomyManager;

use kxleph\elysium\commands\utils\PermissionUtils;

use pocketmine\plugin\Plugin;

use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

use libs\CortexPE\Commando\BaseSubCommand;
use libs\CortexPE\Commando\args\RawStringArgument;
use libs\CortexPE\Commando\args\IntegerArgument;

class AdminSubCommand extends BaseSubCommand {

    public function __construct(private Plugin $plugin) {
        parent::__construct("admin", "Admin commands for economy", ["manage"]);
        $this->plugin = $plugin;
    }

    public function prepare(): void {
        $this->registerArgument(0, new RawStringArgument("action", false));
        $this->registerArgument(1, new RawStringArgument("player", false));
        $this->registerArgument(2, new RawStringArgument("currency", false));
        $this->registerArgument(3, new IntegerArgument("amount", false));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
        $action = strtolower($args["action"]);
        $playerName = $args["player"];
        $currency = strtolower($args["currency"]);
        $amount = $args["amount"];

        if (!$this->plugin instanceof KXEconomy) return;

        $player = $this->plugin->getServer()->getPlayerExact($playerName);
        if ($player === null) {
            $sender->sendMessage(TextFormat::RED . "Player not found!");
            return;
        }

        $economy = EconomyManager::getInstance();
        $currencyManager = $economy->getCurrencyManager();

        if (!$currencyManager->isValidCurrency($currency)) {
            $sender->sendMessage(TextFormat::RED . "Invalid currency! Available: " . implode(", ", $currencyManager->getCurrencies()));
            return;
        }

        switch ($action) {
            case "add":
                $economy->addMoney($player, $currency, $amount, "Admin command by " . $sender->getName());
                $sender->sendMessage(TextFormat::GREEN . "Added " . $currencyManager->formatCurrency($currency, $amount) . " to " . $player->getName());
                break;

            case "remove":
                if ($economy->removeMoney($player, $currency, $amount, "Admin command by " . $sender->getName())) {
                    $sender->sendMessage(TextFormat::GREEN . "Removed " . $currencyManager->formatCurrency($currency, $amount) . " from " . $player->getName());
                } else {
                    $sender->sendMessage(TextFormat::RED . "Player doesn't have enough " . $currency . "!");
                }
                break;

            case "set":
                $economy->addMoney($player, $currency, $amount, "Admin command by " . $sender->getName());
                $sender->sendMessage(TextFormat::GREEN . "Set " . $player->getName() . "'s " . $currency . " to " . $currencyManager->formatCurrency($currency, $amount));
                break;
                
            default:
                $sender->sendMessage(TextFormat::RED . "Invalid action! Use: add, remove, or set");
                break;
        }
    }
}