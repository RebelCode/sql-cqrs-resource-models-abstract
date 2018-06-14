<?php

namespace RebelCode\Resource\Storage\Sql\FuncTest;

use ArrayIterator;
use Dhii\Storage\Resource\Sql\EntityFieldInterface;
use Dhii\Util\String\StringableInterface as Stringable;
use InvalidArgumentException;
use OutOfRangeException;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use stdClass;
use Xpmock\TestCase;

/**
 * Tests {@see \RebelCode\Resource\Storage\Sql\BuildSqlGroupByClauseCapableT}.
 *
 * @since [*next-version*]
 */
class BuildSqlGroupByClauseCapableTraitTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'RebelCode\Storage\Resource\Sql\BuildSqlGroupByClauseCapableTrait';

    /**
     * Creates a new instance of the test subject.
     *
     * @since [*next-version*]
     *
     * @param array $methods Optional additional mock methods.
     *
     * @return MockObject
     */
    public function createInstance(array $methods = [])
    {
        $builder = $this->getMockBuilder(static::TEST_SUBJECT_CLASSNAME)
                        ->setMethods(
                            array_merge(
                                $methods,
                                [
                                    '_getSqlColumnName',
                                    '_escapeSqlReference',
                                    '_normalizeIterable',
                                    '_createOutOfRangeException',
                                    '__',
                                ]
                            )
                        );

        $mock = $builder->getMockForTrait();
        $mock->method('__')->willReturnArgument(0);

        return $mock;
    }

    /**
     * Creates an entity field mock instance.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable $entity The entity name.
     * @param string|Stringable $field  The field name.
     *
     * @return EntityFieldInterface
     */
    public function createEntityField($entity, $field)
    {
        return $this->mock('Dhii\Storage\Resource\Sql\EntityFieldInterface')
                    ->getEntity($entity)
                    ->getField($field)
                    ->new();
    }

    /**
     * Creates a mock that both extends a class and implements interfaces.
     *
     * This is particularly useful for cases where the mock is based on an
     * internal class, such as in the case with exceptions. Helps to avoid
     * writing hard-coded stubs.
     *
     * @since [*next-version*]
     *
     * @param string   $className      Name of the class for the mock to extend.
     * @param string[] $interfaceNames Names of the interfaces for the mock to implement.
     *
     * @return MockObject The object that extends and implements the specified class and interfaces.
     */
    public function mockClassAndInterfaces($className, $interfaceNames = [])
    {
        $paddingClassName = uniqid($className);
        $definition       = vsprintf(
            'abstract class %1$s extends %2$s implements %3$s {}',
            [
                $paddingClassName,
                $className,
                implode(', ', $interfaceNames),
            ]
        );
        eval($definition);

        return $this->getMockBuilder($paddingClassName)->getMockForAbstractClass();
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
     * Tests the group by building method with an array argument.
     *
     * @since [*next-version*]
     */
    public function testBuildSqlGroupByArray()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        // The input argument of fields to group by
        $grouping = [
            $f1 = uniqid('field1'),
            $f2 = uniqid('field2'),
            $f3 = uniqid('field3'),
        ];
        $count    = count($grouping);
        // The corresponding column names
        $c1 = uniqid('column1');
        $c2 = uniqid('column2');
        $c3 = uniqid('column3');
        // The escaped versions of the columns
        $e1 = uniqid('escaped1');
        $e2 = uniqid('escaped2');
        $e3 = uniqid('escaped3');

        $subject->expects($this->once())
                ->method('_normalizeIterable')
                ->with($grouping)
                ->willReturn($grouping);

        $subject->expects($this->exactly($count))
                ->method('_getSqlColumnName')
                ->withConsecutive([$f1], [$f2], [$f3])
                ->willReturnOnConsecutiveCalls($c1, $c2, $c3);

        $subject->expects($this->exactly($count))
                ->method('_escapeSqlReference')
                ->withConsecutive([$c1], [$c2], [$c3])
                ->willReturnOnConsecutiveCalls($e1, $e2, $e3);

        $expected = "GROUP BY $e1, $e2, $e3";
        $actual   = $reflect->_buildSqlGroupByClause($grouping);

        $this->assertEquals($expected, $actual, 'Expected and built GROUP BY portions do not match');
    }

    /**
     * Tests the group by building method with a traversable argument.
     *
     * @since [*next-version*]
     */
    public function testBuildSqlGroupByTraversable()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        // The input argument of fields to group by
        $grouping = new ArrayIterator([
            $f1 = uniqid('field1'),
            $f2 = uniqid('field2'),
            $f3 = uniqid('field3'),
        ]);
        $count    = count($grouping);
        // The corresponding column names
        $c1 = uniqid('column1');
        $c2 = uniqid('column2');
        $c3 = uniqid('column3');
        // The escaped versions of the columns
        $e1 = uniqid('escaped1');
        $e2 = uniqid('escaped2');
        $e3 = uniqid('escaped3');

        $subject->expects($this->once())
                ->method('_normalizeIterable')
                ->with($grouping)
                ->willReturn($grouping);

        $subject->expects($this->exactly($count))
                ->method('_getSqlColumnName')
                ->withConsecutive([$f1], [$f2], [$f3])
                ->willReturnOnConsecutiveCalls($c1, $c2, $c3);

        $subject->expects($this->exactly($count))
                ->method('_escapeSqlReference')
                ->withConsecutive([$c1], [$c2], [$c3])
                ->willReturnOnConsecutiveCalls($e1, $e2, $e3);

        $expected = "GROUP BY $e1, $e2, $e3";
        $actual   = $reflect->_buildSqlGroupByClause($grouping);

        $this->assertEquals($expected, $actual, 'Expected and built GROUP BY portions do not match');
    }

    /**
     * Tests the group by building method with a list of entity fields.
     *
     * @since [*next-version*]
     */
    public function testBuildSqlGroupByEntityFields()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        // The input argument of fields to group by
        $grouping = [
            $ef1 = $this->createEntityField($e1 = uniqid('entity1'), $f1 = uniqid('field1')),
            $ef2 = $this->createEntityField($e2 = uniqid('entity2'), $f2 = uniqid('field2')),
            $ef3 = $this->createEntityField($e3 = uniqid('entity3'), $f3 = uniqid('field3')),
        ];
        $count    = count($grouping);
        // The corresponding column names
        $c1 = uniqid('column1');
        $c2 = uniqid('column2');
        $c3 = uniqid('column3');
        // The escaped versions of the columns
        $es1 = uniqid('escaped1');
        $es2 = uniqid('escaped2');
        $es3 = uniqid('escaped3');

        $subject->expects($this->once())
                ->method('_normalizeIterable')
                ->with($grouping)
                ->willReturn($grouping);

        $subject->expects($this->exactly($count))
                ->method('_getSqlColumnName')
                ->withConsecutive([$f1], [$f2], [$f3])
                ->willReturnOnConsecutiveCalls($c1, $c2, $c3);

        $subject->expects($this->exactly($count))
                ->method('_escapeSqlReference')
                ->withConsecutive([$c1, $e1], [$c2, $e2], [$c3, $e3])
                ->willReturnOnConsecutiveCalls($es1, $es2, $es3);

        $expected = "GROUP BY $es1, $es2, $es3";
        $actual   = $reflect->_buildSqlGroupByClause($grouping);

        $this->assertEquals($expected, $actual, 'Expected and built GROUP BY portions do not match');
    }

    /**
     * Tests the group by building method with an invalid argument.
     *
     * @since [*next-version*]
     */
    public function testBuildSqlGroupByInvalidArg()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        // The input argument
        $grouping = uniqid('invalid');

        $subject->expects($this->once())
                ->method('_normalizeIterable')
                ->with($grouping)
                ->willThrowException(new InvalidArgumentException());

        $this->setExpectedException('InvalidArgumentException');

        $reflect->_buildSqlGroupByClause($grouping);
    }

    /**
     * Tests the group by building method with a list that contains an invalid element
     *
     * @since [*next-version*]
     */
    public function testBuildSqlGroupByInvalidElement()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        // The input argument of fields to group by
        $grouping = [
            $f1 = uniqid('field1'),
            $f2 = new stdClass(),
            $f3 = uniqid('field3'),
        ];

        $subject->expects($this->once())
                ->method('_normalizeIterable')
                ->with($grouping)
                ->willReturn($grouping);

        // Called twice, once for the valid element, once for the invalid one
        $subject->expects($this->exactly(2))
                ->method('_getSqlColumnName')
                ->withConsecutive([$f1], [$f2])
                ->willReturnCallback(function ($field) use ($f2) {
                    if ($field === $f2) {
                        throw new InvalidArgumentException();
                    }

                    return $field;
                });

        $subject->expects($this->once())
                ->method('_escapeSqlReference')
                ->willReturnArgument(0);

        $subject->expects($this->once())
                ->method('_createOutOfRangeException')
                ->willReturn(new OutOfRangeException());

        $this->setExpectedException('OutOfRangeException');

        $reflect->_buildSqlGroupByClause($grouping);
    }

    /**
     * Tests the group by building method with a list that contains an element that triggers the `_getSqlColumnName()`
     * method to throw an internal exception.
     *
     * @since [*next-version*]
     */
    public function testBuildSqlGroupByInternalException()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        // The input argument of fields to group by
        $grouping = [
            $f1 = uniqid('field1'),
            $f2 = uniqid('field1'),
            $f3 = uniqid('field3'),
        ];

        $subject->expects($this->once())
                ->method('_normalizeIterable')
                ->with($grouping)
                ->willReturn($grouping);

        // Called twice, once for the first element, once for the element that will trigger the throw
        $subject->expects($this->exactly(2))
                ->method('_getSqlColumnName')
                ->withConsecutive([$f1], [$f2])
                ->willReturnCallback(function ($field) use ($f2) {
                    if ($field === $f2) {
                        throw $this->mockClassAndInterfaces('Exception', [
                            'Dhii\Exception\InternalExceptionInterface',
                        ]);
                    }

                    return $field;
                });

        $subject->expects($this->once())
                ->method('_escapeSqlReference')
                ->willReturnArgument(0);

        $this->setExpectedException('Dhii\Exception\InternalExceptionInterface');

        $reflect->_buildSqlGroupByClause($grouping);
    }
}
