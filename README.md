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

**Configuration for [Hostbase Vagrant-built Development Server](https://github.com/shift31/hostbase#vagrant):**
```php
<?php

 return array(
     'baseUrl' => 'http://hostbase.192.168.33.10.xip.io'
 );
```

## Usage

### Help

```
$ hostbase
Hostbase Client version 0.9.5

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

```bash
hostbase hosts hostname.example.com
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

### Delete a host

```bash
hostbase hosts -d hostname.example.com
```

### Other entities

The 'subnets' and 'ips' commands work the same way as 'hosts'