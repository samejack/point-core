<?php

use point\core\Bean;

$point['SymbolicName'] = 'StdPlugin';
$point['Enabled']      = true;
$point['Class-Path']   = array('/src');
$point['Activator']    = 'Activity';

$point['Beans'] = array(
    [
        Bean::CLASS_NAME => '\StdPlugin\MyClass',
        Bean::INIT_METHOD => [
            'init' => ['string!'],
            'inits' => [[1, 2, 3], 'b!']
        ],
    ],
);
