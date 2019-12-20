<?php
require_once('IntcodeComputer.php');
require_once('Location.php');

$input = file_get_contents(__DIR__ . '/input/day15');
$code = explode(',', $input);

$map = new Map();
$droid = new Droid($code);
$tankLocation = null;

while (!$tankLocation || !$map->droidInStartingLocation()) {
    $direction = $map->getBestDirection();
    $droid->tryDirection($direction);
    $testedLocation = new Location($map->droidLocation->x, $map->droidLocation->y, $direction);

    if ($droid->status === Droid::STATUS_WALL) {
        $map->setWall($testedLocation);
        continue;
    }
    if (!$map->hasSomething($testedLocation)) {
        $currentSteps = $map->getValue($map->droidLocation);
        $map->setValue($testedLocation, $currentSteps + 1);
    }
    $map->droidLocation = $testedLocation;

    if ($droid->status === Droid::STATUS_TANK) {
        $tankLocation = clone($map->droidLocation);
        echo 'Part 1: '. $map->getValue($map->droidLocation) . PHP_EOL;
    }
}

$locationsToSpreadFrom = [$tankLocation];
$tick = 0;
while (!empty($locationsToSpreadFrom)) {
    foreach ($locationsToSpreadFrom as $key => $location) {
        unset($locationsToSpreadFrom[$key]);
        $map->setOxygen($location);
        foreach (Location::DIRECTIONS as $direction) {
            $newLocation = new Location($location->x, $location->y, $direction);
            if (!$map->isWall($newLocation) && !$map->isOxygen($newLocation)) {
                $locationsToSpreadFrom[] = $newLocation;
            }
        }
    }
    if (empty($locationsToSpreadFrom)) {
        echo 'Part 2: '. $tick . PHP_EOL;
        break;
    }
    $tick++;
}

class Map {
    public $state = [];
    public $droidLocation;

    const WALL = '#';
    const OXYGEN = 'O';

    public function __construct() {
        $this->droidLocation = new Location(0,0);
    }

    public function getBestDirection() : int {
        $minWalkedDirection = null;
        $minWalkedDistance = null;
        foreach (Location::DIRECTIONS as $direction) {
            $location = new Location($this->droidLocation->x, $this->droidLocation->y, $direction);

            if (!$this->hasSomething($location)) {
                return $direction;
            }
            if ($this->isWall($location)) {
                continue;
            }
            $distance = $this->getValue($location);
            if ($minWalkedDistance === null || $distance < $minWalkedDistance) {
                $minWalkedDistance = $distance;
                $minWalkedDirection = $direction;
            }
        }
        return $minWalkedDirection;
    }

    public function hasSomething(Location $location) {
        return isset($this->state[$location->y][$location->x]);
    }

    public function isWall(Location $location) {
        return $this->hasSomething($location) && $this->state[$location->y][$location->x] === self::WALL;
    }

    public function isOxygen(Location $location) {
        return $this->hasSomething($location) && $this->state[$location->y][$location->x] === self::OXYGEN;
    }

    public function getValue(Location $location) {
        if (!$this->hasSomething($location) || $this->isWall($location)) {
            return null;
        }
        return $this->state[$location->y][$location->x];
    }

    public function setWall(Location $location) {
        $this->state[$location->y][$location->x] = self::WALL;
    }

    public function setOxygen(Location $location) {
        $this->state[$location->y][$location->x] = self::OXYGEN;
    }

    public function setValue(Location $location, $value) {
        $this->state[$location->y][$location->x] = $value;

    }

    public function droidInStartingLocation() {
        return $this->droidLocation->x === 0 && $this->droidLocation->y === 0;
    }
}

class Droid {
    private  $computer;
    public $status;

    const STATUS_WALL = 0;
    const STATUS_STEP = 1;
    const STATUS_TANK = 2;

    public function __construct(array $code) {
        $this->computer = new IntcodeComputer($code);
    }

    public function tryDirection(int $direction) {
        $this->status = $this->computer->addInput($direction)->getFirstOutput();
    }
}