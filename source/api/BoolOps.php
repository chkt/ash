<?php

declare(strict_types=1);
namespace ash\api;



class BoolOps
extends AOps
{

	public function boolInt(int $a) : bool {
		return $a !== 0;
	}

	public function boolFloat(float $a) : bool {
		return $a !== 0.0 && !is_nan($a);
	}

	public function boolString(string $a) : bool {
		return $a !== '';
	}

	public function teqBool(bool $a, bool $b) : bool {
		return $a === $b;
	}

	public function tneBool(bool $a, bool $b) : bool {
		return $a !== $b;
	}
}
