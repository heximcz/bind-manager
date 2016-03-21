<?php
namespace Src\BindManager;

use Src\Logger\OutputLogger;
use App\Config\GetYAMLConfig;

use Exception;
/*
 * TODO:
 * Better check input params from config file
 * 1. check if bindservice: (systemctl) exist in the system - if systemctl: = 1
 * 2. check if bind-restart: path exist - if systemctl: != 1
 */

class BindManager extends AbstractBindManager implements IBindManager {
	
	public function __construct(GetYAMLConfig $config, OutputLogger $output) {
		parent::__construct($config, $output);
	}
	
	public function updateBind() {
		if ( $this->getRootZone() ) {
			$this->restartBindService();
			$this->testDomainZone();
			$this->checkErrorEmail();
		}
	}

	public function restartBind() {
		$this->logger->log("Restart service only.");
		$this->restartBindService();
		$this->testDomainZone();
		$this->checkErrorEmail();
	}
	
	public function createBindStatistics() {
		if (! $this->getBindStatisticsXml() )
			throw new Exception( 'Cannot get bind statistics from: ' . $this->config->system['statsurl'] );
		$this->logger->log("Create statistics.");
		$this->parseXmlStats();
		$this->logger->log("Done.");
	}

}