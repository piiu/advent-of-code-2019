<?php
require_once('IntcodeComputer.php');
require_once('Utils.php');

$input = file_get_contents(__DIR__ . '/input/day13');
$code = explode(',', $input);

$arcade = new Arcade($code);
echo 'Part 1: '. $arcade->getNumberOfBlocks() . PHP_EOL;

$arcade = new Arcade($code, 2);
while ($arcade->getNumberOfBlocks()) {
    $arcade->draw();
    if ($arcade->paddleX < $arcade->ballX) {
        $arcade->updateState(1);
    } elseif ($arcade->paddleX > $arcade->ballX) {
        $arcade->updateState(-1);
    } else {
        $arcade->updateState(0);
    }
}
$arcade->draw();
echo 'Part 2: '. $arcade->score . PHP_EOL;

class Arcade {
    private $computer;
    private $state;
    public $score;
    public $ballX;
    public $paddleX;

    const TILE_EMPTY = 0;
    const TILE_WALL = 1;
    const TILE_BLOCK = 2;
    const TILE_PADDLE = 3;
    const TILE_BALL = 4;

    const TILE_DRAW_MAPPING = [
        self::TILE_EMPTY => ' ',
        self::TILE_WALL => '#',
        self::TILE_BLOCK => 'X',
        self::TILE_PADDLE => '-',
        self::TILE_BALL => 'o',
    ];

    public function __construct(array $code, int $coins = null) {
        $this->computer = new IntcodeComputer($code);
        if ($coins) {
            $this->computer->setPosition(0, $coins);
        }
        $this->updateState(null);
    }

    public function updateState(int $input = null) {
        if ($input !== null) {
            $this->paddleX += $input;
            $this->computer->addInput($input);
        }
        $instructions = $this->computer->getOutput();
        $i = 0;
        while (isset($instructions[$i])) {
            $x = $instructions[$i];
            $y = $instructions[$i+1];
            $tile = $instructions[$i+2];

            if ($x == -1 && $y == 0) {
                $this->score = $tile;
            } else {
                $this->state[$y][$x] = $tile;
            }

            if ($tile === self::TILE_BALL) {
                $this->ballX = $x;
            }

            if ($tile === self::TILE_PADDLE) {
                $this->paddleX = $x;
            }

            $i += 3;
        }
    }

    public function getNumberOfBlocks() {
        $count = 0;
        foreach ($this->state as $row) {
            foreach ($row as $tile) {
                if ($tile === self::TILE_BLOCK) {
                    $count++;
                }
            }
        }
        return $count;
    }

    public function draw() {
        Utils::drawBoard($this->state, self::TILE_DRAW_MAPPING);
    }
}