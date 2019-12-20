<?php
require_once('Location.php');

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
    const MY_Location = '@';
    const EXPLORED = 'X';

    const KEY = '/^[a-z]$/';
    const DOOR = '/^[A-Z]$/';

    public function __construct($input) {
        $rows = explode("\n", $input);
        foreach ($rows as $y => $row) {
            $chars = str_split($row);
            foreach ($chars as $x => $char) {
                if ($char === self::MY_Location) {
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

    public function solve(Location $Location = null, array $mapState = null, $stepsTaken = 0, $currentPath = []) {
        $Location = $Location ?? $this->currentLocation;
        $mapState = $mapState ?? $this->map;

        if ($this->minPath && $stepsTaken >= $this->minPath) {
            $this->pathsTested++;
            $this->outputProgress();
            return;
        }

        if (count($currentPath) === $this->numberOfKeys) {
            $this->pathsTested++;
            $this->outputProgress();
            $this->minPath = $stepsTaken;
            return;
        }

        $keyLocations = $this->getClosestKeys($mapState, $Location);
        foreach ($keyLocations as $keyLocation) {
            $newMapState = $mapState;
            $key = $newMapState[$keyLocation->y][$keyLocation->x];
            $this->openDoor($newMapState, $keyLocation, $key);
            $this->solve($keyLocation, $newMapState, $stepsTaken + $keyLocation->stepsTo, array_merge($currentPath, [$key]));
        }
    }

    private function outputProgress() {
        //echo 'tested: '. $this->pathsTested . ', best: ' .  $this->minPath . PHP_EOL;
    }

    private function getClosestKeys(array $mapState, Location $Location) {
        $keyLocations = [];
        $LocationsToSpreadFrom = [$Location];
        $stepsTaken = 0;
        while (!empty($LocationsToSpreadFrom)) {
            $stepsTaken++;
            foreach ($LocationsToSpreadFrom as $index => $Location) {
                unset($LocationsToSpreadFrom[$index]);
                foreach (Location::DIRECTIONS as $direction) {
                    $newLocation = new Location($Location->x, $Location->y, $direction);
                    if ($this->isKey($mapState, $newLocation)) {
                        $keyLocations[] = new Location($newLocation->x, $newLocation->y, null, $stepsTaken);
                    }
                    if (!$this->isPassage($mapState, $newLocation)) {
                        continue;
                    }
                    $this->markExplored($mapState, $newLocation);
                    $LocationsToSpreadFrom[] = $newLocation;
                }
            }
        }
        return $keyLocations;
    }

    private function markExplored(array &$mapState, Location $Location) {
        $mapState[$Location->y][$Location->x] = self::EXPLORED;
    }

    private function isPassage(array $mapState, Location $Location) {
        return $mapState[$Location->y][$Location->x] === self::PASSAGE;
    }

    private function isKey(array $mapState, Location $location) {
        return preg_match(self::KEY, $mapState[$location->y][$location->x]);
    }

    private function openDoor(array &$mapState, Location $keyLocation, string $key) {
        $mapState[$keyLocation->y][$keyLocation->x] = self::PASSAGE;
        $door = strtoupper($key);
        foreach ($this->map as $y => $row) {
            foreach ($row as $x => $char) {
                if ($char === $door) {
                    $mapState[$y][$x] = self::PASSAGE;
                    return;
                }
            }
        }
    }
}