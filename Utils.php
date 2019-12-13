<?php

class Utils {
    public static function permutations(array $array, array $allCombinations = []) : array {
        for ($i=0; count($allCombinations) < self::factorial(count($array)); $i++) {
            shuffle($array);
            $allCombinations[implode($array)] = $array;
        }
        return array_values($allCombinations);
    }

    public static function factorial(int $number) : int {
        return $number <= 1 ? 1 : $number * self::factorial($number - 1);
    }

    public static function lcmForArray(array $array) : int {
        $lcm = 1;
        foreach ($array as $value) {
            $lcm = self::lcm($lcm, $value);
        }
        return $lcm;
    }

    public static function lcm(int $m, int $n) : int {
        if ($m == 0 || $n == 0) return 0;
        $r = ($m * $n) / self::gcd($m, $n);
        return abs($r);
    }

    public static function gcd(int $a, int $b) : int {
        while ($b != 0) {
            $t = $b;
            $b = $a % $b;
            $a = $t;
        }
        return $a;
    }

    public static function drawBoard(array $board, array $elementDefinitions) {
        $minX = null;
        $maxX = null;
        foreach ($board as $row) {
            foreach (array_keys($row) as $index) {
                if ($minX === null || $minX > $index) {
                    $minX = $index;
                }
                if ($maxX === null || $maxX < $index) {
                    $maxX = $index;
                }
            }
        }

        $minY = min(array_keys($board));
        $maxY = max(array_keys($board));

        for ($y = $minY; $y <= $maxY; $y++) {
            for ($x = $minX; $x <= $maxX; $x++) {
                if (!isset($board[$y][$x])) {
                    echo ' ';
                    continue;
                }
                echo $elementDefinitions[$board[$y][$x]];
            }
            echo PHP_EOL;
        }
    }
}