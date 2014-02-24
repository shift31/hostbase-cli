<?php namespace Hostbase;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;


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


    /**
     * @var string $keySuffixField
     */
    static protected $keySuffixField = 'ipAddress';


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
        $queryOrIpAddr = $this->argument('ip|query');

        if ($this->option('search')) {
            $this->search($queryOrIpAddr);
        } elseif ($this->option('add')) {
            $this->add($queryOrIpAddr);
        } elseif ($this->option('update')) {
            $this->update($queryOrIpAddr);
        } elseif ($this->option('delete')) {
            $this->delete($queryOrIpAddr);
        } else {
            $this->show($queryOrIpAddr);
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
            array('ip|query', InputArgument::REQUIRED, 'An IP address or query string.'),
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
                array(
                    'extendOutput',
                    'x',
                    InputOption::VALUE_NONE,
                    'Show all data for IP address(es) during search.',
                    null
                ),
                array('add', 'a', InputOption::VALUE_REQUIRED, 'Add an IP address.', null),
                array('update', 'u', InputOption::VALUE_REQUIRED, 'Update an IP address.', null),
                array('delete', 'd', InputOption::VALUE_NONE, 'Delete an IP address.', null),
            )
        );
    }


    /**
     * @param $query
     *
     * @return mixed|void
     */
    protected function resourcesNotFound($query)
    {
        $this->error("No IP addresses matching '$query' were found.");
    }
}