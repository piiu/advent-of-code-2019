<?php

$input = file_get_contents(__DIR__ . '\input\day02');
$code = explode(',', $input);

echo 'Part 1: ' . runCode($code, 12, 2) . PHP_EOL;
echo 'Part 2: ' . getReplacementsForTarget($code, 19690720) . PHP_EOL;

function runCode(array $code, int $replacement1, int $replacement2) : int {
    $code[1] = $replacement1;
    $code[2] = $replacement2;
    $i = 0;
    while ($code[$i] != 99) {
        $action = $code[$i];
        $writeIndex = $code[$i+3];
        $param1 = $code[$code[$i+1]];
        $param2 = $code[$code[$i+2]];

        if ($action == 1) {
            $code[$writeIndex] = $param1 + $param2;
        }
        if ($action == 2) {
            $code[$writeIndex] = $param1 * $param2;
        }
        $i += 4;
    }
    return $code[0];
}

function getReplacementsForTarget(array $code, int $target) : int {
    for ($noun = 0; $noun < 100; $noun++) {
        for ($verb = 0; $verb < 100; $verb++) {
            if(runCode($code, $verb, $noun) == $target) {
                return 100 * $noun + $verb;
            }
        }
    }
}