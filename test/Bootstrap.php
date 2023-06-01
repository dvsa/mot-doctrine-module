<?php

if (!file_exists(__DIR__.'/../vendor/autoload.php')) {
    throw new \RuntimeException('Run "composer install" to install dependencies before running tests');
}

require_once __DIR__.'/../vendor/autoload.php';

