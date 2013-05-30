<?php

class SlaveJob extends MongoBase{
    protected $collection = 'job';    
    
    function get_lastrun(){
        if(!isset($this['lastrun'])){
            $this['lastrun'] = new MongoDate(0);
        }
        $this->lastrun = $this['lastrun'];
        return $this->lastrun;
    }
    
    function run($force = false){

        $since_run = time() - $this->lastrun->sec;
        if($this->process){
            $this->process->log($this['function'] . ' - ' . $since_run . 's from last run');
        }   
        if((time() - $this->lastrun->sec) > $this['frequency'] || $force){
            if(function_exists($this['function'])){
                if($this->process){
                    $this->process->log('Running ' . $this['function']);
                }
                if(!isset($this['args'])){
                    $this['args'] = array();
                }
                
                //stupid overloaded properties
                $args = $this['args'];
                array_unshift($args, $this->process);
                
                call_user_func_array($this['function'], $args);
                $this['lastrun'] = new MongoDate(time());
                $this->save();
            }else{
                if($this->process){
                    $this->process->log($this['function'] . ' does not exist');
                }
            }
            return true;    
        }else{
            return false;
        }
    }
    
    function get_process(){
        $this->process = null;
    }
    
    function set_process($process){
        $this->process = $process;
    }
}

class SlaveJobCollection extends Collection{
    protected $collection = 'job', $class_name ='SlaveJob';
    public $default_cols = array(
            'ID' => '_id',
            'Frequency' => 'frequency',
            'Callback' => 'function',
            'Last Run' => 'lastrun'
        );
    
}
