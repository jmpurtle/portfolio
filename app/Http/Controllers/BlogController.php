<?php
namespace Http\Controllers {

	class BlogController {

		private $context;

		public function __construct($context = null) {
			$this->context = $context;
		}

		public function __invoke($path = [], $context = null) {

			return [
				'HTTPStatusCode' => '200',
				'view'           => 'blog/index',
				'title'          => 'John Purtle - Blog'
			];

		}

	}

}
