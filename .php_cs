<?php

$finder = PhpCsFixer\Finder::create()->in(__DIR__);

$cacheDir = getenv('TRAVIS') ? getenv('HOME') . '/.php-cs-fixer' : __DIR__;

return PhpCsFixer\Config::create()
    ->setCacheFile($cacheDir . '/.php_cs.cache')
    ->setFinder($finder)
    ->setRules(array(
        '@PSR2' => true,
        'array_syntax' => array(
            'syntax' => 'short',
        ),
    ));
