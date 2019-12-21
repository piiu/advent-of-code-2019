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

    public function solve(Location $location = null, $stepsTaken = 0, $currentPath = []) {
        $location = $location ?? $this->currentLocation;

        if ($this->minPath && $stepsTaken >= $this->minPath) {
            $this->outputProgress();
            return;
        }

        if (count($currentPath) === $this->numberOfKeys) {
            $this->minPath = $stepsTaken;
            $this->outputProgress();
            return;
        }

        $keyLocations = $this->getClosestKeys($currentPath, $location);
        foreach ($keyLocations as $keyLocation) {
            $key = $this->map[$keyLocation->y][$keyLocation->x];
            $this->solve($keyLocation, $stepsTaken + $keyLocation->stepsTo, array_merge($currentPath, [$key]));
        }
    }

    private function outputProgress() {
        $this->pathsTested++;
        echo 'tested: '. $this->pathsTested . ', best: ' .  $this->minPath . PHP_EOL;
    }

    private function getClosestKeys(array $currentPath, Location $location) {
        $keyLocations = [];
        $locationsToSpreadFrom = [$location];
        $stepsTaken = 0;
        $mapState = $this->map;
        $this->markExplored($mapState, $location);
        while (!empty($locationsToSpreadFrom)) {
            $stepsTaken++;
            if ($this->minPath && $stepsTaken >= $this->minPath) {
                break;
            }
            foreach ($locationsToSpreadFrom as $index => $location) {
                unset($locationsToSpreadFrom[$index]);
                foreach (Location::DIRECTIONS as $direction) {
                    $newLocation = new Location($location->x, $location->y, $direction);
                    if ($key = $this->getKey($newLocation)) {
                        if (!$this->keyUsed($currentPath, $key)) {
                            $keyLocations[] = new Location($newLocation->x, $newLocation->y, null, $stepsTaken);
                        }
                    }

                    if ($this->isExplored($mapState, $newLocation) || !$this->isPassage($newLocation, $currentPath)) {
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

    private function isExplored(array $mapState, Location $location) : bool {
        return $mapState[$location->y][$location->x] === self::EXPLORED;
    }

    private function isPassage(Location $location, array $currentPath) : bool {
        if ($this->map[$location->y][$location->x] === self::PASSAGE) {
            return true;
        }
        if ($this->isOpenDoor($location, $currentPath)) {
            return true;
        }
        if ($key = $this->getKey($location)) {
            return $this->keyUsed($currentPath, $key);
        }
        return false;
    }

    private function getKey(Location $location) {
        $key = $this->map[$location->y][$location->x];
        if (preg_match(self::KEY, $key)) {
            return $key;
        }
        return false;
    }

    private function keyUsed(array $currentPath, string $key) : bool {
        return in_array($key, $currentPath);
    }

    private function isOpenDoor(Location $location, array $currentPath) : bool {
        $door = $this->map[$location->y][$location->x];
        if (!preg_match(self::DOOR, $door)) {
            return false;
        }
        return in_array(strtolower($door), $currentPath);
    }
}