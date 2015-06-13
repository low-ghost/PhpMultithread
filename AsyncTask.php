<?php namespace low_ghost\PhpMultithread;

use Symfony\Component\Process\Process,
    Symfony\Component\Process\PhpProcess,
    SuperClosure\Serializer;

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

    public function createPhp($cmd, callable $cb = null)
    {
        $process = new PhpProcess($cmd);
        $process->start();
        yield; //yield to other tasks, avoiding extra calls to isRunning()
        while ($process->isRunning()) {
            yield;
            //throw new \RuntimeException($process->getErrorOutput());
        }

        if ($cb){
            if (!$process->isSuccessful())
                echo $process->getErrorOutput();
            else
                echo $cb($process->getOutput());
            yield;
        }
    }

    public function createPhpClosure($func, callable $cb = null)
    {
        $serializer = new Serializer();
        $process = new Process('php CreatePhpClosure.php ' . escapeshellarg($serializer->serialize($func)));
        $process->start();
        yield; //yield to other tasks, avoiding extra calls to isRunning()
        while ($process->isRunning()) {
            yield;
            //throw new \RuntimeException($process->getErrorOutput());
        }

        if ($cb){
            if (!$process->isSuccessful())
                echo $process->getErrorOutput();
            else
                echo $cb($process->getOutput());
            yield;
        }
    }


}

?>
