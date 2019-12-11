<?php

$input = trim(file_get_contents(__DIR__ . '\input\day03'));

$board = new Board();
$paths = explode("\n", $input);
foreach ($paths as $index => $pathString) {
    $path = explode(',', $pathString);
    $board->drawPath($index, $path);
}

$minDistance = $minSteps = null;
foreach ($board->crossings as $point) {
    $distance = $point->getDistance();
     if (!$minDistance || $distance < $minDistance) {
         $minDistance = $distance;
     }
    if (!$minSteps || $point->stepsTaken < $minSteps) {
        $minSteps = $point->stepsTaken;
    }
}

echo 'Part 1: ' . $minDistance . PHP_EOL;
echo 'Part 2: ' . $minSteps . PHP_EOL;

class Board {
    /** @var Point[] */
    public $points = [];
    /** @var Point[] */
    public $crossings = [];

    public function drawPath($marker, $path) {
        $x = $y = $stepsTaken = 0;
        foreach ($path as $step) {
            $axis = in_array($step[0], ['L', 'R']) ? 'x' : 'y';
            $direction = in_array($step[0], ['R', 'U']) ? 1 : -1;
            $distance = substr($step, 1);

            for ($k = 0 ; $k < $distance; $k++) {
                $stepsTaken++;
                $$axis = $$axis + $direction;

                $existingPoint = $this->points[$x.'-'.$y] ?? null;
                if ($existingPoint && $existingPoint->marker != $marker) {
                    $this->crossings[] = new Point($x, $y, $marker, $stepsTaken + $existingPoint->stepsTaken);
                }
                $this->points[$x.'-'.$y] = new Point($x, $y, $marker, $stepsTaken);
            }
        }
    }
}

class Point {
    public $x;
    public $y;
    public $marker;
    public $stepsTaken;

    public function __construct($x, $y, $marker, $stepsTaken) {
        $this->x = $x;
        $this->y = $y;
        $this->marker = $marker;
        $this->stepsTaken = $stepsTaken;
    }

    public function getDistance() {
        return abs($this->x) + abs($this->y);
    }
}