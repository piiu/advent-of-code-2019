<?php

$input = file_get_contents(__DIR__ . '\input\day16');

$input1 = str_split($input);
$basePattern = [0, 1, 0, -1];
for ($phase=1; $phase<=100; $phase++) {
    $output = [];
    for ($outputPosition = 1; $outputPosition <= count($input1); $outputPosition++) {
        $sum = 0;
        $chunks = array_chunk(array_merge([0], $input1), $outputPosition);
        foreach ($chunks as $index => $chunk) {
            $modifier = $basePattern[$index % 4];
            if ($modifier !== 0) {
                $sum += array_sum($chunk) * $modifier;
            }
        }
        $chunks = null;
        $output[] = (int)substr($sum, -1);
    }
    $input1 = $output;
}
$solutionString = implode('', $output);
echo 'Part 1: '. substr($solutionString, 0, 8) . PHP_EOL;

$offset = (int)substr($input, 0, 7);
$input2 = str_split(substr(str_repeat($input, 10000), $offset));

$inputLength = count($input2);
for ($i = 0; $i < 100; $i++) {
    $output = [];
    for ($j = $inputLength; $j >= 0; $j--) {
        if ($j === $inputLength) {
            $value = 0;
        } else {
            $value = ($input2[$j] + $output[$j + 1]) % 10;
        }
        $output[$j] = $value;
    }
    $input2 = $output;
}
$solutionString = implode('', $input2);
echo 'Part 2: ' . strrev(substr($solutionString, -8)) . PHP_EOL;