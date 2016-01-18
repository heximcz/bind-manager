<?php 
namespace Src\Logger;

use Symfony\Component\Console\Output\OutputInterface;
class OutputLogger implements ILogger{
	private $output;
	
	public function __construct(OutputInterface $output){
		$this->output = $output;
	}
	
	public function log($message, $level = self::LEVEL_INFO){
		$this->output->writeln(sprintf('%1$s [%2$s]: %3$s', 
			$level, date("Y-d-m H:i:s"), $message));
	}
}