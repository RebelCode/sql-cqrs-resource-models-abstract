<?php

namespace RebelCode\Storage\Resource\Sql\UnitTest;

use InvalidArgumentException;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use RebelCode\Storage\Resource\Sql\BuildInsertSqlCapableTrait as TestSubject;
use Xpmock\TestCase;

/**
 * Tests {@see TestSubject}.
 *
 * @since [*next-version*]
 */
class BuildInsertSqlCapableTraitTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'RebelCode\Storage\Resource\Sql\BuildInsertSqlCapableTrait';

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
                                '_buildSqlRecordValues',
                                '_escapeSqlReferences',
                                '_createInvalidArgumentException',
                                '__',
                            ]
                        );

        $mock = $builder->getMockForTrait();

        // Simple, zero-escaping, mock implementations
        $mock->method('_escapeSqlReferences')->willReturnCallback(
            function($input) {
                if (is_array($input)) {
                    return implode(', ', $input);
                }

                return $input;
            }
        );
        $mock->method('_createInvalidArgumentException')->willReturnCallback(
            function($m, $c, $p) {
                return new InvalidArgumentException($m, $c, $p);
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
     * Tests the INSERT SQL build method to assert whether the built query reflects the arguments given.
     *
     * @since [*next-version*]
     */
    public function testBuildInsertSql()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $table = 'test';
        $columns = ['id', 'name', 'surname'];
        $rows = [
            [
                'id'      => 1,
                'name'    => 'Miguel',
                'surname' => 'Muscat',
            ],
            [
                'id'      => 2,
                'name'    => 'Anton',
                'surname' => 'Ukhanev',
            ],
        ];
        $valueHashMap = [
            '1'      => ':123',
            '2'      => ':456',
            'Miguel' => ':321',
            'Muscat' => ':654',
        ];

        $values1 = '(:123, :321, :654)';
        $values2 = '(:456, "Anton", "Ukhanev")';

        $subject->expects($this->exactly(count($rows)))
                ->method('_buildSqlRecordValues')
                ->withConsecutive([$columns, $rows[0], $valueHashMap], [$columns, $rows[1], $valueHashMap])
                ->willReturnOnConsecutiveCalls($values1, $values2);

        $result = $reflect->_buildInsertSql(
            $table,
            $columns,
            $rows,
            $valueHashMap
        );

        $this->assertEquals(
            'INSERT INTO test (id, name, surname) VALUES ' . $values1 . ', ' . $values2 . ';',
            $result,
            'Retrieved and expected queries do not match.'
        );
    }

    /**
     * Tests the INSERT SQL build method with no rows to assert whether the VALUES portion of the query is omitted.
     *
     * @since [*next-version*]
     */
    public function testBuildInsertSqlNoRows()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $this->setExpectedException('InvalidArgumentException');

        $reflect->_buildInsertSql(
            $table = 'test',
            $columns = ['id', 'name', 'surname'],
            $rows = []
        );
    }
}
