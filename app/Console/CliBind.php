<?php
namespace App\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\ArrayInput;
use Src\BindManager\BindManager;
use Src\Logger\OutputLogger;

class CliBind extends Command
{


	public function __construct($config)
	{
		parent::__construct();
		$this->config = $config;
	}

	protected function configure()
	{
		$this
		->setName('bind:sys')
		->setDescription('Update db.root, checks and reload actions.')
		->addArgument ( 'action', InputArgument::OPTIONAL, 'update | restart | statistics', 'update' )
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output) {

		$logger = new OutputLogger($output);
		$logger->log("Start bind manager.");
		$action = $input->getArgument ( 'action' );
		$bind = new BindManager($this->config, $logger);
		switch ($action) {
			case "restart":
				$bind->restartBind();
				break;
			case "update":
				$bind->updateBind();
				break;
			case "statistics":
				$bind->createBindStatistics();
				break;
			default:
				$command = $this->getApplication()->get('help');
				$command->run(new ArrayInput(['command_name' => $this->getName()]), $output);
				break;
		}
	}

}
