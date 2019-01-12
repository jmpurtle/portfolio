<?php
namespace Brigid {

	class Smith {
		/* Smith takes a list of data for blocks and translates them into the appropriate
		 * blocks for consumption via rendering engine. Keep in mind, Brigid does -not-
		 * attempt to resolve any content unless they happen to be blocks themselves.
		 * Resolution of block contents (for example, placing data from the database into
		 * the generated markup) is the responsibility of your rendering engine.
		 */

		public $logger;

		public function __construct($context, $logger = null) {

			$this->logger = $logger;

			if (isset($context['debug']) && $context['debug'] && $this->logger) {
				$this->logger->debug('Brigid Smith initialized');
			}

		}

		public function blockIterator(&$blocks) {

			/* Iterate through the list of blocks, popping elements from the left as they are seen. */

			while ($blocks) {
				yield $blocks[0];
				array_shift($blocks);

				/* By shifting elements after they're yielded, we avoid having to put back a block in the event
				 * of changing widget ownerships. This consideration allows for Brigid to hand off the remaining
				 * blocks to a different widget construction engine as necessary.
				 * Our testing has shown that direct array maninpulation is more performant than implementing
				 * SPLDoublyLinkedList for deque behavior. Likewise, just tracking the array index is a bit
				 * slower and adds complexity when dealing with handoffs.
				 */
			}

		}

		public function __invoke($context, Array $blocks) {

			if ($context['debug'] && $this->logger) {
				$this->logger->debug('Start Brigid smithing process');
			}

			$blockIterator = $this->blockIterator($blocks);

			foreach ($blockIterator as $block) {

				if ($context['debug'] && $this->logger) {
					$this->logger->debug('Current block metadata: ' . htmlentities(var_export($block, true)));
				}

				$blockType = "\\Brigid\\Components\\" . $block['block-type'];
				$blockObj = new $blockType($block);
				if ($context['debug'] && $this->logger) {
					$this->logger->debug('Current block as obj: ' . htmlentities(var_export($blockObj, true)));
				}
				yield $blockObj;
			}

		}


	}

}