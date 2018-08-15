<?php

$file = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($file)) {
    $file = __DIR__ . '/../../../vendor/autoload.php';
    if (!file_exists($file)) {
        throw new RuntimeException('Install dependencies to run test suite.');
    }
}

$autoload = require_once $file;

$parsed_phpunit_version = `./vendor/bin/phpunit --version | head -n 1 | tr ' ' '\n' | head -n 2 | tail -n 1 | head -c 1`;
if (!is_null($parsed_phpunit_version) && $parsed_phpunit_version < 6) {
    class_alias('PHPUnit_Framework_Constraint_IsType', 'PHPUnit\Framework\Constraint\IsType');
}

// Make PHPUnit 6 tests backward compatible for PHPUnit 5 code base
if (PHP_VERSION_ID < 70000) {
  class_alias('PHPUnit_Framework_Constraint_IsType', 'PHPUnit\Framework\Constraint\IsType');
}