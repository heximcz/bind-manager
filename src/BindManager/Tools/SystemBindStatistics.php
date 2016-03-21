<?php
/**
 * Parse BIND statistics XML
 *
 * @version 0.1.1-dev
 *
 */

namespace Src\BindManager;

use App\Config\GetYAMLConfig;
use Src\Logger\OutputLogger;
use Exception;

class SystemBindStatistics extends AbstractBindManager {

	private $data;

	public function __construct(GetYAMLConfig $config, OutputLogger $logger) {
		parent::__construct($config, $logger);
	}
	
	/**
	 * Get Bind Statistics from url (xml, json)
	 * @throws \Exception
	 */
	public function getBindStatistics() {
		if (! ($this->data = @file_get_contents($this->config->system['statsurl'])) === false ) {
				return $this;
		}
		throw new Exception( 'Cannot get bind statistics from: ' . $this->config->system['statsurl'] );
	}

	/**
	 * Parse statistics data
	 */
	public function parseStatistics() {
		if ( $this->isJson() ) {
			$this->jsonData();
			return $this;
		}
		if (! ($this->data = simplexml_load_string($this->data)) === false ) {
			$this->xmlData();
			return $this;
		}
		throw new Exception( 'Corrupted statistisc data.' );
	}
	
	protected function jsonData() {
			$bind = new BindJsonStatistics($this->config);
			$this->data = json_decode($this->data);
			$bind->parseJsonStats( $this->data );
	}
	
	protected function xmlData() {
		// usually version 2.x
		if ( !empty($this->data->bind) ) {
			$xmlBindVersion = $this->data->bind->statistics->attributes()->version;
			if ( $xmlBindVersion >= 2 && $xmlBindVersion < 3 ) {
				$bind = new BindXmlStatistics($this->config);
				$bind->parseXmlStatsV2($this->data);
				return $this;
			}
		}
		// usually version 3.x
		if ( !empty($this->data->server) ) {
			$xmlBindVersion = $this->data->attributes()->version;
			if ( $xmlBindVersion >= 3 && $xmlBindVersion < 4 ) {
				$bind = new BindXmlStatistics($this->config);
				$bind->parseXmlStatsV3($this->data);
				return $this;
			}
		}
		throw new Exception('Cannot detect Bind Statistics XML version!');
	}

	private function isJson() {
		json_decode($this->data);
		return ( json_last_error() == JSON_ERROR_NONE );
	}

}
