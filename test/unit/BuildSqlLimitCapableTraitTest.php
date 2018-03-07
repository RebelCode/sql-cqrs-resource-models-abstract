<?php

namespace RebelCode\Storage\Resource\Sql\FuncTest;

use InvalidArgumentException;
use OutOfRangeException;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use RebelCode\Storage\Resource\Sql\BuildSqlLimitCapableTrait as TestSubject;
use Xpmock\TestCase;

/**
 * Tests {@see TestSubject}.
 *
 * @since [*next-version*]
 */
class BuildSqlLimitCapableTraitTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'RebelCode\Storage\Resource\Sql\BuildSqlLimitCapableTrait';

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
                                    '_normalizeInt',
                                    '_createOutOfRangeException',
                                    '__',
                                ]
                            )
                        );

        $mock = $builder->getMockForTrait();
        $mock->method('_createOutOfRangeException')->willReturnCallback(
            function($m, $c, $p, $a) {
                return new OutOfRangeException($m, $c, $p);
            }
        );
        $mock->method('__')->willReturnArgument(0);

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
     * Tests the build SQL limit method to assert whether the result is a correct SQL LIMIT query portion.
     *
     * @since [*next-version*]
     */
    public function testBuildSqlLimit()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $arg = rand(0, 100);
        $subject->method('_normalizeInt')->willReturn($arg);

        $expected = "LIMIT $arg";
        $actual = $reflect->_buildSqlLimit($arg);

        $this->assertEquals($expected, $actual, 'Expected and retrieved SQL LIMIT do not match.');
    }

    /**
     * Tests the build SQL limit method with a negative limit to assert whether an out-of-range exception is thrown.
     *
     * @since [*next-version*]
     */
    public function testBuildSqlLimitNegative()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $arg = - rand(0, 100);
        $subject->method('_normalizeInt')->willReturn($arg);

        $this->setExpectedException('OutOfRangeException');

        $reflect->_buildSqlLimit($arg);
    }

    /**
     * Tests the build SQL limit method with an invalid argument to assert whether an invalid-argument exception is
     * thrown.
     *
     * @since [*next-version*]
     */
    public function testBuildSqlLimitInvalid()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $arg = uniqid('invalid-');
        $subject->method('_normalizeInt')->willThrowException(new InvalidArgumentException());

        $this->setExpectedException('InvalidArgumentException');

        $reflect->_buildSqlLimit($arg);
    }
}
