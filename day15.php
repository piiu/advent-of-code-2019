<?php
require_once('IntcodeComputer.php');

$input = file_get_contents(__DIR__ . '/input/day15');
$code = explode(',', $input);

const WALL = '#';
const OXYGEN = 'O';

const NORTH = 1;
const SOUTH = 2;
const WEST = 3;
const EAST = 4;

const DIRECTIONS = [NORTH, EAST, SOUTH, WEST];

$board[0][0] = 0;
$droid = new Droid($code);
$tankLocation = null;

while (!$tankLocation || !$droid->isInStartingLocation()) {
    $direction = $droid->getBestDirection($board);
    list($previousX, $previousY) = $droid->getCurrentLocation();
    list($x, $y) = $droid->getLocationInDirection($direction);
    $droid->move($direction);
    if ($droid->status === Droid::STATUS_WALL) {
        $board[$y][$x] = WALL;
    } elseif (!isset($board[$y][$x])) {
        $board[$y][$x] = $board[$previousY][$previousX] + 1;
    }
    if ($droid->status === Droid::STATUS_TANK) {
        $tankLocation = $droid->getCurrentLocation();
        echo 'Part 1: '. $board[$droid->locationY][$droid->locationX] . PHP_EOL;
    }
}

$locationsToSpreadFrom = [$tankLocation];
$tick = 0;
while (!empty($locationsToSpreadFrom)) {
    foreach ($locationsToSpreadFrom as $key => $location) {
        unset($locationsToSpreadFrom[$key]);
        list($currentX, $currentY) = $location;
        $board[$currentY][$currentX] = OXYGEN;
        foreach (DIRECTIONS as $direction) {
            list($x, $y) = getLocationForDirection($currentX, $currentY, $direction);
            if ($board[$y][$x] !== WALL && $board[$y][$x] !== OXYGEN) {
                $locationsToSpreadFrom[] = [$x, $y];
            }
        }
    }
    if (empty($locationsToSpreadFrom)) {
        echo 'Part 2: '. $tick . PHP_EOL;
        break;
    }
    $tick++;
}

class Droid {
    private  $computer;
    public $status;
    public $locationX = 0;
    public $locationY = 0;

    const STATUS_WALL = 0;
    const STATUS_STEP = 1;
    const STATUS_TANK = 2;

    public function __construct(array $code) {
        $this->computer = new IntcodeComputer($code);
    }

    public function getBestDirection(array $board) : int {
        $minWalkedDirection = null;
        $minWalkedDistance = null;
        foreach (DIRECTIONS as $direction) {
            list($x, $y) = $this->getLocationInDirection($direction);
            if (!isset($board[$y][$x])) {
                return $direction;
            }
            if (isset($board[$y][$x]) && $board[$y][$x] === WALL) {
                continue;
            }
            if ($minWalkedDistance === null || $board[$y][$x] < $minWalkedDistance) {
                $minWalkedDistance = $board[$y][$x];
                $minWalkedDirection = $direction;
            }
        }
        return $minWalkedDirection;
    }

    public function getCurrentLocation() {
        return [$this->locationX, $this->locationY];
    }

    public function move(int $direction) {
        $this->status = $this->computer->addInput($direction)->getFirstOutput();
        if ($this->status !== self::STATUS_WALL) {
            list($this->locationX, $this->locationY) = $this->getLocationInDirection($direction);
        }
    }

    public function getLocationInDirection(int $direction) : array {
        return getLocationForDirection($this->locationX, $this->locationY, $direction);
    }

    public function isInStartingLocation() {
        return $this->locationX === 0 && $this->locationY === 0;
    }
}

function getLocationForDirection(int $x, int $y, int $direction) {
    if ($direction == NORTH) {
        return [$x, $y + 1];
    }
    if ($direction == SOUTH) {
        return [$x, $y - 1];
    }
    if ($direction == WEST) {
        return [$x - 1, $y];
    }
    if ($direction == EAST) {
        return [$x + 1, $y];
    }
}