<?php

$input = file_get_contents(__DIR__ . '\input\day06');
$rows = explode(PHP_EOL, $input);

$solarSystem = new SolarSystem($rows);

$orbits = 0;
foreach ($solarSystem->planets as $planet) {
    $orbits += $planet->distance;
}

echo 'Part 1: ' . $orbits . PHP_EOL;

$myLocation = $solarSystem->getPlanetByName('YOU')->parent;
$santaLocation = $solarSystem->getPlanetByName('SAN')->parent;

$commonPath = array_intersect($myLocation->getPathToCom(), $santaLocation->getPathToCom());
$totalDistance = $myLocation->distance + $santaLocation->distance - 2 * (count($commonPath) - 1);

echo 'Part 2: ' . $totalDistance . PHP_EOL;

class SolarSystem {
    /** @var Planet[] $planets */
    public $planets;

    public function __construct(array $orbits) {
        $this->planets[] = new Planet('COM', null, 0);
        while (!empty($orbits)) {
            foreach ($orbits as $key => $row) {
                $elements = explode(')', $row);
                if (!$parent = $this->getPlanetByName($elements[0])) {
                    continue;
                }
                $this->planets[] = new Planet($elements[1], $parent);
                unset($orbits[$key]);
            }
        }
    }

    public function getPlanetByName(string $name) {
        foreach ($this->planets as $planet) {
            if ($planet->name == $name) {
                return $planet;
            }
        }
        return null;
    }
}


class Planet {
    public $name;
    /** @var Planet */
    public $parent;
    public $distance;

    public function __construct(string $name, Planet $parent = null, int $distance = null) {
        $this->name = $name;
        $this->parent = $parent;
        $this->distance = $distance ?? $parent->distance +1;
    }

    public function getPathToCom() : array {
        $path = [];
        $location = $this->parent;
        while ($location) {
            $path[] = $location->name;
            $location = $location->parent;
        }
        return $path;
    }
}