<?php

namespace Src\BindManager;

use stdClass;
use Exception;

class BindJsonStatistics extends StatisticsFileMaker
{

    protected $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * Parse json BIND 9 statistics
     * @param stdClass $json
     * @throws Exception
     * @return \Src\BindManager\BindJsonStatistics
     */
    public function parseJsonStats(stdClass $json)
    {
        if ($json->{'json-stats-version'} >= 1 && $json->{'json-stats-version'} < 2) {
            // Incoming Queries
            $this->parseSimpleValues($json->qtypes, 'queries-in');
            // Incoming Requests
            $this->parseSimpleValues($json->opcodes, 'requests');
            // Server Statistics
            $this->parseSimpleValues($json->nsstats, 'nsstat');
            // Socket I/O Statistics
            $this->parseSimpleValues($json->sockstats, 'sockstat');
            // Memory
            $this->parseSimpleValues($json->memory, 'memory');
            // Cache DB RRsets for View _default
            $this->parseSimpleValues($json->views->{'_default'}->resolver->cache, 'default-cache-rrsets');
            // Cache DB RRsets for View _default
            $this->parseSimpleValues($json->views->{'_default'}->resolver->cachestats, 'default-cache-stats');
            // Outgoing Queries for View _default
            $this->parseSimpleValues($json->views->{'_default'}->resolver->qtypes, 'default-queries-out');
            // Resolver stats _default
            $this->parseSimpleValues($json->views->{'_default'}->resolver->stats, 'default-stats');
            return $this;
        }
        throw new Exception('Incompatible version of the BIND json statistics (actual version is: ' . $json->{'json-stats-version'} . ')');
    }


    /**
     * Parse one element
     * @param stdClass $json
     * @param unknown $filePrefix
     */
    protected function parseSimpleValues(stdClass $json, $filePrefix)
    {
        foreach ($json as $key => $value) {
            if (is_string($key) && is_int($value))
                $this->saveStatsToFile($filePrefix, $key, $value);
        }
    }

}
