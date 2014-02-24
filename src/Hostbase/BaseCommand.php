<?php namespace Hostbase;

use Illuminate\Console\Command;
use Shift31\HostbaseClient;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Yaml\Yaml;


abstract class BaseCommand extends Command
{
    const CONFIG_FILE = 'hostbase-cli.config.php';

    /**
     * The document field to use as the key suffix.
     *
     * @var string $keySuffixField
     */
    static protected $keySuffixField = null;


    public function __construct()
    {
        if (is_null(static::$keySuffixField)) {
            throw new \Exception("The 'keySuffixField' field must not be null");
        }

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

        // data must be returned as an array for proper Yaml conversion
        $this->hbClient->decodeJsonAsArray();
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


    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array(
            array('json', 'j', InputOption::VALUE_NONE, 'Output JSON instead of Yaml.', null),
            array('key', 'k', InputOption::VALUE_REQUIRED, 'Only show the value for this key.', null),
            array('search', 's', InputOption::VALUE_NONE, 'Search with query string.', null),
            array('limit', 'l', InputOption::VALUE_REQUIRED, 'Limit size of result set.', null),
        );
    }


    /**
     * @param $query
     */
    protected function search($query)
    {
        $limit = $this->option('limit') > 0 ? $this->option('limit') : 10000;

        $key = $this->option('key');

        $resources = $this->hbClient->search($query, $limit, $key ? true : $this->option('extendOutput'));

        if (count($resources) > 0) {
            foreach ($resources as $resource) {
                if ($key) {
                    $this->outputKey($resource, $key);
                } elseif ($this->option('extendOutput')) {
                    $this->outputResource($resource);
                } else {
                    $this->info($resource);
                }
            }
        } else {
            $this->resourcesNotFound($query);
        }
    }


    /**
     * @param $id
     */
    protected function show($id)
    {
        try {
            $resource = $this->hbClient->show($id);

            $key = $this->option('key');

            if ($key) {
                $this->outputKey($resource, $key);
            } else {
                $this->outputResource($resource);
            }
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }


    /**
     * @param $id
     */
    protected function add($id)
    {
        $data = json_decode($this->option('add'), true);

        //Log::debug(print_r($data, true));

        if (!is_array($data)) {
            $this->error('Missing JSON');
            exit(1);
        } else {
            $data[static::$keySuffixField] = $id;

            try {
                $resource = $this->hbClient->store($data);
                $this->comment("Added '$id'");
                $this->outputResource($resource, $id);
            } catch (\Exception $e) {
                $this->error($e->getMessage());
            }
        }
    }


    /**
     * @param $id
     */
    protected function update($id)
    {
        $data = json_decode($this->option('update'), true);

        //Log::debug(print_r($data, true));

        if (!is_array($data)) {
            $this->error('Missing JSON');
            exit(1);
        } else {
            try {
                $resource = $this->hbClient->update($id, $data);
                $this->comment("Modified '$id'");
                $this->outputResource($resource, $id);
            } catch (\Exception $e) {
                $this->error($e->getMessage());
            }
        }
    }


    /**
     * @param $id
     */
    protected function delete($id)
    {
        if ($this->confirm("Are you sure you want to delete '$id'? [yes|no]")) {
            try {
                $this->hbClient->destroy($id);
                $this->comment("Deleted '$id'");
            } catch (\Exception $e) {
                $this->error($e->getMessage());
            }
        } else {
            exit;
        }
    }


    /**
     * @param array       $resource
     * @param string      $key
     * @param null|string $title
     */
    protected function outputKey(array $resource, $key, $title = null)
    {
        $title = $title ? : $resource[static::$keySuffixField];

        $value = isset($resource[$key]) ? $resource[$key] : 'undefined';

        $this->outputResource(array($key => $value), $title);
    }


    /**
     * @param array       $resource
     * @param null|string $title
     */
    protected function outputResource(array $resource, $title = null)
    {
        $title = $title ? : $resource[static::$keySuffixField];

        $this->info($title);

        if ($this->option('json')) {
            if (PHP_MAJOR_VERSION == 5 and PHP_MINOR_VERSION == 4) {
                $this->line(json_encode($resource, JSON_FORCE_OBJECT | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            } else {
                $this->line(json_encode($resource, JSON_FORCE_OBJECT));
            }
        } else {
            $this->line(Yaml::dump((array) $resource, 2));
        }
    }


    /**
     * @param $query
     *
     * @return mixed
     */
    abstract protected function resourcesNotFound($query);
}