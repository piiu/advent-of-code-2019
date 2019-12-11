<?php

$input = trim(file_get_contents(__DIR__ . '/input/day10'));

$asteroids = [];
foreach (explode("\n", $input) as $y => $row) {
    foreach(str_split($row) as $x => $element) {
        if ($element == '#') {
            $asteroids[] = [
                'x' => $x,
                'y' => $y
            ];
        }
    }
}

$maxVisible = null;
foreach ($asteroids as $key => $asteroid) {
    $possibleAngles = [];
    foreach ($asteroids as $otherKey => $other) {
        if ($key == $otherKey) {
            continue;
        }
        $x = $other['x'] - $asteroid['x'];
        $y = $asteroid['y'] - $other['y']; // This should be other way around, but that causes y axis to be mirrored ¯\_(ツ)_/¯
        $distance = $x + $y;
        $angle = ($x < 0) ? rad2deg(atan2($x, $y)) + 360 : rad2deg(atan2($x, $y));

        $asteroids[$key]['targets'][] = [
            'x' => $other['x'],
            'y' => $other['y'],
            'distance' => $distance,
            'angle' => $angle
        ];
        $possibleAngles[] = $angle;
    }
    $visibleAsteroidsCount = count(array_unique($possibleAngles));

    if (!$maxVisible || $maxVisible < $visibleAsteroidsCount) {
        $maxVisible = $visibleAsteroidsCount;
        $bestAsteroidIndex = $key;
    }
}

echo 'Part 1:'. $maxVisible . PHP_EOL;

$targets = $asteroids[$bestAsteroidIndex]['targets'];
array_multisort(array_column($targets, 'angle'),SORT_ASC,
    array_column($targets, 'distance'),SORT_ASC,
    $targets);

// Destroy them all! Ain't gonna stop at 200!
$destroyedAsteroids = [];
while (!empty($targets)) {
    $remainingAsteroids = [];
    foreach ($targets as $key => $asteroid) {
        $previous = $targets[$key-1] ?? null;
        if (!$previous || $previous['angle'] != $asteroid['angle']) {
            $destroyedAsteroids[] = $asteroid;
        } else {
            $remainingAsteroids[] = $asteroid;
        }
    }
    $targets = $remainingAsteroids;
}

$solution = $destroyedAsteroids[199]['x'] * 100 + $destroyedAsteroids[199]['y'];

echo 'Part 2:'. $solution . PHP_EOL;