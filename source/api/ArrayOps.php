<?php

declare(strict_types=1);
namespace ash\api;



class ArrayOps
extends AOps
{

	public function getMethodName(string $op, string $btype) : string {
		if ($op === 'ace') $op = 'acc';

		return $op . ucfirst($btype);
	}


	public function accInt(array $source, int $index) {
		if (
			$index >= 0 &&
			$index < count($source)
		) return $source[array_keys($source)[ (string) $index ]];
		else throw new OperatorException(sprintf(
			'[ %s ][%s]',
			count(array_keys($source)),
			$index
		));
	}

	public function accString(array $source, string $key) {
		if (array_key_exists($key, $source)) return $source[$key];
		else throw new OperatorException(sprintf(
			'{ %s }[\'%s\']',
			implode(',', array_keys($source)),
			$key
		));
	}
}
