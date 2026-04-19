<?php

declare(strict_types=1);

use App\Core\App;
use App\Core\Autoloader;

require_once dirname(__DIR__) . '/app/Core/Autoloader.php';

(new Autoloader(dirname(__DIR__) . '/app'))->register();

App::boot(dirname(__DIR__))->run();

