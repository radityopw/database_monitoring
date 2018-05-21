<?php

namespace UserDep\Loader;

use Illuminate\View\FileViewFinder;
use Illuminate\Filesystem\Filesystem;
use Illuminate\View\Engines\PhpEngine;
use Illuminate\View\Engines\FileEngine;
use Illuminate\View\Engines\CompilerEngine;
use Illuminate\View\Engines\EngineResolver;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\View\Factory;
use Illuminate\Events\Dispatcher;

$fileSystem = new Filesystem;

/**
 * Create a new Factory Instance.
 *
 * @param  \Illuminate\View\Engines\EngineResolver  $resolver
 * @param  \Illuminate\View\ViewFinderInterface  $finder
 * @param  \Illuminate\Contracts\Events\Dispatcher  $events
 * @return \Illuminate\View\Factory
 */
function createFactory($resolver, $finder, $events)
{
    return new Factory($resolver, $finder, $events);
}


/**
 * Create the view finder 
 *
 * @return void
 */
function createViewFinder()
{
    global $fileSystem;
    return new FileViewFinder($fileSystem, config('view.paths'));
}


/**
 * Create the engine instance.
 *
 * @return void
 */
function createEngineResolver()
{
    $resolver = new EngineResolver();
    registerFileEngine($resolver);
    registerPhpEngine($resolver);
    registerBladeEngine($resolver);
    return $resolver;
}

/**
 * Register the file engine implementation.
 *
 * @param  \Illuminate\View\Engines\EngineResolver  $resolver
 * @return void
 */
function registerFileEngine($resolver)
{
    $resolver->register('file', function () {
        return new FileEngine;
    });
}

/**
 * Register the PHP engine implementation.
 *
 * @param  \Illuminate\View\Engines\EngineResolver  $resolver
 * @return void
 */
function registerPhpEngine($resolver)
{
    $resolver->register('php', function () {
        return new PhpEngine;
    });
}

/**
 * Register the Blade engine implementation.
 *
 * @param  \Illuminate\View\Engines\EngineResolver  $resolver
 * @return void
 */
function registerBladeEngine($resolver)
{
    global $fileSystem;
    // The Compiler engine requires an instance of the CompilerInterface, which in
    // this case will be the Blade compiler, so we'll first create the compiler
    // instance to pass into the engine so it can compile the views properly.
    $bladeCompiler = new BladeCompiler(
            $fileSystem, config('view.compiled')
    ); 
    $resolver->register('blade', function () use($bladeCompiler){
        return new CompilerEngine($bladeCompiler);
    });
}

/**
 * Create the event implementation
 *
 */
function createEvent()
{
    return new Dispatcher();
}