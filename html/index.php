<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!function_exists('getallheaders'))  {
    function getallheaders()
    {
        if (!is_array($_SERVER)) {
            return array();
        }

        $headers = array();
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }
}

ob_start();
$generationStartTime = microtime(true);

$documentRoot = __DIR__;
$serverRoot   = dirname(__DIR__);
$viewRoot     = $serverRoot . '/src/views/';
$vendorRoot   = $serverRoot . '/vendor';

require_once($serverRoot . '/app/autoload.php');
$config       = require_once($serverRoot . '/app/appEnv.php');

$requestPath  = $_SERVER['REQUEST_URI'];
$path         = explode('?', $requestPath)[0]; //strip QSA from processing path
$path         = explode('/', str_replace($documentRoot, '', $path));
$path         = array_values(array_filter($path));
$queryFields  = isset($_GET) ? $_GET : array();
$postFields   = isset($_POST) ? $_POST : array();

$context = array(
	'debug'       => $config['debug'],
	'config'      => $config,
	'assetRoot'   => $documentRoot . '/assets',
	'request'     => array(
		'path'    => $path,
		'uri'     => $requestPath,
		'method'  => strtolower($_SERVER['REQUEST_METHOD']),
		'query'   => $queryFields,
		'post'    => $postFields,
		'headers' => getallheaders()
	),
	'stats'       => array()
);

$logger = new \Loggers\ScreenLogger();
$router = new \MagnusRouter\Router\Object\ObjectRouter($context, $logger);
$rootObject =  new \Http\Controllers\RootController($context);

$context['logger'] = $logger;

$previous   = null;
$current    = null;
$obj        = $rootObject;
$isEndpoint = false;

foreach ($router($context, $rootObject, $path) as list($previous, $obj, $isEndpoint)) {
	if ($isEndpoint) { break; }
}

$response = [
	'HTTPStatusCode' => '404',
	'view'           => 'error/noResource',
	'context'        => $context
];

$dispatchResponse = [];

//Dispatch, to be factored out into own package
if ($isEndpoint) {

	//Instantiate any class reference returned from routing
	if (!is_object($obj) && !is_array($obj) && class_exists($obj)){

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

	if (is_array($obj)) {
		$dispatchResponse = $obj;
	}

	if (is_array($dispatchResponse)) {

		if ($context['debug'] && $logger) {
	        $logger->debug('Merging array returned from dispatch with response', [
	        	'context' => $context,
	        	'context' => $dispatchResponse
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

	if ($context['debug'] && $logger) {
        $logger->debug('Final server response is', [
        	'context' => $context,
        	'response' => $response
        ]);
	}

}

$serverGenEndTime = microtime(true);
//0.0015 to account for approximate rendering variance
$serverGenTime = (($serverGenEndTime - $generationStartTime + 0.0015)/1000) . ' ms';
$response['context']['stats']['prerender_time'] = $serverGenTime;
//Rendering
require_once $vendorRoot . '/Templating/phptenjin-0.0.2/lib/Tenjin.php';

$layoutFile = ':master';

if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
	/* If it's an AJAX request, we can skip the layout file and simply render the individual view */
	$layoutFile = null;
}

if (strpos($_SERVER['HTTP_ACCEPT'], 'json') !== false) {
    $visualResponse = json_encode($response);
} else {
	$properties = array('postfix'=>'.phtml', 'prefix'=>$viewRoot, 'layout'=>$layoutFile, 'cache'=>false, 'preprocess'=>true);

	$engine = new \Tenjin_Engine($properties);

	$visualResponse = $engine->render(":" . $response['view'], $response);
}

$generationEndTime = microtime(true);
$generationTime = (($generationEndTime - $generationStartTime)/1000) . ' ms';
header('X-Generation-Time: ' . $generationTime);
http_response_code($response['HTTPStatusCode']);

echo $visualResponse;
ob_end_flush();