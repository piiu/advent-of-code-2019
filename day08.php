<?php
$input = trim(file_get_contents(__DIR__ . '\input\day08'));
$input = str_split($input);

$width = 25;
$height = 6;

$minZeroes = $resultOnMin = null;

$layers = $currentLayer = [];
$row = $column = 0;

foreach ($input as $position => $digit) {
    $currentLayer[$row][$column] = $digit;

    if (($position + 1) % ($width * $height) == 0) {
        $layers[] = $currentLayer;
        $zeroCount =  getCountForLayer($currentLayer, 0);
        if (!$minZeroes || $minZeroes > $zeroCount) {
            $minZeroes = $zeroCount;
            $resultOnMin = getCountForLayer($currentLayer, 1) * getCountForLayer($currentLayer, 2);
        }
        $currentLayer = [];
        $row = $column = 0;
    } else if (($position + 1) % $width == 0) {
        $row++;
        $column = 0;
    } else {
        $column++;
    }
}

echo 'Part 1: ' . $resultOnMin . PHP_EOL;

echo 'Part 2: ' . PHP_EOL;
for ($row = 0; $row < $height; $row++) {
    for ($column = 0; $column < $width; $column++) {
        foreach ($layers as $layer) {
            if ($layer[$row][$column] != 2) {
                echo $layer[$row][$column] == 1 ? '█' : ' ';
                break;
            }
        }
    }
    echo "\n";
}

function getCountForLayer($layer, $target) {
    $count = 0;
    foreach ($layer as $row) {
        foreach ($row as $digit) {
            if ($digit == $target) {
                $count++;
            }
        }
    }
    return $count;
}