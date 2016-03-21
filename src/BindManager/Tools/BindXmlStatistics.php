<?php
namespace Src\BindManager;

use Symfony\Component\Filesystem\Filesystem;
use App\Config\GetYAMLConfig;
use SimpleXMLElement;

class BindXmlStatistics {

	private $config;

	public function __construct(GetYAMLConfig $config) {
		$this->config = $config;
	}

	/**
	 * Bind 9 XML version 2.x
	 * Parse statistics elements
	 */
	public function parseXmlStatsV2(SimpleXMLElement $xml) {
		if ( is_object($xml) ) {
			// Incoming Queries
			$this->parseSimpleValues( $xml->bind->statistics->server->{'queries-in'}, 'queries-in', 'rdtype' );
			// Incoming Requests
			$this->parseSimpleValues( $xml->bind->statistics->server->requests, 'requests', 'opcode' );
			// Server Statistics
			$this->parseSimpleValues( $xml->bind->statistics->server->nsstat, 'nsstat' );
			// Socket I/O Statistics
			$this->parseSimpleValues( $xml->bind->statistics->server->sockstat, 'sockstat' );
			// Cache DB RRsets for View _default
			$this->parseDefaultViews( $xml->bind->statistics->views->view->cache, 'default-cache-rrsets', 'rrset' );
			// Outgoing Queries for View _default
			$this->parseDefaultViews( $xml->bind->statistics->views->view, 'default-queries-out', 'rdtype' );
		}
	}

	/**
	 * Bind 9 XML version 3.x
	 * Parse statistics elements
	 */
	public function parseXmlStatsV3(SimpleXMLElement $xml) {
		if ( is_object($xml) ) {
			// Incoming Queries
			$this->parseSimpleValuesV3( $xml->server->counters, 'queries-in', 'qtype' );
			// Incoming Requests
			$this->parseSimpleValuesV3( $xml->server->counters, 'requests', 'opcode' );
			// Server Statistics
			$this->parseSimpleValuesV3( $xml->server->counters, 'nsstat', 'nsstat' );
			// Socket I/O Statistics
			$this->parseSimpleValuesV3( $xml->server->counters, 'sockstat', 'sockstat' );
			// Cache DB RRsets for View _default
			$this->parseDefaultViewsCacheV3( $xml->views->view->cache, 'default-cache-rrsets' );
			// Outgoing Queries for View _default
			$this->parseDefaultViewsV3( $xml->views->view, 'default-queries-out', 'resqtype' );
		}
	}

	/**
	 * Parse xml v2.x simple values
	 * @param SimpleXMLElement $xml - top element object
	 * @param string $filePrefix
	 * @param string $name - subelement object name (last)
	 */
	private function parseSimpleValues(SimpleXMLElement $xml, $filePrefix,$name = NULL) {
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
	 * Parse xml v2.x - '_default' views
	 * @param SimpleXMLElement $xml - top element object
	 * @param string $filePrefix
	 * @param string $name - subelement object name (last)
	 */
	private function parseDefaultViews(SimpleXMLElement $xml, $filePrefix, $name) {
		foreach ( $xml as $value ) {
			if ( $value->name == '_default' || $value->attributes()->name == '_default' ) {
				foreach ( $value->$name as $value ) {
					$this->saveStatsToFile($filePrefix, $value->name, $value->counter);
				}
			}
		}
	}

	/**
	 * Parse xml v3.x simple values
	 * @param SimpleXMLElement $xml
	 * @param string $filePrefix
	 * @param string $name
	 */
	private function parseSimpleValuesV3(SimpleXMLElement $xml,$filePrefix,$name) {
		foreach ( $xml as $value ) {
			if ( $value->attributes()->type == $name ) {
				foreach ( $value as $dest ) {
					$this->saveStatsToFile($filePrefix, $dest->attributes()->name, $dest);
				}
			}
		}
	}

	/**
	 * Parse xml v3.x - '_default' views
	 * @param SimpleXMLElement $xml
	 * @param string $filePrefix
	 * @param string $name
	 */
	private function parseDefaultViewsV3(SimpleXMLElement $xml, $filePrefix, $name) {
		foreach ( $xml as $value ) {
			if ($value->attributes ()->name == '_default') {
				foreach ( $value->counters as $type ) {
					if ( $type->attributes()->type == $name) {
						foreach ( $type as $dest )
							$this->saveStatsToFile($filePrefix, $dest->attributes()->name, $dest);
					}
				}
			}
		}
	}

	/**
	 * Parse xml v3.x - '_default' views cache
	 * @param SimpleXMLElement $xml
	 * @param string $filePrefix
	 */
	private function parseDefaultViewsCacheV3(SimpleXMLElement $xml, $filePrefix) {
		foreach ( $xml as $value ) {
			if ( $value->attributes()->name == '_default' ) {
				foreach ( $value as $dest ) {
					$this->saveStatsToFile($filePrefix, $dest->name, $dest->counter);
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
		// change '!' to 'neg-'
		$name = str_replace("!","neg-",$name);
		// change '#' to 'hash-'
		$name = str_replace("#","hash-",$name);
		// prepare filename
		$filename = strtolower($prefix.'-'.$name);
		if (! $sfs->exists( $this->config->system['statsdir'] ))
			$sfs->mkdir( $this->config->system['statsdir'] );
			$sfs->dumpFile( $this->config->system['statsdir'] . DIRECTORY_SEPARATOR . $filename, $value );
	}
	
}
