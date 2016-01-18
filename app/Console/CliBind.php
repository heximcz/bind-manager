<?php
namespace App\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Src\BindManager\BindManager;
use Exception;

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
		if ($input->getOption('update') && $input->getOption('reload')) {
			throw new Exception("Only one option is enabled.");
		}
		else {
			$bind = new BindManager($this->config);
			echo "INFO: Start bind manager.".PHP_EOL;
			if ($input->getOption('update')) {
				$bind->updateBind();
			}
			if ($input->getOption('restart')) {
				$bind->restartBind();
			}
		}
	}
}
