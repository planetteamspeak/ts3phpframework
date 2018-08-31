<?php

$file = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($file)) {
    $file = __DIR__ . '/../../../vendor/autoload.php';
    if (!file_exists($file)) {
        throw new RuntimeException('Install dependencies to run test suite.');
    }
}

$autoload = require_once $file;

// Creating an alias for PHPUnit version check
class_alias ('PHPUnit_Runner_Version', 'PHPUnit\Runner\Version');

// Make PHPUnit 6 tests backward compatible for PHPUnit 5 code base
if (PHP_VERSION_ID < 70000 || PHPUnit\Runner\Version::id() < 6) {
  class_alias('PHPUnit_Framework_Constraint_IsType', 'PHPUnit\Framework\Constraint\IsType');
}