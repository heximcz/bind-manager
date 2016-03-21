<?php

namespace Src\BindManager;

use Symfony\Component\Filesystem\Filesystem;

class StatisticsFileMaker {
	
	/**
	 * Create a file with statistic value
	 * @param string $prefix - file prefix
	 * @param string $name - filename
	 * @param string $value
	 */
	protected function saveStatsToFile($prefix,$name,$value) {
		$sfs = new Filesystem();
		// change '!' to 'neg-'
		$name = str_replace("!","neg-",$name);
		// change '#' to 'hash-'
		$name = str_replace("#","hash-",$name);
		// change '+' to '-plus'
		$name = str_replace("+","-plus",$name);
		// prepare filename
		$filename = strtolower($prefix.'-'.$name);
		if (! $sfs->exists( $this->config->system['statsdir'] ))
			$sfs->mkdir( $this->config->system['statsdir'] );
		$sfs->dumpFile( $this->config->system['statsdir'] . DIRECTORY_SEPARATOR . $filename, $value );
	}
	
}