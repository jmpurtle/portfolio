<?php
namespace Http\Controllers {

	class ContactController {

		private $context;
		public $contact   = '\\Http\\Controllers\\ContactController';

		public function __construct($context = null) {
			$this->context = $context;
		}

		public function __invoke($path = [], $context = null) {

			return [
				'HTTPStatusCode' => '200',
				'view'           => 'contact/index',
				'title'          => 'John Purtle - Contact'
			];

		}

	}

}
