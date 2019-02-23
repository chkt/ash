<?php

declare(strict_types=1);
namespace ash\api;



class FnOps
extends AOps
{

	public function runArray(callable $fn, array $args) {
		return $fn(...$args);
	}
}
