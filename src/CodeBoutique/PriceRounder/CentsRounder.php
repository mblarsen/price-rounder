<?php

namespace CodeBoutique\PriceRounder;

class CentsRounder extends Rounder
{
    protected $factor;
    protected $cents;

    public function __construct($cents, $factor = 0, $precision = 0, $mode = Rounder::ROUND_HALF_UP)
    {
        parent::__construct($precision, $mode);
        $this->cents = (float) $cents / 100.0;
        $this->factor = (float) $factor;
    }

    public function round($value)
    {
        $multiplier = pow(10, $this->factor);
        
        if (Rounder::ROUND_HALF_UP == $this->mode || Rounder::ROUND_HALF_DOWN == $this->mode) {
            $value = (round($value / $multiplier, $this->precision, $this->mode) - $this->cents) * $multiplier;
        } else if (Rounder::ROUND_CEIL == $this->mode) {
            // echo '$multiplier => ' . $multiplier . PHP_EOL;
            // echo '$value / $multiplier => ' . ($value / $multiplier) . PHP_EOL;
            // echo 'ceil($value / $multiplier) => ' . (ceil($value / $multiplier)) . PHP_EOL;
            // echo '(ceil($value / $multiplier) - $this->cents) => ' . ((ceil($value / $multiplier) - $this->cents)) . PHP_EOL;
            // $value = (ceil($value / $multiplier) - $this->cents) * $multiplier;
            
            $value = round((ceil($value / $multiplier) - $this->cents) * $multiplier, $this->precision, $this->mode);
        }
        
        return parent::round($value);
    }
}