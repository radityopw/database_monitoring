<?php

$configLoader = require_once __DIR__.'/configloader.php';

if (!function_exists('config')) {
    /**
     * Get / set the specified configuration value.
     *
     * If an array is passed as the key, we will assume you want to set an array of values.
     *
     * @param  array|string  $key
     * @param  mixed  $default
     * @return mixed
     */
    function config($key = null, $default = null)
    {
        global $configLoader;
        if (is_null($key)) {
            return $configLoader;
        }
        if (is_array($key)) {
            return $configLoader->set($key);
        }
        return $configLoader->get($key, $default);
    }
}

$viewLoader = require_once __DIR__.'/viewloader.php';

if(!function_exists('view')) {
    /**
    * Get the evaluated view contents for the given view.
    *
    * @param  string  $view
    * @param  array   $data
    * @param  array   $mergeData
    * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
    */
    function view($view = null, $data = [], $mergeData = [])
    {
        global $viewLoader;
        if (func_num_args() === 0) {
            return $viewLoader;
        }
        return $viewLoader->make($view, $data, $mergeData);
    }
}