<?php

namespace RebelCode\Storage\Resource\Sql\FuncTest;

use Dhii\Expression\TermInterface;
use Dhii\Storage\Resource\Sql\EntityFieldInterface;
use Dhii\Util\String\StringableInterface as Stringable;
use Exception as RootException;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use RebelCode\Storage\Resource\Sql\BuildSqlColumnListCapableTrait as TestSubject;
use Xpmock\TestCase;

/**
 * Tests {@see TestSubject}.
 *
 * @since [*next-version*]
 */
class BuildSqlColumnListCapableTraitTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'RebelCode\Storage\Resource\Sql\BuildSqlColumnListCapableTrait';

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
            '_renderSqlExpression',
        ]);

        $mock = $this->getMockBuilder(static::TEST_SUBJECT_CLASSNAME)
                     ->setMethods($methods)
                     ->getMockForTrait();

        return $mock;
    }

    /**
     * Creates an expression mock instance.
     *
     * @since [*next-version*]
     *
     * @param string $type The expression type.
     *
     * @return TermInterface The created expression instance.
     */
    public function createExpression($type)
    {
        return $this->mock('Dhii\Expression\TermInterface')
                    ->getType($type)
                    ->new();
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
     * Tests the `_buildSqlColumnList()` method with string column names to assert whether the resulting column list is
     * correct.
     *
     * @since [*next-version*]
     */
    public function testBuildSqlColumnListString()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);
        $columns = [
            $a1 = uniqid('alias-') => $c1 = uniqid('column-'),
            $a2 = uniqid('alias-') => $c2 = uniqid('column-'),
            $a3 = uniqid('alias-') => $c3 = uniqid('column-'),
        ];
        $expected = "`$c1` AS `$a1`, `$c2` AS `$a2`, `$c3` AS `$a3`";

        $subject->expects($this->exactly(count($columns) * 2))
                ->method('_escapeSqlReference')
                ->withConsecutive([$c1], [$a1], [$c2], [$a2], [$c3], [$a3])
                ->willReturnCallback(function ($arg) {
                    return sprintf('`%s`', $arg);
                });

        $actual = $reflect->_buildSqlColumnList($columns);

        $this->assertEquals($expected, $actual);
    }

    /**
     * Tests the `_buildSqlColumnList()` method with entity field columns to assert whether the resulting column
     * list is correct.
     *
     * @since [*next-version*]
     */
    public function testBuildSqlColumnListEntityFields()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);
        $ef1 = $this->createEntityField(
            $e1 = uniqid('entity-'),
            $f1 = uniqid('field-')
        );
        $ef2 = $this->createEntityField(
            $e2 = uniqid('entity-'),
            $f2 = uniqid('field-')
        );
        $columns = [
            $a1 = uniqid('alias-') => $ef1,
            $a2 = uniqid('alias-') => $ef2,
        ];
        $expected = "`$e1`.`$f1` AS `$a1`, `$e2`.`$f2` AS `$a2`";

        $subject->expects($this->exactly(count($columns) * 2))
                ->method('_escapeSqlReference')
                ->withConsecutive([$f1, $e1], [$a1], [$f2, $e2], [$a2])
                ->willReturnOnConsecutiveCalls("`$e1`.`$f1`", "`$a1`", "`$e2`.`$f2`", "`$a2`");

        $actual = $reflect->_buildSqlColumnList($columns);

        $this->assertEquals($expected, $actual);
    }

    /**
     * Tests the `_buildSqlColumnList()` method with expression columns to assert whether the resulting column list is
     * correct.
     *
     * @since [*next-version*]
     */
    public function testBuildSqlColumnListExpressions()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);
        $columns = [
            $a1 = uniqid('alias-') => $e1 = $this->createExpression(''),
            $a2 = uniqid('alias-') => $e2 = $this->createExpression(''),
        ];
        $r1 = uniqid('rendered-');
        $r2 = uniqid('rendered-');
        $expected = "$r1 AS `$a1`, $r2 AS `$a2`";

        $subject->expects($this->exactly(count($columns)))
                ->method('_escapeSqlReference')
                ->withConsecutive([$a1], [$a2])
                ->willReturnCallback(function ($arg) {
                    return "`$arg`";
                });

        $subject->expects($this->exactly(count($columns)))
                ->method('_renderSqlExpression')
                ->withConsecutive([$e1], [$e2])
                ->willReturnOnConsecutiveCalls($r1, $r2);

        $actual = $reflect->_buildSqlColumnList($columns);

        $this->assertEquals($expected, $actual);
    }

    /**
     * Tests the `_buildSqlColumnList()` method with mixed columns to assert whether the resulting column list is
     * correct.
     *
     * @since [*next-version*]
     */
    public function testBuildSqlColumnListMixed()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);
        $c1 = uniqid('column-');
        $c2 = $this->createEntityField(
            $e2 = uniqid('entity-'),
            $f2 = uniqid('field-')
        );
        $c3 = $this->createExpression('');
        $r3 = uniqid('rendered-');
        $columns = [
            $a1 = uniqid('alias-') => $c1,
            $a2 = uniqid('alias-') => $c2,
            $a3 = uniqid('alias-') => $c3,
        ];

        $expected = "`$c1` AS `$a1`, `$e2`.`$f2` AS `$a2`, $r3 AS `$a3`";

        $subject->method('_escapeSqlReference')
                ->willReturnCallback(function ($arg, $prefix = null) {
                    return ($prefix === null)
                        ? "`$arg`"
                        : "`$prefix`.`$arg`";
                });

        $subject->expects($this->once())
                ->method('_renderSqlExpression')
                ->with($c3)
                ->willReturn($r3);

        $actual = $reflect->_buildSqlColumnList($columns);

        $this->assertEquals($expected, $actual);
    }
}
