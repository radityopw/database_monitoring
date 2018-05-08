<?php 

namespace UserDep\Loader;

use Dotenv\Dotenv;

$envPath = __DIR__.'/../../';

$env = new Dotenv($envPath);

$env->load();
