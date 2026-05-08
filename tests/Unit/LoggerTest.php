<?php
use Thunder\Log\FileLogger;
use Thunder\Log\LogLevel;


test('logger writes entry to file', function () {
    $path = sys_get_temp_dir() . '/thunder_test.log';
    $logger = new FileLogger($path);
    $logger->error('Something broke');
    expect(file_exists($path))->toBeTrue();
    unlink($path);
});

test('assert that file_get_contents($path) contains message', function () {
    $path = sys_get_temp_dir() . '/thunder_test.log';
    $logger = new FileLogger($path);
    $logger->error('Something broke');
    expect(file_get_contents($path))->toContain('Something broke');
    expect(file_get_contents($path))->toContain('ERROR');
    unlink($path);
});