<?php

namespace UserDep\Components;

require_once __DIR__.'/../index.php';

$array = [
    'hello' => "Hello World",
];
return view("test", $array)->render();

// return view("test", $array);