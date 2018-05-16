<?php

namespace RebelCode\Storage\Resource\Sql\FuncTest;

use Dhii\Storage\Resource\Sql\OrderInterface;
use InvalidArgumentException;
use OutOfRangeException;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use RebelCode\Storage\Resource\Sql\BuildSqlOrderByCapableTrait as TestSubject;
use Xpmock\TestCase;

/**
 * Tests {@see TestSubject}.
 *
 * @since [*next-version*]
 */
class BuildSqlOrderByCapableTraitTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'RebelCode\Storage\Resource\Sql\BuildSqlOrderByCapableTrait';

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
                                    '_escapeSqlReference',
                                    '_getSqlColumnName',
                                    '_createOutOfRangeException',
                                    '__',
                                ]
                            )
                        );

        $mock = $builder->getMockForTrait();
        $mock->method('__')->willReturnArgument(0);
        $mock->method('_createOutOfRangeException')->willReturnCallback(
            function($m = '', $c = 0, $p = null) {
                return new OutOfRangeException($m, $c, $p);
            }
        );

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
     * Tests the ORDER BY build method to assert whether the result contains the information given in the argument.
     *
     * @since [*next-version*]
     */
    public function testBuildSqlOrderBy()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $arg = [
            $this->createOrder($e1 = uniqid('entity'), $f1 = uniqid('field'), true),
            $this->createOrder($e2 = uniqid('entity'), $f2 = uniqid('field'), false),
            $this->createOrder($e3 = uniqid('entity'), $f3 = uniqid('field'), false),
        ];

        $expected = "ORDER BY $e1.$f1 ASC, $e2.$f2 DESC, $e3.$f3 DESC";

        $subject->method('_escapeSqlReference')->willReturnCallback(
            function($r, $p) {
                return "$p.$r";
            }
        );
        $subject->method('_getSqlColumnName')->willReturnArgument(0);

        $actual = $reflect->_buildSqlOrderBy($arg);

        $this->assertEquals($expected, $actual);
    }

    /**
     * Tests the ORDER BY build method to assert whether the result contains the information given in the argument,
     * when the fields in the ordering are changed into column names.
     *
     * @since [*next-version*]
     */
    public function testBuildSqlOrderByColumnNames()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $arg = [
            $this->createOrder($e1 = uniqid('entity'), $f1 = uniqid('field'), true),
            $this->createOrder($e2 = uniqid('entity'), $f2 = uniqid('field'), false),
            $this->createOrder($e3 = uniqid('entity'), $f3 = uniqid('field'), false),
        ];

        $c1 = uniqid('column');
        $c2 = uniqid('column');
        $c3 = uniqid('column');

        $subject->expects($this->exactly(count($arg)))
                ->method('_getSqlColumnName')
                ->withConsecutive([$f1], [$f2], [$f3])
                ->willReturnOnConsecutiveCalls($c1, $c2, $c3);

        $expected = "ORDER BY $e1.$c1 ASC, $e2.$c2 DESC, $e3.$c3 DESC";

        $subject->method('_escapeSqlReference')->willReturnCallback(
            function($r, $p) {
                return "$p.$r";
            }
        );

        $actual = $reflect->_buildSqlOrderBy($arg);

        $this->assertEquals($expected, $actual);
    }

    /**
     * Tests the ORDER BY build method to assert whether invalid-argument-exceptions thrown by the internal SQL
     * reference escaping method are wrapped in an out-of-range exception.
     *
     * @since [*next-version*]
     */
    public function testBuildSqlOrderByEscapeSqlReferenceException()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $arg = [
            $this->createOrder($e1 = uniqid('entity'), $f1 = uniqid('field'), true),
            $this->createOrder($e2 = uniqid('entity'), $f2 = uniqid('field'), false),
            $this->createOrder($e3 = uniqid('entity'), $f3 = uniqid('field'), false),
        ];

        $subject->method('_escapeSqlReference')->willThrowException($inner = new InvalidArgumentException());

        try {
            $reflect->_buildSqlOrderBy($arg);

            $this->fail('Failed asserting that an exception was thrown.');
        } catch (OutOfRangeException $outOfRangeException) {
            $this->assertSame(
                $inner,
                $outOfRangeException->getPrevious(),
                'Inner exception is not the exception thrown by the internal SQL reference escaping method.'
            );
        }
    }

    /**
     * Tests the ORDER BY build method to assert whether an out-of-range exception is thrown if the argument contains
     * an invalid element.
     *
     * @since [*next-version*]
     */
    public function testBuildSqlOrderByInvalidArgElement()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $arg = [
            $this->createOrder($e1 = uniqid('entity'), $f1 = uniqid('field'), true),
            uniqid('not-an-order-instance-'),
            $this->createOrder($e3 = uniqid('entity'), $f3 = uniqid('field'), false),
        ];

        $subject->method('_createOutOfRangeException')->willReturn(new OutOfRangeException());

        $this->setExpectedException('OutOfRangeException');

        $reflect->_buildSqlOrderBy($arg);
    }
}
