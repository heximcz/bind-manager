<?php
namespace Src\BindManager;

class BindManager {
	
	private $config;
	
	public function __construct($config) {
		$this->config = $config;
	}
	
	public function updateBind() {
		if ( $this->getRootZone() ) {
			$this->restartBindService();
			$this->testDomainZone();
		}
	}

	public function restartBind() {
		echo "INFO: Restart service.".PHP_EOL;
		$this->restartBindService();
	}

	private function restartBindService() {
		echo "INFO: Use systemctl: ";
		if ($this->config['system']['systemctl'] == 1) {
			echo "OK".PHP_EOL;
			exec("systemctl restart ".$this->config['system']['bindservice']);
		}
		else {
			echo "NO".PHP_EOL;
			exec($this->config['system']['bind-restart']);
		}
		//wait for servis is full started
		sleep(2); //TODO: check over systemctl
	}
	
	private function testDomainZone() {
		echo "INFO: Test dns for domain: ".$this->config['test']['domain'].PHP_EOL;
		if ( !checkdnsrr($this->config['test']['domain'],"A") ) {
			//get back old config file and restart bind
			echo "ERROR: New root zone file is corrupted, revert to old config file.".PHP_EOL;
			rename( $this->config['system']['rzfile'].".bak", $this->config['system']['rzfile']);
			$this->restartBind();
		}
	}
	
	private function getRootZone() {
		// backup original root zone
		echo "INFO: Backup root zones file: " . $this->config['system']['rzfile'].PHP_EOL;
		rename($this->config['system']['rzfile'], $this->config['system']['rzfile'].".bak");
		// get new zones file
		echo "INFO: Get new root zones file from: " . $this->config['source']['url'].PHP_EOL;
		exec("wget -q " . $this->config['source']['url'] . " -O " . $this->config['system']['rzfile'].".new");
		//file_put_contents($this->config['system']['rzfile'].".new", fopen($this->config['source']['url'], 'r'));
		if ( filesize($this->config['system']['rzfile'].".new") ) {
			rename( $this->config['system']['rzfile'].".new", $this->config['system']['rzfile']);
			return true;
		}
		else { 
			echo "ERROR: New zone file is empty, cancel operation.".PHP_EOL;
			unlink($this->config['system']['rzfile'].".new");
			return false;
		}
	}
}