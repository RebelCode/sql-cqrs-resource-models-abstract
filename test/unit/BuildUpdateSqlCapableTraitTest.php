<?php

namespace RebelCode\Storage\Resource\Sql\UnitTest;

use Dhii\Expression\ExpressionInterface;
use Dhii\Expression\LogicalExpressionInterface;
use InvalidArgumentException;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use RebelCode\Storage\Resource\Sql\BuildUpdateSqlCapableTrait as TestSubject;
use Xpmock\TestCase;

/**
 * Tests {@see TestSubject}.
 *
 * @since [*next-version*]
 */
class BuildUpdateSqlCapableTraitTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'RebelCode\Storage\Resource\Sql\BuildUpdateSqlCapableTrait';

    /**
     * Creates a new instance of the test subject.
     *
     * @since [*next-version*]
     *
     * @param array $methods Optional additional mock methods.
     *
     * @return MockObject|TestSubject
     */
    public function createInstance(array $methods = [])
    {
        $builder = $this->getMockBuilder(static::TEST_SUBJECT_CLASSNAME)
                        ->setMethods(
                            array_merge(
                                $methods,
                                [
                                    '_escapeSqlReferences',
                                    '_renderSqlExpression',
                                    '_buildSqlWhereClause',
                                    '_normalizeString',
                                    '_countIterable',
                                    '_createInvalidArgumentException',
                                    '__',
                                ]
                            )
                        );

        $mock = $builder->getMockForTrait();
        $mock->method('_escapeSqlReferences')->willReturnCallback(
            function($input) {
                return is_array($input)
                    ? implode(', ', $input)
                    : $input;
            }
        );
        $mock->method('__')->willReturnArgument(0);
        $mock->method('_createInvalidArgumentException')->willReturnCallback(
            function($m, $c, $p) {
                return new InvalidArgumentException($m, $c, $p);
            }
        );
        $mock->method('_countIterable')->willReturnCallback(
            function($i) {
                return count($i);
            }
        );
        $mock->method('_normalizeString')->willReturnArgument(0);

        return $mock;
    }

    /**
     * Creates an expression mock instance.
     *
     * @since [*next-version*]
     *
     * @param string $type  The expression type.
     * @param array  $terms The expression terms.
     *
     * @return ExpressionInterface The created expression instance.
     */
    public function createExpression($type, $terms)
    {
        return $this->mock('Dhii\Expression\ExpressionInterface')
                    ->getType($type)
                    ->getTerms($terms)
                    ->new();
    }

    /**
     * Creates an expression mock instance.
     *
     * @since [*next-version*]
     *
     * @param string $type    The expression type.
     * @param array  $terms   The expression terms.
     * @param bool   $negated Optional negation flag.
     *
     * @return LogicalExpressionInterface The created expression instance.
     */
    public function createLogicalExpression($type, $terms, $negated = false)
    {
        return $this->mock('Dhii\Expression\LogicalExpressionInterface')
                    ->getType($type)
                    ->getTerms($terms)
                    ->isNegated($negated)
                    ->new();
    }

    /**
     * Tests whether a valid instance of the test subject can be created.
     *
     * @since [*next-version*]
     */
    public function testCanBeCreated()
    {
        $subject = $this->createInstance();

        $this->assertInternalType(
            'object',
            $subject,
            'An instance of the test subject could not be created'
        );
    }

    /**
     * Tests the UPDATE SQL build method with a change set of values.
     *
     * @since [*next-version*]
     */
    public function testBuildUpdateSql()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $table = 'my_table';
        $changeSet = [
            'name'    => 'foo',
            'surname' => 'bar',
        ];
        $valueHashMap = [
            '10'  => ':123',
            'foo' => ':456',
        ];

        $subject->expects($this->once())
                ->method('_buildSqlUpdateSet')
                ->willReturn($set = '`name` = :456, `surname` = "bar"');

        $condition = $this->createLogicalExpression('equal', ['age', 10]);
        $where = 'WHERE age = :123';
        $subject->expects($this->once())
                ->method('_buildSqlWhereClause')
                ->with($condition, $valueHashMap)
                ->willReturn($where);

        $expected = "UPDATE $table SET $set $where;";

        $this->assertEquals(
            $expected,
            $reflect->_buildUpdateSql($table, $changeSet, $condition, $valueHashMap),
            'Expected and retrieved UPDATE queries do not match.'
        );
    }

    /**
     * Tests the UPDATE SQL build method without a condition.
     *
     * @since [*next-version*]
     */
    public function testBuildUpdateSqlNoCondition()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $table = 'my_table';
        $changeSet = [
            'age'  => $cExpr1 = $this->createExpression('plus', ['age', 1]),
            'name' => $cExpr2 = $this->createExpression('string', ['foobar']),
        ];
        $valueHashMap = [
            '1'      => ':123',
            'foobar' => ':456',
        ];

        $subject->expects($this->once())
                ->method('_buildSqlUpdateSet')
                ->willReturn($set = '`age` = age + 1, `name` = "foobar"');

        $condition = null;
        $where = '';
        $subject->expects($this->once())
                ->method('_buildSqlWhereClause')
                ->with($condition, $valueHashMap)
                ->willReturn($where);

        $expected = "UPDATE $table SET $set;";

        $this->assertEquals(
            $expected,
            $reflect->_buildUpdateSql($table, $changeSet, $condition, $valueHashMap),
            'Expected and retrieved UPDATE queries do not match.'
        );
    }

    /**
     * Tests the UPDATE SQL build method with an empty change set to assert whether an exception is thrown.
     *
     * @since [*next-version*]
     */
    public function testBuildUpdateSqlNoChangeSet()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $this->setExpectedException('InvalidArgumentException');

        $reflect->_buildUpdateSql('my_table', []);
    }
}
