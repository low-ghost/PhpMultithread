<?php namespace low_ghost\PhpMultithread;

require 'vendor/autoload.php';

use low_ghost\PhpMultithread\Scheduler,
    Symfony\Component\Process\PhpProcess,
    low_ghost\PhpMultithread\AsyncTask,
    SuperClosure\Serializer;

$scheduler = new Scheduler;
$serializer = new Serializer();
$asyncTask = new AsyncTask;

//basic non-functioning callback, but anything will work
$printResults = function($res){ return $res; };

//creates 2 unix directory listing processes and will execute async with $scheduler->run()
$scheduler->newTask($asyncTask->create('ls -a', $printResults, "a", "ls"));
$scheduler->newTask($asyncTask->create('ls -l', $printResults, "l", "ls"));

$other = "closure scope!";
$preSerial = function($noun = "world") use ($other){
    echo 'hello async ' . $noun;
    echo "\nhello " . $other;
};
$scheduler->newTask($asyncTask->createPhpClosure($preSerial, $printResults));

try {
    //start all tasks and return stored object
    $res = $scheduler->run();
    //all results have been saved with Scheduler->store()
    print_r($res);
} catch (RuntimeException $e) {
     print_r($e->getMessage());
}

?>

