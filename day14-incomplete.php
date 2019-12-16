<?php

$input = file_get_contents(__DIR__ . '/input/day14');
$rows = explode("\n", $input);

$reactions = [];
foreach ($rows as $reactionDefinition) {
    $reactions[] = new Reaction($reactionDefinition);
}

$requiredComponents = ['FUEL' => 1];
while (count($requiredComponents) > 1 || key($requiredComponents) !== 'ORE') {
    foreach ($requiredComponents as $name => $quantity) {
        if ($name === 'ORE') {
            continue;
        }
        unset($requiredComponents[$name]);
        $reaction = Reaction::getByOutput($reactions, $name);
        $multiplier = (int)ceil($quantity / reset($reaction->outputs));

        foreach ($reaction->inputs as $inputName => $inputQuantity) {
            if (isset($requiredComponents[$inputName])) {
                $requiredComponents[$inputName] += $inputQuantity * $multiplier;
            } else {
                $requiredComponents[$inputName] = $inputQuantity * $multiplier;
            }
        }
    }
}
echo 'Part 1: '. reset($requiredComponents) . PHP_EOL;


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
