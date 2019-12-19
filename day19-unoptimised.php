<?php
require_once('IntcodeComputer.php');
require_once('Utils.php');

$input = file_get_contents(__DIR__ . '/input/day19');
$code = explode(',', $input);

$drone = new Drone($code);
$drone->buildMap(50);
//$drone->draw();
echo 'Part 1: '. $drone->affectedPoints . PHP_EOL;

$drone = new Drone($code);
echo 'Part 2: '. $drone->buildUntilFits(100, 230, 730) . PHP_EOL;
//$drone->draw();


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
                $point = $this->markLocation($x, $y);
                if ($point == self::PULLED) {
                    $this->affectedPoints++;
                }
            }
        }
    }

    public function buildUntilFits(int $size, int $initialX, int $initialY) {
        $this->map = [];
        $y = $initialY;
        while (true) {
            $x = $initialX;
            $tractorBeamStarted = null;
            while (true) {
                $point = $this->markLocation($x, $y);
                if (!$tractorBeamStarted && $point === self::PULLED) {
                    $tractorBeamStarted = $x;
                }
                if ($tractorBeamStarted && $point !== self::PULLED) {
                    break;
                }
                $x++;
            }
            if (($y - $initialY) >= $size && $this->fits($size, $tractorBeamStarted, $y)) {
                return $tractorBeamStarted * 10000 + ($y - $size + 1);
            }

            $y++;
        }
    }

    private function fits(int $size, int $x, int $y) : bool {
        for ($offset=0; $offset < $size; $offset++) {
            if (!$this->isBeam($x+$size-1, $y - $offset)) {
                return false;
            }
            if (!$this->isBeam($x, $y - $offset)) {
                return false;
            }
        }
        return true;
    }

    private function isBeam(int $x, int $y) : bool {
        if (!isset($this->map[$y][$x])) {
            return false;
        }
        return $this->map[$y][$x] === self::PULLED;
    }

    public function markLocation(int $x, int $y) : int {
        $this->computer->reset();
        $this->computer->addInput($x)->addInput($y);
        $point = $this->computer->getFirstOutput();
        $this->map[$y][$x] = $point;
        return $point;
    }

    public function draw() {
        Utils::drawBoard($this->map, self::DRAW_MAP);
    }
}