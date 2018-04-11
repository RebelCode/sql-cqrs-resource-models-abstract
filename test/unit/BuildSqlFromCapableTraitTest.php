<?php

namespace RebelCode\Storage\Resource\Sql\FuncTest;

use RebelCode\Storage\Resource\Sql\BuildSqlFromCapableTrait as TestSubject;
use Xpmock\TestCase;
use Exception as RootException;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Tests {@see TestSubject}.
 *
 * @since [*next-version*]
 */
class BuildSqlFromCapableTraitTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'RebelCode\Storage\Resource\Sql\BuildSqlFromCapableTrait';

    /**
     * Creates a new instance of the test subject.
     *
     * @since [*next-version*]
     *
     * @param array $methods The methods to mock.
     *
     * @return TestSubject|MockObject The new instance.
     */
    public function createInstance($methods = [])
    {
        $methods = $this->mergeValues($methods, [
            '_escapeSqlReference',
        ]);

        $mock = $this->getMockBuilder(static::TEST_SUBJECT_CLASSNAME)
                     ->setMethods($methods)
                     ->getMockForTrait();

        return $mock;
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
     * @return MockObject The object that extends and implements the specified class and interfaces.
     */
    public function mockClassAndInterfaces($className, $interfaceNames = [])
    {
        $paddingClassName = uniqid($className);
        $definition = vsprintf('abstract class %1$s extends %2$s implements %3$s {}', [
            $paddingClassName,
            $className,
            implode(', ', $interfaceNames),
        ]);
        eval($definition);

        return $this->getMockForAbstractClass($paddingClassName);
    }

    /**
     * Creates a new exception.
     *
     * @since [*next-version*]
     *
     * @param string $message The exception message.
     *
     * @return RootException|MockObject The new exception.
     */
    public function createException($message = '')
    {
        $mock = $this->getMockBuilder('Exception')
                     ->setConstructorArgs([$message])
                     ->getMock();

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
            'A valid instance of the test subject could not be created.'
        );
    }

    /**
     * Tests the `_buildSqlFrom()` method to assert whether the FROM portion is correctly built.
     *
     * @since [*next-version*]
     */
    public function testBuildSqlFrom()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $t1 = uniqid('table-');
        $t2 = uniqid('table-');
        $t3 = uniqid('table-');
        $a1 = uniqid('alias-');
        $a2 = uniqid('alias-');
        $a3 = uniqid('alias-');

        $tables = [
            $a1 => $t1,
            $a2 => $t2,
            $a3 => $t3,
        ];

        $subject->expects($this->exactly(count($tables) * 2))
                ->method('_escapeSqlReference')
                ->withConsecutive([$t1], [$a1], [$t2], [$a2], [$t3], [$a3])
                ->willReturnOnConsecutiveCalls($t1, $a1, $t2, $a2, $t3, $a3);

        $expected = "FROM $t1 as $a1, $t2 as $a2, $t3 as $a3";
        $actual = $reflect->_buildSqlFrom($tables);

        $this->assertEquals($expected, $actual, 'Built result does not match expected return value.');
    }
}
