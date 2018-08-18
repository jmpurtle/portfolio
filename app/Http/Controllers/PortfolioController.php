<?php
namespace Http\Controllers {

	class PortfolioController {

		private $context;
		private $availablePieces = array(
					'mli' => array(
						'view'  => 'portfolio/mli',
						'title' => 'Michelin Learning Institute'
					),
					'smart-coasters' => array(
						'view'  => 'portfolio/smart-coaster',
						'title' => 'Smart Coasters'
					)
				);

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

		public function __get($portfolioPiece) {
			if (isset($this->availablePieces[$portfolioPiece])) {
				return [
					'HTTPStatusCode' => '200',
					'view'           => $this->availablePieces[$portfolioPiece]['view'],
					'title'          => 'John Purtle - ' . $this->availablePieces[$portfolioPiece]['title'] 
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
