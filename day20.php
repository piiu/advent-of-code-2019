<?php
require_once('Location.php');
require_once('Utils.php');

$input = file_get_contents(__DIR__ . '/input/day20');

$maze = DonutMaze::createFromInput($input);
echo 'Part 1: '. $maze->solve() . PHP_EOL;

$maze = DonutMaze::createFromInput($input);
echo 'Part 2: '. $maze->solveWithMazeRunners() . PHP_EOL;

class MazeRunner {
    public $level = 0;
    public $location;
    public $pathTaken = [];
    public $stepsTaken = -1;
    public $emergedFrom = null;

    public function __construct(Location $startingLocation) {
        $this->setLocation($startingLocation);
    }

    public function setLocation(Location $location) {
        $this->location = $location;
        $this->pathTaken[$location->x . ':' . $location->y] = true;
        $this->stepsTaken++;
    }

    public function hasExplored(Location $location): bool {
        return isset($this->pathTaken[$location->x . ':' . $location->y]);
    }

    public function setLevel(int $level) {
        $this->level = $level;
        $this->pathTaken = [];
    }
}

class DonutMaze {
    public $cleanMap = [];
    public $map = [];
    public $portals = [];
    public $startingLocation;

    const PASSAGE = '.';
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

    public function solve() {
        $locationsToSpreadFrom = [$this->startingLocation];
        $stepsTaken = 0;
        while (!empty($locationsToSpreadFrom)) {
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
                        if ($portal->name == 'ZZ') {
                            return $stepsTaken;
                        }
                        if (!empty($portal->otherEnd)) {
                            $locationsToSpreadFrom[] = $portal->otherEnd->passage;
                        }
                    }
                }
            }
            $stepsTaken++;
        }
    }

    public function solveWithMazeRunners() {
        /** @var MazeRunner[] $runners */
        $runners = [new MazeRunner($this->startingLocation)];
        $step = 1;
        do {
            // echo ($step++) . "\t" . count($runners) . PHP_EOL;
            $currentRunners = $runners;
            $runners = [];
            foreach ($currentRunners as $originalRunner) {
                $clonedRunner = clone $originalRunner;
                $hasMoved = false;
                foreach (Location::DIRECTIONS as $direction) {
                    $newLocation = new Location($clonedRunner->location->x, $clonedRunner->location->y, $direction);
                    if ($clonedRunner->hasExplored($newLocation)) {
                        continue;
                    }
                    if ($newLocation->getValueFromMap($this->map) === self::PASSAGE) {
                        $runnerToMove = $hasMoved ? clone $clonedRunner : $originalRunner;
                        $runners[] = $runnerToMove;
                        $runnerToMove->setLocation($newLocation);
                        $hasMoved = true;
                        continue;
                    }
                    $potentialPortal = $newLocation->getValueFromMap($this->map);
                    if (preg_match(DonutMaze::PORTAL, $potentialPortal)) {
                        $portal = Portal::getByPassage($clonedRunner->location, $this->portals);
                        if ($clonedRunner->level === 0 && $portal->name == 'ZZ') {
                            return $clonedRunner->stepsTaken;
                        }
                        if ($clonedRunner->level === 0 && $portal->isOuter) {
                            continue;
                        }
                        if ($clonedRunner->level !== 0 && in_array($portal->name, ['AA', 'ZZ'])) {
                            continue;
                        }
                        if ($clonedRunner->emergedFrom === $portal) {
                            continue;
                        }
                        $level = $clonedRunner->level + ($portal->isOuter ? -1 : 1);
                        $newLocation = $portal->otherEnd->passage;
                        $runnerToMove = $hasMoved ? clone $clonedRunner : $originalRunner;
                        $runners[] = $runnerToMove;
                        $runnerToMove->setLevel($level);
                        $runnerToMove->emergedFrom = $portal->otherEnd;
                        $runnerToMove->setLocation($newLocation);
                        $hasMoved = true;
                        continue;
                    }
                }
            }
        } while (count($runners));
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

    private function setExplored(Location $location) {
        $this->map[$location->y][$location->x] = self::EXPLORED;
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