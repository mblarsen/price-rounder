<?php

namespace CodeBoutique\PriceRounder;

class HalvesRounder extends Rounder
{
    public function round($value)
    {
        return parent::round(round($values * 2) / 2);
    }
}