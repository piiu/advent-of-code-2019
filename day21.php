<?php
require_once('IntcodeComputer.php');

$input = file_get_contents(__DIR__ . '/input/day21');
$code = explode(',', $input);

/*
 * If there is ground at the given distance, the register will be true; if there is a hole, the register will be false.
 * AND X Y sets Y to true if both X and Y are true; otherwise, it sets Y to false.
 * OR X Y sets Y to true if at least one of X or Y is true; otherwise, it sets Y to false.
 * NOT X Y sets Y to true if X is false; otherwise, it sets Y to false.
 */

$droid = new SpringDroid($code);
$part1 = $droid->runInstructions([
    'NOT A T',

    'OR T J'

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
        $output = $this->computer->addAsciiInput('WALK')->printAsciiOutput();
        return array_pop($output);
    }
}