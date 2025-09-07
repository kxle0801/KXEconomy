<?php
declare(strict_types=1);

namespace kxleph\kxeconomy\commands;

use kxleph\kxeconomy\KXEconomy;
use kxleph\kxeconomy\commands\subcommands\BalanceSubCommand;
use kxleph\kxeconomy\commands\subcommands\PaySubCommand;
use kxleph\kxeconomy\commands\subcommands\AdminSubCommand;

use libs\CortexPE\Commando\BaseCommand;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class EconomyCommand extends BaseCommand {

    public function __construct(KXEconomy $plugin) {
        parent::__construct($plugin, "economy", "Economy management commands", ["eco", "money"]);
    }

    /**
	 * @return void
	 */
	public function getPermission(): void {}

    public function prepare(): void {
        $this->setPermission("kxeconomy.use.command");
        $this->registerSubCommand(new BalanceSubCommand($this->getOwningPlugin()));
        $this->registerSubCommand(new PaySubCommand($this->getOwningPlugin()));
        $this->registerSubCommand(new AdminSubCommand($this->getOwningPlugin()));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
        $sender->sendMessage(TextFormat::YELLOW . "Usage: /economy <balance|pay|admin>");
    }
}