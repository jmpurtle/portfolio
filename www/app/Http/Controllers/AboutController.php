<?php
namespace Http\Controllers {

	class AboutController {

		private $context;

		public function __construct($context = null) {
			$this->context = $context;
		}

		public function __invoke($path = [], $context = null) {

			return [
				'HTTPStatusCode' => '200',
				'view'           => 'about/index',
				'title'          => 'John Purtle - About'
			];

		}

	}

}
