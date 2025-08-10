<?php
declare(strict_types=1);

namespace Marvelic\MveRestrictCheckout\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Serialize\Serializer\Json;
use Psr\Log\LoggerInterface;
use Stringable;

/**
 * Custom logger for MveRestrictCheckout module
 * Writes logs to var/log/mve_restrict_checkout.log regardless of debug mode
 */
class Logger implements \Psr\Log\LoggerInterface
{
    private const LOG_FILE = 'mve_restrict_checkout.log';
    
    /**
     * @var DirectoryList
     */
    private $directoryList;
    
    /**
     * @var File
     */
    private $fileDriver;
    
    /**
     * @var Json
     */
    private $jsonSerializer;
    
    /**
     * @var string|null
     */
    private $logFilePath;
    
    /**
     * @param DirectoryList $directoryList
     * @param File $fileDriver
     * @param Json $jsonSerializer
     */
    public function __construct(
        DirectoryList $directoryList,
        File $fileDriver,
        Json $jsonSerializer
    ) {
        $this->directoryList = $directoryList;
        $this->fileDriver = $fileDriver;
        $this->jsonSerializer = $jsonSerializer;
    }
    
    /**
     * Get the full path to the log file
     *
     * @return string
     */
    private function getLogFilePath(): string
    {
        if ($this->logFilePath === null) {
            $varDir = $this->directoryList->getPath(DirectoryList::VAR_DIR);
            $this->logFilePath = $varDir . '/log/' . self::LOG_FILE;
        }
        
        return $this->logFilePath;
    }
    
    /**
     * Ensure the log directory exists
     *
     * @return void
     */
    private function ensureLogDirectoryExists(): void
    {
        $logDir = dirname($this->getLogFilePath());
        
        if (!$this->fileDriver->isExists($logDir)) {
            $this->fileDriver->createDirectory($logDir, 0755);
        }
    }
    
    /**
     * Write a log message
     *
     * @param string $level
     * @param string $message
     * @param array $context
     * @return void
     */
    private function writeLog(string $level, string $message, array $context = []): void
    {
        // Only log messages that are specifically from our module
        if (!str_starts_with($message, 'MveRestrictCheckout')) {
            return;
        }
        
        try {
            $this->ensureLogDirectoryExists();
            
            $timestamp = date('Y-m-d H:i:s');
            $formattedMessage = sprintf(
                '[%s] [%s] %s',
                $timestamp,
                strtoupper($level),
                $message
            );
            
            // Add context if provided
            if (!empty($context)) {
                $contextJson = $this->jsonSerializer->serialize($context);
                $formattedMessage .= ' Context: ' . $contextJson;
            }
            
            $formattedMessage .= PHP_EOL;
            
            $this->fileDriver->filePutContents($this->getLogFilePath(), $formattedMessage, FILE_APPEND | LOCK_EX);
            
        } catch (\Exception $e) {
            // Fallback to error_log if our custom logging fails
            error_log("MveRestrictCheckout Logger Error: " . $e->getMessage());
        }
    }
    
    /**
     * Log emergency message
     *
     * @param Stringable|string $message
     * @param array $context
     * @return void
     */
    public function emergency(Stringable|string $message, array $context = []): void
    {
        $this->writeLog('emergency', (string) $message, $context);
    }
    
    /**
     * Log alert message
     *
     * @param Stringable|string $message
     * @param array $context
     * @return void
     */
    public function alert(Stringable|string $message, array $context = []): void
    {
        $this->writeLog('alert', (string) $message, $context);
    }
    
    /**
     * Log critical message
     *
     * @param Stringable|string $message
     * @param array $context
     * @return void
     */
    public function critical(Stringable|string $message, array $context = []): void
    {
        $this->writeLog('critical', (string) $message, $context);
    }
    
    /**
     * Log error message
     *
     * @param Stringable|string $message
     * @param array $context
     * @return void
     */
    public function error(Stringable|string $message, array $context = []): void
    {
        $this->writeLog('error', (string) $message, $context);
    }
    
    /**
     * Log warning message
     *
     * @param Stringable|string $message
     * @param array $context
     * @return void
     */
    public function warning(Stringable|string $message, array $context = []): void
    {
        $this->writeLog('warning', (string) $message, $context);
    }
    
    /**
     * Log notice message
     *
     * @param Stringable|string $message
     * @param array $context
     * @return void
     */
    public function notice(Stringable|string $message, array $context = []): void
    {
        $this->writeLog('notice', (string) $message, $context);
    }
    
    /**
     * Log info message
     *
     * @param Stringable|string $message
     * @param array $context
     * @return void
     */
    public function info(Stringable|string $message, array $context = []): void
    {
        $this->writeLog('info', (string) $message, $context);
    }
    
    /**
     * Log debug message
     *
     * @param Stringable|string $message
     * @param array $context
     * @return void
     */
    public function debug(Stringable|string $message, array $context = []): void
    {
        $this->writeLog('debug', (string) $message, $context);
    }
    
    /**
     * Log with arbitrary level
     *
     * @param mixed $level
     * @param Stringable|string $message
     * @param array $context
     * @return void
     */
    public function log($level, Stringable|string $message, array $context = []): void
    {
        $this->writeLog((string) $level, (string) $message, $context);
    }
}
