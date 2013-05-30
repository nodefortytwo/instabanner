<?php

function slave_init() {
    require 'slave.job.class.php';
}

function slave_routes() {
    $routes = array();
    $routes['slave/run'] = array('callback' => 'slave_run');
    $routes['slave/register'] = array('callback' => 'slave_job_register');
    //$routes['slave/jobs'] = array('callback' => 'slave_jobs', 'nav' => array('top'), 'menu_title' => 'Cron Jobs');
    $routes['slave/job/run'] = array('callback' => 'slave_job_run');
    return $routes;
}

function slave_home() {
    $page = new Template();

    return $page->render();
}

//hook_process
function slave_process(){
    $ps = array();
    $ps['slave_main'] = array(
        'name' => 'slave',
        'function' => 'slave_run'
    );
    return $ps;
}

function slave_run($process = null){
    //get all jobs
    //$process->log('logging from slave_run');
    $jobs = new SlaveJobCollection(array());
    foreach($jobs as $job){
        $job->set_process($process);
        $job->run();
    }
    if($process){
        //$process->log('Slave Run');
    }
}

function slave_job_register(){

    $existing_jobs = new SlaveJobCollection(array());
    $existing_jobs = $existing_jobs->ids;
    
    $jobs = exec_hook('job');
    foreach($jobs as $mname=>$module){
        foreach($module as $job_id => $job){
            $job['_id'] = $mname . '_' . $job_id;

            $index = array_search($job['_id'], $existing_jobs);
            if($index !== false){
                unset($existing_jobs[$index]);
            }

            $j = new SlaveJob($job['_id']);
            
            foreach($job as $key=>$val){
                $j[$key] = $val;
            }
            $j->save();
        }
    }

    foreach($existing_jobs as $job){
        $j = new SlaveJob($job);
        $j->delete();
        message($job['_id'] . ' Has been removed');
    }
}

function slave_job_run($jib){
    $job = new SlaveJob($jib);
    $job->run(true);
    if(headers_sent()){
        die('Job Complete');
    }else{
        message($jib . ' completed');
        redirect('slave/jobs/');
    }
}

function slave_jobs(){
    slave_job_register();
    $page = new Template();
    $jobs = new SlaveJobCollection(array());

    $jobs = $jobs->get_table_data();
    $jobs[0][] = 'actions';
    foreach($jobs[1] as &$row){

        if($row[1] < 61){
            $row[1] = $row[1] . 's';
        }elseif($row[1] < (60*60+1)){
            $row[1] = $row[1] / 60 . ' mins';
        }else{
            $row[1] = $row[1] / (60 * 60) . ' hours';
        }

        $row[] = l('Run', '/slave/job/run/~/' . $row[0], 'btn');
    }


    $page->c(template_table($jobs[0], $jobs[1]));
    return $page->render();
}
