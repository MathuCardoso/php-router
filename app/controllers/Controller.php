<?php

namespace App\controllers;

class Controller
{
	public function loadView(string $file, array $var = [])
	{
		$fullPath = __DIR__ . "/../../view/{$file}.php";

		if (file_exists($fullPath)) {
			if (!empty($var)) {
				extract($var);
			}
			require_once $fullPath;
			exit;
		}
		http_response_code(404);
    exit("ERRO 404. PÁGINA NÃO ENCONTRADA!");
	}
}
