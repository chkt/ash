<?php

namespace ash\api;



abstract class AOps
implements IOps
{

	public function getMethodName(string $op, string $btype) : string {
		return $op . ucfirst($btype);
	}
}
