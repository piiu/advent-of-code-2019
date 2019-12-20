<?php
require_once('Utils.php');

$input = file_get_contents(__DIR__ . '/input/day18');
$vault = new Vault($input);

$vault->solve();
echo 'Part 1: '. $vault->minPath . PHP_EOL;


class Vault {
    private $map = [];
    private $numberOfKeys = 0;
    private $pathsTested = 0;
    public $currentLocation;
    public $minPath = null;

    const PASSAGE = '.';
    const WALL = '#';
    const MY_LOCATION = '@';
    const EXPLORED = 'X';

    const KEY = '/^[a-z]$/';
    const DOOR = '/^[A-Z]$/';

    public function __construct($input) {
        $rows = explode("\n", $input);
        foreach ($rows as $y => $row) {
            $chars = str_split($row);
            foreach ($chars as $x => $char) {
                if ($char === self::MY_LOCATION) {
                    $this->currentLocation = new Location($x, $y);
                    $char = self::PASSAGE;
                }
                if (preg_match(self::KEY, $char)) {
                    $this->numberOfKeys++;
                }
                $this->map[$y][$x] = $char;
            }
        }
    }

    public function solve(Location $location = null, array $mapState = null, $stepsTaken = 0, $currentPath = []) {
        $location = $location ?? $this->currentLocation;
        $mapState = $mapState ?? $this->map;

        if ($this->minPath && $stepsTaken >= $this->minPath) {
            echo 'tested: '. $this->pathsTested . ', best: ' .  $this->minPath . PHP_EOL;
            $this->pathsTested++;
            return;
        }

        if (count($currentPath) === $this->numberOfKeys) {
            $this->pathsTested++;
            $this->minPath = $stepsTaken;
            return;
        }

        $keyLocations = $this->getClosestKeys($mapState, $location);
        foreach ($keyLocations as $keyLocation) {
            $newMapState = $mapState;
            $key = $newMapState[$keyLocation->y][$keyLocation->x];
            $this->useKey($newMapState, $key);
            $this->solve($keyLocation, $newMapState, $stepsTaken + $keyLocation->stepsTo, array_merge($currentPath, [$key]));
        }
    }

    private function getClosestKeys(array $mapState, Location $location) {
        $keyLocations = [];
        $locationsToSpreadFrom = [$location];
        $stepsTaken = 0;
        while (!empty($locationsToSpreadFrom)) {
            $stepsTaken++;
            foreach ($locationsToSpreadFrom as $index => $location) {
                unset($locationsToSpreadFrom[$index]);
                foreach (Location::DIRECTIONS as $direction) {
                    $newLocation = new Location($location->x, $location->y, $direction);
                    if ($this->isKey($mapState, $newLocation)) {
                        $keyLocations[] = new Location($newLocation->x, $newLocation->y, null, $stepsTaken);
                    }
                    if ($this->isWall($mapState, $newLocation) || $this->isDoor($mapState, $newLocation) || $this->isExplored($mapState, $newLocation) || $this->isKey($mapState, $newLocation)) {
                        continue;
                    }
                    $this->markExplored($mapState, $newLocation);
                    $locationsToSpreadFrom[] = $newLocation;
                }
            }
        }
        return $keyLocations;
    }

    private function markExplored(array &$mapState, Location $location) {
        $mapState[$location->y][$location->x] = self::EXPLORED;
    }

    private function isExplored(array $mapState, Location $location) {
        return $mapState[$location->y][$location->x] === self::EXPLORED;
    }

    private function isWall(array $mapState, Location $location) {
        return $mapState[$location->y][$location->x] === self::WALL;
    }

    private function isDoor(array $mapState, Location $location) {
        return preg_match(self::DOOR, $mapState[$location->y][$location->x]);
    }

    private function isKey(array $mapState, Location $location) {
        return preg_match(self::KEY, $mapState[$location->y][$location->x]);
    }

    public function useKey(array &$mapState, string $key) {
        foreach ($this->map as $y => $row) {
            foreach ($row as $x => $char) {
                if ($char === $key || $char === strtoupper($key)) {
                    $mapState[$y][$x] = self::PASSAGE;
                }
            }
        }
    }
}

class Location {
    public $x;
    public $y;

    public $stepsTo;

    const UP = 1;
    const DOWN = 2;
    const LEFT = 3;
    const RIGHT = 4;

    const DIRECTIONS = [self::UP, self::DOWN, self::LEFT, self::RIGHT];

    public function __construct(int $x, int $y, int $direction = null, $stepsTo = null) {
        $this->x = $x;
        $this->y = $y;
        $this->stepsTo = $stepsTo;
        if ($direction) {
            $this->addDirection($direction);
        }
    }

    public function addDirection(int $direction) {
        if ($direction == self::UP) {
            $this->y += 1;
        }
        if ($direction == self::DOWN) {
            $this->y -= 1;
        }
        if ($direction == self::LEFT) {
            $this->x += 1;
        }
        if ($direction == self::RIGHT) {
            $this->x -= 1;
        }
        return $this;
    }
}