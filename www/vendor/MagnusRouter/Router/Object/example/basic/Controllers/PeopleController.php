<?php
namespace Controllers {

	class PeopleController {

		private $context;

		public function __construct($context = null) {
			$this->context = $context;
		}

		public function __invoke($context = null) {

			return 'Vox Populi, we are the people';

		}

		public function __get($name) {
			return new PersonController($this->context, $name);
		}

	}

}