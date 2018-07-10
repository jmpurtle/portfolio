<?php
namespace Http\Controllers {

	class PortfolioController {

		private $context;

		public function __construct($context = null) {
			$this->context = $context;
		}

		public function __invoke($path = [], $context = null) {

			return [
				'HTTPStatusCode' => '200',
				'view'           => 'portfolio/index',
				'title'          => 'John Purtle - Portfolio'
			];

		}

	}

}
