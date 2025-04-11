<?php
/**
 * Simple Test Helper Class for SunsetBlogs
 * This provides basic functionality similar to PHPUnit but without dependencies
 */
class TestHelper {
    private static $testCount = 0;
    private static $passedTests = 0;
    private static $failedTests = 0;
    private static $currentTestSuite = '';
    private static $failures = [];

    /**
     * Start a test suite
     */
    public static function startTestSuite($name) {
        self::$currentTestSuite = $name;
        echo "\n\033[1m" . $name . " Test Suite\033[0m\n";
        echo "----------------------------------------\n";
    }

    /**
     * End test suite and print summary
     */
    public static function endTestSuite() {
        echo "----------------------------------------\n";
        echo "Results: " . self::$passedTests . " passed, " . self::$failedTests . " failed\n\n";
    }

    /**
     * Run a test
     */
    public static function runTest($testName, $testFunction) {
        self::$testCount++;
        echo "Running test: " . $testName . "... ";
        
        try {
            $result = $testFunction();
            if ($result === true) {
                echo "\033[32mPASSED\033[0m\n";
                self::$passedTests++;
            } else {
                echo "\033[31mFAILED\033[0m\n";
                self::$failedTests++;
                self::$failures[] = [
                    'suite' => self::$currentTestSuite,
                    'test' => $testName,
                    'reason' => 'Test returned false'
                ];
            }
        } catch (Exception $e) {
            echo "\033[31mFAILED\033[0m (Exception: " . $e->getMessage() . ")\n";
            self::$failedTests++;
            self::$failures[] = [
                'suite' => self::$currentTestSuite,
                'test' => $testName,
                'reason' => 'Exception: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Print summary of all tests
     */
    public static function printSummary() {
        echo "\n\033[1mTest Summary\033[0m\n";
        echo "----------------------------------------\n";
        echo "Total Tests: " . self::$testCount . "\n";
        echo "Passed: " . self::$passedTests . "\n";
        echo "Failed: " . self::$failedTests . "\n";
        
        if (self::$failedTests > 0) {
            echo "\n\033[1mFailed Tests:\033[0m\n";
            foreach (self::$failures as $failure) {
                echo "- " . $failure['suite'] . " - " . $failure['test'] . ": " . $failure['reason'] . "\n";
            }
        }
    }

    /**
     * Assertion methods
     */
    public static function assertEquals($expected, $actual) {
        if ($expected == $actual) {
            return true;
        }
        throw new Exception("Expected " . var_export($expected, true) . " but got " . var_export($actual, true));
    }

    public static function assertNotEquals($expected, $actual) {
        if ($expected != $actual) {
            return true;
        }
        throw new Exception("Expected value to NOT equal " . var_export($expected, true));
    }

    public static function assertTrue($condition) {
        if ($condition === true) {
            return true;
        }
        throw new Exception("Expected true but got " . var_export($condition, true));
    }

    public static function assertFalse($condition) {
        if ($condition === false) {
            return true;
        }
        throw new Exception("Expected false but got " . var_export($condition, true));
    }

    public static function assertNull($value) {
        if ($value === null) {
            return true;
        }
        throw new Exception("Expected null but got " . var_export($value, true));
    }

    public static function assertNotNull($value) {
        if ($value !== null) {
            return true;
        }
        throw new Exception("Expected not null but got null");
    }

    public static function assertContains($needle, $haystack) {
        if (is_string($haystack) && strpos($haystack, $needle) !== false) {
            return true;
        } else if (is_array($haystack) && in_array($needle, $haystack)) {
            return true;
        }
        throw new Exception("Expected " . var_export($haystack, true) . " to contain " . var_export($needle, true));
    }

    public static function assertIsArray($value) {
        if (is_array($value)) {
            return true;
        }
        throw new Exception("Expected array but got " . gettype($value));
    }
    
    public static function assertStringContains($needle, $haystack) {
        if (strpos($haystack, $needle) !== false) {
            return true;
        }
        throw new Exception("Expected string to contain " . var_export($needle, true));
    }
    
    public static function assertGreaterThan($expected, $actual) {
        if ($actual > $expected) {
            return true;
        }
        throw new Exception("Expected value greater than " . var_export($expected, true) . ", got " . var_export($actual, true));
    }
} 