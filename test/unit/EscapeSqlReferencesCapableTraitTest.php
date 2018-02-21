<?php

namespace RebelCode\Storage\Resource\Sql\UnitTest;

use PHPUnit_Framework_MockObject_MockObject as MockObject;
use RebelCode\Storage\Resource\Sql\EscapeSqlReferencesCapableTrait as TestSubject;
use Xpmock\TestCase;

/**
 * Tests {@see TestSubject}.
 *
 * @since [*next-version*]
 */
class EscapeSqlReferencesCapableTraitTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'RebelCode\Storage\Resource\Sql\EscapeSqlReferencesCapableTrait';

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
                        ->setMethods(['_normalizeArray']);

        $mock = $builder->getMockForTrait();

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
     * Tests the reference escape method to assert whether an input reference string is correctly escaped.
     *
     * @since [*next-version*]
     */
    public function testEscapeSqlReferencesString()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $reference = uniqid('ref-');
        $expected = "`$reference`";

        $subject->method('_normalizeArray')->willReturn([$reference]);

        $this->assertEquals(
            $expected,
            $reflect->_escapeSqlReferences($reference),
            'Retrieved and expected escaped references do not match.'
        );
    }

    /**
     * Tests the reference escape method with an empty string to assert whether the output is also empty.
     *
     * @since [*next-version*]
     */
    public function testEscapeSqlReferencesEmptyString()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $result = $reflect->_escapeSqlReferences('');

        $this->assertEquals(0, strlen($result), 'Result is not empty.');
    }

    /**
     * Tests the reference escape array method to assert whether an input reference list is correctly escaped.
     *
     * @since [*next-version*]
     */
    public function testEscapeSqlReferencesArray()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $references = [
            $ref1 = uniqid('ref-'),
            $ref2 = uniqid('ref-'),
            $ref3 = uniqid('ref-'),
        ];
        $expected = "`$ref1`, `$ref2`, `$ref3`";

        $subject->method('_normalizeArray')->willReturn($references);

        $this->assertEquals(
            $expected,
            $reflect->_escapeSqlReferences($references),
            'Retrieved and expected escaped reference lists do not match.'
        );
    }

    /**
     * Tests the reference escape array method with an empty array to assert whether the output is also empty.
     * escaped.
     *
     * @since [*next-version*]
     */
    public function testEscapeSqlReferencesArrayEmpty()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $references = [];
        $expected = '';

        $this->assertEquals(
            $expected,
            $reflect->_escapeSqlReferences($references),
            'Retrieved and expected escaped reference lists do not match.'
        );
    }
}
