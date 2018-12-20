<?php
namespace Http\Controllers {

	class ContactsController {

		private $context;

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

		public function lead($path = [], $context = null) {

			if ($this->context['request']['method'] == 'post') {

				$contactModel = new \Database\Models\ContactModel($this->context['config']['dbConfig']);
				$contactModel->create($this->context['request']['post']);
				$contactModel->save();

				return [
					'HTTPStatusCode' => '200',
					'view'           => 'contact/thanks',
					'title'          => 'John Purtle - Contact'
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
