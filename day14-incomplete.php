<?php

$input = file_get_contents(__DIR__ . '/input/day14');
$rows = explode("\n", $input);

$reactions = [];
foreach ($rows as $reactionDefinition) {
    $reactions[] = new Reaction($reactionDefinition);
}

$requiredComponents = ['FUEL' => 1];
getFromReactionOrLeftovers('FUEL', 1, $reactions, $oreCount);
echo 'Part 1: '. $oreCount . PHP_EOL;

function getFromReactionOrLeftovers(string $name, int $need, $reactions, &$oreCount = 0, &$myComponents = []) : void {
    if ($name === 'ORE') {
        $oreCount += $need;
        return;
    }

    $have = $myComponents[$name] ?? 0;
    unset($myComponents[$name]);

    if ($have >= $need) {
        $myComponents[$name] = $have - $need;
        return;
    }
    $need = $need - $have;

    $reaction = Reaction::getByOutput($reactions, $name);
    $output = reset($reaction->outputs);
    $multiplier = ceil($need / $output);
    $producedQuantity = $output * $multiplier;
    $myComponents[$name] = $producedQuantity - $need;

    foreach ($reaction->inputs as $inputName => $inputQuantity) {
        getFromReactionOrLeftovers($inputName, $inputQuantity * $multiplier, $reactions, $oreCount, $myComponents);
    }
}

class Reaction {
    public $inputs = [];
    public $outputs = [];

    public function __construct(string $reactionDefinition) {
        $components = explode('=>', $reactionDefinition);
        preg_match_all("/\d+ \w+/", $components[0], $input);
        preg_match_all("/\d+ \w+/", $components[1], $output);
        foreach ($input[0] as $inputString) {
            $inputStringComponents = explode(' ', $inputString);
            $this->inputs[$inputStringComponents[1]] = (int)$inputStringComponents[0];
        }
        foreach ($output[0] as $outputString) {
            $outputStringComponents = explode(' ', $outputString);
            $this->outputs[$outputStringComponents[1]] = (int)$outputStringComponents[0];
        }
    }

    public static function getByOutput(array $reactions, string $search) : Reaction {
        foreach ($reactions as $reaction) {
            foreach ($reaction->outputs as $name => $quantity) {
                if ($search === $name) {
                    return $reaction;
                }
            }
        }
    }
}
