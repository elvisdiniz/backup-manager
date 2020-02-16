<?php namespace BackupManager\Databases;

/**
 * Class PostgresqlDatabase
 * @package BackupManager\Databases
 */
class PostgresqlDatabase implements Database
{
    /** @var array */
    private $config;

    /**
     * @param $type
     * @return bool
     */
    public function handles($type)
    {
        return in_array(strtolower($type), ['postgresql', 'pgsql']);
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
        return [
            'PGPASSWORD=' . escapeshellarg($this->config['pass']),
            'pg_dump',
            '--clean',
            '--host=' . escapeshellarg($this->config['host']),
            '--port=' . escapeshellarg($this->config['port']),
            '--username=' . escapeshellarg($this->config['user']),
            escapeshellarg($this->config['database']),
            '-f',
            escapeshellarg($outputPath)
        ];
    }

    /**
     * @param $inputPath
     * @return array
     */
    public function getRestoreCommandLine($inputPath)
    {
        return [
            'PGPASSWORD=' . escapeshellarg($this->config['pass']),
            'psql',
            '--host=' . escapeshellarg($this->config['host']),
            '--port=' . escapeshellarg($this->config['port']),
            '--username=' . escapeshellarg($this->config['user']),
            escapeshellarg($this->config['database']),
            '-f',
            escapeshellarg($inputPath)
        ];
    }
}