<?php

namespace CodeBoutique\PriceRounder;

class NinetyfiveRounder extends CentsRounder
{
    public function __construct($factor = 0, $precision = 0, $mode = Rounder::ROUND_HALF_UP)
    {
        parent::__construct(5, $factor, $precision, $mode);
    }
}