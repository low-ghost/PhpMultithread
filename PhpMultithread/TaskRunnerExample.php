<?php namespace low_ghost\PhpMultithread;

require 'vendor/autoload.php';

use low_ghost\PhpMultithread\Scheduler,
    low_ghost\PhpMultithread\AsyncTask;

$scheduler = new Scheduler;
$asyncTask = new AsyncTask;

$printResults = function($res){ return $res; };

$scheduler->newTask($asyncTask->create('ls -a', $printResults));

