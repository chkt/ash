<?php

declare(strict_types=1);
namespace ash\token;



abstract class AValueToken
implements IValueToken
{

	private $_value;


	public function __construct(ITokenFactory $factory, array $data) {
		if (count($data) < 1) throw new \ErrorException();

		$this->_value = $data[0];
	}


	public function getType() : int {
		return IToken::TOKEN_VALUE;
	}

	public function getChars() : string {
		return (string) $this->_value;
	}

	public function getValue() {
		return $this->_value;
	}


	public function getProjection() : array {
		return [
			'type' => $this->getType(),
			'data' => $this->getValue()
		];
	}
}
