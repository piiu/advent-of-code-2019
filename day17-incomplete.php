<?php
require_once('IntcodeComputer.php');
require_once('Utils.php');

$input = file_get_contents(__DIR__ . '/input/day17');
$code = explode(',', $input);

$robot = new VacuumRobot($code);
echo 'Part 1: '. $robot->getCalibration() . PHP_EOL;

$robot->wake();
$output = $robot->getDustCollected([
    'A,A,B,C,B,A,C,B,C,A',
    'L,6,R,12,L,6,L,8,L,8',
    'L,6,R,12,R,8,L,8',
    'L,4,L,4,L,6'
]);
echo 'Part 2: '. array_pop($output) . PHP_EOL;

class VacuumRobot {
    private $computer;
    private $scaffolding;

    const NEWLINE = 10;
    const SCAFFOLD = 35;

    public function __construct(array $code) {
        $this->computer = new IntcodeComputer($code);
    }

    public function getCalibration() {
        $output = $this->computer->getOutput();
        $row = 0;
        $this->scaffolding = [];
        foreach ($output as $element) {
            if ($element === self::NEWLINE) {
                $row++;
            } else {
                $this->scaffolding[$row][] = $element;
            }
        }
        return $this->getAlignmentParameterSum();
    }

    public function getDustCollected(array $inputs) {
        $this->computer->getOutput();
        foreach ($inputs as $inputString) {
            $chars = str_split($inputString);
            foreach ($chars as $char) {
                $this->computer->addInput(ord($char));
            }
            $this->computer->addInput(self::NEWLINE);
            $this->computer->getOutput();
        }
        $this->computer->addInput(ord('n'));
        $this->computer->addInput(self::NEWLINE);
        return $this->computer->getOutput();
    }

    private function getAlignmentParameterSum() {
        $sum = 0;
        foreach ($this->scaffolding as $y => $row) {
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
        if (!isset($this->scaffolding[$y][$x])) {
            return false;
        }
        return $this->scaffolding[$y][$x] === self::SCAFFOLD;
    }

    public function wake() {
        $this->computer->reset();
        $this->computer->setPosition(0, 2);
    }
}