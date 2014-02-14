<?php namespace Hostbase;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Yaml\Yaml;


class IpAddressesCommand extends BaseCommand
{

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'ips';


	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'View and manipulate IP addresses';


	public function __construct()
	{
		parent::__construct();
		$this->hbClient->setResource('ipaddresses');
	}


	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function fire()
	{
		$queryOrIpAddr = $this->argument('query|ip');

		if ($this->option('add')) {
			$this->add($queryOrIpAddr);
		} elseif ($this->option('update')) {
			$this->update($queryOrIpAddr);
		} elseif ($this->option('delete')) {
			$this->delete($queryOrIpAddr);
		} else {
			$this->search($queryOrIpAddr);
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
			array('query|ip', InputArgument::REQUIRED, 'A query or IP address.'),
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
			array('limit', 'l', InputOption::VALUE_REQUIRED, 'Limit size of result set.', null),
			array('showdetails', 's', InputOption::VALUE_NONE, 'Show all data for IP address(es).', null),
			array('add', 'a', InputOption::VALUE_REQUIRED, 'Add an IP address.', null),
			array('update', 'u', InputOption::VALUE_REQUIRED, 'Update an IP address.', null),
			array('delete', 'd', InputOption::VALUE_NONE, 'Delete an IP address.', null),
		);
	}


	/**
	 * @param $query
	 */
	protected function search($query)
	{
		$limit = $this->option('limit') > 0 ? $this->option('limit') : 10000;

		$ipAddresses = $this->hbClient->search($query, $limit, $this->option('showdetails'));

		if (count($ipAddresses) > 0) {
			foreach ($ipAddresses as $ipAddress) {
				if ($this->option('showdetails')) {
					$this->info($ipAddress['ipAddress']);
					$this->line(Yaml::dump((array) $ipAddress, 2));
				} else {
					$this->info($ipAddress);
				}
			}
		} else {
			$this->error("No IP addresses matching '$query' were found.");
		}
	}


	/**
	 * @param $ipAddress
	 */
	protected function add($ipAddress)
	{
		$data = json_decode($this->option('add'), true);

		//Log::debug(print_r($data, true));

		if (!is_array($data)) {
			$this->error('Missing JSON');
			exit(1);
		} else {
			$data['ipAddress'] = $ipAddress;

			try {
				$this->hbClient->store($data);
				$this->info("Added '$ipAddress'");
			} catch (\Exception $e) {
				$this->error($e->getMessage());
			}
		}
	}


	/**
	 * @param $ipAddress
	 */
	protected function update($ipAddress)
	{
		$data = json_decode($this->option('update'), true);

		//Log::debug(print_r($data, true));

		if (!is_array($data)) {
			$this->error('Missing JSON');
			exit(1);
		} else {
			try {
				$this->hbClient->update($ipAddress, $data);
				$this->info("Modified '$ipAddress'");
			} catch (\Exception $e) {
				$this->error($e->getMessage());
			}
		}
	}


	/**
	 * @param $ipAddress
	 */
	protected function delete($ipAddress)
	{
		if ($this->confirm("Are you sure you want to delete '$ipAddress'? [yes|no]")) {
			try {
				$this->hbClient->destroy($ipAddress);
				$this->info("Deleted $ipAddress");
			} catch (\Exception $e) {
				$this->error($e->getMessage());
			}
		} else {
			exit;
		}
	}
}