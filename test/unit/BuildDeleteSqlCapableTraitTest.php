<?php

namespace RebelCode\Storage\Resource\Sql\UnitTest;

use Dhii\Expression\LogicalExpressionInterface;
use Dhii\Storage\Resource\Sql\OrderInterface;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use RebelCode\Storage\Resource\Sql\BuildDeleteSqlCapableTrait as TestSubject;
use Xpmock\TestCase;

/**
 * Tests {@see TestSubject}.
 *
 * @since [*next-version*]
 */
class BuildDeleteSqlCapableTraitTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'RebelCode\Storage\Resource\Sql\BuildDeleteSqlCapableTrait';

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
                                    '_buildSqlOrderBy',
                                    '_buildSqlLimit',
                                    '_buildSqlOffset',
                                    '_escapeSqlReference',
                                    '_buildSqlWhereClause',
                                ]
                            )
                        );

        $mock = $builder->getMockForTrait();
        $mock->method('_escapeSqlReferences')->willReturnArgument(0);

        return $mock;
    }

    /**
     * Creates a mock OrderInterface instance.
     *
     * @since [*next-version*]
     *
     * @param string $entity The entity.
     * @param string $field  The field.
     * @param bool   $isAsc  The ascending flag.
     *
     * @return MockObject|OrderInterface The created instance.
     */
    public function createOrder($entity = '', $field = '', $isAsc = true)
    {
        $mock = $this->getMockBuilder('Dhii\Storage\Resource\Sql\OrderInterface')
                     ->setMethods(
                         [
                             'getEntity',
                             'getField',
                             'isAscending',
                         ]
                     )
                     ->getMockForAbstractClass();

        $mock->method('getEntity')->willReturn($entity);
        $mock->method('getField')->willReturn($field);
        $mock->method('isAscending')->willReturn($isAsc);

        return $mock;
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
     * Tests the DELETE SQL build method to assert whether the built query matches the given arguments.
     *
     * @since [*next-version*]
     */
    public function testBuildDeleteSql()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $condition = $this->createLogicalExpression(
            'or',
            [
                $this->createLogicalExpression('smaller', ['age', 18]),
                $this->createLogicalExpression('equals', ['verified', false]),
            ]
        );
        $ordering = [
            $this->createOrder(null, 'age'),
            $this->createOrder(null, 'verified', false),
        ];
        $nLimit = rand(0, 50);
        $nOffset = rand(50, 100);
        $valueHashMap = [
            '18'       => ':12345',
            'verified' => ':56789',
        ];
        $where = 'WHERE `user_age` < :12345 OR `acc_verified` = :56789';

        $subject->expects($this->once())
                ->method('_buildSqlWhereClause')
                ->with($condition, $valueHashMap)
                ->willReturn($where);
        $subject->expects($this->once())
                ->method('_buildSqlOrderBy')
                ->with($ordering)
                ->willReturn($orderBy = 'ORDER BY age ASC, verified DESC');
        $subject->expects($this->once())
                ->method('_buildSqlLimit')
                ->with($nLimit)
                ->willReturn($limit = 'LIMIT ' . $nLimit);
        $subject->expects($this->once())
                ->method('_buildSqlOffset')
                ->with($nOffset)
                ->willReturn($offset = 'OFFSET ' . $nOffset);
        $subject->method('_escapeSqlReference')->willReturnArgument(0);

        $table = uniqid('table');
        $expected = "DELETE FROM $table $where $orderBy $limit $offset;";

        $result = $reflect->_buildDeleteSql(
            $table,
            $condition,
            $ordering,
            $nLimit,
            $nOffset,
            $valueHashMap
        );

        $this->assertEquals($expected, $result, 'Expected and retrieved queries do not match.');
    }

    /**
     * Tests the DELETE SQL build method with no limit to assert whether the offset is ignored and the rest of
     * the query is built as expected.
     *
     * @since [*next-version*]
     */
    public function testBuildDeleteSqlNoLimit()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $condition = $this->createLogicalExpression(
            'or',
            [
                $this->createLogicalExpression('smaller', ['age', 18]),
                $this->createLogicalExpression('equals', ['verified', false]),
            ]
        );
        $ordering = [
            $this->createOrder(null, 'age'),
            $this->createOrder(null, 'verified', false),
        ];
        $nOffset = rand(50, 100);
        $valueHashMap = [
            '18'       => ':12345',
            'verified' => ':56789',
        ];
        $where = 'WHERE `user_age` < :12345 OR `acc_verified` = :56789';

        $subject->expects($this->once())
                ->method('_buildSqlWhereClause')
                ->with($condition, $valueHashMap)
                ->willReturn($where);
        $subject->expects($this->once())
                ->method('_buildSqlOrderBy')
                ->with($ordering)
                ->willReturn($orderBy = 'ORDER BY age ASC, verified DESC');
        $subject->expects($this->never())
                ->method('_buildSqlLimit');
        $subject->expects($this->never())
                ->method('_buildSqlOffset');
        $subject->method('_escapeSqlReference')->willReturnArgument(0);

        $table = uniqid('table');
        $expected = "DELETE FROM $table $where $orderBy;";

        $result = $reflect->_buildDeleteSql(
            $table,
            $condition,
            $ordering,
            null,
            $nOffset,
            $valueHashMap
        );

        $this->assertEquals($expected, $result, 'Expected and retrieved queries do not match.');
    }

    /**
     * Tests the DELETE SQL build method without an offset to assert whether the query is built without the OFFSET
     * and the remainder of the query is as expected.
     *
     * @since [*next-version*]
     */
    public function testBuildDeleteSqlNoOffset()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $condition = $this->createLogicalExpression(
            'or',
            [
                $this->createLogicalExpression('smaller', ['age', 18]),
                $this->createLogicalExpression('equals', ['verified', false]),
            ]
        );
        $ordering = [
            $this->createOrder(null, 'age'),
            $this->createOrder(null, 'verified', false),
        ];
        $nLimit = rand(0, 50);
        $valueHashMap = [
            '18'       => ':12345',
            'verified' => ':56789',
        ];
        $where = 'WHERE `user_age` < :12345 OR `acc_verified` = :56789';

        $subject->expects($this->once())
                ->method('_buildSqlWhereClause')
                ->with($condition, $valueHashMap)
                ->willReturn($where);
        $subject->expects($this->once())
                ->method('_buildSqlOrderBy')
                ->with($ordering)
                ->willReturn($orderBy = 'ORDER BY age ASC, verified DESC');
        $subject->expects($this->once())
                ->method('_buildSqlLimit')
                ->with($nLimit)
                ->willReturn($limit = 'LIMIT ' . $nLimit);
        $subject->expects($this->never())
                ->method('_buildSqlOffset');
        $subject->method('_escapeSqlReference')->willReturnArgument(0);

        $table = uniqid('table');
        $expected = "DELETE FROM $table $where $orderBy $limit;";

        $result = $reflect->_buildDeleteSql(
            $table,
            $condition,
            $ordering,
            $nLimit,
            null,
            $valueHashMap
        );

        $this->assertEquals($expected, $result, 'Expected and retrieved queries do not match.');
    }

    /**
     * Tests the DELETE SQL build method without an ordering to assert whether the query is built without the ORDER BY
     * and the remainder of the query is as expected.
     *
     * @since [*next-version*]
     */
    public function testBuildDeleteSqlNoOrdering()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $condition = $this->createLogicalExpression(
            'or',
            [
                $this->createLogicalExpression('smaller', ['age', 18]),
                $this->createLogicalExpression('equals', ['verified', false]),
            ]
        );
        $nLimit = rand(0, 50);
        $nOffset = rand(50, 100);
        $valueHashMap = [
            '18'       => ':12345',
            'verified' => ':56789',
        ];
        $where = 'WHERE `user_age` < :12345 OR `acc_verified` = :56789';

        $subject->expects($this->once())
                ->method('_buildSqlWhereClause')
                ->with($condition, $valueHashMap)
                ->willReturn($where);
        $subject->expects($this->never())
                ->method('_buildSqlOrderBy');
        $subject->expects($this->once())
                ->method('_buildSqlLimit')
                ->with($nLimit)
                ->willReturn($limit = 'LIMIT ' . $nLimit);
        $subject->expects($this->once())
                ->method('_buildSqlOffset')
                ->with($nOffset)
                ->willReturn($offset = 'OFFSET ' . $nOffset);
        $subject->method('_escapeSqlReference')->willReturnArgument(0);

        $table = uniqid('table');
        $expected = "DELETE FROM $table $where $limit $offset;";

        $result = $reflect->_buildDeleteSql(
            $table,
            $condition,
            null,
            $nLimit,
            $nOffset,
            $valueHashMap
        );

        $this->assertEquals($expected, $result, 'Expected and retrieved queries do not match.');
    }

    /**
     * Tests the DELETE SQL build method without an ordering and a limit to assert whether the query is built without
     * the ORDER BY and LIMIT parts and the remainder of the query is as expected.
     *
     * @since [*next-version*]
     */
    public function testBuildDeleteSqlNoOrderingNoLimit()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $condition = $this->createLogicalExpression(
            'or',
            [
                $this->createLogicalExpression('smaller', ['age', 18]),
                $this->createLogicalExpression('equals', ['verified', false]),
            ]
        );
        $valueHashMap = [
            '18'       => ':12345',
            'verified' => ':56789',
        ];
        $where = 'WHERE `user_age` < :12345 OR `acc_verified` = :56789';

        $subject->expects($this->once())
                ->method('_buildSqlWhereClause')
                ->with($condition, $valueHashMap)
                ->willReturn($where);
        $subject->expects($this->never())
                ->method('_buildSqlOrderBy');
        $subject->expects($this->never())
                ->method('_buildSqlLimit');
        $subject->expects($this->never())
                ->method('_buildSqlOffset');
        $subject->method('_escapeSqlReference')->willReturnArgument(0);

        $table = uniqid('table');
        $expected = "DELETE FROM $table $where;";

        $result = $reflect->_buildDeleteSql(
            $table,
            $condition,
            null,
            null,
            null,
            $valueHashMap
        );

        $this->assertEquals($expected, $result, 'Expected and retrieved queries do not match.');
    }

    /**
     * Tests the DELETE SQL build method with a null condition to assert whether the WHERE clause is omitted.
     *
     * @since [*next-version*]
     */
    public function testBuildDeleteSqlNullCondition()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $condition = null;
        $columnMap = [];
        $valueHashMap = [];

        $table = uniqid('table');
        $expected = "DELETE FROM $table;";

        $subject->method('_escapeSqlReference')->willReturnArgument(0);

        $result = $reflect->_buildDeleteSql(
            $table,
            $condition,
            $columnMap,
            $valueHashMap
        );

        $this->assertEquals($expected, $result, 'Expected and retrieved queries do not match.');
    }
}
