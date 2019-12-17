<?php
require_once('IntcodeComputer.php');
require_once('Utils.php');

$input = file_get_contents(__DIR__ . '/input/day17');
$code = explode(',', $input);
$computer = new IntcodeComputer($code);

$scaffold = new Scaffold($computer->getOutput());
//$scaffold->draw();

echo 'Part 1: '. $scaffold->getAlignmentParameterSum() . PHP_EOL;


class Scaffold {
    public $map = [];

    const SCAFFOLD = 35;
    const SPACE = 46;
    const NEWLINE = 10;

    const OUTPUT_MAP = [
        self::SCAFFOLD => '#',
        self::SPACE => '.',
    ];

    public function __construct($input) {
        $row = 0;
        foreach ($input as $element) {
            if ($element === self::NEWLINE) {
                $row++;
            } else {
                $this->map[$row][] = $element;
            }
        }
    }

    public function getAlignmentParameterSum() {
        $sum = 0;
        foreach ($this->map as $y => $row) {
            foreach ($row as $x => $element) {
                if ($element === self::SCAFFOLD
                    && $this->isScaffold($x - 1, $y)
                    && $this->isScaffold($x + 1, $y)
                    && $this->isScaffold($x, $y - 1)
                    && $this->isScaffold($x, $y + 1)) {
                        $sum += $x * $y;
                }
            }
        }
        return $sum;
    }

    private function isScaffold(int $x, int $y) : bool {
        if (!isset($this->map[$y][$x])) {
            return false;
        }
        return $this->map[$y][$x] === self::SCAFFOLD;
    }

    public function draw() {
        Utils::drawBoard($this->map, self::OUTPUT_MAP);
    }
}