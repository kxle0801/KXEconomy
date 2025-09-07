<?php
declare(strict_types=1);

namespace kxleph\kxeconomy;

use kxleph\kxeconomy\managers\EconomyManager;

use kxleph\kxeconomy\commands\EconomyCommand;

use kxleph\kxeconomy\events\EventListener;

use kxleph\kxeconomy\provider\MySQLProvider;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\SingletonTrait;

use libs\CortexPE\Commando\PacketHooker;

class KXEconomy extends PluginBase {

    use SingletonTrait;
    
    public function onLoad(): void {
        self::setInstance($this);
    }

    public function onEnable(): void {
        $this->saveDefaultConfig();
        if (!PacketHooker::isRegistered()) PacketHooker::register($this);
        
        $this->initDatabase();
        $this->initEconomy();
        $this->registerCommands();
        $this->registerListeners();
        
        $this->getLogger()->info("Economy Plugin has been enabled!");
    }

    public function onDisable(): void {
        $this->getLogger()->info("Economy Plugin has been disabled!");
        MySQLProvider::close();
    }

    private function initDatabase(): void {
        MySQLProvider::getInstance()->create(self::getInstance()->getConfig()->get("database"));
    }

    private function initEconomy(): void {
        EconomyManager::init();
    }

    private function registerCommands(): void {
        $this->getServer()->getCommandMap()->register("kxeconomy", new EconomyCommand($this));
    }

    private function registerListeners(): void {
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
    }
}