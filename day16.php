<?php

$input = file_get_contents(__DIR__ . '\input\day16');

$input1 = str_split($input);
$output = doPhases($input1, 100);
$solutionString = implode('', $output); //42205986
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
            if ($outputPosition % 10000 == 0) {
                echo 'aha!';
            }
            $isFirst = true;
            $sum = 0;
            $patternPosition = 0;
            $workingArray = $input;
            $elementsLeft = count($input);
            while ($elementsLeft) {
                if ($isFirst) {
                    $chunkLength = $outputPosition - 1;
                    $isFirst = false;
                } else {
                    $chunkLength = $outputPosition;
                    if ($chunkLength > $elementsLeft) {
                        $chunkLength = $elementsLeft;
                    }
                }
                $modifier = $basePattern[$patternPosition % 4];
                $patternPosition++;

                $affected = array_splice($workingArray, 0, $chunkLength);
                if ($modifier !== 0) {
                    $sum += $modifier * array_sum($affected);
                }
                $elementsLeft = count($workingArray);
            }
            $output[] = (int)substr($sum, -1);
        }
        $input = $output;
    }
    return $output ?? null;
}