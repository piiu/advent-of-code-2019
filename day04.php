<?php

$input = file_get_contents(__DIR__ . '\input\day04');
list($min, $max) = explode('-', $input);

$count1 = $count2 = 0;
for ($i=$min; $i<=$max; $i++) {
    $pw = str_split($i);

    $orderExists = true;
    $pairExists = $singlePairExists = false;

    foreach ($pw as $index => $digit) {
        $previousDigit = $pw[$index-1] ?? null;
        $nextDigit = $pw[$index+1] ?? null;
        $beforePrevious = $pw[$index-2] ?? null;

        if ($previousDigit > $digit) {
            $orderExists = false;
            break;
        }

        if ($previousDigit === $digit) {
            $pairExists = true;

            if ($nextDigit !== $digit && $beforePrevious !== $digit) {
                $singlePairExists = true;
            }
        }
    }

    if ($orderExists && $pairExists) {
        $count1++;
        if ($singlePairExists) {
            $count2++;
        }
    }
}

echo 'Part 1: ' . $count1 . PHP_EOL;
echo 'Part 2: ' . $count2 . PHP_EOL;