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

	private $xml;

	public function __construct(GetYAMLConfig $config, OutputLogger $logger) {
		parent::__construct($config, $logger);
	}
	
	/**
	 * Get Bind Statistics from url (xml, json)
	 * @throws \Exception
	 */
	public function getBindStatisticsXml() {
		if (! ($xml = @file_get_contents($this->config->system['statsurl'])) === false ) {
			if (! ($this->xml = simplexml_load_string($xml)) === false )
				return $this;
		}
		throw new Exception( 'Cannot get bind statistics from: ' . $this->config->system['statsurl'] );
	}

	/**
	 * Parse statistics elements
	 */
	public function parseXmlStats() {
		// usually version 2.x
		if ( !empty($this->xml->bind) ) {
			$xmlBindVersion = $this->xml->bind->statistics->attributes()->version;
			if ( $xmlBindVersion >= 2 && $xmlBindVersion < 3 ) {
				$bind = new BindXmlStatistics($this->config);
				$bind->parseXmlStatsV2($this->xml);
				return $this;
			}
		}
		// usually version 3.x
		if ( !empty($this->xml->server) ) {
			$xmlBindVersion = $this->xml->attributes()->version;
			if ( $xmlBindVersion >= 3 && $xmlBindVersion < 4 ) {
				$bind = new BindXmlStatistics($this->config);
				$bind->parseXmlStatsV3($this->xml);
				return $this;
			}
		}
		throw new Exception('Cannot detect Bind Statistics XML version!');
	}


}
