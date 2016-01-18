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
				'reload',
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
			if ($input->getOption('update')) {
				echo "update".PHP_EOL;
				$update = new BindManager($this->config);
				$update->updateBind();
			}
			if ($input->getOption('reload')) {
				echo "reload".PHP_EOL;
			}
		}
	}
}
