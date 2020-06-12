<?php

namespace ash\token;



class Branch
implements IBranchToken
{

	private $_tokens;
	private $_condition;

	public function __construct(ITokenFactory $factory, array $config) {
		$this->_tokens = $config['tokens'];
		$this->_condition = $config['test'];
	}

	public function getType() : int {
		return IToken::TOKEN_BRANCH;
	}

	public function getChars() : string {
		$res = [ ':' . count($this->_tokens) ];

		foreach ($this->_tokens as $token) $res[] = $token->getChars();

		return implode(' ', $res);
	}

	public function numChildren() : int {
		return count($this->_tokens);
	}

	public function getBranchIndex($value) : int {
		return call_user_func($this->_condition, $value);
	}

	public function getChildAt(int $index) : IToken {
		$index = min(max($index, 0), count($this->_tokens) - 1);

		return $this->_tokens[$index];
	}

	public function getChildren() : array {
		return $this->_tokens;
	}

	public function getProjection() : array {
		$data = [];

		foreach ($this->_tokens as $token) $data[] = $token->getProjection();

		return [
			'type' => $this->getType(),
			'data' => $data
		];
	}
}
