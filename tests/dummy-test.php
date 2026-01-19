<?php

/**
 * Dummy Test Script for PHP Light SQL Parser
 * 
 * This is a basic smoke test to verify the library loads correctly
 * across different PHP versions and can perform basic operations.
 */

// Exit codes
const EXIT_SUCCESS = 0;
const EXIT_FAILURE = 1;

// Unicode symbols
const CHECK_MARK = "✓";
const CROSS_MARK = "✗";

echo "==================================\n";
echo "Testing PHP Light SQL Parser...\n";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "==================================\n\n";

// Step 1: Load the autoloader
echo "[1/3] Loading autoloader... ";
try {
    $autoloaderPath = __DIR__ . '/../vendor/autoload.php';
    if (!file_exists($autoloaderPath)) {
        echo CROSS_MARK . "\n";
        echo "Error: Autoloader not found at: $autoloaderPath\n";
        echo "Please run 'composer install' first.\n";
        exit(EXIT_FAILURE);
    }
    require_once $autoloaderPath;
    echo CHECK_MARK . "\n";
} catch (Exception $e) {
    echo CROSS_MARK . "\n";
    echo "Error loading autoloader: " . $e->getMessage() . "\n";
    exit(EXIT_FAILURE);
}

// Step 2: Instantiate the class
echo "[2/3] Instantiating LightSQLParser... ";
try {
    $parser = new marcocesarato\sqlparser\LightSQLParser();
    if (!$parser instanceof marcocesarato\sqlparser\LightSQLParser) {
        echo CROSS_MARK . "\n";
        echo "Error: Failed to create LightSQLParser instance\n";
        exit(EXIT_FAILURE);
    }
    echo CHECK_MARK . "\n";
} catch (Exception $e) {
    echo CROSS_MARK . "\n";
    echo "Error instantiating class: " . $e->getMessage() . "\n";
    exit(EXIT_FAILURE);
}

// Step 3: Parse a simple SQL query
echo "[3/3] Parsing SQL query... ";
try {
    $testQuery = "SELECT * FROM users WHERE id = 1";
    $parser->setQuery($testQuery);
    
    // Verify the query was set
    $retrievedQuery = $parser->getQuery();
    if ($retrievedQuery !== $testQuery) {
        echo CROSS_MARK . "\n";
        echo "Error: Query mismatch. Expected: '$testQuery', Got: '$retrievedQuery'\n";
        exit(EXIT_FAILURE);
    }
    
    // Verify we can get the method
    $method = $parser->getMethod();
    if ($method !== 'SELECT') {
        echo CROSS_MARK . "\n";
        echo "Error: Method mismatch. Expected: 'SELECT', Got: '$method'\n";
        exit(EXIT_FAILURE);
    }
    
    // Verify we can get the table
    $table = $parser->getTable();
    if ($table !== 'users') {
        echo CROSS_MARK . "\n";
        echo "Error: Table mismatch. Expected: 'users', Got: '$table'\n";
        exit(EXIT_FAILURE);
    }
    
    echo CHECK_MARK . "\n";
} catch (Exception $e) {
    echo CROSS_MARK . "\n";
    echo "Error parsing query: " . $e->getMessage() . "\n";
    exit(EXIT_FAILURE);
}

echo "\n==================================\n";
echo "All tests passed! " . CHECK_MARK . "\n";
echo "==================================\n";

exit(EXIT_SUCCESS);
