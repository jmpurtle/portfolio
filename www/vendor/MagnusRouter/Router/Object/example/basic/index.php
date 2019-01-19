<?php

require_once 'autoload.php'; //application autoloader

/* Request parsing, assuming HTTP requests */
$webRoot      = str_replace($_SERVER['DOCUMENT_ROOT'], '', __DIR__);
$requestPath  = $_SERVER['REQUEST_URI'];
$requestPath  = explode('/', str_replace($webRoot, '', $requestPath));
$requestPath  = array_values(array_filter($requestPath));
$assetRoot    = $webRoot . '/assets';

$context = [
	'debug' => false
];

$logger     = new \Loggers\ScreenLogger();
$router     = new \Router\Object\ObjectRouter($context, $logger);
$rootObject =  new \Controllers\RootController($context);

$previous   = null;
$current    = null;
$obj        = $rootObject;
$isEndpoint = false;

foreach ($router($context, $rootObject, $requestPath) as list($previous, $obj, $isEndpoint)) {
	if ($isEndpoint) { break; }
}

//Response preparation
$response = ['view' => 'error/noResource', 'context' => $context, 'assetRoot' => $assetRoot];
$dispatchResponse = null;

//Dispatch, to be factored out into own package
if ($isEndpoint) {

	//Instantiate any class reference returned from routing
	if (!is_object($obj) && class_exists($obj)){

		if ($context['debug'] && $logger) {
            $logger->debug('Instantiating routed class reference', [
                'context' => $context,
                'current' => $obj
            ]);
            
        }

		$obj = new $obj($context);

	}

	if (is_object($obj)) {

		if (in_array($previous, get_class_methods($obj))) {

			if ($context['debug'] && $logger) {
		        $logger->debug('Generating dispatch response from obj->method', [
		        	'context' => $context,
		            'obj'     => $obj,
		            'method'  => $previous
		        ]);
		        
        	}

			$dispatchResponse = $obj->$previous();

		} else if (in_array('__invoke', get_class_methods($obj))) {

			if ($context['debug'] && $logger) {
		        $logger->debug('Generating dispatch response from obj->__invoke', [
		        	'context' => $context,
		            'obj'     => $obj
		        ]);
		        
        	}

			$dispatchResponse = $obj->__invoke();

		}

	}

	if (is_array($dispatchResponse)) {

		if ($context['debug'] && $logger) {
	        $logger->debug('Merging array returned from dispatch with response', [
	        	'context' => $context,
	        ]);
    	}

		$response = array_merge($response, $dispatchResponse);

	} else if ($dispatchResponse instanceof \Traversable) {
		/* for sake of simplicity, we assume generators do not emit anything but arrays. In the future, generators may 
		 * yield static elements, functions, objects, generators and more.
		 */
		if ($context['debug'] && $logger) {
	        $logger->debug('Generator returned from dispatch, iterating to build response', [
	        	'context' => $context,
	        ]);
    	}

		foreach ($dispatchResponse() as $chunk) {
			$response = array_merge($response, $chunk);
		}

	}

}

if (!is_null($dispatchResponse)) { echo var_export($dispatchResponse, true) . '<br>'; }
