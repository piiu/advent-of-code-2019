<?php
require_once('IntcodeComputer.php');

$input = file_get_contents(__DIR__ . '/input/day21');
$code = explode(',', $input);

$droid = new SpringDroid($code);
$part1 = $droid->runInstructions('WALK', [
    'NOT C T', // !C
    'AND D T', // !C AND D
    'NOT T T', // !(!C AND D)
    'AND A T', // !(!C AND D) AND A
    'NOT T J', // (!C AND D) OR !A
]);
echo 'Part 1: '. $part1 . PHP_EOL;

$droid = new SpringDroid($code);
$part2 = $droid->runInstructions('RUN', [
// T register
    'NOT F T', // !F
    'NOT T T', // F
    'OR I T', // F OR I
    'AND E T', // E AND (F OR I)
    'OR H T', // (E AND (F OR I)) OR H
    'AND D T', // D AND ((E AND (F OR I)) OR H)
// J register
    'NOT A J', // !A
    'NOT J J', // A
    'AND B J', // A AND B
    'AND C J', // A AND B AND C
    'NOT J J', // !(A AND B AND C)
// sum
    'AND T J',
]);
echo 'Part 2: '. $part2 . PHP_EOL;


class SpringDroid {
    private $computer;

    public function __construct($input) {
        $this->computer = new IntcodeComputer($input);
    }

    public function runInstructions(string $method, array $instructions) {
        foreach ($instructions as $instruction) {
            $this->computer->addAsciiInput($instruction)->getOutput();
        }
        $output = $this->computer->addAsciiInput($method)->getOutput();
        return array_pop($output);
    }
}