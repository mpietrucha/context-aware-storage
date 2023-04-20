<?php

require 'vendor/autoload.php';

use Mpietrucha\Storage\Adapter;

$a = Adapter::create();

$a->put('xd', fn () => 'xd');

dd($a->get('xd'));
