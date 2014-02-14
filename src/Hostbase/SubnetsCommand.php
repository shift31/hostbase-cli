<?php namespace Hostbase;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Yaml\Yaml;


class SubnetsCommand extends BaseCommand
{

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'subnets';


	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'View and manipulate subnets';


	public function __construct()
	{
		parent::__construct();
		$this->hbClient->setResource('subnets');
	}


	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function fire()
	{
		$queryOrNetAddr = $this->argument('query|subnet');

		if ($this->option('add')) {
			$this->add($queryOrNetAddr);
		} elseif ($this->option('update')) {
			$this->update($queryOrNetAddr);
		} elseif ($this->option('delete')) {
			$this->delete($queryOrNetAddr);
		} else {
			$this->search($queryOrNetAddr);
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
			array('query|subnet', InputArgument::REQUIRED, 'A query or network address.'),
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
			array('showdetails', 's', InputOption::VALUE_NONE, 'Show all data for subnet(s).', null),
			array('add', 'a', InputOption::VALUE_REQUIRED, 'Add a subnet.', null),
			array('update', 'u', InputOption::VALUE_REQUIRED, 'Update a subnet.', null),
			array('delete', 'd', InputOption::VALUE_NONE, 'Delete a subnet.', null),
		);
	}


	/**
	 * @param $query
	 */
	protected function search($query)
	{
		$query = str_replace('/', '_', $query);

		$limit = $this->option('limit') > 0 ? $this->option('limit') : 10000;

		$subnets = $this->hbClient->search($query, $limit, $this->option('showdetails'));

		if (count($subnets) > 0) {
			foreach ($subnets as $subnet) {
				if ($this->option('showdetails')) {
					$this->info("{$subnet['network']}/{$subnet['cidr']}");
					$this->line(Yaml::dump((array) $subnet, 2));
				} else {
					$this->info($subnet);
				}
			}
		} else {
			$this->error("No subnets matching '$query' were found.");
		}
	}


	/**
	 * @param $subnet
	 */
	protected function add($subnet)
	{
		$data = json_decode($this->option('add'), true);

		//Log::debug(print_r($data, true));

		if (!is_array($data)) {
			$this->error('Missing JSON');
			exit(1);
		} else {
			$data['network'] = $subnet;

			try {
				$this->hbClient->store($data);
				$this->info("Added '$subnet'");
			} catch (\Exception $e) {
				$this->error($e->getMessage());
			}
		}
	}


	/**
	 * @param $subnet
	 */
	protected function update($subnet)
	{
		$data = json_decode($this->option('update'), true);

		//Log::debug(print_r($data, true));

		if (!is_array($data)) {
			$this->error('Missing JSON');
			exit(1);
		} else {
			try {
				$this->hbClient->update($subnet, $data);
				$this->info("Modified '$subnet'");
			} catch (\Exception $e) {
				$this->error($e->getMessage());
			}
		}
	}


	/**
	 * @param $subnet
	 */
	protected function delete($subnet)
	{
		if ($this->confirm("Are you sure you want to delete '$subnet'? [yes|no]")) {
			try {
				$this->hbClient->destroy($subnet);
				$this->info("Deleted $subnet");
			} catch (\Exception $e) {
				$this->error($e->getMessage());
			}
		} else {
			exit;
		}
	}
}