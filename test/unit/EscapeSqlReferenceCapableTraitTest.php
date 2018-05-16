<?php

namespace RebelCode\Storage\Resource\Sql\UnitTest;

use Dhii\Util\String\StringableInterface;
use InvalidArgumentException;
use OutOfRangeException;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use RebelCode\Storage\Resource\Sql\EscapeSqlReferenceCapableTrait as TestSubject;
use Xpmock\TestCase;

/**
 * Tests {@see TestSubject}.
 *
 * @since [*next-version*]
 */
class EscapeSqlReferenceCapableTraitTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'RebelCode\Storage\Resource\Sql\EscapeSqlReferenceCapableTrait';

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
                                '_normalizeString',
                                '_createOutOfRangeException',
                                '__',
                            ]
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
     * Creates a mock stringable object instance.
     *
     * @since [*next-version*]
     *
     * @param string $string The string the object should return when cast to string.
     *
     * @return MockObject|StringableInterface The created stringable object.
     */
    public function createStringable($string)
    {
        $mock = $this->getMockBuilder('Dhii\Util\String\StringableInterface')
                     ->setMethods(['__toString'])
                     ->getMockForAbstractClass();

        $mock->method('__toString')->willReturn($string);

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
     * Tests the reference escape method to assert whether an input string with no prefix is correctly escaped.
     *
     * @since [*next-version*]
     */
    public function testEscapeSqlReferenceString()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $reference = uniqid('ref-');
        $expected = "`$reference`";

        $subject->method('_normalizeString')->willReturn($reference);

        $this->assertEquals(
            $expected,
            $reflect->_escapeSqlReference($reference),
            'Retrieved and expected escaped references do not match.'
        );
    }

    /**
     * Tests the reference escape method to assert whether a stringable object is correctly normalized and escaped.
     *
     * @since [*next-version*]
     */
    public function testEscapeSqlReferenceStringable()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $reference = uniqid('ref-');
        $arg = $this->createStringable($reference);
        $expected = "`$reference`";

        $subject->expects($this->once())
                ->method('_normalizeString')
                ->with($arg)
                ->willReturn($reference);

        $this->assertEquals(
            $expected,
            $reflect->_escapeSqlReference($arg),
            'Retrieved and expected escaped references do not match.'
        );
    }

    /**
     * Tests the reference escape method to assert whether an input string with a prefix is correctly escaped.
     *
     * @since [*next-version*]
     */
    public function testEscapeSqlReferencesWithPrefix()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $prefix = uniqid('prefix-');
        $reference = uniqid('reference-');
        $expected = "`$prefix`.`$reference`";

        $subject->method('_normalizeString')->willReturnArgument(0);

        $this->assertEquals(
            $expected,
            $reflect->_escapeSqlReference($reference, $prefix),
            'Retrieved and expected escaped references do not match.'
        );
    }

    /**
     * Tests the reference escape method to assert whether an empty input string throws the correct exception.
     *
     * @since [*next-version*]
     */
    public function testEscapeSqlReferencesEmptyString()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $this->setExpectedException('OutOfRangeException');

        $reflect->_escapeSqlReference('');
    }

    /**
     * Tests the reference escape method to assert whether an empty input string throws the correct exception.
     *
     * @since [*next-version*]
     */
    public function testEscapeSqlReferencesInvalidValue()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $subject->method('_normalizeString')->willThrowException(new InvalidArgumentException());

        $this->setExpectedException('InvalidArgumentException');

        $reflect->_escapeSqlReference(null);
    }
}
