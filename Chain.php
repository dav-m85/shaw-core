<?php

/**
 * Implements a chain. Act as an endpoint. Use run to start the chain.
 * @author david
 */
class Sme_Chain
extends Sme_Chain_AbstractLink
{
	public function run()
	{
		do{
			$data = $this->source();
			$this->sink($data); // Send false across chain back once
		}
		while($data !== false);
	}
}
