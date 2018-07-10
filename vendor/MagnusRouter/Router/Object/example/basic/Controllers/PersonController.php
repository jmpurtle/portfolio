<?php
namespace Controllers {

	class PersonController {

		private $context;
		public  $name;

		public function __construct($context, $name) {
			$this->context = $context;
			$this->name    = $name;
		}

		public function __invoke($context = null) {

			return 'Hello, my name is ' . $this->name . '. Nice to meet you!';

		}

	}

}