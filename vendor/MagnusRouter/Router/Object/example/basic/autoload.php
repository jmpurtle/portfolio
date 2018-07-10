<?php
namespace MagnusApp {
	spl_autoload_register(function ($className) {

		$className = str_replace("\\", '/', $className);
		$vendors   = __DIR__ . '/vendor/';
		$repoRoot  = dirname(__DIR__, 4);

		if (file_exists($vendors . $className . ".php")) {
			require_once $vendors . $className . ".php";
		} else if (file_exists(__DIR__ . "/" . $className . ".php")){
			require_once __DIR__ . "/" . $className . ".php";
		} else if (file_exists($repoRoot . "/" . $className . ".php")) {
			require_once $repoRoot . "/" . $className . ".php";
		}
		
	});
}