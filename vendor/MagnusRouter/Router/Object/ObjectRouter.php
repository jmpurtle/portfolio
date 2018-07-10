<?php
namespace MagnusRouter\Router\Object {
	
	class ObjectRouter {

		public $logger;

		public function __construct($context, $logger = null) {

			$this->logger = $logger;

			if (isset($context['debug']) && $context['debug'] && $this->logger) {
				$this->logger->debug('Object Router initialized');
			}

		}

		public function routeIterator(&$path) {

			/* Iterate through the path, popping elements from the left as they are seen. */

			$last = null;

			while ($path) {
				yield [$last, $path[0]];
				$last = array_shift($path);

				/* By shifting elements after they're yielded, we avoid having to put back a value in the event of a
				 * readjustment in the routing path performed by the __construct() method in route objects. Our testing
				 * has shown that direct array maninpulation is more performant than implementing SPLDoublyLinkedList
				 * for deque behavior. Likewise, just tracking the array index is a bit slower and adds complexity
				 * when dealing with reorients or reroutes.
				 */
			}

		}

		public function __invoke($context, $obj, Array $path) {

			$previous   = null;
			$current    = null;
			$isEndpoint = false;

			if ($context['debug'] && $this->logger) {
				$this->logger->debug('Start the routing');
			}

			$routeIterator = $this->routeIterator($path);

			foreach ($routeIterator as list($previous, $current)) {

				if ($context['debug'] && $this->logger) {
					$this->logger->debug('Start routing on ' . $current);
				}

				if (!is_object($obj)) {

					if ($context['debug'] && $this->logger) {
						$this->logger->debug('Provided is not currently an object', [
							'object reference' => $obj,
							'context'          => $context,
							'current'          => $current
						]);
					}

					if (class_exists($obj)) {

						if ($context['debug'] && $this->logger) {
							$this->logger->debug('Instantiating object reference', [
								'object reference' => $obj,
								'context'          => $context,
								'current'          => $current
							]);
						}

						$obj = new $obj($context);

					} else {

						if ($context['debug'] && $this->logger) {
							$this->logger->debug('Refusing to descend on non-objects, must be a static endpoint or function.', [
								'passed'  => $obj,
								'context' => $context,
								'current' => $current
							]);
						}

						yield [$previous, $obj, true];
						return;

					}

				}

				/* Methods using this router are assumed to be endpoints
				 * 
				 * By uaing get_class_methods, we provide a timing safe collection of available methods.
				 * In addition to this, only public methods are exposed which prevents attempts against
				 * private or protected methods designed for internal functionality.
				 */

				if (in_array($current, get_class_methods($obj))) {

					if ($context['debug'] && $this->logger) {
						$this->logger->debug('Found an endpoint', [
							'context'    => $context,
							'isEndpoint' => true,
							'parent'     => var_export($obj, true),
							'handler'    => $current,
							'path'       => var_export($path, true)
                        ]);
                    }

					// Since we found an endpoint, we'll break out of this loop early and yield values after loop
					$isEndpoint = true;
					break;

				}

				/* No methods, huh? Let's check the public properties for controller references or static values.
				 * Like before, we make use of get_object_vars to obtain a safe set of values to check against.
				 */

				if (array_key_exists($current, get_object_vars($obj))) {
                    
                    if ($context['debug'] && $this->logger) {
                        
                        $this->logger->debug('Found a property: ' . $current, [
                            'context' => $context,
                            'source'  => $obj,
                            'current' => $current,
                            'value'   => var_export($obj->$current, true)
                        ]);

                    }

                    yield [$previous, $obj, $isEndpoint];
                    $obj = $obj->$current;
                    continue;

                }

                /* Didn't find a reference or a static asset, might be a variable path element.
                 * Variable path elements are often denoted by {variable} in API documents. Such as {id} for
                 * selecting a specific ID version of a resource. It's expected by the dispatch protocol for
                 * controllers to implement __get for these variable path elements. __get will often times
                 * return a resource controller initialized with that specific ID. For example, in a
                 * PhotosController, the __get method would return a PhotoController($context, {id})
                 * For API clients that may not support altering the HTTP method beyond GET and POST, controllers
                 * may listen in __get for specific HTTP methods like 'put', 'patch', 'delete' and handle it
                 * accordingly.
                 */

                if (method_exists($obj, '__get')) {
                    /* We'll check if we can emulate getattr via __get and approach it that way. */
                    if ($context['debug'] && $this->logger) {
                        
                        $this->logger->debug('Using __get() to recover: ' . $current, [
                            'context' => $context,
                            'source'  => $obj,
                            'current' => $current
                        ]);

                    }

                    $obj = $obj->__get($current);
                    yield [$previous, $obj, $isEndpoint];
                    continue;

                }

                /* If we're this far down, there's no reasonable way for us to descend further via object descent.
                 * If the path dictates it should go further, this is handled up on the application layer. Typically
                 * through inspecting the dispatch value of the controller. For example, a specific controller may
                 * require HTTPRouter be used to access the properties. We'll break out of the loop and hand it on down
                 */

                break;

			}

			if ($routeIterator->valid()) {

				/* We bailed out of the loop early for whatever reason, so obj is our last known attribute, previous
				 * is the path element matching obj, and obj is the current object .
				 */

				if ($context['debug'] && $this->logger) {
                    $this->logger->debug("Routing interrupted while attempting to resolve attribute: $current", [
                        'context'   => $context,
                        'handler'   => var_export($obj, true),
                        'endpoint'  => $isEndpoint,
                        'previous'  => $previous,
                        'attribute' => $current
                    ]);
                }

                /* If it's an endpoint, we want to hand the object and its method back up to the application layer
                 * to dispatch upon instead of previous
                 */
                if ($isEndpoint) {
                	$previous = $current;
                }

                // Turn everything over to the application layer to decide what to do with it.
                yield [$previous, $obj, $isEndpoint];
                return;

			}

			/* This is it, we have nothing else to iterate on in the request. This is often indicative of finding
			 * an invokable controller rather than an endpoint. If there's no invocation allowed, chances are, a
			 * different routing methodology is required to execute upon said controller. We'll leave it up to the
			 * application layer to decide.
			 */

			if (!is_object($obj)) {
				//We have a static endpoint value that should be passed along to the application layer
				$isEndpoint = true;
			} else {
				$isEndpoint = in_array('__invoke', get_class_methods($obj));
			}

			if ($context['debug'] && $this->logger) {
                $this->logger->debug("Terminated normally", [
                    'context'   => $context,
                    'handler'   => var_export($obj, true),
                    'endpoint'  => $isEndpoint,
                    'previous'  => $previous,
                    'attribute' => $current
                ]);
            }

            yield [$previous, $obj, $isEndpoint];
            return;

		}

	}

}