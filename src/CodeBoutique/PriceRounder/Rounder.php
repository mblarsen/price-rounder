<?php

namespace CodeBoutique\PriceRounder;

abstract class Rounder
{
    const ROUND_HALF_UP   = PHP_ROUND_HALF_UP;
    const ROUND_HALF_DOWN = PHP_ROUND_HALF_DOWN;
    const ROUND_CEIL      = 3;
    
    protected $formatter;
    protected $precision;
    protected $mode;

    public function __construct($precision = 0, $mode = Rounder::ROUND_HALF_UP)
    {
        $this->precision = $precision;
        $this->mode = $mode;
    }

    public function round($value)
    {
        if ($this->formatter instanceof \Closure) {
            $formatter = $this->formatter;
            return $formatter($value);
        } else if ($this->formatter instanceof Formatter) {
            return $this->formatter->format($value);
        }
        return $value;
    }

    public function setFormatter($formatter)
    {
        $this->formatter = $formatter;
        return $this;
    }

    public function getFormatter($formatter)
    {
        return $this->formatter;
    }
}
