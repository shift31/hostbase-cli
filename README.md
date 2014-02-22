# Hostbase Command Line Client

The Hostbase CLI features full-text search (using Elasticsearch/Lucene query syntax) and basic CRUD operations, which accept JSON.

## Installation

1. Download the PHAR:  https://github.com/shift31/hostbase-cli/raw/master/hostbase.phar
2. Move it to /usr/local/sbin and rename it to 'hostbase'

## Configuration

Create /etc/hostbase-cli.config.php

```php
<?php

 return array(
     'baseUrl' => 'http://your.hostbase.server'
 );
 ```

## Usage

### Add a host (raw JSON string)

```bash
hostbase hosts -a '{"fqdn": "hostname.domain.tld", "fooKey": "barValue"}' hostname.domain.tld
```

### Add a host (.json file using Bash subshell)

host.json:

```json
{
    "fqdn": "hostname.example.com",
    "fooKey": "barValue"
}
```

```bash
hostbase hosts -a "$(cat host.json)" hostname.example.com
```

### Find a host by FQDN

```bash
hostbase hosts hostname.example.com
```

### Search for a host

#### Example where 'domain' contains 'example.com':

    ```bash
    hostbase hosts -s 'domain:example.com'
    ```

#### Show all host data (output Yaml)

    ```bash
    hostbase hosts -sx 'domain:example.com'
    ```


### Delete a host

```bash
hostbase hosts -d hostname.example.com
```