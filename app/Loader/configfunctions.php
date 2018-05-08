<?php

namespace UserDep\Loader;

use Illuminate\Config\Repository;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Load the configuration items from all of the files.
 *
 * @param  string  $configPath
 * @param  \Illuminate\Config\Repository  $repository
 * @return void
 * @throws \Exception
 */
function loadConfigurationFiles(string $configPath, Repository $repository)
{
    $files = getConfigurationFiles($configPath);

    foreach ($files as $key => $path) {
        $repository->set($key, require $path);
    }
    
    return $repository;
}

/**
 * Get all of the configuration files for the application.
 *
 * @param  string  $configPath
 * @return array
 */
function getConfigurationFiles(string $configPath)
{
    $files = [];

    $configPath = realpath($configPath);

    foreach (Finder::create()->files()->name('*.php')->in($configPath) as $file) {
        $directory = getNestedDirectory($file, $configPath);

        $files[$directory.basename($file->getRealPath(), '.php')] = $file->getRealPath();
    }

    ksort($files, SORT_NATURAL);

    return $files;
}

/**
 * Get the configuration file nesting path.
 *
 * @param  \SplFileInfo  $file
 * @param  string  $configPath
 * @return string
 */
function getNestedDirectory(SplFileInfo $file, $configPath)
{
    $directory = $file->getPath();

    if ($nested = trim(str_replace($configPath, '', $directory), DIRECTORY_SEPARATOR)) {
        $nested = str_replace(DIRECTORY_SEPARATOR, '.', $nested).'.';
    }

    return $nested;
}
