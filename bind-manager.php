<?php
use Symfony\Component\Console\Application;
use App\Config\GetYAMLConfig;
use App\Console\CliBind;

require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'vendor/autoload.php';

try {
	$application = new Application("Bind Manager","0.1.1-dev");
	$application->add(new CliBind( new GetYAMLConfig() ));
	$application->run();
} catch (Exception $e) {
	echo 'Caught exception: ',  $e->getMessage(), "\n";
}
