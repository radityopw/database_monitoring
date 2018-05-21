<?php

namespace UserDep\Loader;

require_once __DIR__.'/viewfunctions.php';
use function UserDep\Loader\createFactory;
use function UserDep\Loader\createViewFinder;
use function UserDep\Loader\createEngineResolver;
use function UserDep\Loader\createEvent;

$resolver = createEngineResolver();
$finder = createViewFinder();
$events = createEvent();
$viewLoader = createFactory($resolver, $finder, $events);
return $viewLoader;