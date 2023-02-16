<?php

$finder = PhpCsFixer\Finder::create()->in(__DIR__);

$config = new PhpCsFixer\Config();
$config->setCacheFile(__DIR__ . '/php-cs-fixer.cache');
$config->setFinder($finder);
$config->setRules([
        '@PSR2' => true,
        'array_syntax' => [
            'syntax' => 'short',
        ],
    ]);

return $config;
