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