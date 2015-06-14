<?php namespace low_ghost\PhpMultithread;

use low_ghost\PhpMultithread\Task,
    low_ghost\PhpMultithread\SystemCall,
    SplQueue,
    Generator;

class Scheduler {
    protected $maxTaskId = 0;
    protected $taskMap = []; // taskId => task
    protected $taskQueue;
    private $returnObj;

    public function __construct() {
        $this->taskQueue = new SplQueue();
    }

    public function store($task, $data, $name = false, $parent = false)
    {
        if ($parent){
            if (!isset($this->returnObj[$parent]))
                $this->returnObj[$parent] = [];
            if ($name)
                $this->returnObj[$parent][$name] = $data;
            else
                $this->returnObj[$parent][$task->getTaskId()] = $data;
        } else if ($name){
            $this->returnObj[$name] = $data;
        } else {
            $this->returnObj[$task->getTaskId()] = $data;
        }
    }

    public function newTask(Generator $coroutine) {
        $tid = ++$this->maxTaskId;
        $task = new Task($tid, $coroutine);
        $this->taskMap[$tid] = $task;
        $this->schedule($task);
        return $tid;
    }

    public function schedule(Task $task)
    {
        $this->taskQueue->enqueue($task);
    }

    public function killUnscheduled($task)
    {
        unset($this->taskMap[$task->getTaskId()]);
    }

    public function run() {
        while (!$this->taskQueue->isEmpty()) {
            $task = $this->taskQueue->dequeue();
            $var = $task->run();
            if ($var instanceof SystemCall){
                try {
                    $var($task, $this);
                } catch (Exception $e) {
                    $task->setException($e);
                    $this->schedule($task);
                }
                continue;
            }

            if ($task->isFinished()) {
                unset($this->taskMap[$task->getTaskId()]);
            } else {
                $this->schedule($task);
            }
        }
        return $this->returnObj;
    }
}

?>
