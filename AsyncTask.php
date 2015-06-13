<?php namespace low_ghost\PhpMultithread;

use Symfony\Component\Process\Process,
    Symfony\Component\Process\PhpProcess,
    SuperClosure\Serializer,
    RuntimeException;

class AsyncTask {

    public function create($cmd, callable $cb = null, $name = false, $parent = false)
    {

        $process = new Process($cmd);
        $process->start();
        yield; //yield to other tasks, avoiding extra calls to isRunning()
        while ($process->isRunning()) {
            yield;
            //throw new \RuntimeException($process->getErrorOutput());
        }

        if (!$process->isSuccessful())
            throw new RuntimeException($process->getErrorOutput());

        $output = $cb ? $cb($process->getOutput()) : $process->getOutput();
        yield ($this->store($output, $name, $parent));
    }

    public function createPhp($cmd, callable $cb = null, $name = false, $parent = false)
    {
        $process = new PhpProcess($cmd);
        $process->start();
        yield; //yield to other tasks, avoiding extra calls to isRunning()
        while ($process->isRunning()) {
            yield;
        }

        if (!$process->isSuccessful())
            throw new RuntimeException($process->getErrorOutput());

        $output = $cb ? $cb($process->getOutput()) : $process->getOutput();
        yield ($this->store($output, $name, $parent));
    }

    public function createPhpClosure($func, callable $cb = null, $name = false, $parent = false)
    {
        $serializer = new Serializer();
        $process = new Process('php CreatePhpClosure.php ' . escapeshellarg($serializer->serialize($func)));
        $process->start();
        yield; //yield to other tasks, avoiding extra calls to isRunning()
        while ($process->isRunning()) {
            yield;
            //throw new \RuntimeException($process->getErrorOutput());
        }

        if (!$process->isSuccessful())
            throw new RuntimeException($process->getErrorOutput());

        $output = $cb ? $cb($process->getOutput()) : $process->getOutput();
        yield ($this->store($output, $name, $parent));
    }

    public function store($data, $name = false, $parent = false)
    {
        return new SystemCall(
            function(Task $task, Scheduler $scheduler) use ($data, $name, $parent)
            {
                $task->setSendValue($scheduler->store($task, $data, $name, $parent));
                //don't schedule and immediately destroy task
                $scheduler->killUnscheduled($task);
            }
        );
    }


}

?>
