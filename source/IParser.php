<?php

namespace ash;



interface IParser
{

	public function parse(string $expression) : ISolver;
}
