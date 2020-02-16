<?php namespace BackupManager\ShellProcessing;

use Symfony\Component\Process\Exception\LogicException;
use Symfony\Component\Process\Process;

/**
 * Class CommandProcessor
 * @package BackupManager
 */
class ShellProcessor
{
    /**
     * @param $command
     * @throws ShellProcessFailed
     * @throws LogicException
     */
    public function process($command)
    {
        if (empty($command)) {
            return;
        }

        $process = new Process($command);
        $process->setTimeout(null);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ShellProcessFailed($process->getErrorOutput());
        }
    }
}
