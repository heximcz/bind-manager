<?php
namespace Src\BindManager;

use App\Config\GetYAMLConfig;
use Src\Logger\OutputLogger;

/*
 * TODO:
 * Better check input params from config file
 * 1. check if bindservice: (systemctl) exist in the system - if systemctl: = 1
 * 2. check if bind-restart: path exist - if systemctl: != 1
 */

class BindManager extends AbstractBindManager implements IBindManager
{

    public function __construct(GetYAMLConfig $config, OutputLogger $output)
    {
        parent::__construct($config, $output);
    }

    public function updateBind()
    {
        $bind = new SystemBindManager($this->config, $this->logger);
        if ($bind->getRootZone()) {
            $bind->restartBindService();
            $bind->testDomainZone();
            $bind->checkErrorEmail();
        }
    }

    public function restartBind()
    {
        $bind = new SystemBindManager($this->config, $this->logger);
        $bind->restartBindService();
        $bind->testDomainZone();
        $bind->checkErrorEmail();
    }

    public function createBindStatistics()
    {
        $bind = new SystemBindStatistics($this->config, $this->logger);
        $this->logger->log("Create statistics.");
        $bind->getBindStatistics()->parseStatistics();
        $this->logger->log("Done.");
    }

}