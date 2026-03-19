<?php

use App\routing\Router;

require_once __DIR__ . '/vendor/autoload.php';

// throw new Exception("EXCEPTION DE TESTE");

require_once __DIR__ . '/app/routing/routes.php';
Router::dispatch();
