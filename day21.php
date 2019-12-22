<?php
require_once('IntcodeComputer.php');

$input = file_get_contents(__DIR__ . '/input/day21');
$code = explode(',', $input);

$droid = new SpringDroid($code);
$part1 = $droid->runInstructions([
    'NOT C T', // !C
    'AND D T', // !C AND D
    'NOT T T', // !(!C AND D)
    'AND A T', // !(!C AND D) AND A
    'NOT T T', // (!C AND D) OR !A
    'NOT T T',
    'NOT T J',
]);
echo 'Part 1: '. $part1 . PHP_EOL;

//$droid = new SpringDroid($code);
//$part2 = $droid->runInstructions([
//
//]);
//echo 'Part 2: '. $part2 . PHP_EOL;


class SpringDroid {
    private $computer;

    public function __construct($input) {
        $this->computer = new IntcodeComputer($input);
    }

    public function runInstructions(array $instructions) {
        foreach ($instructions as $instruction) {
            $this->computer->addAsciiInput($instruction)->getOutput();
        }
        $output = $this->computer->addAsciiInput('WALK')->getOutput();
        return array_pop($output);
    }
}