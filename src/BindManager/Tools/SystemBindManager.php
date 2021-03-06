<?php
namespace Src\BindManager;

use App\Config\GetYAMLConfig;
use Src\Logger\OutputLogger;

class SystemBindManager extends AbstractBindManager
{

    protected $user;
    protected $group;
    protected $permission;

    public function __construct(GetYAMLConfig $config, OutputLogger $logger)
    {
        parent::__construct($config, $logger);
    }

    public function restartBindService()
    {
        $message = "Restart BIND via systemctl: ";
        // systemctl
        if ($this->config->system['systemctl'] == 1) {
            $message .= "YES";
            $this->logger->log($message);
            exec("systemctl restart " . $this->config->system['bindservice']);
            // check for 5 times if bind service is running
            for ($i = 0; $i < 5; $i++) {
                sleep(1);
                $lastState = false;
                if ($this->testBindService()) {
                    $lastState = true;
                    break;
                }
            }
            if (!$lastState)
                $this->logger->log("BIND service not running !!!", $this->logger->setError());
            return;
        }
        $message .= "NO";
        $this->logger->log($message);
        exec($this->config->system['bind-restart']);
        sleep(4);
    }

    public function testDomainZone()
    {
        $this->logger->log("Test dns for domain: " . $this->config->test['domain']);
        if (!checkdnsrr($this->config->test['domain'], "A")) {
            // exist backup file ?
            if (file_exists($this->config->system['rzfile'] . ".bak")) {
                //get back old zone file and restart bind
                $this->logger->log("New zone file is bad, revert to old zones from backup!", $this->logger->setError());
                copy($this->config->system['rzfile'] . ".bak", $this->config->system['rzfile']);
                $this->restartBind();
                return;
            }
            $this->logger->log("Backup zone file is not exist!", $this->logger->setError());
            return;
        }
        $this->logger->log("Test passed.");
    }

    public function testBindService()
    {
        $substate = shell_exec("systemctl show " . $this->config->system['bindservice'] . " -p SubState");
        $result = shell_exec("systemctl show " . $this->config->system['bindservice'] . " -p Result");
        $statusA = explode("=", trim($substate));
        $statusB = explode("=", trim($result));
        if ($statusA[1] != "running" || $statusB[1] != "success")
            return false;
        return true;
    }

    public function getRootZone()
    {
        // get original file user:group and permissions
        $this->getUserGroupPerm();
        // backup original root zone
        $this->logger->log("Backup root zones file: " . $this->config->system['rzfile']);
        copy($this->config->system['rzfile'], $this->config->system['rzfile'] . ".bak");
        // get new zones file
        $this->logger->log("Get new root zones file from: " . $this->config->source['url']);
        exec("wget -q " . $this->config->source['url'] . " -O " . $this->config->system['rzfile'] . ".new");
        if (filesize($this->config->system['rzfile'] . ".new")) {
            rename($this->config->system['rzfile'] . ".new", $this->config->system['rzfile']);
            // set original user:group and permissions
            $this->setUserGroupPerm();
            return true;
        }
        $this->logger->log("New zone file is empty, cancel operation.", $this->logger->setError());
        unlink($this->config->system['rzfile'] . ".new");
        return false;
    }

    protected function getUserGroupPerm()
    {
        if (($this->group = filegroup($this->config->system['rzfile'])) === false)
            $this->logger->log("Get group of file: " . $this->config->system['rzfile'], $this->logger->setError());
        if (($this->user = fileowner($this->config->system['rzfile'])) === false)
            $this->logger->log("Get user of file: " . $this->config->system['rzfile'], $this->logger->setError());
        if (($this->permission = fileperms($this->config->system['rzfile'])) === false)
            $this->logger->log("Get permission of file: " . $this->config->system['rzfile'], $this->logger->setError());
    }

    protected function setUserGroupPerm()
    {
        if (!chown($this->config->system['rzfile'], $this->user))
            $this->logger->log("Change user of file: " . $this->config->system['rzfile'], $this->logger->setError());
        if (!chgrp($this->config->system['rzfile'], $this->group))
            $this->logger->log("Change group of file: " . $this->config->system['rzfile'], $this->logger->setError());
        if (!chmod($this->config->system['rzfile'], $this->permission))
            $this->logger->log("Change permission of file: " . $this->config->system['rzfile'], $this->logger->setError());
    }

    public function checkErrorEmail()
    {
        if ($this->config->mail['sendmail'] == 1 && $this->logger->isMail()) {
            if ($this->logger->send($this->config->mail['email-from'], $this->config->mail['email-from'])) {
                $this->logger->log("Email message with errors has been send to email: " . $this->config->mail['email-from']);
                return;
            }
            $this->logger->log("Email message with errors has not been send !!! ", $this->logger->setError());
        }
    }

}