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

    public static function lcm($m, $n) {
        if ($m == 0 || $n == 0) return 0;
        $r = ($m * $n) / self::gcd($m, $n);
        return abs($r);
    }

    public static function gcd($a, $b) {
        while ($b != 0) {
            $t = $b;
            $b = $a % $b;
            $a = $t;
        }
        return $a;
    }
}