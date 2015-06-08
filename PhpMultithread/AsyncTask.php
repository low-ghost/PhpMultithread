<?php namespace low_ghost\PhpMultithread;

require 'vendor/autoload.php';

use Symfony\Component\Process\Process;

$process = new Process('ls -lsa');
$process->run();

// executes after the command finishes
if (!$process->isSuccessful()) {
    throw new \RuntimeException($process->getErrorOutput());
}

echo $process->getOutput();

?>
