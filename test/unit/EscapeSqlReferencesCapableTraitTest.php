<?php

namespace RebelCode\Storage\Resource\Sql\UnitTest;

use Dhii\Util\String\StringableInterface as Stringable;
use Dhii\Storage\Resource\Sql\EntityFieldInterface;
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
     * Creates an entity field mock instance.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable $entity The entity name.
     *
     * @return EntityFieldInterface
     */
    public function createEntityAware($entity)
    {
        return $this->mock('Dhii\Storage\Resource\Sql\EntityAwareInterface')
                    ->getEntity($entity)
                    ->new();
    }

    /**
     * Creates an entity field mock instance.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable $field  The field name.
     *
     * @return EntityFieldInterface
     */
    public function createFieldAware($field)
    {
        return $this->mock('Dhii\Storage\Resource\Sql\FieldAwareInterface')
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

        $this->assertEquals(
            $expected,
            $reflect->_escapeSqlReferences($reference),
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

        $this->assertEquals(
            $expected,
            $reflect->_escapeSqlReferences($reference),
            'Retrieved and expected escaped references do not match.'
        );
    }

    /**
     * Tests the reference escape method to assert whether an input reference entity field with a null entity is
     * correctly escaped.
     *
     * @since [*next-version*]
     */
    public function testEscapeSqlReferencesNullEntityField()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $field = uniqid('field-');
        $reference = $this->createEntityField(null, $field);
        $expected = "`$field`";

        $subject->method('_normalizeArray')->willReturn([$reference]);

        $this->assertEquals(
            $expected,
            $reflect->_escapeSqlReferences($reference),
            'Retrieved and expected escaped references do not match.'
        );
    }

    /**
     * Tests the reference escape method to assert whether an entity-aware input reference is correctly escaped.
     *
     * @since [*next-version*]
     */
    public function testEscapeSqlReferencesEntityAware()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $entity = uniqid('entity-');
        $reference = $this->createEntityAware($entity);
        $expected = "`$entity`";

        $subject->method('_normalizeArray')->willReturn([$reference]);

        $this->assertEquals(
            $expected,
            $reflect->_escapeSqlReferences($reference),
            'Retrieved and expected escaped references do not match.'
        );
    }

    /**
     * Tests the reference escape method to assert whether a field-aware input reference is correctly escaped.
     *
     * @since [*next-version*]
     */
    public function testEscapeSqlReferencesFieldAware()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $field = uniqid('field-');
        $reference = $this->createFieldAware($field);
        $expected = "`$field`";

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
