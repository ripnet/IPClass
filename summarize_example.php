<?php

require "IP.class.php";
require "Summarize.class.php";

/*
 * Pull from file
 */
$subnets_to_summarize = explode("\n", trim(file_get_contents('s2')));

// or create your own array
/*
$subnets_to_summarize = array(
    '192.168.1.0/24',
    '192.168.2.0/24',
    '192.168.4.0/24',
    '192.168.3.0/24',
    '10.0.0.32/32',
);*/

$s = new Summarize($subnets_to_summarize);
foreach ($s->getSubnets() as $subnet)
    printf("%s\n", $subnet);