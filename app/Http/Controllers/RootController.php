<?php
namespace Http\Controllers {

	class RootController {

		private $context;
		public $about     = '\\Http\\Controllers\\AboutController';
		public $portfolio = '\\Http\\Controllers\\PortfolioController';
		public $news      = '\\Http\\Controllers\\NewsController';
		public $contact   = '\\Http\\Controllers\\ContactsController';

		public function __construct($context = null) {
			$this->context = $context;
		}

		public function __invoke($path = [], $context = null) {

			return [
				'HTTPStatusCode' => '200',
				'view'           => 'home/index',
				'title'          => 'John Purtle - Designer, Developer, Analyst'
			];

		}

		public function maintenance($path = [], $context = null) {

			return [
				'HTTPStatusCode' => '503',
				'Retry-After'    => '3600',
				'view'           => 'error/maintenance'
			];
		
		}

		public function github($path = [], $context = null) {

			return [
				'HTTPStatusCode' => '200',
				'view'           => 'github/index'
			];
		
		}

	}

}
