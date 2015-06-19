<?php namespace low_ghost\PhpMultithread;

use Symfony\Component\Process\Process,
    Symfony\Component\Process\PhpProcess,
    Jeremeamia\SuperClosure\SerializableClosure,
    RuntimeException;

class AsyncTask
{
    public function create($cmd, $cb = null, $name = null, $parent = null, $timeout = null)
    {
        //check cmd type and create appropiate process
        if (is_string($cmd)){
            if (strpos($cmd, "<?php") !== false){
                $process = new PhpProcess($cmd);
            } else {
                $process = new Process($cmd);
            }
        } else if (is_callable($cmd)){
            $postSerialize = new SerializableClosure($cmd);
            $process = new Process('php CreatePhpClosure.php ' . escapeshellarg(serialize($postSerialize)));
        } else {
            throw new Exception("Improper command type, must be string, string containing <?php, or closure");
        }

        if ($timeout)
            $process->setTimeout((int) $timeout);

        //start process
        $process->start();
        //yield once to avoid extra calls to isRunning(), assuming process is not instantly finished
        yield;
        while ($process->isRunning()) {
            if ($timeout){
                try {
                    $process->checkTimeout();
                } catch (RuntimeException $e) {
                    //store the error. no need to send SIGKILL to stop process
                    yield ($this->store(["error" => $e->getMessage()], $name, $parent));
                }
            }
            yield;
        }

        $output = "";
        if (!$process->isSuccessful()){
            $output = ["error" => $process->getErrorOutput()];
        } else {
            $output = $cb ? $cb($process->getOutput()) : $process->getOutput();
        }
        yield ($this->store($output, $name, $parent));
    }

    public function store($data, $name = false, $parent = false)
    {
        return new SystemCall(
            function(Task $task, Scheduler $scheduler) use ($data, $name, $parent)
            {
                $task->setSendValue($scheduler->store($task, $data, $name, $parent));
                $scheduler->schedule($task);
            }
        );
    }
}
?>
