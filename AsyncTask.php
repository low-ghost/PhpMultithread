<?php namespace low_ghost\PhpMultithread;

use Symfony\Component\Process\Process;

class AsyncTask {

    public function create($cmd, callable $cb = null)
    {

        $process = new Process($cmd);
        $process->start();
        yield; //yield to other tasks, avoiding extra calls to isRunning()
        while ($process->isRunning()) {
            yield;
            //throw new \RuntimeException($process->getErrorOutput());
        }

        if ($cb){
            echo $cb($process->getOutput());
            yield;
        }
    }
}

?>
