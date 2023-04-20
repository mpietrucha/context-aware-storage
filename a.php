<?php

require 'vendor/autoload.php';


use Mpietrucha\Storage\Adapter;

$a = Adapter::create();

dd(
    $a->get('xd'),
    $a->entry('xd'),
    $a->raw('xd')
);
