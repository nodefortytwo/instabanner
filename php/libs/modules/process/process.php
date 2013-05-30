<?php
function process_init(){
	require 'process.class.php';
    
}

function process_routes(){
    $routes = array();
    $routes['process/register'] = array('callback'=>'process_register');
    $routes['process/start'] = array('callback' => 'process_start');
    $routes['process/stop'] = array('callback' => 'process_stop');
    $routes['process/restart'] = array('callback' => 'process_restart');
    $routes['process/log'] = array('callback' => 'process_log');
    $routes['process/container'] = array('callback' => 'process_container');
    //$routes['processes'] = array('callback' => 'process_home', 'menu_title' => 'Processes', 'nav' => array('top'));
    return $routes;
}

function process_home(){
    $page = new Template();
    
    $processes = new ProcessCollection(array());
    $table = $processes->get_table_data();
    $table[0][] = 'Actions';
    foreach($table[1] as $key=>$row){
        $actions = array(
            l('Start', 'process/start/~/' . $row[0], 'btn'),
            l('Restart', 'process/restart/~/' . $row[0], 'btn'),
            l('Stop', 'process/stop/~/' . $row[0], 'btn'),
            l('View Log', 'process/log/~/' . $row[0], 'btn'),
        );
        $row[2] = (isRunning($row[2]) ? $row[2] : '<span style="color:red;">' . $row[2] . '</span>');
        $row[4] = template_date($row[4]);
        $row[] = '<div class="btn-grp">' . implode('', $actions) . '</div>';
        $table[1][$key] = $row;
    }
    
    
    $page->c(template_table($table[0], $table[1]));
    
    return $page->render();
}


function process_register(){
    $processes = exec_hook('process');
    foreach($processes as $mname=>$module){
        foreach($module as $process_id => $process){
            $process['_id'] = $mname . '_' . $process_id;
            $p = new Process($process['_id']);
            foreach($process as $field => $val){
                $p[$field] = $val;
            }
            $p->save();
        }
    }
}

function process_start($id){
    $process = new Process($id);
    $process->start();
    redirect('processes');
}

function process_stop($id){
    $process = new Process($id);
    $process->stop();
    redirect('processes');
}

function process_restart($id){
    $process = new Process($id);
    $process->restart();
    redirect('processes');
}

function process_container($id){
    $process = new Process($id);
    $process->attached = true;

    twitter(twitter_random_token());


    while(1){
        $process->refresh();
        //echo $process->status(), "\n";
        if($process['status'] == 'STOPPING'){
            $process->status('STOPPED');
            echo $process->status(), "\n";
            die();
        }
        
        intervals();
        if(function_exists($process['function'])){
            call_user_func($process['function'], $process);
        }
        $process->log($process['function'] . ' took ' . intervals() . ' Seconds');
        
        
        $process->heartbeat();
        //echo 'Loop Complete, Sleeping', "\n";
        sleep(2);
    }
}

function process_log($id){
    $process = new Process($id);
    $log = file_get_contents($process['outputfile']);
    $log = str_replace("dyld: DYLD_ environment variables being ignored because main executable (/bin/ps) is setuid or setgid\n", "", $log);
    $log = explode("\n", $log);
    $log = array_reverse($log);
    $log = array_slice($log, 0, 100);
    $page = new Template();
    $page->c('<pre>' . implode("\n", $log) . '</pre>');
    return $page->render();   
}
