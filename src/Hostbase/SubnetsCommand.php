<?php namespace Hostbase;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;


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


    /**
     * @var string $keySuffixField
     */
    static protected $keySuffixField = 'network';


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
        $queryOrNetAddr = $this->argument('subnet|query');

        if ($this->option('search')) {
            $this->search($queryOrNetAddr);
        } elseif ($this->option('add')) {
            $this->add($queryOrNetAddr);
        } elseif ($this->option('update')) {
            $this->update($queryOrNetAddr);
        } elseif ($this->option('delete')) {
            $this->delete($queryOrNetAddr);
        } else {
            $this->show($queryOrNetAddr);
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
            array('subnet|query', InputArgument::REQUIRED, 'A network address or query string.'),
        );
    }


    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array_merge(
            parent::getOptions(),
            array(
                array('extendOutput', 'x', InputOption::VALUE_NONE, 'Show all data for subnet(s) during search.', null),
                array('add', 'a', InputOption::VALUE_REQUIRED, 'Add a subnet.', null),
                array('update', 'u', InputOption::VALUE_REQUIRED, 'Update a subnet.', null),
                array('delete', 'd', InputOption::VALUE_NONE, 'Delete a subnet.', null),
            )
        );
    }


    /**
     * @param $query
     */
    protected function search($query)
    {
        $limit = $this->option('limit') > 0 ? $this->option('limit') : 10000;

        $key = $this->option('key');

        $subnets = $this->hbClient->search($query, $limit, $key ? true : $this->option('extendOutput'));

        if (count($subnets) > 0) {
            foreach ($subnets as $subnet) {
                if ($key) {
                    $this->outputKey($subnet, $key, "{$subnet['network']}/{$subnet['cidr']}");
                } elseif ($this->option('extendOutput')) {
                    $this->outputResource($subnet, "{$subnet['network']}/{$subnet['cidr']}");
                } else {
                    $this->info($subnet);
                }
            }
        } else {
            $this->resourcesNotFound($query);
        }
    }


    /**
     * @param $query
     *
     * @return mixed|void
     */
    protected function resourcesNotFound($query)
    {
        $this->error("No subnets matching '$query' were found.");
    }
}