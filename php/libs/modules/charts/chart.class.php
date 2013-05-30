<?php

class Chart{
    public $type, $args;
    function __construct($type='line', $args){
        $this->type = $type;
        $this->args = $args;
    }
    
    function render(){
        
        $script = new Template(false);
        $script->load_template('templates/linechart.js', 'charts');
        
        $series = ($this->args['series']);
        $vars = $this->args;
        
        foreach($vars as $var=>$val){
            $vars[$var] = json_encode($val);
        }
        
        $script->add_variable($vars);
        //die($series);
        
        //var_dump($script->render());
        //die();
        
        
        $html = '<div id="'.$this->args['id'].'" style="min-width: 400px; height: 400px; margin: 0 auto"></div>';
        
        return array($script->render(), $html);
    }
    
}
