<?php

class IntcodeComputer {
    private $defaultCode;
    private $code;
    private $relativeBase = 0;
    private $currentPointer = 0;
    private $inputs = [];
    private $isFinished = false;

    public function __construct(array $code) {
        $this->defaultCode = array_map(function ($a) {
            return (int)$a;
        }, $code);
        $this->code = $this->defaultCode;
    }

    public function addInput(int $input) : self {
        $this->inputs[] = $input;
        return $this;
    }

    public function getOutput() : array {
        $output = [];

        while ($this->code[$this->currentPointer] !== 99) {

            $action = $this->code[$this->currentPointer];
            $opcode = substr($action, -2);

            $param1 = $this->getIndex(1, $action);
            $param2 = $this->getIndex(2, $action);
            $param3 = $this->getIndex(3, $action);

            $param1Value = $this->code[$param1] ?? 0;
            $param2Value = $this->code[$param2] ?? 0;

            if ($opcode == 1) {
                $this->code[$param3] = $param1Value + $param2Value;
                $this->currentPointer += 4;
                continue;
            }
            if ($opcode == 2) {
                $this->code[$param3] = $param1Value * $param2Value;
                $this->currentPointer += 4;
                continue;
            }
            if ($opcode == 3) {
                if (empty($this->inputs)) {
                    return $output;
                }
                $this->code[$param1] = array_shift($this->inputs);
                $this->currentPointer += 2;
                continue;
            }
            if ($opcode == 4) {
                $this->currentPointer += 2;
                $output[] = $param1Value;
                continue;
            }
            if ($opcode == 5) {
                $this->currentPointer = $param1Value ? $param2Value : $this->currentPointer + 3;
                continue;
            }
            if ($opcode == 6) {
                $this->currentPointer = !$param1Value ? $param2Value : $this->currentPointer + 3;
                continue;
            }
            if ($opcode == 7) {
                $this->code[$param3] = $param1Value < $param2Value ? 1 : 0;
                $this->currentPointer += 4;
                continue;
            }
            if ($opcode == 8) {
                $this->code[$param3] = $param1Value == $param2Value ? 1 : 0;
                $this->currentPointer += 4;
                continue;
            }

            if ($opcode == 9) {
                $this->relativeBase += $param1Value;
                $this->currentPointer += 2;
                continue;
            }
        }

        $this->isFinished = true;
        return $output;
    }

    public function getFirstOutput() : int {
        $output = $this->getOutput();
        return $output[0];
    }

    public function reset() : self {
        $this->code = $this->defaultCode;
        $this->relativeBase = 0;
        $this->currentPointer = 0;
        $this->inputs = [];
        $this->isFinished = false;
        return $this;
    }

    public function isFinished(): bool {
        return $this->isFinished;
    }

    private function getIndex(int $paramNumber, int $action) : int {
        $paramMode = strlen($action) > $paramNumber + 1 ? (int)substr($action, -1 * ($paramNumber + 2), 1) : 0;
        if ($paramMode == 0) {
            return $this->code[$this->currentPointer + $paramNumber] ?? 0;
        }
        if ($paramMode == 1) {
            return $this->currentPointer + $paramNumber;
        }
        return $this->code[$this->currentPointer + $paramNumber] + $this->relativeBase;
    }
}
