<?php
/**
 * Parse BIND statistics XML
 *
 * @version 0.1.1-dev
 *
 */

namespace Src\BindManager;

use Symfony\Component\Filesystem\Filesystem;
use Exception;

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
		// usually version 2.x
		if ( !empty($this->xml->bind) ) {
			$xmlBindVersion = $this->xml->bind->statistics->attributes()->version;
			if ( $xmlBindVersion >= 2 && $xmlBindVersion < 3 ) {
				$this->parseXmlStatsV2();
				return;
			}
		}
		// usually version 3.x
		if ( !empty($this->xml->server) ) {
			$xmlBindVersion = $this->xml->attributes()->version;
			if ( $xmlBindVersion >= 3 && $xmlBindVersion < 4 ) {
				$this->parseXmlStatsV3();
				return;
			}
		}
		throw new Exception('Cannot detect Bind Statistics XML version!');
	}

	/**
	 * Bind 9 XML version 2.x
	 * Parse statistics elements
	 */
	protected function parseXmlStatsV2() {
		if ( is_object($this->xml) ) {
			// Incoming Queries
			$this->parseSimpleValues( $this->xml->bind->statistics->server->{'queries-in'}, 'queries-in', 'rdtype' );
			// Incoming Requests
			$this->parseSimpleValues( $this->xml->bind->statistics->server->requests, 'requests', 'opcode' );
			// Server Statistics
			$this->parseSimpleValues( $this->xml->bind->statistics->server->nsstat, 'nsstat' );
			// Socket I/O Statistics
			$this->parseSimpleValues( $this->xml->bind->statistics->server->sockstat, 'sockstat' );
			// Cache DB RRsets for View _default
			$this->parseDefaultViews( $this->xml->bind->statistics->views->view->cache, 'default-cache-rrsets', 'rrset' );
			// Outgoing Queries for View _default
			$this->parseDefaultViews( $this->xml->bind->statistics->views->view, 'default-queries-out', 'rdtype' );
		}
	}

	/**
	 * Bind 9 XML version 3.x
	 * Parse statistics elements
	 */
	protected function parseXmlStatsV3() {
		if ( is_object($this->xml) ) {
			// Incoming Queries
			$this->parseSimpleValuesV3( $this->xml->server->counters, 'queries-in', 'qtype' );
			// Incoming Requests
			$this->parseSimpleValuesV3( $this->xml->server->counters, 'requests', 'opcode' );
			// Server Statistics
			$this->parseSimpleValuesV3( $this->xml->server->counters, 'nsstat', 'nsstat' );
			// Socket I/O Statistics
			$this->parseSimpleValuesV3( $this->xml->server->counters, 'sockstat', 'sockstat' );
			// Cache DB RRsets for View _default
			$this->parseDefaultViewsCacheV3( $this->xml->views->view->cache, 'default-cache-rrsets' );
			// Outgoing Queries for View _default
			$this->parseDefaultViewsV3( $this->xml->views->view, 'default-queries-out', 'resqtype' );
		}
	}

	/**
	 * Parse xml v2.x simple values
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
	 * Parse xml v2.x - '_default' views
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
	 * Parse xml v3.x simple values 
	 * @param SimpleXMLElement $xml
	 * @param string $filePrefix
	 * @param string $name
	 */
	private function parseSimpleValuesV3($xml,$filePrefix,$name) {
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
	private function parseDefaultViewsV3($xml, $filePrefix, $name) {
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
	private function parseDefaultViewsCacheV3($xml, $filePrefix) {
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
		if (! $sfs->exists( $this->config['system']['statsdir'] ))
			$sfs->mkdir( $this->config['system']['statsdir'] );
		$sfs->dumpFile( $this->config['system']['statsdir'] . DIRECTORY_SEPARATOR . $filename, $value );
	}

}
