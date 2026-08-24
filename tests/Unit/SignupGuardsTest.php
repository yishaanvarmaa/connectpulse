<?php

namespace Tests\Unit;

use App\Support\EmailAddress;
use App\Support\PhoneNumber;
use PHPUnit\Framework\TestCase;

class SignupGuardsTest extends TestCase
{
    public function test_gmail_aliases_normalize_to_the_same_address(): void
    {
        $this->assertSame(
            'johnsmith@gmail.com',
            EmailAddress::normalize('John.Smith+promo@gmail.com')
        );
        $this->assertSame(
            'johnsmith@gmail.com',
            EmailAddress::normalize('johnsmith@googlemail.com')
        );
    }

    public function test_indian_mobile_normalizes_to_ten_digits(): void
    {
        $this->assertSame('9876543210', PhoneNumber::national('+91 98765 43210'));
        $this->assertSame('9876543210', PhoneNumber::national('09876543210'));
        $this->assertTrue(PhoneNumber::isValidIndianMobile('9876543210'));
        $this->assertFalse(PhoneNumber::isValidIndianMobile('1234567890'));
    }
}
