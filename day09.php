<?php
require_once('IntcodeComputer.php');

$input = file_get_contents(__DIR__ . '\input\day09');
$code = explode(',', $input);

$computer = new IntcodeComputer($code);

$output = $computer->addInput(1)->getFirstOutput();
echo 'Part 1: '. $output . PHP_EOL;

$output = $computer->reset()->addInput(2)->getFirstOutput();
echo 'Part 2: '. $output . PHP_EOL;
