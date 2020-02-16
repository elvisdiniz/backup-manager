<?php namespace BackupManager\Databases;

/**
 * Class MysqlDatabase
 * @package BackupManager\Databases
 */
class MysqlDatabase implements Database
{
    /** @var array */
    private $config;

    /**
     * @param $type
     * @return bool
     */
    public function handles($type)
    {
        return strtolower($type) == 'mysql';
    }

    /**
     * @param array $config
     * @return null
     */
    public function setConfig(array $config)
    {
        $this->config = $config;
    }

    /**
     * @param $outputPath
     * @return array
     */
    public function getDumpCommandLine($outputPath)
    {
        $extras = [];
        if (array_key_exists('singleTransaction', $this->config) && $this->config['singleTransaction'] === true) {
            $extras[] = '--single-transaction';
        }
        if (array_key_exists('ignoreTables', $this->config)) {
            $extras[] = $this->getIgnoreTableParameter();
        }
        if (array_key_exists('ssl', $this->config) && $this->config['ssl'] === true) {
            $extras[] = '--ssl';
        }
        if (array_key_exists('extraParams', $this->config) && $this->config['extraParams']) {
            $extras[] = $this->config['extraParams'];
        }

        // Prepare a "params" string from our config
        $params = '';
        $keys = ['host' => 'host', 'port' => 'port', 'user' => 'user', 'pass' => 'password'];
        foreach ($keys as $key => $mysqlParam) {
            if (!empty($this->config[$key])) {
                $params .= sprintf(' --%s=%s', $mysqlParam, escapeshellarg($this->config[$key]));
            }
        }

        return array_merge(
            ['mysqldump', '--routines'],
            $extras,
            [$params, escapeshellarg($this->config['database']), '>', escapeshellarg($outputPath)]
        );
    }

    /**
     * @param $inputPath
     * @return array
     */
    public function getRestoreCommandLine($inputPath)
    {
        $extras = [];
        if (array_key_exists('ssl', $this->config) && $this->config['ssl'] === true) {
            $extras[] = '--ssl';
        }

        // Prepare a "params" string from our config
        $params = '';
        $keys = ['host' => 'host', 'port' => 'port', 'user' => 'user', 'pass' => 'password'];
        foreach ($keys as $key => $mysqlParam) {
            if (!empty($this->config[$key])) {
                $params .= sprintf(' --%s=%s', $mysqlParam, escapeshellarg($this->config[$key]));
            }
        }

        return array_merge(
            ['mysql', trim($params)],
            $extras,
            [escapeshellarg($this->config['database']), '-e', 'source', $inputPath]
        );
    }

    /**
     * @return string
     */
    public function getIgnoreTableParameter()
    {

        if (!is_array($this->config['ignoreTables']) || count($this->config['ignoreTables']) === 0) {
            return '';
        }

        $db = $this->config['database'];
        $ignoreTables = array_map(function ($table) use ($db) {
            return $db . '.' . $table;
        }, $this->config['ignoreTables']);

        $commands = [];
        foreach ($ignoreTables AS $ignoreTable) {
            $commands[] = sprintf('--ignore-table=%s',
                escapeshellarg($ignoreTable)
            );
        }

        return implode(' ', $commands);
    }
}
