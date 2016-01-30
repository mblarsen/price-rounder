<?php

// To run example be sure to run composer with dev dependencies.

require_once "vendor/autoload.php";

use CodeBoutique\PriceRounder\BestPriceRounder as BestPriceRounder;

$data = [
    "5.1"     => [4.95, 4.99, 5, 5.49, 5.5],
    "5.23"    => [4.95, 4.99, 5.25, 5.49, 5.5, 5.95, 5.99],
    "5.6"     => [5.5, 5.95, 5.99],
    "6.1"     => [5.95, 5.99, 6.49, 6.5, 6.95, 6.99],
    "9.6"     => [9.49, 9.5, 9.95, 9.99],
    "14.51"   => [14.5, 14.49, 14.55, 14.95, 14.99, 15],
    "27.53"   => [27.5, 27.49, 27.55, 27.49, 29.95, 29.99],
    "66.33"   => [65.95, 65.99, 66.95, 66.99, 69.5, 69.95, 69.99],
    "512"     => [495, 499, 519, 515, 525, 549, 550],
    "752"     => [749, 750, 755, 759, 799],
    "1253.2"  => [1249, 1253, 1255, 1259, 1299],
    "2502.33" => [2495, 2499, 2500, 2505, 2549, 2550],
    "2517.33" => [2495, 2499, 2500, 2505, 2549, 2550]
];

$data = [
    "372.30" => [1],
    "40.10" => [1],
    "56.59" => [1]
];

$rounder = new BestPriceRounder([ "gain" => 10.0 ]);
$rounder->printAnalysis(66.33);
$rounder->printAnalysis(1253.2);

if (class_exists("Elkuku\Console\Helper\ConsoleTable", false)) {
    test([
        [],
        [ "odd" => 2.5 ],
        [ "gain" => 2.0 ],
        [ "odd" => 2.5, "gain" => 2.0 ]
    ], $data);
}

function test($edges, $data)
{
    $table = new Elkuku\Console\Helper\ConsoleTable();
    $table->setHeaders(array_merge(["Original"], array_map(function ($set) {
        if (empty($set)) {
            return "No edges";
        }
        array_walk($set, function (&$value, $key) {
            $value = "$key: " . $value;
        });
        return join(", ", array_values($set));
    }, $edges)));
    foreach ($data as $base => $candidates) {
        $base = (float) $base;
        // $rounder->printAnalysis($base);
        $row = [ $base ];
        foreach ($edges as $set) {
            $rounder = new BestPriceRounder($set);
            $row[] = $rounder->round($base);
        }
        $table->addRow($row);
    }
    echo $table->getTable();
}
