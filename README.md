# XLIFF Generator

Extract XLIFF files from HMTL templates.

Author: Carsten Walther

Date: January, 18th 2021

---

## Requirements

- PHP min. 7.3
- Composer

## Installation

Download this repository. Make shure you have installed composer. Unzip the package e.g. to `/var/www/xliff-gen/` an run

```bash
composer install
```

Done.

## Configuration

Add a file ```config.php``` into your root folder with the following content:

```php
<?php

define('APIKEY', 'your watson api key');
```

## Running

If everything is done you can run this locally via the build in php webserver. You have to call

```bash
php -S localhost:8000
```

## How to use

Open your browser and put localhost:8000 into the address bar and hit enter. Now generate your xliff files.
