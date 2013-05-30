<?php

class Process extends MongoBase {
    protected $collection = 'process', $pid = 0;
    public $attached = false;
    
    function load_postprocess(){
        $this->get_running();
    }
    
    public function start() {
        if (!$this->running) {
            $this->status('STARTING');
            $this->spawn_process();
        }
    }

    public function restart() {
        $this->stop();
        while ($this->status() != 'STOPPED') {
            $this->refresh();
            sleep(1);
        }
        $this->start();
    }

    public function stop() {
        $this->status('STOPPING');
    }

    public function heartbeat() {
        $this->status('RUNNING');
        $this['heartbeat'] = new MongoDate(time());
        $this->save();
    }

    public function get_running() {
        
        if (!isRunning($this['pid'])) {
            $this['status'] = 'STOPPED';
            $this->save();
        }
        $this->running = isRunning($this['pid']);
        return $this->running;
    }

    function status($new_state = null) {
        $this->refresh();
        if ($new_state && $new_state != $this['status']) {
            if ($this['status'] == 'STOPPING') {
                if ($new_state == 'STOPPED') {
                    $this['status'] = $new_state;
                    $this->save();
                }
            } else {
                $this['status'] = $new_state;
                $this->save();
            }
        }
        return $this['status'];
    }

    function log($string) {
        if ($this->attached) {
            echo $string, "\n";
        }
    }

    protected function spawn_process() {
        $outputfile = $_SERVER['DOCUMENT_ROOT'] . '/' . config('UPLOAD_PATH') . '/' . $this['_id'] . '-output.txt';
        $pidfile = $_SERVER['DOCUMENT_ROOT'] . '/' . config('UPLOAD_PATH') . '/' . $this['_id'] . '-pid.txt';
        $this['outputfile'] = $outputfile;
        $this['pidfile'] = $pidfile;

        if (file_exists($pidfile)) {
            unlink($pidfile);
        }

        $cmd = PHP_BINDIR . "/php " . $_SERVER['DOCUMENT_ROOT'] . "/index.php " . $_SERVER['SERVER_NAME'] . " process/container/~/" . $this['_id'];
        $cmd = sprintf("%s > %s 2>&1 & echo $! >> %s", $cmd, $outputfile, $pidfile);
        exec($cmd);

        $pid = trim(file_get_contents($pidfile));
        $this['pid'] = $pid;
        $this->save();
    }

}

class ProcessCollection extends Collection {
    protected $collection = 'process', $class_name = 'Process';
    protected $default_cols = array(
        'ID' => '_id',
        'Name' => 'name',
        'PID' => 'pid',
        'Status' => 'status',
        'Heartbeat' => 'heartbeat'
    );

}
