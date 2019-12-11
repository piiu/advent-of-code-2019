<?php
require_once('IntcodeComputer.php');

$code = trim(file_get_contents(__DIR__ . '\input\day05'));
$code = explode(',', $code);

$computer = new IntcodeComputer($code);
$computer->addInput(1);
$output = $computer->runCode();
echo 'Part 1: ' . array_pop($output) . PHP_EOL;

$computer->reset();
$computer->addInput(5);
$output = $computer->runCode(true);
echo 'Part 1: ' . $output . PHP_EOL;