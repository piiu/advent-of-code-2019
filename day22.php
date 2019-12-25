<?php
require_once 'vendor/autoload.php';
require_once 'config.php';

$input = file_get_contents(__DIR__ . '/input/day22');
$instructions = array_map(function (string $instruction) : array {
    preg_match('/([\w\s]*) ([-\d]*)?/', $instruction, $matches);
    return [
        'operation' => $matches[1],
        'parameter' => (int)$matches[2]
    ];
}, explode("\n", $input));

$deck = new CardPosition(10007, 2019);

//$deck->getSimplifiedExpression($instructions);
// // (5322 - 14730401476308983246289100067650956319185174528000000000000 x) mod 10007 = (5322 - 388x) mod 10007
$resultWithFormula = gmp_mod(gmp_sub('5322', gmp_mul('2019', '388')), '10007');
echo 'Part 1 (formula): ' . $resultWithFormula . PHP_EOL;

$deck->shuffle($instructions);
echo 'Part 1 (shuffle): '. $deck->position . PHP_EOL;

$input = 2019;
$deck = new CardPosition(10007, 2019);
for ($i = 0; $i<10; $i++) {
    $input = gmp_mod(gmp_sub('5322', gmp_mul((string)$input, '388')), '10007');
    $deck->shuffle($instructions);
}

// $deck = new CardPosition(119315717514047, 2020);
//$deck->getSimplifiedExpression($instructions);

/*
 * Final expression : (70339139553642 - 9092859308131 x) mod 119315717514047
 * Now we just implement https://www.nayuki.io/page/fast-skipping-in-a-linear-congruential-generator
 */

$a = -9092859308131 + 119315717514047;
$b = 70339139553642;
$m = 119315717514047;
$n = 101741582076661;
$x = 2020;

$a1 = gmp_sub($a, 1);
$ma = gmp_mul($a1, $m);
$an = gmp_powm($a, $n, $m);
$y = gmp_mul(gmp_div(gmp_powm($a, $n, gmp_sub($ma, 1)), $a1), $b);
$z = gmp_mul(gmp_powm($a, $n, $m), $x);
$x = gmp_mod(gmp_add($y, $z), $m);

echo 'Part 2: '. $x . PHP_EOL;

class CardPosition {
    private $deckSize;
    public $position;
    
    const INCREMENT_SHUFFLE = 'deal with increment';
    const CUT = 'cut';
    const REVERSE = 'deal into new';

    public function __construct(float $deckSize, float $target) {
        $this->deckSize = $deckSize;
        $this->position = $target;
    }

    public function shuffle($instructions) {
        foreach ($instructions as $instruction) {
            switch ($instruction['operation']) {
                case self::INCREMENT_SHUFFLE:
                    $this->dealWithIncrement($instruction['parameter']);
                    break;
                case self::CUT:
                    $this->cut($instruction['parameter']);
                    break;
                case self::REVERSE:
                    $this->dealIntoNewStack();
            }
            while ($this->position < 0) {
                $this->position += $this->deckSize;
            }
        }
    }

    private function dealWithIncrement($parameter) {
        $this->position = ($this->position * $parameter) % $this->deckSize;
    }

    private function cut($parameter) {
        if ($parameter < 0) {
            $parameter = $this->deckSize + $parameter;
        }
        $this->position = ($this->position - $parameter) % $this->deckSize;
    }

    private function dealIntoNewStack() {
        $this->position = $this->deckSize - $this->position - 1;
    }

    public function getSimplifiedExpression(array $instructions) {
        $instructions = array_reverse($instructions);
        $deckSize = number_format($this->deckSize, 0, '', '');

        $waEngine = new WolframAlpha\Engine(WOLFRAM_ALPHA_APP_ID);

        foreach ($instructions as $index => $instruction) {
            $operation = $instruction['operation'];
            $parameter = $instruction['parameter'];
            $string = '';

            if ($operation === self::INCREMENT_SHUFFLE) {
                $string = "(x * $parameter) mod $deckSize";
            }

            if ($operation === self::CUT) {
                if ($parameter < 0) {
                    $parameter = number_format($this->deckSize + $parameter, 0, '', '');
                }
                $string =  "(x - $parameter) mod $deckSize";
            }

            if ($operation === self::REVERSE) {
                $string = "$deckSize-x-1";
            }

            if (empty($expression)) {
                $expression = $string;
            } else {
                $expression = str_replace('x', '('. $string . ')', $expression);
            }

            if ($index == 0) {
                continue;
            }

            echo 'Expression '. $index .': '. $expression . PHP_EOL;
            $result = $waEngine->process($expression, [], ['plaintext']);
            $alternateForms = $result->pods->find('AlternateForm')->subpods;
            $expression = $alternateForms[0]->plaintext;
            echo 'Simplified: ' . $expression . PHP_EOL;
        }

        echo 'Final expression:' . PHP_EOL . $expression. PHP_EOL;
        return $expression;
    }
}