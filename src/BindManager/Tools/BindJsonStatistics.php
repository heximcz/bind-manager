<?php

namespace Src\BindManager;

use stdClass;

class BindJsonStatistics {

	private $config;
	
	public function __construct($config) {
		$this->config = $config;
	}

	public function parseJsonStats(stdClass $json) {
		var_dump($json);
	}
	
}