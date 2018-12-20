<?php
namespace Http\Controllers {

	class NewsController {

		private $context;
		private $availablePieces = array(
					'web-lab-mutation-observer' => array(
						'view'  => 'news/web-lab-mutation-observer',
						'title' => 'Web Laboratory: Observing Mutations'
					)
				);

		public function __construct($context = null) {
			$this->context = $context;
		}

		public function __invoke($path = [], $context = null) {

			return [
				'HTTPStatusCode' => '200',
				'view'           => 'news/index',
				'title'          => 'John Purtle - News'
			];

		}

		public function __get($articleSlug) {
			if (isset($this->availablePieces[$articleSlug])) {
				return [
					'HTTPStatusCode' => '200',
					'view'           => $this->availablePieces[$articleSlug]['view'],
					'title'          => 'John Purtle - ' . $this->availablePieces[$articleSlug]['title'] 
				];
			}

			return [
				'HTTPStatusCode' => '404',
				'view'           => 'error/noResource',
				'title'          => 'John Purtle - Not Found'
			];
			
		}

	}

}
