<?php

namespace RebelCode\Storage\Resource\Sql\UnitTest;

use Dhii\Util\String\StringableInterface as Stringable;
use Dhii\Storage\Resource\Sql\EntityFieldInterface;
use OutOfRangeException;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use RebelCode\Storage\Resource\Sql\EscapeSqlReferenceListCapableTrait as TestSubject;
use Xpmock\TestCase;

/**
 * Tests {@see TestSubject}.
 *
 * @since [*next-version*]
 */
class EscapeSqlReferenceListCapableTraitTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'RebelCode\Storage\Resource\Sql\EscapeSqlReferenceListCapableTrait';

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
        $subject->expects($this->once())
                ->method('_escapeSqlReference')
                ->with($reference, null)
                ->willReturn($expected);

        $this->assertEquals(
            $expected,
            $reflect->_escapeSqlReferenceList([$reference]),
            'Retrieved and expected escaped references do not match.'
        );
    }

    /**
     * Tests the reference escape method to assert whether an input reference entity field is correctly escaped.
     *
     * @since [*next-version*]
     */
    public function testEscapeSqlReferencesEntityField()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $entity = uniqid('entity-');
        $field = uniqid('field-');
        $reference = $this->createEntityField($entity, $field);
        $expected = "`$entity`.`$field`";

        $subject->method('_normalizeArray')->willReturn([$reference]);
        $subject->expects($this->once())
                ->method('_escapeSqlReference')
                ->with($field, $entity)
                ->willReturn($expected);

        $this->assertEquals(
            $expected,
            $reflect->_escapeSqlReferenceList([$reference]),
            'Retrieved and expected escaped references do not match.'
        );
    }

    /**
     * Tests the reference escape method to assert whether out of range exceptions bubble up.
     *
     * @since [*next-version*]
     */
    public function testEscapeSqlReferencesOutOfRange()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $subject->method('_normalizeArray')->willReturn(['']);
        $subject->expects($this->once())
                ->method('_escapeSqlReference')
                ->with('', null)
                ->willThrowException(new OutOfRangeException());

        $this->setExpectedException('OutOfRangeException');

        $reflect->_escapeSqlReferenceList(['']);
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
        $subject->expects($this->exactly(count($references)))
                ->method('_escapeSqlReference')
                ->withConsecutive([$ref1, null], [$ref2, null], [$ref3, null])
                ->willReturnOnConsecutiveCalls("`$ref1`", "`$ref2`", "`$ref3`");

        $this->assertEquals(
            $expected,
            $reflect->_escapeSqlReferenceList($references),
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
            $reflect->_escapeSqlReferenceList($references),
            'Retrieved and expected escaped reference lists do not match.'
        );
    }
}
