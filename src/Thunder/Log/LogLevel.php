<?php
namespace Thunder\Log;
enum LogLevel: string{
    case Error = 'ERROR';
    case Emergency = 'EMERGENCY';
    case Debug = 'DEBUG';
    case Info = 'INFO';
    case Warning = 'WARNING';
    case Notice = 'NOTICE';
    case Critical = 'CRITICAL';
    case Alert = 'ALERT';
}
