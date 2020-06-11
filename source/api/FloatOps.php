<?php

declare(strict_types=1);
namespace ash\api;



class FloatOps
extends AOps
{

	public function addFloat(float $a, float $b) : float {
		return $a + $b;
	}

	public function mulFloat(float $a, float $b) : float {
		return $a * $b;
	}

	public function subFloat(float $a, float $b) : float {
		return $a - $b;
	}

	public function divFloat(float $a, float $b) : float {
		if ($b !== 0.0) return $a / $b;

		$sgn = (float) ($a <=> 0);

		if ($sgn !== 0.0) return INF * $sgn;
		else return NAN;
	}

	public function modFloat(float $a, float $b) : float {
		if ($b !== 0.0) return fmod($a, $b);
		else return NAN;
	}

	public function addInt(float $a, int $b) : float {
		return $a + (float) $b;
	}

	public function mulInt(float $a, int $b) : float {
		return $a * (float) $b;
	}

	public function subInt(float $a, int $b) : float {
		return $a - (float) $b;
	}

	public function divInt(float $a, int $b) : float {
		return $this->divFloat($a, (float) $b);
	}

	public function modInt(float $a, int $b) : float {
		return $this->modFloat($a, (float) $b);
	}

	public function lttFloat(float $a , float $b) : bool {
		return !is_nan($a) && !is_nan($b) && $a < $b;
	}

	public function lteFloat(float $a, float $b) : bool {
		return !is_nan($a) && !is_nan($b) && $a <= $b;
	}

	public function gttFloat(float $a, float $b) : bool {
		return !is_nan($a) && !is_nan($b) && $a > $b;
	}

	public function gteFloat(float $a, float $b) : bool {
		return !is_nan($a) && !is_nan($b) && $a >= $b;
	}

	public function teqFloat(float $a, float $b) : bool {
		return !is_nan($a) && !is_nan($b) && $a === $b;
	}

	public function tneFloat(float $a, float $b) : bool {
		return is_nan($a) || is_nan($b) || $a !== $b;
	}
}
