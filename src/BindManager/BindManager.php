<?php
namespace Src\BindManager;

use Src\Logger\OutputLogger;
use Src\Logger\ILogger;

class BindManager {
	
	private $config;
	private $logger;
	private $user;
	private $group;
	private $permission;
	
	public function __construct($config, $output) {
		$this->config = $config;
		$this->logger = new OutputLogger($output);
	}
	
	public function updateBind() {
		if ( $this->getRootZone() ) {
			$this->restartBindService();
			$this->testDomainZone();
		}
	}

	public function restartBind() {
		$this->logger->log("Restart service only.");
		$this->restartBindService();
		$this->testDomainZone();
	}

	private function restartBindService() {
		$message = "Use systemctl: ";
		// systemctl
		if ($this->config['system']['systemctl'] == 1) {
			$message .= "YES";
			$this->logger->log($message);
			exec("systemctl restart ".$this->config['system']['bindservice']);
			// check for 5 times if bind service is running
			for ( $i=0; $i<5; $i++ ) {
				sleep(1);
				if ( $this->testBindService() ) {
					$lastState = true;
					break;
				}
				else $lastState = false;
			}
			if (!$lastState)
				$this->logger->log("BIND service not running !!!: ",ILogger::LEVEL_ERROR);
		}
		// old method
		else {
			$message .= "NO!";
			$this->logger->log($message);
			exec($this->config['system']['bind-restart']);
			sleep(4);
		}
	}
	
	private function testDomainZone() {
		$this->logger->log("Test dns for domain: ".$this->config['test']['domain']);
		if ( !checkdnsrr($this->config['test']['domain'],"A") ) {
			// exist backup file ?
			if ( file_exists ($this->config['system']['rzfile'].".bak") ) {
				//get back old zone file and restart bind
			    $this->logger->log("New zone file is bad, revert to old zones from backup!",ILogger::LEVEL_ERROR);
				copy( $this->config['system']['rzfile'].".bak", $this->config['system']['rzfile']);
				$this->restartBind();
			}
			else
				$this->logger->log("Backup zone file is not exist!",ILogger::LEVEL_ERROR);
		}
		else 
			$this->logger->log("Test passed.");
	}
	
	private function testBindService() {
		$substate = shell_exec( "systemctl show ".$this->config['system']['bindservice']." -p SubState" );
		$result   = shell_exec( "systemctl show ".$this->config['system']['bindservice']." -p Result" );
		$statusA  = explode( "=", trim($substate) );
		$statusB  = explode( "=", trim($result) );
		
		if (!$statusA[1]=="running" || !$statusB[1]=="success") return false;
		else return true;
	}
	
	private function getRootZone() {
		// get original file user:group and permissions
		$this->getUserGroupPerm();
		// backup original root zone
		$this->logger->log("Backup root zones file: " . $this->config['system']['rzfile']);
		copy($this->config['system']['rzfile'], $this->config['system']['rzfile'].".bak");
		// get new zones file
		$this->logger->log("Get new root zones file from: " . $this->config['source']['url']);
		exec("wget -q " . $this->config['source']['url'] . " -O " . $this->config['system']['rzfile'].".new");
		if ( filesize($this->config['system']['rzfile'].".new") ) {
			rename( $this->config['system']['rzfile'].".new", $this->config['system']['rzfile']);
			// set original user:group and permissions
			$this->setUserGroupPerm();
			return true;
		}
		else { 
		    $this->logger->log("New zone file is empty, cancel operation.",ILogger::LEVEL_ERROR);
			unlink($this->config['system']['rzfile'].".new");
			return false;
		}
	}
	
	private function getUserGroupPerm() {
		if (! $this->group = filegroup($this->config['system']['rzfile']) )
			$this->logger->log("Get group of file: " . $this->config['system']['rzfile'], ILogger::LEVEL_ERROR);
		if (! $this->user = fileowner($this->config['system']['rzfile']) )
			$this->logger->log("Get user of file: " . $this->config['system']['rzfile'], ILogger::LEVEL_ERROR);
		if (! $this->permission = fileperms($this->config['system']['rzfile']) )
			$this->logger->log("Get permission of file: " . $this->config['system']['rzfile'], ILogger::LEVEL_ERROR);
	}
	
	private function setUserGroupPerm() {
		if (!chown ( $this->config['system']['rzfile'], $this->user ) )
			$this->logger->log("Change user of file: " . $this->config['system']['rzfile'], ILogger::LEVEL_ERROR);
		if (!chgrp ( $this->config['system']['rzfile'], $this->group ) )
			$this->logger->log("Change group of file: " . $this->config['system']['rzfile'], ILogger::LEVEL_ERROR);
		if (!chmod( $this->config['system']['rzfile'], $this->permission ) )
			$this->logger->log("Change permission of file: " . $this->config['system']['rzfile'], ILogger::LEVEL_ERROR);
	}
	
}