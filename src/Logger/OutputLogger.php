<?php 
namespace Src\Logger;

use Symfony\Component\Console\Output\OutputInterface;
use Src\Logger\AbstractMailLogger;

class OutputLogger extends AbstractMailLogger implements ILogger{

	private $output;
	
	public function __construct(OutputInterface $output){
		$this->output = $output;
	}
	
	public function log($message, $level = self::LEVEL_INFO){
		$this->output->writeln(sprintf('%1$s [%2$s]: %3$s', 
			$level, date("Y-d-m H:i:s"), $message));
		if ($level == self::LEVEL_ERROR) {
			$this->setMailBody($message, $level);
		}
	}
	
	protected function setMailBody($message, $level) {
		$this->mailBody .= sprintf("%1s [%2s]: %3s\n", 
			$level, date("Y-d-m H:i:s"), $message);
	}

}