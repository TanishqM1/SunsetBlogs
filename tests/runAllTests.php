<?php
/**
 * Master Test Runner for SunsetBlogs
 */
require_once 'TestHelper.php';

// Include all test files
$testFiles = glob(__DIR__ . '/unit/*.php');
$testFiles = array_merge($testFiles, glob(__DIR__ . '/integration/*.php'));
$testFiles = array_merge($testFiles, glob(__DIR__ . '/functional/*.php'));

echo "\n\033[1mSunset Blogs Test Suite\033[0m\n";
echo "==============================\n";
echo "Running " . count($testFiles) . " test files...\n";

foreach ($testFiles as $testFile) {
    require_once $testFile;
}

// Print overall summary
TestHelper::printSummary();
echo "\nTests completed.\n"; 