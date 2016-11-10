<?php

namespace Src\BindManager;

use Src\Logger\OutputLogger;
use App\Config\GetYAMLConfig;

class AbstractBindManager
{

    protected $config;
    protected $logger;

    public function __construct(GetYAMLConfig $config, OutputLogger $logger)
    {
        $this->config = $config;
        $this->logger = $logger;
    }

}