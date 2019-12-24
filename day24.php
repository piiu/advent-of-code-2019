<?php
require_once('Location.php');
require_once('Utils.php');

$input = file_get_contents(__DIR__ . '/input/day24');
$eris = new Eris($input);

$biodiversityList = [];
while(true) {
    $eris->tick();
    Utils::drawBoard($eris->map);
    $biodiversity = $eris->getBiodiversity();
    echo $biodiversity. PHP_EOL . PHP_EOL;
    if (in_array($biodiversity, $biodiversityList)) {
        echo 'Part 1: ' . $biodiversity . PHP_EOL;
        break;
    }
    $biodiversityList[] = $biodiversity;
}

class Eris {
    public $map;

    const BUG = '#';
    const SPACE = '.';

    public function __construct(string $input) {
        $rows = explode("\n", $input);
        foreach ($rows as $y => $row) {
            $chars = str_split(rtrim($row, "\r"));
            foreach ($chars as $x => $char) {
                $this->map[$y][$x] = $char;
            }
        }
    }

    public function tick() {
        $newMap = [];
        foreach ($this->map as $y => $row) {
            foreach ($row as $x => $tile) {
                $neighbours = 0;
                foreach (Location::DIRECTIONS as $direction) {
                    $neighbourLocation = new Location($x, $y, $direction);
                    if ($neighbourLocation->getValueFromMap($this->map) === self::BUG) {
                        $neighbours++;
                    }
                }
                if ($tile === self::BUG) {
                    $newMap[$y][$x] = $neighbours === 1 ? self::BUG : self::SPACE;
                } else {
                    $newMap[$y][$x] = in_array($neighbours, [1,2]) ? self::BUG : self::SPACE;
                }
            }
        }
        $this->map = $newMap;
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
}