<?php namespace low_ghost\PhpMultithread;

require 'vendor/autoload.php';

use SuperClosure\Serializer;

$serializer = new Serializer();
$unserial = $serializer->unserialize($argv[1]);
$unserial();


