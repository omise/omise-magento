<?php

namespace Omise\Payment\Test\Unit\Helper;

use Omise\Payment\Helper\TokenHelper;

class TokenHelperTest extends \PHPUnit\Framework\TestCase
{
    protected $model;

    /**
     * This function is called before the test runs.
     * Ideal for setting the values to variables or objects.
     * @coversNothing
     */
    public function setUp(): void
    {
        $this->model = new TokenHelper();
    }

    /**
     * This function is called after the test runs.
     * Ideal for setting the values to variables or objects.
     * @coversNothing
     */
    public function tearDown(): void
    {
    }

    /**
     * * Test the function returns string with 32 characters by default.
     *
     * @covers \Omise\Payment\Helper\TokenHelper
     * @test
     */
    public function radomReturns32CharactersByDefault()
    {
        $token = $this->model->random();
        $expectedCharacterLength = 64;
        $this->assertEquals(strlen($token), $expectedCharacterLength);
    }

    /**
     * Test the function returns string with exact length as passed to the function
     *
     * @covers \Omise\Payment\Helper\TokenHelper
     * @test
     */
    public function radomReturnsStringWithLengthAsPassed()
    {
        $expectedCharacterLength = 40;
        $token = $this->model->random($expectedCharacterLength/2);
        $this->assertEquals(strlen($token), $expectedCharacterLength);
    }
}
