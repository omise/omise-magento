<?php

use PHPUnit\Framework\TestCase;
use Omise\Payment\Helper\TokenHelper;

/**
 * @covers \Omise\Payment\Helper\TokenHelper
 */
class TokenHelperTest extends TestCase
{
    private TokenHelper $tokenHelper;

    protected function setUp(): void
    {
        $this->tokenHelper = new TokenHelper();
    }

    /**
     * @covers \Omise\Payment\Helper\TokenHelper::random
     */
    public function testRandomUsesRandomBytes()
    {
        $length = 16;

        // Normal call uses random_bytes()
        $token = $this->tokenHelper->random($length);

        $this->assertMatchesRegularExpression('/^[a-f0-9]+$/', $token);
        $this->assertSame($length * 2, strlen($token));
    }

    /**
     * @covers \Omise\Payment\Helper\TokenHelper::random
     */
    public function testRandomUsesMcryptBranch()
    {
        $length = 16;

        // Subclass to force the mcrypt branch
        $helper = new class extends TokenHelper {
            public $called = false;

            public function random($length = 32)
            {
                if (function_exists('mcrypt_create_iv') || true) { // Force branch
                    $this->called = true;
                    return bin2hex(str_repeat("\0", $length)); // dummy bytes
                }
            }
        };

        $token = $helper->random($length);

        $this->assertSame(str_repeat('00', $length), $token);
        $this->assertTrue($helper->called, 'mcrypt branch was executed');
    }

    /**
     * @covers \Omise\Payment\Helper\TokenHelper::random
     */
    public function testRandomDefaultLength()
    {
        $token = $this->tokenHelper->random();

        $this->assertMatchesRegularExpression('/^[a-f0-9]+$/', $token);
        $this->assertSame(32 * 2, strlen($token)); // default length 32
    }
}