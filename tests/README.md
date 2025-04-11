# SunsetBlogs Test Suite

This folder contains a comprehensive test suite for the SunsetBlogs application. The tests are organized into different categories to cover various aspects of the application.

## Test Structure

- **Unit Tests**: Tests for individual components and functions
- **Integration Tests**: Tests for interactions between different parts of the system
- **Functional Tests**: Tests for complete features and workflows

## How to Run Tests

### Running All Tests

To run all tests at once, use the following command from the project root:

```bash
cd SunsetBlogs
php tests/runAllTests.php
```

### Running Specific Test Categories

You can run specific categories of tests by calling the PHP files directly:

```bash
# Run only unit tests
php tests/unit/UserTest.php
php tests/unit/PostTest.php
php tests/unit/CommentTest.php
php tests/unit/LikeTest.php

# Run only integration tests
php tests/integration/UserPostsTest.php
php tests/integration/PostCommentsTest.php
php tests/integration/LikesAnalyticsTest.php

# Run only functional tests
php tests/functional/ContactFormTest.php
php tests/functional/AuthenticationTest.php
```

## Test Results

Test results will be displayed in the console with a summary at the end. Passed tests will be shown in green, and failed tests will be shown in red with error details.

## Adding New Tests

To add new tests:

1. Create a new PHP file in the appropriate directory (unit, integration, or functional)
2. Include the TestHelper.php file at the top of your test file
3. Use the TestHelper class to define and run your tests
4. Run the tests to verify they work as expected

Example:

```php
<?php
require_once __DIR__ . '/../TestHelper.php';

TestHelper::startTestSuite('My New Test Suite');

TestHelper::runTest('My Test Case', function() {
    // Test logic here
    return TestHelper::assertTrue(true);
});

TestHelper::endTestSuite();
```

## Custom Test Assertions

The TestHelper class provides several assertion methods for verifying test conditions:

- `assertEquals($expected, $actual)`
- `assertNotEquals($expected, $actual)`
- `assertTrue($condition)`
- `assertFalse($condition)`
- `assertNull($value)`
- `assertNotNull($value)`
- `assertContains($needle, $haystack)`
- `assertIsArray($value)`
- `assertStringContains($needle, $haystack)`
- `assertGreaterThan($expected, $actual)`

## Mock Database

For testing database operations without affecting the actual database, we use a MockDatabase class that simulates database interactions. 

The mock database contains sample data for users, posts, comments, and likes, which allows for realistic testing of database-related functionality. 