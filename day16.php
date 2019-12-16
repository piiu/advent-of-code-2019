<?php

$input = file_get_contents(__DIR__ . '\input\day16');
$input = str_split($input);

$basePattern = [0, 1, 0, -1];

for ($phase=1; $phase<=100; $phase++) {
    $output = [];
    foreach (array_keys($input) as $outputPosition) {
        $pattern = [];
        foreach ($basePattern as $element) {
            for ($i=0; $i<=$outputPosition; $i++) {
                $pattern[] = $element;
            }
        }

        $sum = 0;
        foreach ($input as $index => $digit) {
            $positionInPattern = ($index + 1) % count($pattern);
            $sum += $digit * $pattern[$positionInPattern];
        }
        $output[] = (int)substr($sum, -1);
    }
    $input = $output;
}
$solutionString = implode('', $output);
echo 'Part 1: '. substr($solutionString, 0, 8) . PHP_EOL;