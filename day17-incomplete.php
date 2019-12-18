<?php
require_once('IntcodeComputer.php');
require_once('Utils.php');

$input = file_get_contents(__DIR__ . '/input/day17');
$code = explode(',', $input);

$robot = new VacuumRobot($code);
echo 'Part 1: '. $robot->getCalibration() . PHP_EOL;

$robot->wake();
$robot->addInputs([
    ['A','B','C'],
    ['L','1'],
    ['L','1'],
    ['L','2']
]);
$robot->getCameraFeed();

class VacuumRobot {
    private $computer;
    private $scaffolding;

    const NEWLINE = 10;
    const COMMA = 44;
    const SCAFFOLD = 35;
    const SPACE = 46;
    const ROBOT = 94;

    const OUTPUT_MAP = [
        self::SCAFFOLD => '#',
        self::SPACE => '.',
        self::ROBOT => '^'
    ];

    public function __construct(array $code) {
        $this->computer = new IntcodeComputer($code);
    }

    public function getCalibration() {
        $this->getCameraFeed();
        return $this->getAlignmentParameterSum();
    }

    public function getCameraFeed() {
        $output = $this->computer->getOutput();
        $this->drawScaffolding($output);
    }

    public function drawScaffolding(array $input) {
        $row = 0;
        $this->scaffolding = [];
        foreach ($input as $element) {
            if ($element === self::NEWLINE) {
                $row++;
            } else {
                $this->scaffolding[$row][] = $element;
            }
        }
        Utils::drawBoard($this->scaffolding, self::OUTPUT_MAP);
    }

    public function addInputs(array $inputs, $showVideo = true) {
        foreach ($inputs as $input) {
            $input = array_map(function($char) {
                return ord($char);
            }, $input);
            foreach ($input as $char) {
                $this->computer->addInput($char);
                $this->computer->addInput(self::COMMA);
            }
            $this->computer->addInput(self::NEWLINE);
        }
        $this->computer->addInput( $showVideo ? ord('y') : ord('n'));
        $this->computer->addInput(self::NEWLINE);
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