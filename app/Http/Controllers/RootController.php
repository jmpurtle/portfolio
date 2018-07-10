<?php
namespace Http\Controllers {

	class RootController {

		private $context;
		public $about     = '\\Http\\Controllers\\AboutController';
		public $blog      = '\\Http\\Controllers\\BlogController';
		public $portfolio = '\\Http\\Controllers\\PortfolioController';
		public $contact   = '\\Http\\Controllers\\ContactController';

		public function __construct($context = null) {
			$this->context = $context;
		}

		public function __invoke($path = [], $context = null) {

			return [
				'HTTPStatusCode' => '200',
				'view'           => 'home/index',
				'title'          => 'John Purtle - Web Developer'
			];

		}

		public function maintenance($path = [], $context = null) {

			return [
				'HTTPStatusCode' => '503',
				'Retry-After'    => '3600',
				'view'           => 'error/maintenance'
			];
		
		}

	}

}
