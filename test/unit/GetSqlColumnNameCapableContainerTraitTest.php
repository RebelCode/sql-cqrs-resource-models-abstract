<?php

namespace RebelCode\Storage\Resource\Sql\FuncTest;

use Dhii\Storage\Resource\Sql\EntityFieldInterface;
use Dhii\Util\String\StringableInterface as Stringable;
use Exception;
use InvalidArgumentException;
use OutOfBoundsException;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use RebelCode\Storage\Resource\Sql\GetSqlColumnNameCapableContainerTrait as TestSubject;
use stdClass;
use Xpmock\TestCase;

/**
 * Tests {@see TestSubject}.
 *
 * @since [*next-version*]
 */
class GetSqlColumnNameCapableContainerTraitTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'RebelCode\Storage\Resource\Sql\GetSqlColumnNameCapableContainerTrait';

    /**
     * Creates a new instance of the test subject.
     *
     * @since [*next-version*]
     *
     * @param array $methods The methods to mock.
     *
     * @return MockObject|TestSubject The new instance.
     */
    public function createInstance($methods = [])
    {
        $methods = $this->mergeValues(
            $methods,
            [
                '_getSqlFieldColumnMap',
                '_normalizeString',
                '_containerGet',
                '_createOutOfBoundsException',
                '_createInternalException',
                '__',
            ]
        );

        $mock = $this->getMockBuilder(static::TEST_SUBJECT_CLASSNAME)
                     ->setMethods($methods)
                     ->getMockForTrait();

        $mock->method('__')->willReturnArgument(0);
        $mock->method('_createOutOfBoundsException')->willReturnCallback(
            function($m, $c, $p) {
                return new OutOfBoundsException($m, $c, $p);
            }
        );
        $mock->method('_createInternalException')->willReturnCallback(
            function($m, $c, $p) {
                return $this->mockClassAndInterfaces('Exception', ['Dhii\Exception\InternalExceptionInterface']);
            }
        );

        return $mock;
    }

    /**
     * Creates an entity field mock instance.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable $entity The entity name.
     * @param string|Stringable $field  the field name.
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
     * Merges the values of two arrays.
     *
     * The resulting product will be a numeric array where the values of both inputs are present, without duplicates.
     *
     * @since [*next-version*]
     *
     * @param array $destination The base array.
     * @param array $source      The array with more keys.
     *
     * @return array The array which contains unique values
     */
    public function mergeValues($destination, $source)
    {
        return array_keys(array_merge(array_flip($destination), array_flip($source)));
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
     * @return object The object that extends and implements the specified class and interfaces.
     */
    public function mockClassAndInterfaces($className, $interfaceNames = [])
    {
        $paddingClassName = uniqid($className);
        $definition = vsprintf(
            'abstract class %1$s extends %2$s implements %3$s {}',
            [
                $paddingClassName,
                $className,
                implode(', ', $interfaceNames),
            ]
        );
        eval($definition);

        return $this->getMockForAbstractClass($paddingClassName);
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
            'A valid instance of the test subject could not be created.'
        );
    }

    /**
     * Tests the SQL column name getter method to assert whether the column is correctly retrieved from the container.
     *
     * @since [*next-version*]
     */
    public function testGetSqlColumnName()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $field = uniqid('field-');
        $expected = uniqid('expected-');
        $container = [
            $field => $expected,
        ];

        $subject->method('_normalizeString')->willReturn($field);
        $subject->method('_getSqlFieldColumnMap')->willReturn($container);
        $subject->expects($this->once())
                ->method('_containerGet')
                ->with($container, $field)
                ->willReturn($expected);

        $actual = $reflect->_getSqlColumnName($field);

        $this->assertEquals($expected, $actual, 'Expected and retrieved column names do not match.');
    }

    /**
     * Tests the SQL column name getter method to assert whether an entity field column is correctly retrieved as just
     * its field from the container.
     *
     * @since [*next-version*]
     */
    public function testGetSqlColumnNameEntityField()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $field = uniqid('field-');
        $entityField = $this->createEntityField('entity', $expected = uniqid('expected-'));
        $container = [
            $field => $entityField,
        ];

        $subject->method('_normalizeString')->willReturn($field);
        $subject->method('_getSqlFieldColumnMap')->willReturn($container);
        $subject->expects($this->once())
                ->method('_containerGet')
                ->with($container, $field)
                ->willReturn($entityField);

        $actual = $reflect->_getSqlColumnName($field);

        $this->assertEquals($expected, $actual, 'Expected and retrieved column names do not match.');
    }

    /**
     * Tests the SQL column name getter method with an invalid field string to assert whether an exception is thrown.
     *
     * @since [*next-version*]
     */
    public function testGetSqlColumnNameInvalidString()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $subject->method('_normalizeString')->willThrowException(new InvalidArgumentException());

        $this->setExpectedException('InvalidArgumentException');

        $reflect->_getSqlColumnName(new stdClass());
    }

    /**
     * Tests the SQL column name getter method to assert whether an out-of-bounds exception is thrown when the
     * internal container throws a not-found exception.
     *
     * @since [*next-version*]
     */
    public function testGetSqlColumnNameOutOfBoundsException()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $field = uniqid('field-');
        $expected = uniqid('expected-');
        $container = [
            $field => $expected,
        ];
        /* @var $notFound Exception|NotFoundExceptionInterface */
        $notFound = $this->mockClassAndInterfaces(
            'Exception',
            [
                'Psr\Container\NotFoundExceptionInterface',
            ]
        );

        $subject->method('_normalizeString')->willReturn($field);
        $subject->method('_getSqlFieldColumnMap')->willReturn($container);
        $subject->expects($this->once())
                ->method('_containerGet')
                ->with($container, $field)
                ->willThrowException($notFound);

        $this->setExpectedException('OutOfBoundsException');

        $reflect->_getSqlColumnName($field);
    }

    /**
     * Tests the SQL column name getter method to assert whether an internal exception is thrown when the internal
     * container throws a container exception.
     *
     * @since [*next-version*]
     */
    public function testGetSqlColumnNameInternalException()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $field = uniqid('field-');
        $expected = uniqid('expected-');
        $container = [
            $field => $expected,
        ];
        /* @var $containerException Exception|ContainerExceptionInterface */
        $containerException = $this->mockClassAndInterfaces(
            'Exception',
            [
                'Psr\Container\ContainerExceptionInterface',
            ]
        );

        $subject->method('_normalizeString')->willReturn($field);
        $subject->method('_getSqlFieldColumnMap')->willReturn($container);
        $subject->expects($this->once())
                ->method('_containerGet')
                ->with($container, $field)
                ->willThrowException($containerException);

        $this->setExpectedException('Dhii\Exception\InternalExceptionInterface');

        $reflect->_getSqlColumnName(new stdClass());
    }
}
