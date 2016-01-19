<?php

namespace CodeBoutique\PriceRounder;

class NinesRounder extends Rounder
{
    private $factor;

    public function __construct($factor = 0)
    {
        $this->factor = $factor;
    }

    public function round($value)
    {
        $multiplier = pow(10, $this->factor);
        $value = (round($value / $multiplier) - 0.01) * $multiplier;
        return parent::round($value);
    }
}