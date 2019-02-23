<?php

namespace ash\api;



interface IOps
{

	public function getMethodName(string $op, string $btype) : string;
}
