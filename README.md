# Hostbase Command Line Client

The Hostbase CLI features full-text search (using Elasticsearch/Lucene query syntax) and basic CRUD operations, which accept JSON.

- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
    - [Help](#help)
    - [Add a host](#add-a-host)
    - [Update a host](#update-a-host)
    - [Find a host by FQDN](#find-a-host-by-fqdn)
    - [Search for a host](#search-for-a-host)
    - [Delete a host](#delete-a-host)
    - [Other entities](#other-entities)
    - [Laravel Envoy](#laravel-envoy)


## Installation

1. Download the PHAR:  https://github.com/shift31/hostbase-cli/raw/master/hostbase.phar
2. Move it to /usr/local/sbin and rename it to 'hostbase'
3. Make it executable:

    ```bash
	chmod +x /usr/local/sbin/hostbase
	```

## Configuration

Create /etc/hostbase-cli.config.php:

```php
<?php

 return array(
     'baseUrl' => 'http://your.hostbase.server'
 );
```

**Configuration for [Hostbase Development Server](https://github.com/shift31/hostbase#development-server-vagrant):**
```php
<?php

 return array(
     'baseUrl' => 'http://hostbase.192.168.33.10.xip.io'
 );
```

## Usage

### Help

```
Usage:
  [options] command [arguments]

Options:
  --help           -h Display this help message.
  --quiet          -q Do not output any message.
  --verbose        -v|vv|vvv Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
  --version        -V Display this application version.
  --ansi              Force ANSI output.
  --no-ansi           Disable ANSI output.
  --no-interaction -n Do not ask any interactive question.
  --env               The environment the command should run under.

Available commands:
  help          Displays help for a command
  hosts         View and manipulate hosts
  ips           View and manipulate IP addresses
  list          Lists commands
  self-update   Updates the application.
  subnets       View and manipulate subnets
```

#### Hosts
`hostbase hosts [-j|--json] [-k|--key="..."] [-s|--search] [-l|--limit="..."] [-x|--extendOutput] [-a|--add="..."] [-u|--update="..."] [-d|--delete] fqdn|query`

#### Subnets
`hostbase subnets [-j|--json] [-k|--key="..."] [-s|--search] [-l|--limit="..."] [-x|--extendOutput] [-a|--add="..."] [-u|--update="..."] [-d|--delete] subnet|query`

#### IP Addresses
`hostbase ips [-j|--json] [-k|--key="..."] [-s|--search] [-l|--limit="..."] [-x|--extendOutput] [-a|--add="..."] [-u|--update="..."] [-d|--delete] ip|query`

### Add a host

Example adding a host with the mandatory 'fqdn' field and another (arbitrary) field:

- Raw JSON string:

    ```bash
    hostbase hosts -a '{"fqdn": "hostname.domain.tld", "fooField": "barValue"}' hostname.domain.tld
    ```
- .json file using Bash subshell:

    host.json:

    ```json
    {
        "fqdn": "hostname.example.com",
        "fooField": "barValue"
    }
    ```

    ```bash
    hostbase hosts -a "$(cat host.json)" hostname.example.com
    ```

### Update a host

Example adding a field (key/value pair):

- Raw JSON string:

    ```bash
    hostbase hosts -u '{"anotherField": "someValue"}' hostname.domain.tld
    ```
- .json file using Bash subshell:

    host.json:

    ```json
    {
        "anotherField": "someValue"
    }
    ```

    ```bash
    hostbase hosts -u "$(cat host.json)" hostname.example.com
    ```

### Find a host by FQDN

- Output Yaml (default):

    ```bash
    hostbase hosts hostname.example.com
    ```
- Output JSON:

    ```bash
    hostbase hosts -j hostname.example.com
    ```

### Search for a host

Use [Elasticsearch/Lucene query syntax](http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/query-dsl-query-string-query.html#query-string-syntax)

- Example where 'domain' contains 'example.com'

    ```bash
    hostbase hosts -s 'domain:example.com'
    ```
- Show all host data (output Yaml)

    ```bash
    hostbase hosts -sx 'domain:example.com'
    ```
- Only return values for a specific field/key (using 'operatingsystem' as example)

    This also works when requesting a single host.

    ```bash
    hostbase hosts -s 'domain:example.com' -k operatingsystem
    ```
- List all hosts

    **Note that adding the extended output (-x) option won't work here, because the Hostbase server only returns a list of hosts when a search string is null.  This prevents too much data from being output.**

    ```bash
    hostbase hosts -s ""
    ```

### Delete a host

```bash
hostbase hosts -d hostname.example.com
```

### Other entities

The 'subnets' and 'ips' commands work the same way as 'hosts'

### Laravel Envoy

Using [Laravel Envoy](https://github.com/laravel/envoy), you can easily run tasks on multiple servers (in serial or parallel).  Here's an example `Envoy.blade.php` that retrieves an array of hosts from the output of `hostbase` and runs `ls -la` on each server, one at a time:

```php
<?php
$servers = [];
exec('hostbase hosts -s "env:prod AND role:www"', $servers);

$credentials = [];

foreach ($servers as $server) {
  $credentials[$server] = 'root@' . $server;
}
?>

@servers($credentials)

@task('foo', ['on' => $servers])
ls -la
@endtask
```