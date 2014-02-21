<?php namespace Hostbase;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Yaml\Yaml;


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
        return array(
            array('key', 'k', InputOption::VALUE_REQUIRED, 'Only show the value for this key.', null),
            array('search', 's', InputOption::VALUE_NONE, 'Search with query string.', null),
            array('limit', 'l', InputOption::VALUE_REQUIRED, 'Limit size of result set.', null),
            array('extendOutput', 'x', InputOption::VALUE_NONE, 'Show all data for host(s) during search.', null),
            array('add', 'a', InputOption::VALUE_REQUIRED, 'Add a host.', null),
            array('update', 'u', InputOption::VALUE_REQUIRED, 'Update a host.', null),
            array('delete', 'd', InputOption::VALUE_NONE, 'Delete a host.', null),
        );
    }


    /**
     * @param $query
     */
    protected function search($query)
    {
        $limit = $this->option('limit') > 0 ? $this->option('limit') : 10000;

        $key = $this->option('key');

        $hosts = $this->hbClient->search($query, $limit, $key ? true : $this->option('extendOutput'));

        if (count($hosts) > 0) {
            foreach ($hosts as $host) {
                if ($key) {
                    $this->info($host[static::$keySuffixField]);
                    $value = isset($host[$key]) ? $host[$key] : 'undefined';
                    if ($value == 'undefined') {
                        $this->comment("$key: $value\n");
                    } else {
                        $this->line("$key: $value\n");
                    }
                } elseif ($this->option('extendOutput')) {
                    $this->info($host['fqdn']);
                    $this->line(Yaml::dump((array) $host, 2));
                } else {
                    $this->info($host);
                }
            }
        } else {
            $this->error("No hosts matching '$query' were found.");
        }
    }
}