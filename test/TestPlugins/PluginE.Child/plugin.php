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
