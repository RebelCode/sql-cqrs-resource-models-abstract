<?php

namespace RebelCode\Storage\Resource\Sql\UnitTest;

use PHPUnit_Framework_MockObject_MockObject as MockObject;
use RebelCode\Storage\Resource\Sql\NormalizeSqlValueCapableTrait as TestSubject;
use Xpmock\TestCase;

/**
 * Tests {@see TestSubject}.
 *
 * @since [*next-version*]
 */
class NormalizeSqlValueCapableTraitTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'RebelCode\Storage\Resource\Sql\NormalizeSqlValueCapableTrait';

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
                        ->setMethods(['_normalizeString']);

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
     * Tests the normalization method with a string to assert whether the value is correctly normalized.
     *
     * @since [*next-version*]
     */
    public function testNormalizeSqlValue()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $input = uniqid('input-');
        $expected = sprintf('"%s"', $input);
        $subject->expects($this->once())
                ->method('_normalizeString')
                ->willReturn($input);

        $this->assertEquals(
            $expected,
            $reflect->_normalizeSqlValue($input),
            'Retrieved and expected escaped references do not match.'
        );
    }

    /**
     * Tests the normalization method with a stringable object to assert whether the value is correctly normalized.
     *
     * @since [*next-version*]
     */
    public function testNormalizeSqlValueStringable()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $input = $this->mock('Dhii\Util\String\StringableInterface')
                      ->__toString($str = uniqid('input-'))
                      ->new();

        $expected = sprintf('"%s"', $str);
        $subject->expects($this->once())
                ->method('_normalizeString')
                ->willReturn($str);

        $this->assertEquals(
            $expected,
            $reflect->_normalizeSqlValue($input),
            'Retrieved and expected escaped references do not match.'
        );
    }

    /**
     * Tests the normalization method with a string to assert whether the value is correctly normalized.
     *
     * @since [*next-version*]
     */
    public function testNormalizeSqlValueMisc()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $input = rand(0, 500);
        $expected = $input;

        $this->assertEquals(
            $expected,
            $reflect->_normalizeSqlValue($input),
            'Retrieved and expected escaped references do not match.'
        );
    }
}
