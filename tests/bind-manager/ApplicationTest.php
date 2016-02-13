<?php
use App\Config\GetYAMLConfig;
use App\Console\CliBind;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
//use Symfony\Component\Console\Output\NullOutput;

class ApplicationTest extends \PHPUnit_Framework_TestCase {

	protected $config;
/*	
	public static function setUpBeforeClass()
	{	
		copy(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . "config.travis.yml",
			 __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . "config.yml");
	}

	public static function tearDownAfterClass()
	{
		unlink(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . "config.yml");
	}
*/
	public function setUp() {
		$myConfig = new GetYAMLConfig ();
		$this->config = $myConfig->getConfigData ();
	}
	
	public function testConfig() {
		$this->assertTrue ( is_array ( $this->config ) );
	}
	
	public function testExecuteMod() {
		$application = new Application ();
		$application->add ( new CliBind( $this->config ) );
		$command = $application->find ( 'bind' );
		$commandTester = new CommandTester ( $command );
		$commandTester->execute ( array (
				'-u' => true
		) );
		$this->assertRegExp ( '/.../', $commandTester->getDisplay () );
	}
	

}
