<?php namespace Hostbase;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;


class HostsCommand extends BaseCommand
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'hosts';


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'View and manipulate hosts';


    /**
     * @var string $keySuffixField
     */
    static protected $keySuffixField = 'fqdn';


    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        $queryOrFqdn = $this->argument('fqdn|query');

        if ($this->option('search')) {
            $this->search($queryOrFqdn);
        } elseif ($this->option('add')) {
            $this->add($queryOrFqdn);
        } elseif ($this->option('update')) {
            $this->update($queryOrFqdn);
        } elseif ($this->option('delete')) {
            $this->delete($queryOrFqdn);
        } else {
            $this->show($queryOrFqdn);
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
            array('fqdn|query', InputArgument::REQUIRED, 'A FQDN or query string.'),
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
                array('extendOutput', 'x', InputOption::VALUE_NONE, 'Show all data for host(s) during search.', null),
                array('add', 'a', InputOption::VALUE_REQUIRED, 'Add a host.', null),
                array('update', 'u', InputOption::VALUE_REQUIRED, 'Update a host.', null),
                array('delete', 'd', InputOption::VALUE_NONE, 'Delete a host.', null),
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
        $this->error("No hosts matching '$query' were found.");
    }
}