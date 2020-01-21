<?php declare(strict_types=1);

use Tester\Helpers;

if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    // dependencies were installed via composer - this is the main project
    $classLoader = require __DIR__ . '/../vendor/autoload.php';
} else {
    throw new RuntimeException('Can\'t find autoload.php. Did you install dependencies via Composer?');
}

Tester\Environment::setup();
date_default_timezone_set('Europe/Prague');


// create temporary directory
define('TEMP_DIR', __DIR__ . '/../tmp/test' . getmypid());
if (@!mkdir($concurrentDirectory = dirname(TEMP_DIR)) && !is_dir($concurrentDirectory)) {
    throw new RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
} // @ - directory may already exist
Helpers::purge(TEMP_DIR);


$_SERVER = array_intersect_key($_SERVER, array_flip([
    'PHP_SELF', 'SCRIPT_NAME', 'SERVER_ADDR', 'SERVER_SOFTWARE', 'HTTP_HOST', 'DOCUMENT_ROOT', 'OS', 'argc', 'argv']));
$_SERVER['REQUEST_TIME'] = 1234567890;
$_ENV = $_GET = $_POST = [];


function run(Tester\TestCase $testCase): void
{
    $testCase->run(isset($_SERVER['argv'][1]) ? $_SERVER['argv'][1] : null);
}
