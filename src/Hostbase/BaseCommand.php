<?php namespace Hostbase;

use Shift31\HostbaseClient;
use Illuminate\Console\Command;


class BaseCommand extends Command
{

	const CONFIG_FILE = 'hostbase-cli.config.php';



	public function __construct()
	{
		parent::__construct();

		try {
			$config = $this->getConfig();
		} catch (\Exception $e) {
			print $e->getMessage() . PHP_EOL;
			exit(1);
		}

		$this->hbClient = new HostbaseClient(
			$config['baseUrl'],
			'hosts',
			isset($config['username']) ? $config['username'] : null,
			isset($config['password']) ? $config['password'] : null
		);
	}


	/**
	 * @return array
	 * @throws \Exception
	 */
	protected function getConfig()
	{
		$userConfigFile = getenv('HOME') . '/' . self::CONFIG_FILE;
		$systemConfigFile = '/etc/' . self::CONFIG_FILE;

		if (file_exists(self::CONFIG_FILE)) {
			$config = require(self::CONFIG_FILE);
		} elseif (file_exists($userConfigFile)) {
			$config = require($userConfigFile);
		} elseif (file_exists($systemConfigFile)) {
			$config = require($systemConfigFile);
		} else {
			throw new \Exception('No configuration file was found!');
		}

		if (!isset($config['baseUrl'])) {
			throw new \Exception("The configuration array must contain a 'baseUrl' key");
		}

		return $config;
	}
}