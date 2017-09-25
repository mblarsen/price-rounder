<?php

use PHPUnit\Framework\TestCase;

use CodeBoutique\PriceRounder\BestPriceRounder;

class BestPriceTest extends TestCase
{
    public function testFives() {
        $rounder = new BestPriceRounder();
        $this->assertEquals(649, $rounder->round(624));
        $rounder = new BestPriceRounder([], BestPriceRounder::INCLUDE_DEFAULT | BestPriceRounder::INCLUDE_NEAR_FIVES);
        $this->assertEquals(625, $rounder->round(624));
    }
}
