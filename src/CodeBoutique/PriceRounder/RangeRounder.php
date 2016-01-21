<?php

namespace CodeBoutique\PriceRounder;

include "HalvesRounder.php";
include "NinesRounder.php";
include "NinetyfiveRounder.php";

class RangeRounder extends Rounder
{
    private $ranges = [];

    public function __construct($ranges)
    {
        parent::__construct();
        if (!is_array($ranges)) {
            throw new \Exception("Ranges must be an array");
        }
        $this->ranges = $ranges;
        $this->__init();
    }

    private function __init()
    {
        foreach ($this->ranges as $to => &$rounder) {
            if (is_string($rounder)) {
                $rounder_data = $this->explodeShortSyntax($rounder);
                $rounder = $this->__initRounder($rounder_data);
            }
        }
    }

    private function __initRounder($rounder_data)
    {
        if ($rounder_data["type"] === "function") {
            return function ($value) use ($rounder_data) {
                $value = $rounder_data["name"]($value);
                if (isset($rounder_data["formatter"])) {
                    $value = $rounder_data["formatter"]->format($value);
                }
                return $value;
            };
        } else if ($rounder_data["type"] === "class") {
            $rounder_instance = null;
            $rounder_class = $rounder_data["name"];
            if (isset($rounder_data["params"])) {
                $rounder_instance = new $rounder_class(...$rounder_data["params"]);
            } else {
                $rounder_instance = new $rounder_class();
            }
            if (isset($rounder_data["formatter"])) {
                $rounder_instance->setFormatter($rounder_data["formatter"]);
            }
            return $rounder_instance;
        }

    }

    private function __sort()
    {
        uksort($this->ranges, "strcmp");
    }

    public function getRanges()
    {
        return $this->ranges;
    }

    public function round($value)
    {
        foreach ($this->ranges as $to => $rounder) {
            if ($value <= (int) $to || $to === "*") {
                if ($rounder instanceof \Closure) {
                    $value = $rounder($value);
                } else if ($rounder instanceof Rounder) {
                    $value = $rounder->round($value);
                }
                return parent::round($value);
            }
        }
        throw new \Exception("Unable to round value: $value");
    }

    private function explodeShortSyntax($rounder)
    {
        $rounder_set = explode("::", $rounder);
        $name = $rounder_set[0];

        $result = [
            "name" => $name
        ];

        if (function_exists($name)) {
            $result["type"] = "function";
        } else {
            $result["type"] = "class";
            if (!class_exists($name)) {
                if (class_exists("CodeBoutique\PriceRounder\\" . $name)) {
                    $result["name"] = "CodeBoutique\PriceRounder\\" . $name;
                } else {
                    throw new \Exception("Unknown price rounder: " . $name);
                }
            }
        }

        if (isset($rounder_set[1])) {
            $result["params"] = explode("|", $rounder_set[1]);
        }

        if (isset($rounder_set[2])) {
            $format_params = explode("|", $rounder_set[2]);
            $result["formatter"] = new SimpleFormatter(...$format_params);
        }

        return $result;
    }

    public function setRounderForRange($mixed, $to)
    {
        $rounder = $mixed;
        if (is_string($mixed)) {
            $rounder = $this->__initRounder($this->explodeShortSyntax($mixed));
        }
        $this->ranges[$to] = $rounder;
        $this->__sort();
    }
}