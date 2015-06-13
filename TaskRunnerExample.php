<?php namespace low_ghost\PhpMultithread;

require 'vendor/autoload.php';

use low_ghost\PhpMultithread\Scheduler,
    Symfony\Component\Process\PhpProcess,
    low_ghost\PhpMultithread\AsyncTask,
    SuperClosure\Serializer;

$scheduler = new Scheduler;
$serializer = new Serializer();
$asyncTask = new AsyncTask;

$printResults = function($res){ return $res; };

$scheduler->newTask($asyncTask->create('ls -a', $printResults));
$scheduler->newTask($asyncTask->create('ls -l', $printResults));

$other = "closure scope!";
$preSerial = function($noun = "world") use ($other, $scheduler, $asyncTask, $printResults){
    echo 'hello async ' . $noun;
    echo "\nhello " . $other;
    $scheduler->newTask($asycTask->createPhp(<<<EOD
        <?php echo "hello new task!" ?>
EOD
    , $printResults));
};
$scheduler->newTask($asyncTask->createPhpClosure($preSerial, $printResults));

$scheduler->run();

?>

