<?php
namespace Src\BindManager;

use Symfony\Component\Filesystem\Filesystem;

abstract class AbstractBindStatistics {
	
	private $xml;
	
	protected function getBindStatisticsXml() {
		if (! ($xml = @file_get_contents($this->config['system']['statsurl'])) === false ) {
			if (! ($this->xml = simplexml_load_string($xml)) === false )
				return true;
		}
		return false;
	}

	/**
	 * Parse statistics elements
	 */
	protected function parseXmlStats() {
		if ( is_object($this->xml) ) {
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
	}

	/**
	 * Parse xml simple values
	 * @param SimpleXMLElement $xml - top element object
	 * @param string $filePrefix
	 * @param string $name - subelement object name (last)
	 */
	private function parseSimpleValues($xml,$filePrefix,$name = NULL) {
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

	/**
	 * Parse xml - '_default' views
	 * @param SimpleXMLElement $xml - top element object
	 * @param string $filePrefix
	 * @param string $name - subelement object name (last)
	 */
	private function parseDefaultViews($xml, $filePrefix, $name) {
		foreach ( $xml as $value ) {
			if ( $value->name == '_default' || $value->attributes()->name == '_default' ) {
				foreach ( $value->$name as $value ) {
					$this->saveStatsToFile($filePrefix, $value->name, $value->counter);
				}
			}
		}
	}
	/**
	 * Create a file with statistic value
	 * @param string $prefix - file prefix
	 * @param string $name - filename
	 * @param string $value
	 */
	private function saveStatsToFile($prefix,$name,$value) {
		$sfs = new Filesystem();
		if (! $sfs->exists( $this->config['system']['statsdir'] ))
			$sfs->mkdir( $this->config['system']['statsdir'] );
		$sfs->dumpFile( $this->config['system']['statsdir'] . DIRECTORY_SEPARATOR . strtolower($prefix.'-'.$name), $value );
	}
	
}