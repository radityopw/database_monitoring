<?php

namespace UserDep\Loader;

require_once __DIR__.'/configfunctions.php';
use UserDep\Loader\loadConfiguratoinFiles;
use Illuminate\Config\Repository;

$configPath = __DIR__."/../../config";
$configLoader = new Repository($items = []);
$configLoader = loadConfigurationFiles($configPath, $configLoader);

return $configLoader;
