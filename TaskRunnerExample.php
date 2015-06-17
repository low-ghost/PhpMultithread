<?php namespace low_ghost\PhpMultithread;

require 'vendor/autoload.php';

use low_ghost\PhpMultithread\Scheduler,
    Symfony\Component\Process\PhpProcess,
    low_ghost\PhpMultithread\AsyncTask,
    SuperClosure\Serializer;

$asyncTask = new AsyncTask;
$serializer = new Serializer;
//Limit to 4 concurrent processes
$scheduler = new Scheduler(4);

//basic non-functioning callback, but anything will work
//this is run before any store event and can be used to manipulate returned response
$printResults = function($res){ return $res; };

//creates 2 unix directory listing processes and will execute async with $scheduler->run()
//default store obj without callback
$scheduler->newTask($asyncTask->create('ls'));
//no callback and store under "sleep" without parent
$scheduler->newTask($asyncTask->create('sleep 1', false, "sleep"));
//store obj after callback and under parent 'ls' with respective names
$scheduler->newTask($asyncTask->create('ls -a', $printResults, "a", "ls"));
$scheduler->newTask($asyncTask->create('ls -l', $printResults, "l", "ls"));
$scheduler->newTask($asyncTask->create('ls -lh', $printResults, "lh", "ls"));
//intentional error: typo in command
$scheduler->newTask($asyncTask->create('lstypo', $printResults, "ls", "errors"));
//intentional error: timeout of 1 sec exceeded by sleep of 10 sec
$scheduler->newTask($asyncTask->create('sleep 10', $printResults, "sleep", "errors", 1));

//run php in isolation. for closure scope, see below
$scheduler->newTask($asyncTask->create(<<<EOD
    <?php echo 'hello php'; ?>
EOD
, false, "hello", "php"));

//php closure executed in background
$other = "closure scope!";
$preSerial = function($noun = "world") use ($other){
    echo 'hello async ' . $noun;
    echo "\nhello " . $other;
};
$scheduler->newTask($asyncTask->create($preSerial, $printResults));

//add to store directly
$scheduler->store(false, "directly stored data", "directStoreName", "directStoreParent");

//start all tasks and return stored object.
$res = $scheduler->run();
//all results have been saved with Scheduler->store()
print_r($res);

?>

