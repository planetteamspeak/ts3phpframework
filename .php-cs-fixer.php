<?php

$finder = PhpCsFixer\Finder::create()->in(__DIR__);

$cacheDir = getenv('TRAVIS') ? getenv('HOME') . '/.php-cs-fixer' : __DIR__;

$config = new PhpCsFixer\Config();
$config->setCacheFile($cacheDir . '/php-cs-fixer.cache');
$config->setFinder($finder);
$config->setRules([
        '@PSR2' => true,
        'array_syntax' => [
            'syntax' => 'short',
        ],
    ]);

return $config;
