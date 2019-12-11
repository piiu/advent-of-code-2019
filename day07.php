<?php
require_once('IntcodeComputer.php');

$code = trim(file_get_contents(__DIR__ . '\input\day07'));
$code = explode(',', $code);

$maxOutput = null;
$phaseModifierCombinations = permutations([0,1,2,3,4]);

foreach ($phaseModifierCombinations as $phaseModifiers) {
    $signal = 0;
    $amplifiers = getFiveAmplifiers($code, $phaseModifiers);
    foreach ($amplifiers as $amplifier) {
        $signal = $amplifier->addInput($signal)->getFirstOutput();
    }
    $maxOutput = !$maxOutput || $maxOutput < $signal ? $signal : $maxOutput;
}
echo 'Part 1: ' . $maxOutput . PHP_EOL;

$maxOutput = null;
$phaseModifierCombinations = permutations([5,6,7,8,9]);

foreach ($phaseModifierCombinations as $phaseModifiers) {
    $signal = $iteration = 0;
    $amplifiers = getFiveAmplifiers($code, $phaseModifiers);

    while (true) {
        $ampIndex = $iteration % 5;
        $amplifier = $amplifiers[$ampIndex];
        $signal = $amplifier->addInput($signal)->getFirstOutput();
        if ($ampIndex === 4 && $amplifier->isFinished()) {
            break;
        }
        $iteration++;
    }
    $maxOutput = !$maxOutput || $maxOutput < $signal ? $signal : $maxOutput;
}

echo 'Part 2: ' . $maxOutput . PHP_EOL;

function getFiveAmplifiers($code, $phaseModifiers) {
    $amplifiers = [];
    for ($i=0; $i<5; $i++) {
        $amplifier = new IntcodeComputer($code);
        $amplifier->addInput($phaseModifiers[$i]);
        $amplifiers[] = $amplifier;
    }
    return $amplifiers;
}

function permutations($array, $allCombinations = []) {
    for ($i=0; count($allCombinations) < factorial(count($array)); $i++) {
        shuffle($array);
        $allCombinations[implode($array)] = $array;
    }
    return array_values($allCombinations);
}

function factorial($number){
    return $number <= 1 ? 1 : $number * factorial($number - 1);
}