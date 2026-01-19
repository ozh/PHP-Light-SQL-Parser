# Test Suite for PHP Light SQL Parser

This directory contains comprehensive unit tests for the `LightSQLParser` class.

## Running Tests Locally

### Prerequisites

Ensure you have [Composer](https://getcomposer.org/) installed on your system.

### Installation

1. Install dependencies including PHPUnit:
   ```bash
   composer install
   ```

### Running Tests

Run all tests:
```bash
vendor/bin/phpunit
```

Run tests with verbose output:
```bash
vendor/bin/phpunit --verbose
```

Run tests with coverage report (requires Xdebug):
```bash
vendor/bin/phpunit --coverage-html coverage/
```

Run specific test groups:
```bash
vendor/bin/phpunit --group getMethod
vendor/bin/phpunit --group complex
vendor/bin/phpunit --group edge-cases
```

Run a specific test method:
```bash
vendor/bin/phpunit --filter testGetMethodWithSelectQuery
```

## Test Structure

### LightSQLParserTest.php

The main test suite covering all public methods of the `LightSQLParser` class:

- **Constructor and Query Management** (`@group constructor`, `@group query`)
  - `__construct()` with and without query
  - `setQuery()` and `getQuery()`
  
- **Query Parsing** (`@group getAllQueries`)
  - Single and multiple queries
  - UNION and UNION ALL handling
  - Comment removal
  - Quote handling

- **Method Detection** (`@group getMethod`)
  - SELECT, INSERT, UPDATE, DELETE
  - CREATE TABLE, CREATE INDEX
  - DROP, ALTER, TRUNCATE

- **Field Extraction** (`@group getFields`)
  - SELECT fields with and without aliases
  - INSERT fields (both formats)
  - UPDATE fields

- **Table Extraction** (`@group getTable`, `@group getAllTables`)
  - Single and multiple tables
  - Tables from various query types
  - Table aliases

- **JOIN Operations** (`@group getJoinTables`, `@group hasJoin`)
  - INNER, LEFT, RIGHT, OUTER JOINs
  - Multiple JOINs

- **Subquery Detection** (`@group hasSubQuery`, `@group getSubQueries`)
  - Subqueries in WHERE clauses
  - Subqueries in SELECT clauses
  - Nested subqueries

- **Complex Queries** (`@group complex`)
  - Multi-table queries with JOINs
  - Queries with WHERE, GROUP BY, HAVING, ORDER BY
  - Queries with comments and aliases
  - UNION queries

- **Edge Cases** (`@group edge-cases`)
  - Empty queries
  - Malformed SQL
  - Mixed case keywords
  - Extra whitespace

## Adding New Tests

When adding new tests:

1. Follow PHPUnit naming conventions:
   ```php
   public function testMethodNameWithScenario()
   ```

2. Use appropriate assertions:
   - `assertEquals()`, `assertSame()` - for exact matches
   - `assertContains()` - for array/string containment
   - `assertTrue()`, `assertFalse()` - for boolean checks
   - `assertCount()` - for array lengths
   - `assertEmpty()`, `assertNotEmpty()` - for empty checks

3. Add `@group` annotations to categorize tests:
   ```php
   /**
    * @group groupName
    */
   public function testSomething()
   ```

4. Use the `setUp()` method for test initialization:
   ```php
   protected function setUp(): void
   {
       $this->parser = new LightSQLParser();
   }
   ```

5. Test both positive cases and edge cases

## Test Coverage

The test suite aims for comprehensive coverage of:
- All public methods
- Various SQL statement types
- Complex multi-table scenarios
- Edge cases and error conditions

## Continuous Integration

Tests are automatically run on GitHub Actions for PHP versions:
- 7.2, 7.3, 7.4
- 8.0, 8.1, 8.2, 8.3, 8.4, 8.5, 8.6

See `.github/workflows/ci.yml` for CI configuration.

## Troubleshooting

### PHPUnit Not Found

If you get "PHPUnit not found" error:
```bash
composer install
```

### Tests Failing

1. Check PHP version compatibility
2. Ensure all dependencies are installed
3. Check for syntax errors in test files
4. Run specific failing test with `--verbose` flag for details

### Permission Issues

If you encounter permission issues:
```bash
chmod +x vendor/bin/phpunit
```

## Resources

- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- [PHP Light SQL Parser Repository](https://github.com/marcocesarato/PHP-Light-SQL-Parser-Class)
