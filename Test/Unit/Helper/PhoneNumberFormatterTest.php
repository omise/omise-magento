<?php

namespace Omise\Payment\Test\Unit\Helper;

use Omise\Payment\Helper\PhoneNumberFormatter;
use PHPUnit\Framework\TestCase;

class PhoneNumberFormatterTest extends TestCase
{
    private $helper;

    /**
     * This function is called before the test runs.
     * Ideal for setting the values to variables or objects.
     * @coversNothing
     */
    public function setUp(): void
    {
        $this->helper = new PhoneNumberFormatter();
    }

    /**
     * Test TH phone number
     *
     * @covers Omise\Payment\Helper\PhoneNumberFormatter
     */
    public function testTHPhoneNumber()
    {
        $numbers  = [
            '+66888888888',     // string with +countryCode
            '0888888888',       // string with leading zero
            '888888888',        // string with non leading zero
            888888888,          // number
            '88-88-888-88',     // string with non numeric
        ];

        foreach ($numbers as $number) {
            $result = $this->helper->process($number, 'TH');
            $this->assertEquals('+66888888888', $result);
        }
    }

    /**
     * Test SG phone number
     *
     * @covers Omise\Payment\Helper\PhoneNumberFormatter
     */
    public function testSGPhoneNumber()
    {
        $numbers  = [
            '+65888888888',     // string with +countryCode
            '0888888888',       // string with leading zero
            '888888888',        // string with non leading zero
            888888888,          // number
            '88-88-888-88',     // string with non numeric
        ];

        foreach ($numbers as $number) {
            $result = $this->helper->process($number, 'SG');
            $this->assertEquals('+65888888888', $result);
        }
    }

    /**
     * Test JP phone number
     *
     * @covers Omise\Payment\Helper\PhoneNumberFormatter
     */
    public function testJPPhoneNumber()
    {
        $numbers  = [
            '+81888888888',     // string with +countryCode
            '0888888888',       // string with leading zero
            '888888888',        // string with non leading zero
            888888888,          // number
            '88-88-888-88',     // string with non numeric
        ];

        foreach ($numbers as $number) {
            $result = $this->helper->process($number, 'JP');
            $this->assertEquals('+81888888888', $result);
        }
    }

    /**
     * Test not supported country phone number
     *
     * @covers Omise\Payment\Helper\PhoneNumberFormatter
     */
    public function testInvalidCountryPhoneNumber()
    {
        // not supported country will return the value from input
        // function will only remove non numeric characters except for the "+" sign
        $numbers  = [
            '+81888888888' => '+81888888888',    // string with +countryCode
            '0888888888' => '0888888888',        // string with leading zero
            '888888888' => '888888888',          // string with non leading zero
            888888888 => '888888888',            // number
            '88-88-888-88' => '888888888',       // string with non numeric
        ];

        foreach ($numbers as $input => $expected) {
            $result = $this->helper->process($input, 'ZZ');
            $this->assertEquals($expected, $result);
        }
    }
}
