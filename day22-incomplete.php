<?php

$input = file_get_contents(__DIR__ . '/input/day22');
$instructions = array_map(function (string $instruction) : array {
    preg_match('/([\w\s]*) ([-\d]*)?/', $instruction, $matches);
    return [
        'operation' => $matches[1],
        'parameter' => (int)$matches[2]
    ];
}, explode("\n", $input));

$deck = new CardPosition(10007, 2019);
$deck->shuffle($instructions);
echo 'Part 1: '. $deck->position . PHP_EOL;

$deck = new CardPosition(119315717514047, 2020);
for ($i=0;$i<101741582076661;$i++) {
    $deck->shuffle($instructions);
}
echo 'Part 2: '. $deck->position . PHP_EOL;


class CardPosition {
    private $deckSize;
    public $position;

    public function __construct(float $deckSize, float $target) {
        $this->deckSize = $deckSize;
        $this->position = $target;
    }

    public function shuffle($instructions) {
        foreach ($instructions as $instruction) {
            switch ($instruction['operation']) {
                case 'deal with increment':
                    $this->dealWithIncrement($instruction['parameter']);
                    break;
                case 'cut':
                    $this->cut($instruction['parameter']);
                    break;
                case 'deal into new':
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
        if ($parameter > $this->position) {
            $this->position += $this->deckSize - $parameter;
        } else {
            $this->position -= $parameter;
        }
    }

    private function dealIntoNewStack() {
        $this->position = $this->deckSize - $this->position - 1;
    }
}