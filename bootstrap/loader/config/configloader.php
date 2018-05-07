<?php

require_once __DIR__.'/configfunctions.php';
use Illuminate\Config\Repository;

$configPath = __DIR__."/../../../config";



$configLoader = new Repository($items = []);
$configLoader = loadConfigurationFiles($configPath, $configLoader);

return $configLoader;