<?php
require_once('IntcodeComputer.php');

$code = trim(file_get_contents(__DIR__ . '\input\day09'));
$code = explode(',', $code);

$computer = new IntcodeComputer($code);
$computer->addInput(1);

echo 'Part 1: '. $computer->runCode(true) . PHP_EOL;

$computer->reset();
$computer->addInput(2);
echo 'Part 2: '. $computer->runCode(true) . PHP_EOL;
