<?php
namespace Controllers {

	class RootController {

		private $context;
		public  $people = '\\Controllers\\PeopleController';

		public function __construct($context = null) {
			$this->context = $context;
		}

		public function __invoke($context = null) {

			return 'I am the origin of all';

		}

	}

}