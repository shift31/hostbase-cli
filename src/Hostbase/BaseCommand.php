<?php namespace Hostbase;

use Illuminate\Console\Command;
use Shift31\HostbaseClient;
use Symfony\Component\Yaml\Yaml;


class BaseCommand extends Command
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
     * @param $id
     */
    protected function show($id) {
        try {
            $resource = $this->hbClient->show($id);

            $key = $this->option('key');

            if ($key) {
                $this->info($resource[static::$keySuffixField]);
                $value = isset($resource[$key]) ? $resource[$key] : 'undefined';
                if ($value == 'undefined') {
                    $this->comment("$key: $value\n");
                } else {
                    $this->line("$key: $value\n");
                }
            } else {
                $this->info($resource[static::$keySuffixField]);
                $this->line(Yaml::dump((array) $resource, 2));
            }
        } catch (\Exception $e) {
            $this->error($e->getMessage());
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
            $data[static::$keySuffixField] = $subnet;

            try {
                $this->hbClient->store($data);
                $this->info("Added '$subnet'");
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
                $this->hbClient->update($id, $data);
                $this->info("Modified '$id'");
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
                $this->info("Deleted '$id'");
            } catch (\Exception $e) {
                $this->error($e->getMessage());
            }
        } else {
            exit;
        }
    }
}