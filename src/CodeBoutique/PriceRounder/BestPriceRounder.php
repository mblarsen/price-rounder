<?php

namespace CodeBoutique\PriceRounder;

class BestPriceRounder extends Rounder
{
    const INCLUDE_NINES = 1;
    const INCLUDE_NINETYFIVES = 2;
    const INCLUDE_EIGHTS = 4;
    const INCLUDE_HALVES = 8;

    const INCLUDE_DEFAULT = 11;

    const INCLUDE_ALL = 15;

    protected $edges = [
        "odd"         => 3.0,
        "even"        => 1.0,
        "nines"       => 1.0,
        "ninetyfives" => 1.0,
        "eights"      => 1.0,
        "halves"      => 1.0,
        "gain"        => 2.5,
        "loss"        => 1.0
    ];

    public function __construct($edges = [], $includes = BestPriceRounder::INCLUDE_DEFAULT)
    {
        $unknown_edges = array_diff(array_keys($edges), array_keys($this->edges));
        if (!empty($unknown_edges)) {
            throw new \Exception("Unknown edge keys: " . join(", ", $unknown_edges));
        }
        $this->includes = $includes;
        $this->edges = array_merge($this->edges, $edges);
    }

    public function round($value)
    {
        $candidates = $this->findCandidates($value);
        $analysis = $this->analyze($candidates, $value);
        return parent::round($analysis["best"]);
    }

    private function analyze($candidates, $original)
    {
        $original = (float) $original;
        $original_whole = floor($original);

        $rows = [];
        foreach ($candidates as $candidate) {
            $whole          = floor($candidate);
            $whole_diff     = $whole - $original_whole;
            $whole_diff_pct = 100 * $whole_diff / $original_whole;
            $total_diff     = $candidate - $original;
            $total_diff_pct = 100 - 100 * $original / $candidate;
            $score          = abs(($total_diff_pct * $total_diff));

            $rows[] = [
                $candidate,
                $whole_diff,
                number_format($whole_diff_pct, 1, ".", ""),
                number_format($total_diff, 2, ".", ""),
                number_format($total_diff_pct, 1, ".", ""),
                number_format($score, 2, ".", "")
            ];
        }

        foreach ($this->edges as $edge => $edge_value) {
            $compare_to = null;
            
            $edge_value = (float) $edge_value;
            switch ($edge) {

                case "odd":
                case "even":
                    array_walk($rows, function (&$row, $index, $edge) {
                        $edge_value = $edge[0];
                        $remainder  = $edge[1];

                        if ($row[0] % 2 === $remainder) {
                            $row[5] = $row[5] * 1.0 / $edge_value;
                        }
                    }, [ $edge_value, ($edge === "odd" ? 1 : 0) ]);
                    break;

                case "nines":
                    $compare_to = "9";
                case "ninetyfives":
                    $compare_to = empty($compare_to) ? "95" : $compare_to;
                case "eights":
                    $compare_to = empty($compare_to) ? "8" : $compare_to;
                case "halves":
                    $compare_to = empty($compare_to) ? "5" : $compare_to;
                    
                    array_walk($rows, function (&$row, $index, $edge) {
                        $edge_value = $edge[0];
                        $compare_to = $edge[1];

                        $new_value = str_replace(".", "", (string) $row[0]);
                        $new_value = rtrim($new_value, "0");
                        $compare_to_len = strlen($compare_to);
                        if (strlen($new_value) > $compare_to_len &&
                            substr($new_value, -1 * $compare_to_len, $compare_to_len) === $compare_to) {
                            $row[5] = $row[5] * 1.0 / $edge_value;
                        }
                    }, [$edge_value, $compare_to]);
                    break;

                case "gain":
                    array_walk($rows, function (&$row, $index, $edge_value) {
                        if ($row[3] > 0) {
                            $row[5] = $row[5] * 1.0 / $edge_value;
                        }
                    }, $edge_value);
                    break;

                case "loss":
                    array_walk($rows, function (&$row, $index, $edge_value) {
                        if ($row[3] < 0) {
                            $row[5] = $row[5] * 1.0 / $edge_value;
                        }
                    }, $edge_value);
                    break;

                default:
                    break;
            }
        }

        // Find the best
        $sort_rows = $rows;
        usort($sort_rows, function ($a, $b) {
            if ($a[5] === $b[5]) return 0;
            return $a[5] < $b[5] ? -1 : 1;
        });
        $best = $sort_rows[0][0];

        array_walk($rows, function (&$row, $index, $best) {
            if ($row[0] == $best) {
                $row[] = "*";
            }
        }, $sort_rows[0][0]);

        return [
            "original"   => $original,
            "best"       => $best,
            "candidates" => $rows,
            "edges"      => $this->edges
        ];
    }

    public function getCandidates($value)
    {
        return $this->findCandidates($value);
    }

    public function getAnalysis($value)
    {
        return $this->analyze($this->findCandidates($value), $value);
    }

    public function printAnalysis($mixed, $to_string = false)
    {
        $analysis = $this->getAnalysis($mixed);
        $has_console_table = class_exists("Elkuku\Console\Helper\ConsoleTable");

        $headers = [ "Candidate for: " . $analysis["original"], "Base Diff", "%", "Total Diff", "%", "Score" ];

        if ($has_console_table) {
            $table = new \Elkuku\Console\Helper\ConsoleTable(\Elkuku\Console\Helper\ConsoleTable::ALIGN_RIGHT);
            $table->setHeaders($headers);
            array_walk($analysis["candidates"], function ($row, $index, $table) { $table->addRow($row); }, $table);
            $table_content = $table->getTable();
            if ($to_string) {
                return $table_content;
            }
            echo $table_content;
        } else {
            array_unshift($analysis["candidates"], $headers);
            return print_r($analysis, $to_string);
        }
    }

    private function findCandidates($value)
    {
        $value           = round($value, 2);
        $whole           = floor($value);
        $ceil            = ceil($value);
        // $fraction        = $value - $whole;
        // $fraction_length = strlen((string)$fraction);
        $whole_length    = strlen((string)$whole);

        $factor     = $whole_length - 2;
        $factor     = $factor < 0 ? 0 : $factor;
        $factor     = $factor == 1 ? 2 : $factor;
        $multiplier = pow(10, $factor);

        // var_dump(
        //     "value", $value,
        //     "whole", $whole,
        //     "ceil", $ceil,
        //     "fraction", $fraction,
        //     "fraction_length", $fraction_length,
        //     "whole_length", $whole_length,
        //     "factor", $factor,
        //     "multiplier", $multiplier
        // );

        $candiates = [];
        if (self::INCLUDE_NINES & $this->includes) {
            $candiates[] = round(floor($whole / $multiplier) - 0.01, 2) * $multiplier;
            $candiates[] = round(round($whole / $multiplier) - 0.01, 2) * $multiplier;
            $candiates[] = round(ceil($value / $multiplier) - 0.51, 2) * $multiplier;
            $candiates[] = round(ceil($value / $multiplier) - 0.01, 2) * $multiplier;
        }

        if (self::INCLUDE_NINETYFIVES & $this->includes) {
            $candiates[] = round(floor($whole / $multiplier) - 0.05, 2) * $multiplier;
            $candiates[] = round(round($whole / $multiplier) - 0.05, 2) * $multiplier;
            $candiates[] = round(ceil($value / $multiplier) - 0.05, 2) * $multiplier;
        }

        if (self::INCLUDE_HALVES & $this->includes) {
            $candiates[] = round(ceil($value / $multiplier) - 0.5, 1) * $multiplier;
        }

        if (self::INCLUDE_EIGHTS & $this->includes) {
            $candiates[] = round(floor($whole / $multiplier) - 0.02, 2) * $multiplier;
            $candiates[] = round(round($whole / $multiplier) - 0.02, 2) * $multiplier;
            $candiates[] = round(ceil($value / $multiplier) - 0.52, 2) * $multiplier;
            $candiates[] = round(ceil($value / $multiplier) - 0.02, 2) * $multiplier;
        }

        $candiates[] = round(ceil($value / $multiplier)) * $multiplier;

        sort($candiates);
        $candiates = array_unique($candiates);
        return $candiates;
    }
}
