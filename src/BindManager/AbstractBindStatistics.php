<?php
namespace Src\BindManager;

use Src\Logger\OutputLogger;
use Symfony\Component\Filesystem\Filesystem;


abstract class AbstractBindStatistics {
	
	private $config;
	private $logger;
	private $xml;
	
	public function __construct(array $config, OutputLogger $logger) {
		$this->config = $config;
		$this->logger = $logger;
	}
	
	protected function getBindStatisticsXml() {
		if (! ($xml=file_get_contents($this->config['system']['statsurl'])) === false ) {
			if (! ($this->xml = simplexml_load_string($xml)) === false )
				return true;
		}
		return false;
	}

	/**
	 * Parse all statistics elements
	 */
	protected function parseXmlStats() {
		// Incoming Queries
		$this->parseSimpleValues($this->xml->bind->statistics->server->{'queries-in'}, 'queries-in', 'rdtype');
		// Incoming Requests
		$this->parseSimpleValues($this->xml->bind->statistics->server->requests, 'requests', 'opcode');
		// Server Statistics
		$this->parseSimpleValues($this->xml->bind->statistics->server->nsstat, 'nsstat');
		// Socket I/O Statistics
		$this->parseSimpleValues($this->xml->bind->statistics->server->sockstat, 'sockstat');
		// Cache DB RRsets for View _default
		$this->parseDefaultViews($this->xml->bind->statistics->views->view->cache, 'default-cache-rrsets', 'rrset');
		// Outgoing Queries for View _default
		$this->parseDefaultViews($this->xml->bind->statistics->views->view, 'default-queries-out', 'rdtype');
	}

	/**
	 * parse xml simple values
	 * @param SimpleXMLElement $xml - top element object
	 * @param string $filePrefix
	 * @param string $name - subelement object name (last)
	 */
	private function parseSimpleValues($xml,$filePrefix,$name = NULL) {
		if ( is_object($this->xml) ) {
			if (! is_null($name) ) {
				foreach ( $xml->$name as $value ) {
					$this->saveStatsToFile($filePrefix, $value->name, $value->counter);
				}
				return;
			}
			foreach ( $xml as $value ) {
				$this->saveStatsToFile($filePrefix, $value->name, $value->counter);
			}
		}
	}

	/**
	 * parse xml - '_default' views
	 * @param SimpleXMLElement $name - top element object
	 * @param string $filePrefix
	 * @param string $name - subelement object name (last)
	 */
	private function parseDefaultViews($xml, $filePrefix, $name) {
		if ( is_object($this->xml) ) {
			foreach ( $xml as $value ) {
				if ( $value->name == '_default' ) {
					foreach ( $value->$name as $value ) {
						$this->saveStatsToFile($filePrefix, $value->name, $value->counter);
					}
				}
			}
		}
	}
	
	private function saveStatsToFile($prefix,$name,$value) {
		$sfs = new Filesystem();
		if (! $sfs->exists( $this->config['system']['statsdir'] ))
			$sfs->mkdir( $this->config['system']['statsdir'] );
		$sfs->dumpFile( $this->config['system']['statsdir'] . DIRECTORY_SEPARATOR . strtolower($prefix.'-'.$name), $value );
	}
	
}