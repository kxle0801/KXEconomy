<?php
declare(strict_types = 1);

namespace kxleph\kxeconomy\provider;

use kxleph\kxeconomy\KXEconomy;

use pocketmine\utils\SingletonTrait;

use libs\poggit\libasynql\libasynql;
use libs\poggit\libasynql\DataConnector;

class MySQLProvider {

	use SingletonTrait;

	/** @var DataConnector */
	private DataConnector $database;

	/**
	 * @param string[] $config
	 * @return void
	 */
	public function create(array $config): void {
		$this->database = libasynql::create(KXEconomy::getInstance(), $config, [
			"mysql"  => "provider/mysql.sql",
		]);
		$this->database->executeGeneric('table.economy');
		$this->database->executeGeneric('table.economy_transactions');
	}

    /**
     * @return DataConnector
     */
    public function get(): DataConnector {
        return $this->database;
    }

	/**
	 * @return void
	 */
	public static function close(): void {
		self::getInstance()->get()->waitAll();
		self::getInstance()->get()->close();
    }
}