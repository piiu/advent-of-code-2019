<?php
require_once('IntcodeComputer.php');

$input = file_get_contents(__DIR__ . '/input/day25');
$code = explode(',', $input);
$computer = new IntcodeComputer($code);

while(true) {
    $computer->printAsciiOutput();
    $computer->addAsciiInput(readline());
}