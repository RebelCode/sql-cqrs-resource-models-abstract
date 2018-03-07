<?php

namespace RebelCode\Storage\Resource\Sql\UnitTest;

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
     * Tests the reference escape method to assert whether a field input string with no entity is correctly escaped.
     *
     * @since [*next-version*]
     */
    public function testEscapeSqlReferenceFieldString()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $field = uniqid('ref-');
        $expected = "`$field`";

        $subject->method('_normalizeString')->willReturn($field);

        $this->assertEquals(
            $expected,
            $reflect->_escapeSqlReference(null, $field),
            'Retrieved and expected escaped references do not match.'
        );
    }

    /**
     * Tests the reference escape method to assert whether a field input string with an entity is correctly escaped.
     *
     * @since [*next-version*]
     */
    public function testEscapeSqlReferencesEntityField()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $entity = uniqid('entity-');
        $field = uniqid('field-');
        $expected = "`$entity`.`$field`";

        $subject->method('_normalizeString')->willReturnOnConsecutiveCalls($entity, $field);

        $this->assertEquals(
            $expected,
            $reflect->_escapeSqlReference($entity, $field),
            'Retrieved and expected escaped references do not match.'
        );
    }

    /**
     * Tests the reference escape method to assert whether an empty entity string throws the correct exception.
     *
     * @since [*next-version*]
     */
    public function testEscapeSqlReferencesEmptyEntityString()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $this->setExpectedException('OutOfRangeException');

        $reflect->_escapeSqlReference('', '');
    }

    /**
     * Tests the reference escape method to assert whether an empty field string throws the correct exception.
     *
     * @since [*next-version*]
     */
    public function testEscapeSqlReferencesEmptyFieldString()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $this->setExpectedException('OutOfRangeException');

        $reflect->_escapeSqlReference(uniqid('entity-'), '');
    }

    /**
     * Tests the reference escape method to assert whether an empty field string throws the correct exception.
     *
     * @since [*next-version*]
     */
    public function testEscapeSqlReferencesInvalidValue()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $subject->method('_normalizeString')->willThrowException(new InvalidArgumentException());

        $this->setExpectedException('InvalidArgumentException');

        $reflect->_escapeSqlReference(null, null);
    }
}
