<?php

namespace App\controllers;

use App\controllers\Controller;

class HomeController extends Controller {

  public function index() {
    $this->loadView("home");
  }
}