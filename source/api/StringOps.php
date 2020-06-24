<?php

namespace ash\api;



final class StringOps
extends AOps
{

	public function teqString(string $a, string $b) : bool {
		return $a === $b;
	}

	public function tneString(string $a, string $b) : bool {
		return $a !== $b;
	}

	public function pinArray(string $key, array $a) : bool {
		return array_key_exists($key, $a);
	}
}
