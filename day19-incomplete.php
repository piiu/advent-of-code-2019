<?php
require_once('IntcodeComputer.php');
require_once('Utils.php');

$input = file_get_contents(__DIR__ . '/input/day19');
$code = explode(',', $input);

$drone = new Drone($code);
$drone->buildMap(50);
//$drone->draw();
echo 'Part 1: '. $drone->affectedPoints . PHP_EOL;


class Drone {
    private $computer;
    private $map = [];
    public $affectedPoints = 0;

    const STATIONARY = 0;
    const PULLED = 1;

    const DRAW_MAP = [
        self::STATIONARY => '.',
        self::PULLED => '#'
    ];

    public function __construct(array $code) {
        $this->computer = new IntcodeComputer($code);
    }

    public function buildMap(int $size) {
        for ($y=0; $y < $size; $y++) {
            for ($x = 0; $x < $size; $x++) {
                $this->markLocation($x, $y);
            }
        }
    }

    public function markLocation(int $x, int $y) {
        $this->computer->reset();
        $this->computer->addInput($x)->addInput($y);
        $point = $this->computer->getFirstOutput();
        $this->map[$y][$x] = $point;
        if ($point == self::PULLED) {
            $this->affectedPoints++;
        }
    }

    public function draw() {
        Utils::drawBoard($this->map, self::DRAW_MAP);
    }
}