<?php

namespace CodeBoutique\PriceRounder;

abstract class Rounder
{
    protected $formatter;

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
