<?php

namespace ash;

use eve\common\IHost;
use ash\api\IOps;
use ash\token\IToken;
use ash\token\IOperationToken;
use ash\token\IListToken;



final class Solver
implements ISolver
{

	private $_context;
	private $_api;
	private $_root;


	public function __construct(IHost $api, IToken $root) {
		$this->_context = null;
		$this->_api = $api;
		$this->_root = $root;
	}


	private function _getType($value) : string {
		$type = gettype($value);

		switch ($type) {
			case 'NULL' : return 'null';
			case 'integer' : return 'int';
			case 'double' : return 'float';
			case 'string' : return class_exists($value) ? $value : $type;		//TODO: technically all strings representing global functions or static methods are callable...
			case 'array' : return count($value) === 2 && is_callable($value) ? 'fn' : $type;
			case 'object' : return is_callable($value) ? 'fn' : get_class($value);
			default : return $type;
		}
	}

	private function _getOps(string $ltype) : IOps {
		$api = $this->_api;
		$id = 'op-' . $ltype;

		if ($api->hasKey($id)) return $api->getItem($id);
		else throw new \ErrorException(sprintf(
			'EXPR no ops "%s"',
			$ltype
		));
	}


	private function _resolveName(IToken $token) {
		$type = $token->getType();

		switch ($type) {
			case IToken::TOKEN_NAME_LITERAL : return $token->getChars();

			default : throw new \ErrorException(sprintf(
				'EXPR malformed accessor "%s"',
				$token->getChars()
			));
		}
	}


	private function _resolveRightOperand(string $op, IToken $right) {
		switch ($op) {
			case 'acc' : return $this->_resolveName($right);
			case 'run' : return $this->_resolveExpressionList($right);
			default : return $this->_resolveExpression($right);
		}
	}

	private function _resolveBinaryOperation(IOperationToken $token) {
		$left = $token->getOperandAt(IOperationToken::OPERAND_BINARY_BEFORE);
		$lval = $this->_resolveExpression($left);

		$ops = $this->_getOps($this->_getType($lval));
		$op = $token->getOperator()->getChars();

		$right = $token->getOperandAt(IOperationToken::OPERAND_BINARY_AFTER);
		$rval = $this->_resolveRightOperand($op, $right);

		$methodName = $ops->getMethodName($op, $this->_getType($rval));

		if (method_exists($ops, $methodName)) return $ops->$methodName($lval, $rval);
		else throw new\ErrorException(sprintf(
			'EXPR no op "%s %s %s"',
			$op,
			$this->_getType($lval),
			$this->_getType($rval)
		));
	}

	private function _resolveExpressionList(IListToken $token) : array {
		$res = [];

		foreach ($token->getChildren() as $item) $res[] = $this->_resolveExpression($item);

		return $res;
	}

	private function _resolveExpression(IToken $token) {
		$type = $token->getType();

		switch ($type) {
			case IToken::TOKEN_NAME_LITERAL : return $this->_api
				->getItem('op-array')
				->accString($this->_context, $token->getChars());
			case IToken::TOKEN_VALUE : return $token->getValue();
			case IToken::TOKEN_BINARY_OPERATION : return $this->_resolveBinaryOperation($token);
			default : throw new \ErrorException($type);
		}
	}


	public function resolve(array $context) {
		$this->_context = $context;

		return $this->_resolveExpression($this->_root);
	}
}
