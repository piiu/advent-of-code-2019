<?php
require_once('Utils.php');

$input = file_get_contents(__DIR__ . '/input/day18');
$vault = new Vault($input);

$vault->solve();
echo 'Part 1: '. min($vault->allPaths) . PHP_EOL;


class Vault {
    private $map = [];
    public $currentLocation;
    public $allPaths = [];

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
                $this->map[$y][$x] = $char;
            }
        }
    }

    public function solve(Location $location = null, array $mapState = null, $stepsTaken = 0, $currentPath = []) {
        $location = $location ?? $this->currentLocation;
        $mapState = $mapState ?? $this->map;

        if (!empty($this->allPaths) && $stepsTaken >= min($this->allPaths)) {
            return;
        }

        if ($this->getNumberOfKeys($mapState) === 0) {
            $this->allPaths[] = $stepsTaken;
            return;
        }

        $keyLocations = $this->getClosestKeys($mapState, $location);
        foreach ($keyLocations as $keyLocation) {
            $newMapState = $mapState;
            $key = $newMapState[$keyLocation->y][$keyLocation->x];
            $currentPath[] = $key;
            $this->useKey($newMapState, $key);
            $this->solve($keyLocation, $newMapState, $stepsTaken + $keyLocation->stepsTo, $currentPath);
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
                    if ($this->isWall($mapState, $newLocation) || $this->isDoor($mapState, $newLocation) || $this->isExplored($mapState, $newLocation)) {
                        continue;
                    }
                    if ($this->isKey($mapState, $newLocation)) {
                        $keyLocations[] = new Location($newLocation->x, $newLocation->y, null, $stepsTaken);
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

    public function getNumberOfKeys(array $mapState) : int {
        $count = 0;
        foreach ($mapState as $row) {
            foreach ($row as $char) {
                if (preg_match(self::KEY, $char)) {
                    $count++;
                }
            }
        }
        return $count;
    }

    public function draw() {
        $mapCopy = $this->map;
        $mapCopy[$this->currentLocation->y][$this->currentLocation->x] = self::MY_LOCATION;
        Utils::drawBoard($mapCopy);
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