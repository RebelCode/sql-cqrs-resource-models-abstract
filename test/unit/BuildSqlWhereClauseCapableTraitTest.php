<?php

namespace RebelCode\Storage\Resource\Sql\UnitTest;

use Dhii\Expression\LogicalExpressionInterface;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use RebelCode\Storage\Resource\Sql\BuildSqlWhereClauseCapableTrait as TestSubject;
use Xpmock\TestCase;

/**
 * Tests {@see TestSubject}.
 *
 * @since [*next-version*]
 */
class BuildSqlWhereClauseCapableTraitTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'RebelCode\Storage\Resource\Sql\BuildSqlWhereClauseCapableTrait';

    /**
     * Creates a new instance of the test subject.
     *
     * @since [*next-version*]
     *
     * @return MockObject|TestSubject
     */
    public function createInstance()
    {
        $builder = $this->getMockBuilder(static::TEST_SUBJECT_CLASSNAME)
                        ->setMethods(
                            [
                                '_renderSqlCondition',
                                '_normalizeString',
                            ]
                        );

        $mock = $builder->getMockForTrait();
        $mock->method('_normalizeString')->willReturnCallback(
            function($input) {
                return strval($input);
            }
        );

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
     * Tests the SQL WHERE build method.
     *
     * @since [*next-version*]
     */
    public function testBuildSqlWhereClause()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $condition = $this->createLogicalExpression(
            'and',
            [
                $this->createLogicalExpression('equals', ['name', 'foobar']),
                $this->createLogicalExpression('greater', ['age', 17]),
            ]
        );
        $rCondition = '`name` = :12345 AND `age` > 17';
        $valueHashMap = [
            'foobar' => ':12345',
            'age'    => ':45678',
        ];

        $subject->expects($this->once())
                ->method('_renderSqlCondition')
                ->with($condition, $valueHashMap)
                ->willReturn($rCondition);

        $expected = 'WHERE ' . $rCondition;
        $result = $reflect->_buildSqlWhereClause($condition, $valueHashMap);

        $this->assertEquals($expected, $result, 'Expected and retrieved WHERE clauses are not the same.');
    }

    /**
     * Tests the SQL WHERE build method.
     *
     * @since [*next-version*]
     */
    public function testBuildSqlWhereClauseNullCondition()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $result = $reflect->_buildSqlWhereClause(null);

        $this->assertEquals('', $result, 'Retrieved WHERE clause is not empty.');
    }
}
