<?php

namespace UserDep\Loader;

require_once __DIR__.'/configfunctions.php';
use function UserDep\Loader\loadConfigurationFiles as loadConfig;
use Illuminate\Config\Repository;

$configPath = __DIR__."/../../config";
$configLoader = new Repository($items = []);
$configLoader = loadConfig($configPath, $configLoader);

return $configLoader;
