<?php

namespace ash;

use ash\token\IToken;
use ash\token\IOperationToken;
use ash\token\IListToken;



final class Solver
implements ISolver
{

	private $_context;
	private $_root;


	public function __construct(IToken $root) {
		$this->_context = null;
		$this->_root = $root;
	}


	private function _opAccess(array $source, $prop) {
		if (!array_key_exists($prop, $source)) throw new \ErrorException(sprintf(
			'EXPR inaccessible "%s"',
			$prop
		));

		return $source[$prop];
	}

	private function _opCall(callable $fn, array $args) {
		return $fn(...$args);
	}

	private function _opMul($a, $b) {
		return $a * $b;
	}

	private function _opDiv($a, $b) {
		return $a / $b;
	}

	private function _opMod($a, $b) {
		return $a % $b;
	}

	private function _opAdd($a, $b) {
		return $a + $b;
	}

	private function _opSub($a, $b) {
		return $a - $b;
	}


	private function _resolveNameOrOperation(IToken $token) {
		$type = $token->getType();

		switch ($type) {
			case IToken::TOKEN_NAME_LITERAL :
				return $token->getChars();

			case IToken::TOKEN_BINARY_OPERATION :
				return $this->_resolveBinaryOperation($token);

			default : throw new \ErrorException($type);
		}
	}

	private function _resolveVariableOrOperation(IToken $token) {
		$type = $token->getType();

		switch ($type) {
			case IToken::TOKEN_NAME_LITERAL : return $this->_opAccess($this->_context, $token->getChars());
			case IToken::TOKEN_BINARY_OPERATION : return $this->_resolveBinaryOperation($token);

			default : throw new \ErrorException($type);
		}
	}

	private function _resolveBinaryOperation(IOperationToken $token) {
		$op = $token->getOperator()->getChars();
		$left = $token->getOperandAt(IOperationToken::OPERAND_BINARY_BEFORE);
		$right = $token->getOperandAt(IOperationToken::OPERAND_BINARY_AFTER);

		$lval = $this->_resolveVariableOrOperation($left);

		switch ($op) {
			case '.' : return $this->_opAccess($lval, $this->_resolveNameOrOperation($right));
			case '[...]' : return $this->_opAccess($lval, $this->_resolveExpression($right));
			case 'call' : return $this->_opCall($lval, $this->_resolveExpressionList($right));
			case '*' : return $this->_opMul($lval, $this->_resolveVariableOrOperation($right));
			case '/' : return $this->_opDiv($lval, $this->_resolveVariableOrOperation($right));
			case '%' : return $this->_opMod($lval, $this->_resolveVariableOrOperation($right));
			case '+' : return $this->_opAdd($lval, $this->_resolveVariableOrOperation($right));
			case '-' : return $this->_opSub($lval, $this->_resolveVariableOrOperation($right));
			default : throw new \ErrorException($op);
		}
	}

	private function _resolveExpressionList(IListToken $token) : array {
		$res = [];

		foreach ($token->getChildren() as $item) $res[] = $this->_resolveExpression($item);

		return $res;
	}

	private function _resolveExpression(IToken $token) {
		$type = $token->getType();

		switch ($type) {
			case IToken::TOKEN_NAME_LITERAL : return $this->_opAccess($this->_context, $token->getChars());
			case IToken::TOKEN_BINARY_OPERATION : return $this->_resolveBinaryOperation($token);
			default : throw new \ErrorException($type);
		}
	}


	public function resolve(array $context) {
		$this->_context = $context;

		return $this->_resolveExpression($this->_root);
	}
}
