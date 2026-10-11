<?php

namespace Tests\Unit;

use App\Support\Money;
use PHPUnit\Framework\TestCase;

class MoneyTest extends TestCase
{
    public function test_formats_paise_to_rupee_string(): void
    {
        $this->assertEquals('₹1,499.00', Money::format(149900));
        $this->assertEquals('₹0.00', Money::format(0));
        $this->assertEquals('₹0.50', Money::format(50));
        $this->assertEquals('₹25,000.00', Money::format(2500000));
    }

    public function test_converts_paise_to_rupee_float(): void
    {
        $this->assertEquals(1499.00, Money::toRupees(149900));
        $this->assertEquals(0.00, Money::toRupees(0));
        $this->assertEquals(49.50, Money::toRupees(4950));
    }

    public function test_converts_rupees_to_integer_paise(): void
    {
        $this->assertEquals(149900, Money::toPaise(1499.00));
        $this->assertEquals(4950, Money::toPaise('49.50'));
        $this->assertEquals(0, Money::toPaise(0));
        $this->assertEquals(100, Money::toPaise(1));
    }
}
