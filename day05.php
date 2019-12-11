<?php
require_once('IntcodeComputer.php');

$code = trim(file_get_contents(__DIR__ . '\input\day05'));
$code = explode(',', $code);

$computer = new IntcodeComputer($code);
$output = $computer->addInput(1)->getOutput();
echo 'Part 1: ' . array_pop($output) . PHP_EOL;

$output = $computer->reset()->addInput(5)->getFirstOutput();
echo 'Part 1: ' . $output . PHP_EOL;