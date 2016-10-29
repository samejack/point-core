<?php

$point['SymbolicName'] = 'PluginE.Child';
$point['Enabled']      = true;
$point['Description']  = 'PHP Point Framework Test Plugin E';
$point['Class-Path']   = array('/src');
$point['Activator']    = 'Activity';
$point['Depends']      = array('PluginD.Parent');

$extension['PluginD.Parent']['Menu'] = array(
    array(
        'Title' => 'PluginD Menu'
    )
);

//$point['Beans'] = array(
//    array(
//        Bean::CLASS_NAME => '\PluginD.Parent\PluginDClass',
//        Bean::INIT_METHOD => array('start'),
//    ),
//);
