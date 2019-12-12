<?php

$input = file_get_contents(__DIR__ . '\input\day12');

/** @var Moon[] $moons */
$moons = array_map(function(string $row) : Moon {
    return new Moon(new Coordinates($row));
}, explode(PHP_EOL, $input));

$step = 0;
$intervals = [];
while (count($intervals) !== count(Coordinates::AXISES) || $step < 1000) {
    $step ++;

    array_walk($moons, function(Moon $moon) use ($moons) {
        $moon->setVelocity($moons);
    });
    array_walk($moons, function(Moon $moon) {
        $moon->move();
    });

    if ($step === 1000) {
        $totalEnergy = 0;
        foreach ($moons as $moon) {
            $totalEnergy += $moon->getEnergy();
        }
        echo 'Part 1: '. $totalEnergy . PHP_EOL;
    }

    foreach (Coordinates::AXISES as $axis) {
        if (isset($intervals[$axis])) {
            continue;
        }
        foreach ($moons as $moon) {
            if (!$moon->isInitial($axis)) {
                continue 2;
            }
        }
        $intervals[$axis] = $step;
    }
}
$lcm = lcm(lcm($intervals['x'], $intervals['y']), $intervals['z']);

echo 'Part 2: '. $lcm . PHP_EOL;

class Moon {
    public $position;
    public $velocity;

    private $initialPosition;

    public function __construct(Coordinates $position) {
        $this->position = $position;
        $this->initialPosition = clone($this->position);
        $this->velocity = new Coordinates();
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

    public function move() {
        $this->position->add($this->velocity);
    }

    public function getEnergy() : int {
        return $this->position->getEnergy() * $this->velocity->getEnergy();
    }

    public function isInitial($axis) : bool {
        return $this->velocity->$axis === 0 && $this->position->$axis === $this->initialPosition->$axis;
    }
}

class Coordinates {
    public $x = 0;
    public $y = 0;
    public $z = 0;

    const AXISES = ['x', 'y', 'z'];

    public function __construct(string $coordinatesString = null) {
        if ($coordinatesString) {
            $this->setCoordinatesFromString($coordinatesString);
        }
    }

    public function add(Coordinates $vector) {
        foreach (self::AXISES as $axis) {
            $this->$axis += $vector->$axis;
        }
    }

    public function getEnergy() : int {
        return abs($this->x) + abs($this->y) + abs($this->z);
    }

    private function setCoordinatesFromString(string $string) {
        preg_match_all('/[a-z]=([0-9\-]*)[,>]/', $string, $matches);
        foreach (self::AXISES as $index=>$axis) {
            $this->$axis = (int)$matches[1][$index];
        }
    }
}

function lcm($m, $n) {
    if ($m == 0 || $n == 0) return 0;
    $r = ($m * $n) / gcd($m, $n);
    return abs($r);
}

function gcd($a, $b) {
    while ($b != 0) {
        $t = $b;
        $b = $a % $b;
        $a = $t;
    }
    return $a;
}