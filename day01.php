<?php

$input = file_get_contents(__DIR__ . '\input\day01');
$modules = explode("\n", $input);

$sum1 = $sum2 = 0;
foreach ($modules as $mass) {
    $mass = intval($mass);
    $sum1 += getFuelNeeded($mass);
    $sum2 += getFuelNeededRecursive($mass);
}

echo 'Part 1: ' . $sum1 . PHP_EOL;
echo 'Part 2: ' . $sum2 . PHP_EOL;

function getFuelNeeded(int $mass) : int {
    return floor($mass / 3) - 2;
}

function getFuelNeededRecursive(int $mass, int $totalFuelNeeded = 0) : int {
    $fuelNeeded = getFuelNeeded($mass);
    if ($fuelNeeded > 0) {
        $totalFuelNeeded += $fuelNeeded;
        return getFuelNeededRecursive($fuelNeeded, $totalFuelNeeded);
    }
    return $totalFuelNeeded;
}