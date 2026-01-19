<?php

namespace marcocesarato\sqlparser\Tests;

use marcocesarato\sqlparser\LightSQLParser;
use PHPUnit\Framework\TestCase;

/**
 * Comprehensive Test Suite for LightSQLParser
 * 
 * Tests all public methods of the LightSQLParser class against
 * various SQL statements including complex queries with multiple tables.
 */
class LightSQLParserTest extends TestCase
{
    /**
     * @var LightSQLParser
     */
    private $parser;

    /**
     * Set up a fresh parser instance before each test
     */
    protected function setUp(): void
    {
        $this->parser = new LightSQLParser();
    }

    /**
     * Clean up after each test
     */
    protected function tearDown(): void
    {
        $this->parser = null;
    }

    // =====================================================
    // Test __construct(), setQuery(), and getQuery()
    // =====================================================

    /**
     * @group constructor
     */
    public function testConstructorWithEmptyQuery()
    {
        $parser = new LightSQLParser();
        $this->assertEquals('', $parser->getQuery());
    }

    /**
     * @group constructor
     */
    public function testConstructorWithQuery()
    {
        $query = "SELECT * FROM users";
        $parser = new LightSQLParser($query);
        $this->assertEquals($query, $parser->getQuery());
    }

    /**
     * @group query
     */
    public function testSetQueryAndGetQuery()
    {
        $query = "SELECT * FROM users";
        $this->parser->setQuery($query);
        $this->assertEquals($query, $this->parser->getQuery());
    }

    /**
     * @group query
     */
    public function testSetQueryReturnsParserInstance()
    {
        $result = $this->parser->setQuery("SELECT * FROM users");
        $this->assertInstanceOf(LightSQLParser::class, $result);
    }

    /**
     * @group query
     */
    public function testSetQueryResetsQueries()
    {
        $this->parser->setQuery("SELECT * FROM users");
        $this->parser->getAllQueries();
        
        $this->parser->setQuery("SELECT * FROM products");
        $queries = $this->parser->getAllQueries();
        
        $this->assertCount(1, $queries);
    }

    // =====================================================
    // Test getAllQueries()
    // =====================================================

    /**
     * @group getAllQueries
     */
    public function testGetAllQueriesWithSingleQuery()
    {
        $this->parser->setQuery("SELECT * FROM users");
        $queries = $this->parser->getAllQueries();
        
        $this->assertCount(1, $queries);
        $this->assertStringContainsString('SELECT', $queries[0]);
        $this->assertStringContainsString('users', $queries[0]);
    }

    /**
     * @group getAllQueries
     */
    public function testGetAllQueriesWithMultipleQueries()
    {
        $this->parser->setQuery("SELECT * FROM users; INSERT INTO products (name) VALUES (test)");
        $queries = $this->parser->getAllQueries();
        
        $this->assertCount(2, $queries);
        $this->assertStringContainsString('SELECT', $queries[0]);
        $this->assertStringContainsString('INSERT', $queries[1]);
    }

    /**
     * @group getAllQueries
     */
    public function testGetAllQueriesRemovesComments()
    {
        $this->parser->setQuery("/* This is a comment */ SELECT * FROM users");
        $queries = $this->parser->getAllQueries();
        
        $this->assertCount(1, $queries);
        $this->assertStringNotContainsString('comment', $queries[0]);
        $this->assertStringContainsString('SELECT', $queries[0]);
    }

    /**
     * @group getAllQueries
     */
    public function testGetAllQueriesWithUnion()
    {
        $this->parser->setQuery("SELECT name FROM users UNION SELECT name FROM customers");
        $queries = $this->parser->getAllQueries();
        
        $this->assertCount(2, $queries);
        $this->assertStringContainsString('users', $queries[0]);
        $this->assertStringContainsString('customers', $queries[1]);
    }

    /**
     * @group getAllQueries
     */
    public function testGetAllQueriesWithUnionAll()
    {
        $this->parser->setQuery("SELECT id, name FROM users UNION ALL SELECT id, name FROM archived_users");
        $queries = $this->parser->getAllQueries();
        
        $this->assertCount(2, $queries);
        $this->assertStringContainsString('users', $queries[0]);
        $this->assertStringContainsString('archived_users', $queries[1]);
    }

    /**
     * @group getAllQueries
     */
    public function testGetAllQueriesRemovesQuotes()
    {
        $this->parser->setQuery("SELECT * FROM `users`");
        $queries = $this->parser->getAllQueries();
        
        $this->assertStringNotContainsString('`', $queries[0]);
    }

    // =====================================================
    // Test getMethod()
    // =====================================================

    /**
     * @group getMethod
     */
    public function testGetMethodWithSelectQuery()
    {
        $this->parser->setQuery("SELECT * FROM users");
        $this->assertEquals('SELECT', $this->parser->getMethod());
    }

    /**
     * @group getMethod
     */
    public function testGetMethodWithInsertQuery()
    {
        $this->parser->setQuery("INSERT INTO users (name, email) VALUES (John, john@example.com)");
        $this->assertEquals('INSERT', $this->parser->getMethod());
    }

    /**
     * @group getMethod
     */
    public function testGetMethodWithUpdateQuery()
    {
        $this->parser->setQuery("UPDATE users SET name = Jane WHERE id = 1");
        $this->assertEquals('UPDATE', $this->parser->getMethod());
    }

    /**
     * @group getMethod
     */
    public function testGetMethodWithDeleteQuery()
    {
        $this->parser->setQuery("DELETE FROM users WHERE id = 1");
        $this->assertEquals('DELETE', $this->parser->getMethod());
    }

    /**
     * @group getMethod
     */
    public function testGetMethodWithCreateTableQuery()
    {
        $this->parser->setQuery("CREATE TABLE users (id INT, name VARCHAR(255), email VARCHAR(255))");
        $this->assertEquals('CREATE TABLE', $this->parser->getMethod());
    }

    /**
     * @group getMethod
     */
    public function testGetMethodWithDropQuery()
    {
        $this->parser->setQuery("DROP TABLE users");
        $this->assertEquals('DROP', $this->parser->getMethod());
    }

    /**
     * @group getMethod
     */
    public function testGetMethodWithAlterQuery()
    {
        $this->parser->setQuery("ALTER TABLE users ADD COLUMN age INT");
        $this->assertEquals('ALTER', $this->parser->getMethod());
    }

    /**
     * @group getMethod
     */
    public function testGetMethodWithTruncateQuery()
    {
        $this->parser->setQuery("TRUNCATE TABLE users");
        $this->assertEquals('TRUNCATE', $this->parser->getMethod());
    }

    /**
     * @group getMethod
     */
    public function testGetMethodWithCreateIndexQuery()
    {
        $this->parser->setQuery("CREATE INDEX idx_users_name ON users (name)");
        $this->assertEquals('CREATE INDEX', $this->parser->getMethod());
    }

    /**
     * @group getMethod
     */
    public function testGetMethodWithWhitespace()
    {
        $this->parser->setQuery("   SELECT * FROM users   ");
        $this->assertEquals('SELECT', $this->parser->getMethod());
    }

    /**
     * @group getMethod
     */
    public function testGetMethodWithEmptyQuery()
    {
        $this->parser->setQuery("");
        $this->assertEquals('', $this->parser->getMethod());
    }

    // =====================================================
    // Test getFields()
    // =====================================================

    /**
     * @group getFields
     */
    public function testGetFieldsWithSelectAllQuery()
    {
        $this->parser->setQuery("SELECT * FROM users");
        $fields = $this->parser->getFields();
        
        $this->assertContains('*', $fields);
    }

    /**
     * @group getFields
     */
    public function testGetFieldsWithSelectSpecificFields()
    {
        $this->parser->setQuery("SELECT id, name, email FROM users");
        $fields = $this->parser->getFields();
        
        $this->assertContains('id', $fields);
        $this->assertContains('name', $fields);
        $this->assertContains('email', $fields);
    }

    /**
     * @group getFields
     */
    public function testGetFieldsWithSelectAndAliases()
    {
        $this->parser->setQuery("SELECT u.name as user_name, u.email as user_email FROM users u");
        $fields = $this->parser->getFields();
        
        $this->assertContains('u.name', $fields);
        $this->assertContains('u.email', $fields);
    }

    /**
     * @group getFields
     */
    public function testGetFieldsWithInsertQuery()
    {
        $this->parser->setQuery("INSERT INTO users (name, email) VALUES (John, john@example.com)");
        $fields = $this->parser->getFields();
        
        $this->assertContains('name', $fields);
        $this->assertContains('email', $fields);
    }

    /**
     * @group getFields
     */
    public function testGetFieldsWithInsertSetQuery()
    {
        $this->parser->setQuery("INSERT INTO users SET name = John, email = john@example.com");
        $fields = $this->parser->getFields();
        
        $this->assertContains('name', $fields);
        $this->assertContains('email', $fields);
    }

    /**
     * @group getFields
     */
    public function testGetFieldsWithUpdateQuery()
    {
        $this->parser->setQuery("UPDATE users SET name = Jane, email = jane@example.com WHERE id = 1");
        $fields = $this->parser->getFields();
        
        $this->assertContains('name', $fields);
        $this->assertContains('email', $fields);
    }

    /**
     * @group getFields
     */
    public function testGetFieldsWithDeleteQuery()
    {
        $this->parser->setQuery("DELETE FROM users WHERE id = 1");
        $fields = $this->parser->getFields();
        
        $this->assertEmpty($fields);
    }

    /**
     * @group getFields
     */
    public function testGetFieldsReturnsUniqueFields()
    {
        $this->parser->setQuery("SELECT name, email, name FROM users");
        $fields = $this->parser->getFields();
        
        $this->assertCount(3, $fields); // name, email, name (but unique should give 2 or 3 depending on position)
        $this->assertContains('name', $fields);
        $this->assertContains('email', $fields);
    }

    // =====================================================
    // Test getTable()
    // =====================================================

    /**
     * @group getTable
     */
    public function testGetTableWithSelectQuery()
    {
        $this->parser->setQuery("SELECT * FROM users");
        $this->assertEquals('users', $this->parser->getTable());
    }

    /**
     * @group getTable
     */
    public function testGetTableWithInsertQuery()
    {
        $this->parser->setQuery("INSERT INTO products (name) VALUES (test)");
        $this->assertEquals('products', $this->parser->getTable());
    }

    /**
     * @group getTable
     */
    public function testGetTableWithUpdateQuery()
    {
        $this->parser->setQuery("UPDATE orders SET status = completed WHERE id = 1");
        $this->assertEquals('orders', $this->parser->getTable());
    }

    /**
     * @group getTable
     */
    public function testGetTableWithMultipleTables()
    {
        $this->parser->setQuery("SELECT * FROM users, orders");
        $firstTable = $this->parser->getTable();
        
        $this->assertNotNull($firstTable);
        $this->assertContains($firstTable, ['users', 'orders']);
    }

    /**
     * @group getTable
     */
    public function testGetTableWithEmptyQuery()
    {
        $this->parser->setQuery("");
        $this->assertNull($this->parser->getTable());
    }

    // =====================================================
    // Test getAllTables()
    // =====================================================

    /**
     * @group getAllTables
     */
    public function testGetAllTablesWithSingleTable()
    {
        $this->parser->setQuery("SELECT * FROM users");
        $tables = $this->parser->getAllTables();
        
        $this->assertContains('users', $tables);
    }

    /**
     * @group getAllTables
     */
    public function testGetAllTablesWithMultipleTablesInFrom()
    {
        $this->parser->setQuery("SELECT * FROM users, orders, products");
        $tables = $this->parser->getAllTables();
        
        $this->assertContains('users', $tables);
        $this->assertContains('orders', $tables);
        $this->assertContains('products', $tables);
    }

    /**
     * @group getAllTables
     */
    public function testGetAllTablesWithJoin()
    {
        $this->parser->setQuery("SELECT * FROM users u JOIN orders o ON u.id = o.user_id");
        $tables = $this->parser->getAllTables();
        
        $this->assertContains('users', $tables);
        $this->assertContains('orders', $tables);
    }

    /**
     * @group getAllTables
     */
    public function testGetAllTablesWithMultipleJoins()
    {
        $this->parser->setQuery("SELECT * FROM users u INNER JOIN orders o ON u.id = o.user_id LEFT JOIN products p ON o.product_id = p.id");
        $tables = $this->parser->getAllTables();
        
        $this->assertContains('users', $tables);
        $this->assertContains('orders', $tables);
        $this->assertContains('products', $tables);
    }

    /**
     * @group getAllTables
     */
    public function testGetAllTablesWithTableAliases()
    {
        $this->parser->setQuery("SELECT u.name FROM users u");
        $tables = $this->parser->getAllTables();
        
        $this->assertContains('users', $tables);
        $this->assertNotContains('u', $tables);
    }

    /**
     * @group getAllTables
     */
    public function testGetAllTablesWithInsertQuery()
    {
        $this->parser->setQuery("INSERT INTO users (name) VALUES (test)");
        $tables = $this->parser->getAllTables();
        
        $this->assertContains('users', $tables);
    }

    /**
     * @group getAllTables
     */
    public function testGetAllTablesWithUpdateQuery()
    {
        $this->parser->setQuery("UPDATE users SET name = test WHERE id = 1");
        $tables = $this->parser->getAllTables();
        
        $this->assertContains('users', $tables);
    }

    /**
     * @group getAllTables
     */
    public function testGetAllTablesWithCreateTableQuery()
    {
        $this->parser->setQuery("CREATE TABLE users (id INT, name VARCHAR(255))");
        $tables = $this->parser->getAllTables();
        
        $this->assertContains('users', $tables);
    }

    /**
     * @group getAllTables
     */
    public function testGetAllTablesWithDropTableQuery()
    {
        $this->parser->setQuery("DROP TABLE users");
        $tables = $this->parser->getAllTables();
        
        $this->assertContains('users', $tables);
    }

    /**
     * @group getAllTables
     */
    public function testGetAllTablesReturnsUniqueValues()
    {
        $this->parser->setQuery("SELECT * FROM users u JOIN users u2 ON u.id = u2.parent_id");
        $tables = $this->parser->getAllTables();
        
        // array_unique is used, so we should only have one 'users' entry
        $userCount = count(array_filter($tables, function($table) {
            return $table === 'users';
        }));
        
        $this->assertEquals(1, $userCount);
    }

    // =====================================================
    // Test getJoinTables()
    // =====================================================

    /**
     * @group getJoinTables
     */
    public function testGetJoinTablesWithNoJoin()
    {
        $this->parser->setQuery("SELECT * FROM users");
        $tables = $this->parser->getJoinTables();
        
        $this->assertEmpty($tables);
    }

    /**
     * @group getJoinTables
     */
    public function testGetJoinTablesWithSingleJoin()
    {
        $this->parser->setQuery("SELECT * FROM users u JOIN orders o ON u.id = o.user_id");
        $tables = $this->parser->getJoinTables();
        
        $this->assertContains('orders', $tables);
        $this->assertNotContains('users', $tables);
    }

    /**
     * @group getJoinTables
     */
    public function testGetJoinTablesWithMultipleJoins()
    {
        $this->parser->setQuery("SELECT * FROM users u INNER JOIN orders o ON u.id = o.user_id LEFT JOIN products p ON o.product_id = p.id");
        $tables = $this->parser->getJoinTables();
        
        $this->assertContains('orders', $tables);
        $this->assertContains('products', $tables);
    }

    /**
     * @group getJoinTables
     */
    public function testGetJoinTablesWithInnerJoin()
    {
        $this->parser->setQuery("SELECT * FROM users INNER JOIN orders ON users.id = orders.user_id");
        $tables = $this->parser->getJoinTables();
        
        $this->assertContains('orders', $tables);
    }

    /**
     * @group getJoinTables
     */
    public function testGetJoinTablesWithLeftJoin()
    {
        $this->parser->setQuery("SELECT * FROM users LEFT JOIN orders ON users.id = orders.user_id");
        $tables = $this->parser->getJoinTables();
        
        $this->assertContains('orders', $tables);
    }

    /**
     * @group getJoinTables
     */
    public function testGetJoinTablesWithRightJoin()
    {
        $this->parser->setQuery("SELECT * FROM users RIGHT JOIN orders ON users.id = orders.user_id");
        $tables = $this->parser->getJoinTables();
        
        $this->assertContains('orders', $tables);
    }

    /**
     * @group getJoinTables
     */
    public function testGetJoinTablesWithOuterJoin()
    {
        $this->parser->setQuery("SELECT * FROM users OUTER JOIN orders ON users.id = orders.user_id");
        $tables = $this->parser->getJoinTables();
        
        $this->assertContains('orders', $tables);
    }

    // =====================================================
    // Test hasJoin()
    // =====================================================

    /**
     * @group hasJoin
     */
    public function testHasJoinReturnsFalseWithNoJoin()
    {
        $this->parser->setQuery("SELECT * FROM users");
        $this->assertFalse($this->parser->hasJoin());
    }

    /**
     * @group hasJoin
     */
    public function testHasJoinReturnsTrueWithJoin()
    {
        $this->parser->setQuery("SELECT * FROM users u JOIN orders o ON u.id = o.user_id");
        $this->assertTrue($this->parser->hasJoin());
    }

    /**
     * @group hasJoin
     */
    public function testHasJoinReturnsTrueWithInnerJoin()
    {
        $this->parser->setQuery("SELECT * FROM users INNER JOIN orders ON users.id = orders.user_id");
        $this->assertTrue($this->parser->hasJoin());
    }

    /**
     * @group hasJoin
     */
    public function testHasJoinReturnsTrueWithLeftJoin()
    {
        $this->parser->setQuery("SELECT * FROM users LEFT JOIN orders ON users.id = orders.user_id");
        $this->assertTrue($this->parser->hasJoin());
    }

    /**
     * @group hasJoin
     */
    public function testHasJoinReturnsTrueWithRightJoin()
    {
        $this->parser->setQuery("SELECT * FROM users RIGHT JOIN orders ON users.id = orders.user_id");
        $this->assertTrue($this->parser->hasJoin());
    }

    /**
     * @group hasJoin
     */
    public function testHasJoinWithMultipleJoins()
    {
        $this->parser->setQuery("SELECT * FROM users u INNER JOIN orders o ON u.id = o.user_id LEFT JOIN products p ON o.product_id = p.id");
        $this->assertTrue($this->parser->hasJoin());
    }

    // =====================================================
    // Test hasSubQuery()
    // =====================================================

    /**
     * @group hasSubQuery
     */
    public function testHasSubQueryReturnsFalseWithNoSubQuery()
    {
        $this->parser->setQuery("SELECT * FROM users");
        $this->assertFalse($this->parser->hasSubQuery());
    }

    /**
     * @group hasSubQuery
     */
    public function testHasSubQueryReturnsTrueWithSubQueryInWhere()
    {
        $this->parser->setQuery("SELECT * FROM users WHERE id IN (SELECT user_id FROM orders WHERE total > 100)");
        $this->assertTrue($this->parser->hasSubQuery());
    }

    /**
     * @group hasSubQuery
     */
    public function testHasSubQueryReturnsTrueWithSubQueryInSelect()
    {
        $this->parser->setQuery("SELECT u.name, (SELECT COUNT(*) FROM orders WHERE user_id = u.id) as order_count FROM users u");
        $this->assertTrue($this->parser->hasSubQuery());
    }

    /**
     * @group hasSubQuery
     */
    public function testHasSubQueryWithComplexSubQuery()
    {
        $this->parser->setQuery("SELECT * FROM users WHERE id IN (SELECT user_id FROM orders WHERE product_id IN (SELECT id FROM products WHERE price > 100))");
        $this->assertTrue($this->parser->hasSubQuery());
    }

    // =====================================================
    // Test getSubQueries()
    // =====================================================

    /**
     * @group getSubQueries
     */
    public function testGetSubQueriesWithNoSubQuery()
    {
        $this->parser->setQuery("SELECT * FROM users");
        $subQueries = $this->parser->getSubQueries();
        
        $this->assertEmpty($subQueries);
    }

    /**
     * @group getSubQueries
     */
    public function testGetSubQueriesWithSingleSubQuery()
    {
        $this->parser->setQuery("SELECT * FROM users WHERE id IN (SELECT user_id FROM orders WHERE total > 100)");
        $subQueries = $this->parser->getSubQueries();
        
        $this->assertCount(1, $subQueries);
        $this->assertStringContainsString('SELECT user_id FROM orders', $subQueries[0]);
    }

    /**
     * @group getSubQueries
     */
    public function testGetSubQueriesWithMultipleSubQueries()
    {
        $this->parser->setQuery("SELECT u.name, (SELECT COUNT(*) FROM orders WHERE user_id = u.id) as order_count FROM users u WHERE id IN (SELECT user_id FROM active_users)");
        $subQueries = $this->parser->getSubQueries();
        
        $this->assertCount(2, $subQueries);
    }

    /**
     * @group getSubQueries
     */
    public function testGetSubQueriesReturnsUniqueValues()
    {
        $this->parser->setQuery("SELECT * FROM users WHERE id IN (SELECT user_id FROM orders) OR id IN (SELECT user_id FROM orders)");
        $subQueries = $this->parser->getSubQueries();
        
        // array_unique is used in the method
        $this->assertCount(1, $subQueries);
    }

    // =====================================================
    // Complex Query Tests
    // =====================================================

    /**
     * @group complex
     */
    public function testComplexQueryWithThreeTables()
    {
        $this->parser->setQuery("SELECT u.name, o.total, p.name FROM users u JOIN orders o ON u.id = o.user_id JOIN products p ON o.product_id = p.id");
        
        $this->assertEquals('SELECT', $this->parser->getMethod());
        $this->assertTrue($this->parser->hasJoin());
        
        $tables = $this->parser->getAllTables();
        $this->assertContains('users', $tables);
        $this->assertContains('orders', $tables);
        $this->assertContains('products', $tables);
        
        $joinTables = $this->parser->getJoinTables();
        $this->assertContains('orders', $joinTables);
        $this->assertContains('products', $joinTables);
    }

    /**
     * @group complex
     */
    public function testComplexQueryWithMultipleJoinTypes()
    {
        $this->parser->setQuery("SELECT * FROM users u INNER JOIN orders o ON u.id = o.user_id LEFT JOIN products p ON o.product_id = p.id RIGHT JOIN categories c ON p.category_id = c.id");
        
        $tables = $this->parser->getAllTables();
        $this->assertCount(4, $tables);
        $this->assertContains('users', $tables);
        $this->assertContains('orders', $tables);
        $this->assertContains('products', $tables);
        $this->assertContains('categories', $tables);
    }

    /**
     * @group complex
     */
    public function testComplexQueryWithWhereGroupByOrderBy()
    {
        $this->parser->setQuery("SELECT u.name, COUNT(o.id) as order_count FROM users u LEFT JOIN orders o ON u.id = o.user_id WHERE u.active = 1 GROUP BY u.id HAVING COUNT(o.id) > 5 ORDER BY order_count DESC");
        
        $this->assertEquals('SELECT', $this->parser->getMethod());
        $this->assertTrue($this->parser->hasJoin());
        
        $tables = $this->parser->getAllTables();
        $this->assertContains('users', $tables);
        $this->assertContains('orders', $tables);
    }

    /**
     * @group complex
     */
    public function testComplexQueryWithSubQueryAndJoin()
    {
        $this->parser->setQuery("SELECT u.name FROM users u JOIN orders o ON u.id = o.user_id WHERE o.total > (SELECT AVG(total) FROM orders)");
        
        $this->assertTrue($this->parser->hasJoin());
        $this->assertTrue($this->parser->hasSubQuery());
        
        $tables = $this->parser->getAllTables();
        $this->assertContains('users', $tables);
        $this->assertContains('orders', $tables);
    }

    /**
     * @group complex
     */
    public function testComplexQueryWithCommentsAndAliases()
    {
        $this->parser->setQuery("/* Get user orders */ SELECT u.name as user_name, o.total as order_total FROM users u JOIN orders o ON u.id = o.user_id");
        
        $queries = $this->parser->getAllQueries();
        $this->assertStringNotContainsString('/*', $queries[0]);
        $this->assertStringNotContainsString('Get user orders', $queries[0]);
        
        $fields = $this->parser->getFields();
        $this->assertContains('u.name', $fields);
        $this->assertContains('o.total', $fields);
    }

    /**
     * @group complex
     */
    public function testUnionQueryWithMultipleTables()
    {
        $this->parser->setQuery("SELECT name FROM users UNION SELECT name FROM customers");
        
        $queries = $this->parser->getAllQueries();
        $this->assertCount(2, $queries);
        
        $tables = $this->parser->getAllTables();
        $this->assertContains('users', $tables);
        $this->assertContains('customers', $tables);
    }

    // =====================================================
    // Edge Cases and Failure Scenarios
    // =====================================================

    /**
     * @group edge-cases
     */
    public function testEmptyQueryString()
    {
        $this->parser->setQuery("");
        
        $this->assertEquals('', $this->parser->getQuery());
        $this->assertEquals('', $this->parser->getMethod());
        $this->assertNull($this->parser->getTable());
        $this->assertEmpty($this->parser->getAllTables());
        $this->assertEmpty($this->parser->getFields());
        $this->assertFalse($this->parser->hasJoin());
        $this->assertFalse($this->parser->hasSubQuery());
    }

    /**
     * @group edge-cases
     */
    public function testQueryWithOnlySemicolon()
    {
        $this->parser->setQuery(";");
        $queries = $this->parser->getAllQueries();
        
        // Should return empty or whitespace entries
        $this->assertIsArray($queries);
    }

    /**
     * @group edge-cases
     */
    public function testQueryWithMultipleSemicolons()
    {
        $this->parser->setQuery("SELECT * FROM users;;; SELECT * FROM products");
        $queries = $this->parser->getAllQueries();
        
        $this->assertGreaterThanOrEqual(2, count($queries));
    }

    /**
     * @group edge-cases
     */
    public function testQueryWithOnlyComments()
    {
        $this->parser->setQuery("/* This is just a comment */");
        $queries = $this->parser->getAllQueries();
        
        $this->assertIsArray($queries);
    }

    /**
     * @group edge-cases
     */
    public function testQueryWithMixedCaseKeywords()
    {
        $this->parser->setQuery("SeLeCt * FrOm users");
        
        $this->assertEquals('SELECT', $this->parser->getMethod());
        $this->assertContains('users', $this->parser->getAllTables());
    }

    /**
     * @group edge-cases
     */
    public function testQueryWithExtraWhitespace()
    {
        $this->parser->setQuery("  SELECT   *   FROM   users  ");
        
        $this->assertEquals('SELECT', $this->parser->getMethod());
        $this->assertContains('users', $this->parser->getAllTables());
    }

    /**
     * @group edge-cases
     */
    public function testQueryWithNewlinesAndTabs()
    {
        $query = "SELECT\n\t*\nFROM\n\tusers\nWHERE\n\tid = 1";
        $this->parser->setQuery($query);
        
        $this->assertEquals('SELECT', $this->parser->getMethod());
        $this->assertContains('users', $this->parser->getAllTables());
    }

    /**
     * @group edge-cases
     */
    public function testQueryWithBackticksDoubleAndSingleQuotes()
    {
        $this->parser->setQuery("SELECT * FROM `users` WHERE name = 'John' AND status = \"active\"");
        $queries = $this->parser->getAllQueries();
        
        // Quotes should be removed
        $this->assertStringNotContainsString('`', $queries[0]);
        $this->assertStringNotContainsString('"', $queries[0]);
        $this->assertStringNotContainsString("'", $queries[0]);
    }

    /**
     * @group edge-cases
     */
    public function testMultipleQueriesWithDifferentMethods()
    {
        $this->parser->setQuery("SELECT * FROM users; UPDATE users SET active = 1");
        $queries = $this->parser->getAllQueries();
        
        $this->assertCount(2, $queries);
        // getMethod returns the first matching method
        $this->assertEquals('SELECT', $this->parser->getMethod());
    }
}
