<?php
require_once('Location.php');
require_once('Utils.php');

$input = file_get_contents(__DIR__ . '/input/day20');
$maze = new DonutMaze($input);

$locationsToSpreadFrom = [$maze->startingLocation];
$tick = 0;
while (!empty($locationsToSpreadFrom)) {
    //Utils::drawBoard($maze->map);
    foreach ($locationsToSpreadFrom as $key => $location) {
        unset($locationsToSpreadFrom[$key]);
        $maze->setExplored($location);
        foreach (Location::DIRECTIONS as $direction) {
            $newLocation = new Location($location->x, $location->y, $direction);

            if ($newLocation->getValueFromMap($maze->map) === $maze::PASSAGE) {
                $locationsToSpreadFrom[] = $newLocation;
                continue;
            }

            $potentialPortal = $newLocation->getValueFromMap($maze->map);
            if (preg_match(DonutMaze::PORTAL, $potentialPortal)) {
                $portal = Portal::getByPassage($location, $maze->portals);
                if ($portal->name === 'ZZ') {
                    echo 'Part 1: '. $tick . PHP_EOL;
                }
                if (!empty($portal->otherEnd)) {
                    $locationsToSpreadFrom[] = $portal->otherEnd->passage;
                }
            }
        }
    }
    $tick++;
}


class DonutMaze {
    public $map = [];
    public $portals = [];
    public $startingLocation;

    const PASSAGE = '.';
    const WALL = '#';
    const EXPLORED = '%';

    const PORTAL = '/^[A-Z]$/';

    public function __construct(string $input) {
        $rows = explode("\n", $input);
        foreach ($rows as $y => $row) {
            $chars = str_split(rtrim($row, "\r"));
            foreach ($chars as $x => $char) {
                $this->map[$y][$x] = $char;
            }
        }
        $this->listPortals();
    }

    public function setExplored(Location $location) {
        $this->map[$location->y][$location->x] = self::EXPLORED;
    }

    public function listPortals() {
        foreach ($this->map as $y => $row) {
            foreach ($row as $x => $char) {
                if (preg_match(self::PORTAL, $char)) {
                    $portal = Portal::createBySingleLetter($this->map, new Location($x, $y));
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
}

class Portal {
    public $name;
    public $passage;
    public $otherEnd;

    public function __construct(string $name, Location $passage) {
        $this->passage = $passage;
        $this->name = $name;
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

    public static function createBySingleLetter(array $mapState, Location $location) {
        $currentLetter = $location->getValueFromMap($mapState);
        foreach (Location::DIRECTIONS as $direction) {
            $secondLetterLocation = new Location($location->x, $location->y, $direction);
            $secondLetter = $secondLetterLocation->getValueFromMap($mapState);
            if ($secondLetter && preg_match(DonutMaze::PORTAL, $secondLetter)) {
                $passageLocation = new Location($secondLetterLocation->x, $secondLetterLocation->y, $direction);
                $passage = $passageLocation->getValueFromMap($mapState);
                if ($passage && $passage === DonutMaze::PASSAGE) {
                    return new Portal($currentLetter.$secondLetter, $passageLocation);
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