<?php
require_once('Location.php');
require_once('Utils.php');

$input = file_get_contents(__DIR__ . '/input/day24');
$eris = new Eris($input);

$biodiversityList = [];
while(true) {
    $eris->tick();
    $biodiversity = $eris->getBiodiversity();
    if (in_array($biodiversity, $biodiversityList)) {
        echo 'Part 1: ' . $biodiversity . PHP_EOL;
        break;
    }
    $biodiversityList[] = $biodiversity;
}

$centerEris = new Eris($input);
$centerEris->map[2][2] = Eris::INNER_LEVEL;
$centerEris->preTickMap = $centerEris->map;
/** @var Eris[] $erisLevels */
$erisLevels = [
    0 => $centerEris
];

for ($i=0;$i<200;$i++) {
    $minLevel = min(array_keys($erisLevels));
    $newInnerLevel = $minLevel - 1;
    $newInner = new Eris(null, $newInnerLevel);
    $newInner->tick($i, $erisLevels);

    $maxLevel = max(array_keys($erisLevels));
    $newOuterLevel = $maxLevel + 1;
    $newOuter = new Eris(null, $newOuterLevel);
    $newOuter->tick($i, $erisLevels);

    foreach ($erisLevels as $number => $eris) {
        $eris->tick($i, $erisLevels);
    }

    if ($newInner->getBugCount()) {
        $erisLevels[$newInnerLevel] = $newInner;
    }

    if ($newOuter->getBugCount()) {
        $erisLevels[$newOuterLevel] = $newOuter;
    }

    ksort($erisLevels);
}

$bugCount = 0;
foreach ($erisLevels as $eris) {
    $bugCount += $eris->getBugCount();
}
echo 'Part 2: ' . $bugCount . PHP_EOL;

class Eris {
    public $map;
    public $preTickMap;
    public $level;
    public $tickNumber = 0;

    const BUG = '#';
    const SPACE = '.';
    const INNER_LEVEL = '?';

    public function __construct(string $input = null, $level = 0) {
        if ($input) {
            $rows = explode("\n", $input);
            foreach ($rows as $y => $row) {
                $chars = str_split(rtrim($row, "\r"));
                foreach ($chars as $x => $char) {
                    $this->map[$y][$x] = $char;
                }
            }
        } else {
            for ($y=0;$y<5;$y++) {
                for ($x=0;$x<5;$x++) {
                    $this->map[$y][$x] = self::SPACE;
                }
            }
            $this->map[2][2] = self::INNER_LEVEL;
        }
        $this->level = $level;
        $this->preTickMap = $this->map;
    }

    /**
     * @param int $tickNumber
     * @param Eris[] $otherTiles
     */
    public function tick($tickNumber = 0, $otherTiles = []) {
        $this->tickNumber = $tickNumber;
        $this->preTickMap = $this->map;

        if ($outerMap = $otherTiles[$this->level + 1] ?? null) {
            $outerMap = $outerMap->tickNumber === $this->tickNumber ? $outerMap->preTickMap : $outerMap->map;
        }
        if ($innerMap = $otherTiles[$this->level - 1] ?? null) {
            $innerMap = $innerMap->tickNumber === $this->tickNumber ? $innerMap->preTickMap : $innerMap->map;
        }
        foreach ($this->map as $y => $row) {
            foreach ($row as $x => $tile) {
                if ($tile === self::INNER_LEVEL) {
                    continue;
                }
                $neighbours = 0;
                foreach (Location::DIRECTIONS as $direction) {
                    $neighbourLocation = new Location($x, $y, $direction);
                    if ($neighbourLocation->getValueFromMap($this->preTickMap) === self::BUG) {
                        $neighbours++;
                    }
                    if (!empty($outerMap)) {
                        if (($neighbourLocation->y === -1 && $outerMap[1][2] === self::BUG)
                            || ($neighbourLocation->y === 5 && $outerMap[3][2] === self::BUG)) {
                            $neighbours++;
                        }
                        if (($neighbourLocation->x === -1 && $outerMap[2][1] === self::BUG)
                            || ($neighbourLocation->x === 5 && $outerMap[2][3] === self::BUG)) {
                            $neighbours++;
                        }
                    }

                    if (!empty($innerMap) && $neighbourLocation->x === 2 && $neighbourLocation->y === 2) {
                        if ($x === 2 && $y === 1) {
                            $neighbours+= self::getTopCount($innerMap);
                        }
                        if ($x === 2 && $y === 3) {
                            $neighbours+= self::getBottomCount($innerMap);
                        }
                        if ($x === 1 && $y === 2) {
                            $neighbours+= self::getLeftCount($innerMap);
                        }
                        if ($x === 3 && $y === 2) {
                            $neighbours+= self::getRightCount($innerMap);
                        }
                    }
                }
                if ($tile === self::BUG) {
                    $this->map[$y][$x] = $neighbours === 1 ? self::BUG : self::SPACE;
                }
                if ($tile === self::SPACE) {
                    $this->map[$y][$x] = in_array($neighbours, [1,2]) ? self::BUG : self::SPACE;
                }
            }
        }
    }

    public function getBiodiversity() {
        $tileNumber = 0;
        $total = 0;
        foreach ($this->map as $y => $row) {
            foreach ($row as $x => $tile) {
                if ($tile === self::BUG) {
                    $total += pow(2, $tileNumber);
                }
                $tileNumber++;
            }
        }
        return $total;
    }

    public static function getTopCount(array $map) {
        $count = 0;
        foreach ($map[0] as $tile) {
            if ($tile === self::BUG) {
                $count++;
            }
        }
        return $count;
    }

    public static function getBottomCount(array $map) {
        $count = 0;
        foreach ($map[4] as $tile) {
            if ($tile === self::BUG) {
                $count++;
            }
        }
        return $count;
    }

    public static function getLeftCount(array $map) {
        $count = 0;
        foreach ($map as $row) {
            if ($row[0] === self::BUG) {
                $count++;
            }
        }
        return $count;
    }

    public static function getRightCount(array $map) {
        $count = 0;
        foreach ($map as $row) {
            if ($row[4] === self::BUG) {
                $count++;
            }
        }
        return $count;
    }

    public function getBugCount() {
        $total = 0;
        foreach ($this->map as $y => $row) {
            foreach ($row as $x => $tile) {
                if ($tile === self::BUG) {
                    $total ++;
                }
            }
        }
        return $total;
    }
}