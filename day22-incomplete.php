<?php
require_once __DIR__.'/vendor/autoload.php';
require_once __DIR__.'config.php';

$input = file_get_contents(__DIR__ . '/input/day22');
$instructions = array_map(function (string $instruction) : array {
    preg_match('/([\w\s]*) ([-\d]*)?/', $instruction, $matches);
    return [
        'operation' => $matches[1],
        'parameter' => (int)$matches[2]
    ];
}, explode("\n", $input));

$deck = new CardPosition(10007, 2019);

//$deck->getSimplifiedExpression($instructions); // (5322 - 14730401476308983246289100067650956319185174528000000000000 x) mod 10007
$resultWithFormula = bcmod(bcsub(5322, bcmul(2019, 14730401476308983246289100067650956319185174528000000000000)), 10007);
echo 'Part 1: ' . $resultWithFormula . PHP_EOL;

$deck->shuffle($instructions);
echo 'Part 1: '. $deck->position . PHP_EOL;

$deck = new CardPosition(119315717514047, 2020);
//$deck->getSimplifiedExpression($instructions); // (70339139553642 - 14730401476308983246289100067650956319185174528000000000000 x) mod 119315717514047

$input = $deck->position;
for ($i = 0; $i < 101741582076661; $i++) {
    $output = bcmod(bcsub(70339139553642, bcmul($input, 14730401476308983246289100067650956319185174528000000000000)), 119315717514047);
    $input = $output;
}
echo 'Part 2: '. $output . PHP_EOL;

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