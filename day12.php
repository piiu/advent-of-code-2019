<?php

$input = file_get_contents(__DIR__ . '\input\day12');

/** @var Moon[] $moons */
$moons = array_map(function(string $row) : Moon {
    return new Moon(new Coordinates($row));
}, explode(PHP_EOL, $input));

$step = $totalEnergy = 0;
$allHaveInterval = false;
while (!$allHaveInterval || $step < 1000) {
    $step ++;
    foreach ($moons as $moon) {
        $moon->setVelocity($moons);
    }
    $allHaveInterval = true;
    foreach ($moons as $moon) {
        $moon->move($step);
        if ($step === 1000) {
            $totalEnergy += $moon->getEnergy();
        }
        if (!$moon->interval) {
            $allHaveInterval = false;
        }
    }
}

echo 'Part 1: '. $totalEnergy . PHP_EOL;

$lcm = 1;
foreach ($moons as $moon) {
    $lcm = lcm($lcm, $moon->interval);
}

echo 'Part 2: '. $lcm . PHP_EOL;

class Moon {
    public $position;
    public $velocity;
    public $interval;

    private $initialPosition;
    private $positionInterval;

    public function __construct(Coordinates $position) {
        $this->position = $position;
        $this->velocity = new Coordinates();

        $this->initialPosition = clone($this->position);
        $this->positionInterval = new Coordinates();
    }

    public function setVelocity(array $moons) {
        foreach ($moons as $moon) {
            foreach (Coordinates::AXISES as $axis) {
                if ($moon->position->$axis === $this->position->$axis) {
                    continue;
                }
                $modifier = $moon->position->$axis > $this->position->$axis ? 1 : -1;
                $this->velocity->$axis += $modifier;
            }
        }
    }

    public function move(int $step) {
        $this->position->add($this->velocity);
        $this->updateIntervals($step);
    }


    public function getEnergy() : int {
        return $this->position->getEnergy() * $this->velocity->getEnergy();
    }

    private function updateIntervals(int $step) {
        $gotAllIntervals = true;
        foreach (Coordinates::AXISES as $axis) {
            if (empty($this->positionInterval->$axis)) {
                if ($this->velocity->$axis === 0 && $this->position->$axis === $this->initialPosition->$axis) {
                    $this->positionInterval->$axis = $step;
                } else {
                    $gotAllIntervals = false;
                }
            }
        }
        if (!$this->interval && $gotAllIntervals) {
            $this->interval = $this->positionInterval->getLCM();
        }
    }
}

class Coordinates {
    public $x = 0;
    public $y = 0;
    public $z = 0;

    const AXISES = ['x', 'y', 'z'];

    public function __construct(string $coordinatesString = null) {
        if ($coordinatesString) {
            $this->setCoordinatedFromString($coordinatesString);
        }
    }

    public function getLCM() : int {
        return lcm(lcm($this->x, $this->y), $this->z);
    }

    public function add(Coordinates $vector) {
        foreach (self::AXISES as $axis) {
            $this->$axis += $vector->$axis;
        }
    }

    public function getEnergy() : int {
        return abs($this->x) + abs($this->y) + abs($this->z);
    }

    private function setCoordinatedFromString(string $string) {
        preg_match_all('/[xyz]=([0-9\-]*)[,>]/', $string, $matches);
        foreach (self::AXISES as $index=>$axis) {
            $this->$axis = (int)$matches[1][$index];
        }
    }
}

function lcm(int $m, int $n) {
    if ($m == 0 || $n == 0) return 0;
    $r = ($m * $n) / gcd($m, $n);
    return abs($r);
}

function gcd(int $a, int $b) {
    while ($b != 0) {
        $t = $b;
        $b = $a % $b;
        $a = $t;
    }
    return $a;
}