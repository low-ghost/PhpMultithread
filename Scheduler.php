<?php namespace low_ghost\PhpMultithread;

use low_ghost\PhpMultithread\Task,
    low_ghost\PhpMultithread\SystemCall,
    SplQueue,
    Generator;

class Scheduler
{
    protected $maxTaskId = 0;
    protected $taskMap = []; // taskId => task
    protected $taskQueue;
    private $returnObj;
    private $limit;
    public $runningTasks = [];

    public function __construct($limit = 0)
    {
        $this->limit = $limit;
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

    public function newTask(Generator $coroutine)
    {
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

    public function killUnscheduled($task, $tid = null)
    {
        $tid = $task ? $task->getTaskId() : $tid;
        unset($this->taskMap[$tid]);
        if ($this->limit > 0)
            unset($this->runningTasks[$tid]);
    }

    public function run()
    {
        $total = $this->maxTaskId;
        $init = true;
        while (!$this->taskQueue->isEmpty()) {
            $task = $this->taskQueue->dequeue();
            $tid = $task->getTaskId();

            if ($this->limit > 0){
                $init = false;
                if (count($this->runningTasks) < $this->limit && !isset($this->runningTasks[$tid])){
                    $this->runningTasks[$tid] = $task;
                    $init = true;
                }
            }

            $var = $task->run($init);
            if ($var instanceof SystemCall){
                try {
                    $var($task, $this);
                } catch (Exception $e) {
                    $task->setException($e);
                    $this->schedule($task);
                }
                continue;
            }

            if ($task->isFinished()){
                $this->killUnscheduled(false, $tid);
            } else {
                $this->schedule($task);
            }
        }
        return $this->returnObj;
    }
}
?>
