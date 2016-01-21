<?php

namespace CodeBoutique\PriceRounder;

class HalvesRounder extends Rounder
{
    public function __construct($precision = 0, $mode = Rounder::ROUND_HALF_UP)
    {
        parent::__construct($precision, $mode);
    }

    public function round($value)
    {
        return parent::round(round($values * 2, $this->precision, $this->mode) / 2);
    }
}