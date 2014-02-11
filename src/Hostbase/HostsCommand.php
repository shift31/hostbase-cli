<?php namespace Hostbase;

use Shift31\HostbaseClient;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Yaml\Yaml;


class HostsCommand extends Command
{

	const CONFIG_FILE = 'hostbase-cli.config.php';

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'hostbase';


	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'View and manipulate your host database.';



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
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function fire()
	{
		$queryOrFqdn = $this->argument('query|fqdn');

		if ($this->option('add')) {
			$this->add($queryOrFqdn);
		} elseif ($this->option('update')) {
			$this->update($queryOrFqdn);
		} elseif ($this->option('delete')) {
			$this->delete($queryOrFqdn);
		} else {
			$this->search($queryOrFqdn);
		}
	}


	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return array(
			array('query|fqdn', InputArgument::REQUIRED, 'A query or FQDN .'),
		);
	}


	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return array(
			array('limit', null, InputOption::VALUE_REQUIRED, 'Limit size of result set.', null),
			array('showdata', null, InputOption::VALUE_NONE, 'Show all data for host(s).', null),
			array('add', null, InputOption::VALUE_REQUIRED, 'Add a host.', null),
			array('update', null, InputOption::VALUE_REQUIRED, 'Update a host.', null),
			array('delete', null, InputOption::VALUE_NONE, 'Delete a host.', null),
		);
	}


	/**
	 * @param $query
	 */
	protected function search($query)
	{
		$limit = $this->option('limit') > 0 ? $this->option('limit') : 10000;

		$hosts = $this->hbClient->search($query, $limit, $this->option('showdata'));

		if (count($hosts) > 0) {
			foreach ($hosts as $host) {
				if ($this->option('showdata')) {
					$this->info($host->fqdn);
					$this->line(Yaml::dump((array) $host, 2));
				} else {
					$this->info($host);
				}
			}
		} else {
			$this->error("No hosts matching '$query' were found.");
		}
	}


	/**
	 * @param $fqdn
	 */
	protected function add($fqdn)
	{
		$data = json_decode($this->option('add'), true);

		//Log::debug(print_r($data, true));

		if (!is_array($data)) {
			$this->error('Missing JSON');
			exit(1);
		} else {
			$data['fqdn'] = $fqdn;

			try {
				$this->hbClient->store($data);
				$this->info("Added '$fqdn'");
			} catch (\Exception $e) {
				$this->error($e->getMessage());
			}
		}
	}


	/**
	 * @param $fqdn
	 */
	protected function update($fqdn)
	{
		$data = json_decode($this->option('update'), true);

		//Log::debug(print_r($data, true));

		if (!is_array($data)) {
			$this->error('Missing JSON');
			exit(1);
		} else {
			try {
				$this->hbClient->update($fqdn, $data);
				$this->info("Modified '$fqdn'");
			} catch (\Exception $e) {
				$this->error($e->getMessage());
			}
		}
	}


	/**
	 * @param $fqdn
	 */
	protected function delete($fqdn)
	{
		if ($this->confirm("Are you sure you want to delete '$fqdn'? [yes|no]")) {
			try {
				$this->hbClient->destroy($fqdn);
				$this->info("Deleted $fqdn");
			} catch (\Exception $e) {
				$this->error($e->getMessage());
			}
		} else {
			exit;
		}
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