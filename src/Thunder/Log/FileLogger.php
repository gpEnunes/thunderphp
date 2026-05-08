<?php

namespace Thunder\Log;

use Thunder\Log\LoggerInterface;

class FileLogger implements LoggerInterface
{
    public function __construct(private readonly string $path){}

    public function error(string $message, array $context = []):void{
        $this->log(LogLevel::Error, $message, $context);
    }
    public function emergency(string $message, array $context = []):void{
        $this->log(LogLevel::Emergency, $message, $context);
    }
    public function debug(string $message, array $context = []):void{
        $this->log(LogLevel::Debug, $message, $context);
    }
    public function info(string $message, array $context = []):void{
        $this->log(LogLevel::Info, $message, $context);
    }
    public function warning(string $message, array $context = []):void{
        $this->log(LogLevel::Warning, $message, $context);
    }
    public function notice(string $message, array $context = []):void{
        $this->log(LogLevel::Notice, $message, $context);
    }
    public function critical(string $message, array $context = []):void{
        $this->log(LogLevel::Critical, $message, $context);
    }
    public function alert(string $message, array $context = []):void{
        $this->log(LogLevel::Alert, $message, $context);
    }
    public function log(LogLevel $level, string $message, array $context = []):void{
        $entry = '['.date('Y-m-d H:i:s').'] ' . $level->value . ": $message " . (!empty($context) ? json_encode($context) : '') . "\n";
        file_put_contents($this->path, $entry, FILE_APPEND);
    }
}
