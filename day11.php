<?php
require_once('IntcodeComputer.php');

const DIRECTIONS = ['<', '^', '>', 'v'];
const WHITE = 1;
const BLACK = 0;

$input = file_get_contents(__DIR__ . '\input\day11');
$code = explode(',', $input);

$board = [];
$robot = new Robot($code, $board);
$robot->paintBoard();
echo 'Part 1: '. $robot->paintedPanels . PHP_EOL;

$board[0][0] = WHITE;
$robot = new Robot($code, $board);
$board = $robot->paintBoard();
echo 'Part 2: '. PHP_EOL;

$minX = null;
$maxX = null;
foreach ($board as $row) {
    foreach (array_keys($row) as $index) {
        if (!$minX || $minX > $index) {
            $minX = $index;
        }
        if (!$maxX || $maxX < $index) {
            $maxX = $index;
        }
    }
}

$minY = min(array_keys($board));
$maxY = max(array_keys($board));

//for ($y = $minY; $y <= $maxY; $y++) {
for ($y = $maxY; $y >= $minY; $y--) {
    for ($x = $minX; $x <= $maxX; $x++) {
        $color = $board[$y][$x] ?? BLACK;
        echo $color == WHITE ? 'X' : ' ';
    }
    echo PHP_EOL;
}

class Robot {
    private $computer;
    private $board;
    private $x = 0;
    private $y = 0;
    private $currentDirection = 1;
    public $paintedPanels = 0;

    public function __construct(array $code, array $board) {
        $this->computer = new IntcodeComputer($code);
        $this->board = $board;
    }

    public function paintBoard() : array {
        while(true) {
            $currentPanelColor = $this->board[$this->y][$this->x] ?? BLACK;
            $instructions = $this->computer->addInput($currentPanelColor)->getOutput();
            if (empty($instructions)) {
                break;
            }
            $this->paint($instructions[0]);
            $direction = $this->getDirection($instructions[1]);
           $this->step($direction);
        }
        return $this->board;
    }

    private function getDirection(int $directionModifier) : string {
        $direction = $this->currentDirection + ($directionModifier == 1 ? 1 : -1);
        $direction = $direction < 0 ? $direction + 4 : $direction % 4;
        $this->currentDirection = $direction;

        return DIRECTIONS[$direction];
    }

    private function paint(int $color) {
        if (!isset($this->board[$this->y][$this->x])) {
            $this->paintedPanels++;
        }
        $this->board[$this->y][$this->x] = $color;
    }

    private function step(string $direction) {
        switch ($direction) {
            case '<':
                $this->x -= 1;
                break;
            case '^':
                $this->y += 1;
                break;
            case '>':
                $this->x += 1;
                break;
            case 'v':
                $this->y -= 1;
        }
    }
}