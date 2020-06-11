<?php

declare(strict_types=1);
namespace ash\api;



class IntOps
extends AOps
{

	public function addInt(int $a, int $b) : int {
		return $a + $b;
	}

	public function mulInt(int $a, int $b) : int {
		return $a * $b; }

	public function subInt(int $a, int $b) : int {
		return $a - $b;
	}

	public function divInt(int $a, int $b) : int {
		if ($b !== 0) return intdiv($a, $b);
		else throw new OperatorException(sprintf(
			'%s / 0',
			$a
		));
	}

	public function modInt(int $a, int $b) : int {
		if ($b !== 0) return $a % $b;
		else throw new OperatorException(sprintf(
			'%s %% 0',
			$a
		));
	}

	public function addFloat(int $a, float $b) : float {
		return (float) $a + $b;
	}

	public function mulFloat(int $a, float $b) : float {
		return (float) $a * $b;
	}

	public function subFloat(int $a, float $b) : float {
		return (float) $a - $b;
	}

	public function divFloat(int $a, float $b) : float {
		if ($b !== 0.0) return (float) $a / $b;

		$sgn = (float) ($a <=> 0);

		if ($sgn !== 0.0) return INF * $sgn;
		else return NAN;
	}

	public function modFloat(int $a, float $b) : float {
		if ($b !== 0.0) return fmod($a, $b);
		else return NAN;
	}

	public function lttInt(int $a, int $b) : bool {
		return $a < $b;
	}

	public function lteInt(int $a, int $b) : bool {
		return $a <= $b;
	}

	public function gttInt(int $a, int $b) : bool {
		return $a > $b;
	}

	public function gteInt(int $a, int $b) : bool {
		return $a >= $b;
	}

	public function teqInt(int $a, int $b) : bool {
		return $a === $b;
	}

	public function tneInt(int $a, int $b) : bool {
		return $a !== $b;
	}
}
