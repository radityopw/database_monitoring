<?php

namespace UserDep\Components;

require_once __DIR__.'/../index.php';

$array = [
    'hello' => "Hello World",
];

return response(view("test", $array))->send();

// return view("test", $array);