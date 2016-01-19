<?php

namespace CodeBoutique\PriceRounder;

class SimpleFormatter extends Formatter
{
    private $decimals;
    private $dec_point;
    private $thousands_sep;

    public function __construct($decimals = 2, $dec_point = ".", $thousands_sep = ",")
    {
        $this->decimals      = $decimals;
        $this->dec_point     = $dec_point;
        $this->thousands_sep = $thousands_sep;
    }

    public function format($value)
    {
        return number_format($value, $this->decimals, $this->dec_point, $this->thousands_sep);
    }
}