<?php

$input = file_get_contents(__DIR__ . '\input\day16');

$input1 = str_split($input);
$output = doPhases($input1, 100);
$solutionString = implode('', $output);
echo 'Part 1: '. substr($solutionString, 0, 8) . PHP_EOL;

$input2 = str_split(str_repeat($input, 10000));
$output = doPhases($input2, 100);
$solutionString = implode('', $output);
echo 'Part 2: '. substr($solutionString, 0, 8) . PHP_EOL;

function doPhases(array $input, int $phaseCount) {
    $basePattern = [0, 1, 0, -1];

    for ($phase=1; $phase<=$phaseCount; $phase++) {
        $output = [];
        for ($outputPosition = 1; $outputPosition <= count($input); $outputPosition++) {
            $sum = 0;
            $chunks = array_chunk(array_merge([0], $input), $outputPosition);
            foreach ($chunks as $index => $chunk) {
                $modifier = $basePattern[$index % 4];
                if ($modifier !== 0) {
                    $sum += array_sum($chunk) * $modifier;
                }
            }
            $chunks = null;
            $output[] = (int)substr($sum, -1);
        }
        $input = $output;
    }
    return $output;
}