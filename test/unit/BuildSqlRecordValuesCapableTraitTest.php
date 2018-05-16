<?php

namespace RebelCode\Storage\Resource\Sql\UnitTest;

use Xpmock\TestCase;
use RebelCode\Storage\Resource\Sql\BuildSqlRecordValuesCapableTrait as TestSubject;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Tests {@see TestSubject}.
 *
 * @since [*next-version*]
 */
class BuildSqlRecordValuesCapableTraitTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'RebelCode\Storage\Resource\Sql\BuildSqlRecordValuesCapableTrait';

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
                                '_containerGet',
                                '_containerHas',
                                '_normalizeString',
                                '_normalizeSqlValue',
                            ]
                        );

        $mock = $builder->getMockForTrait();
        $mock->method('_normalizeString')->willReturnCallback(
            function ($input) {
                return strval($input);
            }
        );

        return $mock;
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
        $definition = vsprintf(
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
     * Tests the SQL record values build method to assert whether the returned values string is correct.
     *
     * @since [*next-version*]
     */
    public function testBuildSqlRecordValues()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $val1 = rand(0, 50);
        $val2 = uniqid('str-');
        $val3 = rand(30, 100);
        $hash1 = uniqid('hash1-');
        $hash2 = uniqid('hash2-');
        $hash3 = uniqid('hash3-');

        $columns = [
            $col1 = uniqid('column1-'),
            $col2 = uniqid('column2-'),
            $col3 = uniqid('column3-'),
        ];
        $record = [
            $col1 => $val1,
            $col2 => $val2,
            $col3 => $val3,
        ];

        $valueHashMap = [
            $val1 => $hash1,
            $val2 => $hash2,
            $val3 => $hash3,
        ];

        $subject->expects($this->exactly(count($columns)))
                ->method('_containerGet')
                ->withConsecutive([$record, $col1], [$record, $col2], [$record, $col3])
                ->willReturnOnConsecutiveCalls($val1, $val2, $val3);

        $expected = sprintf('(%1$s, %2$s, %3$s)', $hash1, $hash2, $hash3);
        $actual = $reflect->_buildSqlRecordValues($columns, $record, $valueHashMap);

        $this->assertEquals($expected, $actual);
    }

    /**
     * Tests the SQL record values build method to assert whether the returned values string is correct when a record
     * is missing a value for a particular column.
     *
     * @since [*next-version*]
     */
    public function testBuildSqlRecordValuesMissingColumn()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $val1 = rand(0, 50);
        $val2 = uniqid('str-');
        $val3 = rand(30, 100);
        $hash1 = uniqid('hash1-');
        $hash2 = uniqid('hash2-');
        $hash3 = uniqid('hash3-');

        $columns = [
            $col1 = uniqid('column1-'),
            $col2 = uniqid('column2-'),
            $col3 = uniqid('column3-'),
        ];
        $record = [
            $col1 => $val1,
            $col3 => $val3,
        ];

        $valueHashMap = [
            $val1 => $hash1,
            $val2 => $hash2,
            $val3 => $hash3,
        ];

        $subject->expects($this->exactly(count($columns)))
                ->method('_containerGet')
                ->withConsecutive([$record, $col1], [$record, $col2], [$record, $col3])
                ->willReturnCallback(
                    function ($c, $k) {
                        if (isset($c[$k])) {
                            return $c[$k];
                        }

                        throw $this->mockClassAndInterfaces('Exception', ['Psr\Container\NotFoundExceptionInterface']);
                    }
                );

        $expected = sprintf('(%1$s, DEFAULT, %2$s)', $hash1, $hash3);
        $actual = $reflect->_buildSqlRecordValues($columns, $record, $valueHashMap);

        $this->assertEquals($expected, $actual);
    }
}
