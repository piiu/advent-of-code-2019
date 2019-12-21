<?php
require_once('Location.php');
require_once('Utils.php');

$input = file_get_contents(__DIR__ . '/input/day20');

$maze = DonutMaze::createFromInput($input);
echo 'Part 1: '. $maze->solve(false) . PHP_EOL;

$maxNestingLevel = 255;

do {
    $maze = DonutMaze::createFromInput($input);
    $maze->maxNestingLevel = $maxNestingLevel++;
    $maze->solve(true);
} while (true);


class DonutMaze {
    public $cleanMap = [];
    public $map = [];
    /** @var Portal[] */
    public $portals = [];
    public $startingLocation;
    public $level = 0;
    public $stepsTaken = 0;
    public $solutions = [];
    public $maxNestingLevel;

    const PASSAGE = '.';
    const WALL = '#';
    const EXPLORED = '+';

    const PORTAL = '/^[A-Z]$/';

    public static function createFromInput(string $input) : self {
        $maze = new self();
        $rows = explode("\n", $input);
        foreach ($rows as $y => $row) {
            $chars = str_split(rtrim($row, "\r"));
            foreach ($chars as $x => $char) {
                $maze->map[$y][$x] = $char;
            }
        }
        $maze->cleanMap = $maze->map;
        $maze->listPortals();
        return $maze;
    }

    public function cloneWithCleanMap(Location $startingLocation, int $level) : self {
        $maze = new self();
        $maze->cleanMap = $maze->map = $this->cleanMap;
        $maze->portals = $this->portals;
        $maze->startingLocation = $startingLocation;
        $maze->level = $level;
        $maze->stepsTaken = $this->stepsTaken;
        $maze->maxNestingLevel = $this->maxNestingLevel;
        return $maze;
    }

    public function solve(bool $recursive, Location $currentLocation = null, Portal $usedPortal = null, array $levelStates = [], $nestingLevel = 0) {
        $locationsToSpreadFrom = [$currentLocation ?? $this->startingLocation];

        if ($nestingLevel > $this->maxNestingLevel) {
            return false;
        }

        while (!empty($locationsToSpreadFrom)) {
            $this->stepsTaken++;
            foreach ($locationsToSpreadFrom as $index => $location) {
                unset($locationsToSpreadFrom[$index]);
                $this->setExplored($location);
                foreach (Location::DIRECTIONS as $direction) {
                    $newLocation = new Location($location->x, $location->y, $direction);

                    if ($newLocation->getValueFromMap($this->map) === self::PASSAGE) {
                        $locationsToSpreadFrom[] = $newLocation;
                        continue;
                    }

                    $potentialPortal = $newLocation->getValueFromMap($this->map);
                    if (preg_match(DonutMaze::PORTAL, $potentialPortal)) {
                        $portal = Portal::getByPassage($location, $this->portals);
                        if ($this->level === 0 && $portal->name == 'ZZ') {
                            $solution = $this->stepsTaken - 1;
                            $this->solutions[] = $solution;
                            if (!$recursive) {
                                return $solution;
                            }
                            echo 'Part 2 possible solution: ' . $solution . PHP_EOL;
                        }
                        if (!$recursive) {
                            if (!empty($portal->otherEnd)) {
                                $locationsToSpreadFrom[] = $portal->otherEnd->passage;
                            }
                            continue;
                        }
                        if ($this->level === 0 && $portal->isOuter) {
                            continue;
                        }
                        if ($this->level !== 0 && in_array($portal->name, ['AA', 'ZZ'])) {
                            continue;
                        }
                        if ($usedPortal && $usedPortal->isOtherEnd($portal)) {
                            continue;
                        }

                        $levelStates[$this->level] = $this;
                        $level = $this->level + ($portal->isOuter ? -1 : 1);

                        if (isset($levelStates[$level])) {
                            $newLevel = $levelStates[$level];
                            $newLevel->stepsTaken = $this->stepsTaken;
                        } else {
                            $newLevel = $this->cloneWithCleanMap($portal->otherEnd->passage, $level);
                        }

                        $newLevel->solve(true, $portal->otherEnd->passage, $portal, $levelStates, $nestingLevel +1);
                    }
                }
            }
        }
        return false;
    }

    public function setExplored(Location $location) {
        $this->map[$location->y][$location->x] = self::EXPLORED;
    }

    private function listPortals() {
        foreach ($this->map as $y => $row) {
            foreach ($row as $x => $char) {
                if (preg_match(self::PORTAL, $char)) {
                    $portalLocation = new Location($x, $y);
                    $portal = Portal::createBySingleLetter($this->map,$portalLocation, $this->isOuter($portalLocation));
                    if (!$portal || Portal::existsInList($portal->passage, $this->portals)) {
                        continue;
                    }
                    if ($portal->name === 'AA') {
                        $this->startingLocation = $portal->passage;
                    }
                    $portal->findOtherEnd($this->portals);
                    $this->portals[] = $portal;
                }
            }
        }
    }

    private function isOuter(Location $location) : bool {
        if ($location->x < 3 || $location->y < 3) {
            return true;
        }
        $maxY = count($this->map) - 1;
        $maxX = count($this->map[3]) - 1;
        if ($location->x > $maxX - 3 || $location->y > $maxY - 3) {
            return true;

        }
        return false;
    }
}

class Portal {
    public $name;
    public $passage;
    public $otherEnd;
    public $isOuter;

    public function __construct(string $name, Location $passage, bool $isOuter) {
        $this->passage = $passage;
        $this->name = $name;
        $this->isOuter = $isOuter;
    }

    public function findOtherEnd(array $list) {
        foreach ($list as $portal) {
            if ($this->isOtherEnd($portal)) {
                $portal->otherEnd = $this;
                $this->otherEnd = $portal;
                return;
            }
        }
    }

    public function isOtherEnd(self $portal) : bool {
        return ($this->name === $portal->name || $this->name === strrev($portal->name))
            && !$this->passage->isEqual($portal->passage);
    }

    public static function createBySingleLetter(array $mapState, Location $location, bool $isOuter) {
        $currentLetter = $location->getValueFromMap($mapState);
        foreach (Location::DIRECTIONS as $direction) {
            $secondLetterLocation = new Location($location->x, $location->y, $direction);
            $secondLetter = $secondLetterLocation->getValueFromMap($mapState);
            if ($secondLetter && preg_match(DonutMaze::PORTAL, $secondLetter)) {
                $passageLocation = new Location($secondLetterLocation->x, $secondLetterLocation->y, $direction);
                $passage = $passageLocation->getValueFromMap($mapState);
                if ($passage && $passage === DonutMaze::PASSAGE) {
                    return new Portal($currentLetter.$secondLetter, $passageLocation, $isOuter);
                }
            }
        }
        return null;
    }

    public static function getByPassage(Location $location, array $list) {
        foreach ($list as $portal) {
            if ($portal->passage->isEqual($location)) {
                return $portal;
            }
        }
        return null;
    }

    public static function existsInList(Location $location, array $list) {
        return !empty(self::getByPassage($location, $list));
    }
}