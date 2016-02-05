<?php
namespace App\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Src\BindManager\BindManager;
use Exception;
use Src\Logger\OutputLogger;

class CliBind extends Command
{

	private $config;

	public function __construct($config)
	{
		parent::__construct();
		$this->config = $config;
	}

	protected function configure()
	{
		$this
		->setName('bind')
		->setDescription('Update db.root, checks and reload actions.')
		->addOption(
				'update',
				'u',
				InputOption::VALUE_NONE,
				'update db.root and reload bind with test'
				)
		->addOption(
				'restart',
				'r',
				InputOption::VALUE_NONE,
				'reload bind with tests'
		);
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		if ($input->getOption('update') && $input->getOption('restart')) {
			throw new Exception("Only one option is enabled.");
		}
		else {
			$logger = new OutputLogger($output);
			$bind = new BindManager($this->config, $output);
			$logger->log("Start bind manager.");
			if ($input->getOption('update')) {
				$bind->updateBind();
			}
			elseif ($input->getOption('restart')) {
				$bind->restartBind();
			}
			else 
				$logger->log("Nothing to do.");
			$logger->log("All done.");
		}
	}
}
