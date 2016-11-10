<?php
namespace App\Config;

use Symfony\Component\Yaml\Parser;
use ArrayObject;
use Exception;

class GetYAMLConfig extends ArrayObject
{

    public function __construct()
    {
        $this->createConfig(
            dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'config.default.yml',
            dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'config.yml'
        );
    }

    /**
     * Parse YAML config file
     * @param string $configPath
     */
    private function parseConfig($configPath)
    {
        $yaml = new Parser();
        return $yaml->parse(file_get_contents($configPath));
    }

    /**
     * Create array object with configurations values,
     * Overwrite default config values if a custom file does exist
     *
     * @param string $defaultPath
     * @param string $customPath
     * @throws Exception
     */
    private function createConfig($defaultPath, $customPath)
    {
        if (file_exists($defaultPath)) {
            $defaultConfig = $this->parseConfig($defaultPath);
            if (file_exists($customPath)) {
                $customConfig = $this->parseConfig($customPath);
                parent::__construct(array_replace_recursive($defaultConfig, $customConfig), ArrayObject::ARRAY_AS_PROPS);
                return;
            }
            parent::__construct($defaultConfig, ArrayObject::ARRAY_AS_PROPS);
        } else
            throw new Exception(get_class($this) . ' FATAL ERROR: config.default.yml no exist!');
    }

}
