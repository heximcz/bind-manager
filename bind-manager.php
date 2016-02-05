<?php
use Symfony\Component\Console\Application;
use App\Config\GetYAMLConfig;
use App\Console\CliBind;

require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'vendor/autoload.php';

try {
	$myConfig = new GetYAMLConfig();
	$config   = $myConfig->getConfigData();
	$application = new Application("Bind Manager","0.0.7");
	$application->add(new CliBind($config));
	$application->run();
} catch (Exception $e) {
	echo 'Caught exception: ',  $e->getMessage(), "\n";
}
