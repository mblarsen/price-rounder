<?php

use PHPUnit\Framework\TestCase;

use CodeBoutique\PriceRounder\Rounder;
use CodeBoutique\PriceRounder\RangeRounder;

class RangeTest extends TestCase
{

    public function testRangeWith()
    {
        $rounder = new RangeRounder([
            10  => "NinesRounder",
            50  => "NinetyfiveRounder",
            150 => "NinetyfiveRounder::1",
            999 => "NinesRounder::2",
            "*" => "round"
        ]);

        $this->assertEquals(5.99, $rounder->round(5.6));
        $this->assertEquals(5.99, $rounder->round(6.1));
        $this->assertEquals(9.99, $rounder->round(9.6));
        $this->assertEquals(9.95, $rounder->round(10.3));
        $this->assertEquals(13.95, $rounder->round(14.49));
        $this->assertEquals(14.95, $rounder->round(14.51));
        $this->assertEquals(49.95, $rounder->round(50));
        $this->assertEquals(79.50, $rounder->round(75));
        $this->assertEquals(79.50, $rounder->round(79));
        $this->assertEquals(79.50, $rounder->round(83));
        $this->assertEquals(89.50, $rounder->round(92));
        $this->assertEquals(99.50, $rounder->round(99));
        $this->assertEquals(499, $rounder->round(512));
        $this->assertEquals(799, $rounder->round(752));
        $this->assertEquals(999, $rounder->round(982));
        $this->assertEquals(1100, $rounder->round(1100));
        $this->assertEquals(2222, $rounder->round(2222));
        $this->assertEquals(2502, $rounder->round(2502.33));
    }

    public function testRangeWithFormatting()
    {
        $rounder = new RangeRounder([
            10  => "NinesRounder::::2",
            50  => "NinetyfiveRounder::::2",
            150 => "NinetyfiveRounder::1::2",
            999 => "NinesRounder::2::0",
            "*" => "round::::0"
        ]);

        $this->assertEquals("1,100", $rounder->round(1100));
        $this->assertEquals("2,222", $rounder->round(2222));
        $this->assertEquals("2,502", $rounder->round(2502.33));
    }

    public function testInsert()
    {
        $rounder = new RangeRounder([
            10  => "NinesRounder",
            50  => "NinetyfiveRounder"
        ]);

        $this->assertEquals(25.95, $rounder->round(26));

        $rounder->setRounderForRange("NinesRounder::::2", 40);

        $this->assertEquals(25.99, $rounder->round(26));
    }
}
