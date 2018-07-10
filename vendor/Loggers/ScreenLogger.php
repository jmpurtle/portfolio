<?php
namespace Loggers {
	
	class ScreenLogger {

		public $loglevel = 'warn';
		
		public function debug($message, $data = []) {
			echo "Timestamp: " . microtime() . " Log: " . $message . "<br>" . var_export($data, true) . "<hr>";
		}

	}

}