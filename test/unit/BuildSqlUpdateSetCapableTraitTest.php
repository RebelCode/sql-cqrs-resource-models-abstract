<?php

namespace RebelCode\Storage\Resource\Sql\UnitTest;

use Dhii\Expression\ExpressionInterface;
use Xpmock\TestCase;
use RebelCode\Storage\Resource\Sql\BuildSqlUpdateSetCapableTrait as TestSubject;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Tests {@see TestSubject}.
 *
 * @since [*next-version*]
 */
class BuildSqlUpdateSetCapableTraitTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'RebelCode\Storage\Resource\Sql\BuildSqlUpdateSetCapableTrait';

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
                                '_normalizeSqlValue',
                                '_normalizeString',
                                '_renderSqlExpression',
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
     * Creates an expression mock instance.
     *
     * @since [*next-version*]
     *
     * @param string $type  The expression type.
     * @param array  $terms The expression terms.
     *
     * @return ExpressionInterface The created expression instance.
     */
    public function createExpression($type, $terms)
    {
        return $this->mock('Dhii\Expression\ExpressionInterface')
                    ->getType($type)
                    ->getTerms($terms)
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
     * Tests the SQL update set build method to assert whether the returned string is correct.
     *
     * @since [*next-version*]
     */
    public function testBuildSqlUpdateSet()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $val1 = rand(0, 50);
        $val2 = uniqid('str-');
        $val3 = $this->createExpression('', []);
        $col1 = uniqid('column-');
        $col2 = uniqid('column-');
        $col3 = uniqid('column-');
        $hash1 = uniqid('hash-');

        $changeSet = [
            $col1 => $val1,
            $col2 => $val2,
            $col3 => $val3,
        ];

        $valueHashMap = [
            $val1 => $hash1,
        ];

        $subject->method('_normalizeString')->willReturnArgument(0);

        $subject->expects($this->once())
                ->method('_normalizeSqlValue')
                ->with($val2)
                ->willReturn($nVal2 = "\"$val2\"");

        $subject->expects($this->once())
                ->method('_renderSqlExpression')
                ->with($val3, $valueHashMap)
                ->willReturn($rVal3 = uniqid('rendered-'));

        $expected = sprintf('`%1$s` = %2$s, `%3$s` = %4$s, `%5$s` = %6$s', $col1, $hash1, $col2, $nVal2, $col3, $rVal3);
        $actual = $reflect->_buildSqlUpdateSet($changeSet, $valueHashMap);

        $this->assertEquals($expected, $actual);
    }
}
