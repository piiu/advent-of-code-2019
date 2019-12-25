<?php
require_once('Location.php');

const PASSAGE = '.';
const WALL = '#';
const ENTRANCE = '@';
const EXPLORED = 'X';

const KEY = '/^[a-z]$/';

$input = file_get_contents(__DIR__ . '/input/day18');
$rows = explode("\n", $input);

$baseMap = [];
$currentLocation = null;
$numberOfKeys = 0;
foreach ($rows as $y => $row) {
    $chars = str_split($row);
    foreach ($chars as $x => $char) {
        if ($char === ENTRANCE) {
            $currentLocation = new Location($x, $y);
            $char = PASSAGE;
        }
        if (preg_match(KEY, $char)) {
            $numberOfKeys++;
        }
        $baseMap[$y][$x] = $char;
    }
}

class KeyLocation extends Location {
    public $stepsTo;
    public $fromRobot;
    public $key;

    public function __construct(int $x, int $y, int $direction = null, int $stepsTo = null, string $key = null, string $fromRobot = null) {
        parent::__construct($x, $y, $direction);
        $this->stepsTo = $stepsTo;
        $this->key = $key;
        $this->fromRobot = $fromRobot;
    }
}

$runners = [
    new Runner($baseMap, 0, null, [], $currentLocation)
];
for ($tick = 1; $tick <= $numberOfKeys; $tick++) {
    $newRunners = [];
    foreach ($runners as $runner) {
        $keyLocations = getClosestKeys($runner->map, $runner->currentLocation);
        foreach ($keyLocations as $keyLocation) {
            $newRunners[] = new Runner($runner->map, $runner->stepsTaken, $keyLocation, $runner->keys);
        }
    }
    $runners = killBadRunners($newRunners);
    echo 'Tick: ' . $tick . ' Runners: ' . count($runners) . PHP_EOL;
}

$minPath = null;
foreach ($runners as $runner) {
    $minPath = !$minPath || $runner->stepsTaken < $minPath ? $runner->stepsTaken : $minPath;
}
echo 'Part 1: '. $minPath . PHP_EOL;

$baseMap[39][40] = $baseMap[40][39] = $baseMap[40][41] = $baseMap[41][40] = WALL;

$runners = [
    new MultiRunner($baseMap, 0, null, [], [
        'A' => new Location(39, 39),
        'B' => new Location(39, 41),
        'C' => new Location(41, 39),
        'D' => new Location(41, 41)
    ])
];

for ($tick = 1; $tick <= $numberOfKeys; $tick++) {
    $newRunners = [];
    /** @var MultiRunner $runner */
    foreach ($runners as $runner) {
        $keyLocations = [];
        foreach ($runner->robotLocations as $robot => $robotLocation) {
            $keyLocations = array_merge($keyLocations, getClosestKeys($runner->map, $robotLocation, $robot));
        }
        /** @var KeyLocation $keyLocation */
        foreach ($keyLocations as $keyLocation) {
            $robotLocations = $runner->robotLocations;
            $robotLocations[$keyLocation->fromRobot] = new Location($keyLocation->x, $keyLocation->y);
            $newRunners[] = new MultiRunner($runner->map, $runner->stepsTaken, $keyLocation, $runner->keys, $robotLocations);
        }
    }
    $runners = killBadRunners($newRunners);
    echo 'Tick: ' . $tick . ' Runners: ' . count($runners) . PHP_EOL;
}

$minPath = null;
foreach ($runners as $runner) {
    $minPath = !$minPath || $runner->stepsTaken < $minPath ? $runner->stepsTaken : $minPath;
}
echo 'Part 2: '. $minPath . PHP_EOL;


function getClosestKeys(array $mapState, Location $location, $robot = null) {
    $keyLocations = [];
    $locationsToSpreadFrom = [$location];
    $stepsTaken = 0;
    while (!empty($locationsToSpreadFrom)) {
        $stepsTaken++;
        foreach ($locationsToSpreadFrom as $index => $location) {
            unset($locationsToSpreadFrom[$index]);
            foreach (Location::DIRECTIONS as $direction) {
                $newLocation = new Location($location->x, $location->y, $direction);
                $locationValue = $newLocation->getValueFromMap($mapState);
                if (preg_match(KEY, $locationValue)) {
                    $keyLocations[] = new KeyLocation($newLocation->x, $newLocation->y, null, $stepsTaken, $locationValue, $robot);
                }
                if ($mapState[$newLocation->y][$newLocation->x] !== PASSAGE) {
                    continue;
                }
                $mapState[$newLocation->y][$newLocation->x] = EXPLORED;
                $locationsToSpreadFrom[] = $newLocation;
            }
        }
    }
    return $keyLocations;
}

function killBadRunners(array $runners) {
    $bestPaths = [];
    /** @var Runner $runner */
    foreach ($runners as $index => $runner) {
        $keyString = $runner->getKeyString();
        $bestSoFar = $bestPaths[$keyString] ?? null;
        if (!$bestSoFar || $runner->stepsTaken < $bestPaths[$keyString]) {
            $bestPaths[$keyString] = $runner->stepsTaken;
        }
        if ($bestSoFar && $runner->stepsTaken === $bestSoFar) {
            unset($runners[$index]);
        }
    }

    foreach ($runners as $index => $runner) {
        if ($runner->stepsTaken > $bestPaths[$runner->getKeyString()]) {
            unset ($runners[$index]);
        }
    }
    return $runners;
}

class Runner {
    public $map;
    public $stepsTaken;
    public $currentLocation;
    public $keys;

    public function __construct(array $map, int $stepsTaken, KeyLocation $keyLocation = null, array $existingKeys = [], Location $currentLocation = null) {
        $this->map = $map;
        $this->stepsTaken = $stepsTaken;
        $this->keys = $existingKeys;
        if ($keyLocation) {
            $this->stepsTaken += $keyLocation->stepsTo;
            $this->openDoor($keyLocation);
            $this->keys[] = $keyLocation->key;
        }
        if ($currentLocation) {
            $this->currentLocation = $currentLocation;
        } elseif ($keyLocation) {
            $this->currentLocation = new Location($keyLocation->x, $keyLocation->y);
        }
    }

    public function getKeyString() : string {
        $keys = $this->keys;
        sort($keys);
        return $this->currentLocation->x . '-' . $this->currentLocation->y . '-' . implode('', $keys);
    }

    private function openDoor(KeyLocation $key) {
        $this->map[$key->y][$key->x] = PASSAGE;
        $door = strtoupper($key->key);
        foreach ($this->map as $y => $row) {
            foreach ($row as $x => $char) {
                if ($char === $door) {
                    $this->map[$y][$x] = PASSAGE;
                    return;
                }
            }
        }
    }
}

class MultiRunner extends Runner {
    public $robotLocations = [];

    public function __construct(array $map, int $stepsTaken, KeyLocation $keyLocation = null, array $existingKeys = [], $robotLocations = []) {
        parent::__construct($map, $stepsTaken, $keyLocation, $existingKeys);
        $this->robotLocations = $robotLocations;
    }

    public function getKeyString(): string {
        $keys = $this->keys;
        sort($keys);
        $string = implode('', $keys);
        foreach ($this->robotLocations as $robotLocation) {
            $string .= '-' . $robotLocation->x . '-'. $robotLocation->y;
        }
        return $string;
    }
}