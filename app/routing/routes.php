<?php

use App\controllers\HomeController;
use App\routing\Router;

Router::get("/", [HomeController::class, "index"]);