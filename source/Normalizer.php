<?php

namespace ash;

use ash\token\IToken;
use ash\token\ILiteralToken;
use ash\token\IGroupToken;
use ash\token\IListToken;
use ash\token\IOperatorToken;
use ash\token\IOperationToken;
use ash\token\ITokenFactory;



final class Normalizer
implements INormalizer
{

	private $_factory;


	public function __construct(ITokenFactory $factory) {
		$this->_factory = $factory;
	}


	private function _getOperatorCode(string $symbol) : string {
		return [
			'+' => 'add',
			'*' => 'mul',
			'-' => 'sub',
			'/' => 'div',
			'%' => 'mod',
			'.' => 'acc'
		][$symbol];
	}

	private function _getOperatorPrecedence(string $op) : int {
		return [
			'grp' => 4,
			'acc' => 3,
			'ace' => 3,
			'run' => 3,
			'mul' => 2,
			'div' => 2,
			'mod' => 2,
			'add' => 1,
			'sub' => 1
		][$op];
	}

	private function _getOperatorAssociativity(string $op) : int {
		return [
			'grp' => 0,
			'acc' => 1,
			'ace' => 1,
			'run' => 1,
			'mul' => 1,
			'div' => 1,
			'mod' => 1,
			'add' => 1,
			'sub' => 1
		][$op];
	}


	private function _produceOperator(string $chars, int $precedence = -1, int $associativity = -1) {
		if ($precedence === -1) $precedence = $this->_getOperatorPrecedence($chars);
		if ($associativity === -1) $associativity = $this->_getOperatorAssociativity($chars);

		return $this->_factory->produce('operator', [
			$chars,
			$precedence,
			$associativity
		]);
	}

	private function _composeBinaryOperation(IToken $op, IToken $left, IToken $right) : IToken {
		$leftPrec = 10;

		if ($left instanceof IOperationToken) $leftPrec = $left->getOperator()->getPrecedence();

		if (!($op instanceof IOperatorToken)) $op = $this->_produceOperator($this->_getOperatorCode($op->getChars()));

		$opPrec = $op->getPrecedence();

		if (
			$leftPrec > $opPrec ||
			$leftPrec === $opPrec && $op->getAssociativity() === 1
		) return $this->_factory->produce('binaryOperation', [ $op, $left, $right ]);

		return $this->_factory->produce('binaryOperation', [
			$left->getOperator(),
			$left->getOperandAt(IOperationToken::OPERAND_BINARY_BEFORE),
			$this->_composeBinaryOperation($op, $left->getOperandAt(IOperationToken::OPERAND_BINARY_AFTER), $right)
		]);
	}


	private function _composeValue(ILiteralToken $token) : IToken {
		$type = $token->getValueType();
		$chars = $token->getChars();

		switch ($type) {
			case ILiteralToken::TYPE_INT_DEC : return $this->_factory->produce('integerValue', [ (int) $chars ]);
			case ILiteralToken::TYPE_FLOAT : return $this->_factory->produce('floatValue', [ (float) $chars ]);
			case ILiteralToken::TYPE_INT_HEX : return $this->_factory->produce('integerValue', [ hexdec(substr($chars, 2)) ]);
			case ILiteralToken::TYPE_INT_BIN : return $this->_factory->produce('integerValue', [ bindec(substr($chars, 2)) ]);
			case ILiteralToken::TYPE_STRING : return $this->_factory->produce('stringValue', [ substr($chars, 1, strlen($chars) - 2) ]);
			default : throw new \ErrorException($type);
		}
	}


	private function _composeAccess(IToken $scope, IGroupToken $access) : IToken {
		$op = $this->_produceOperator('ace');
		$prop = $this->_resolveExpression($access->getChild());

		return $this->_composeBinaryOperation($op, $scope, $prop);
	}

	private function _composeCall(IToken $scope, IGroupToken $call) : IToken {
		$op = $this->_produceOperator('run');
		$args = $this->_resolveExpressionList($call->getChild());

		return $this->_composeBinaryOperation($op, $scope, $args);
	}


	private function _resolveExpressionOperand(IToken $token) : IToken {
		$type = $token->getType();

		switch ($type) {
			case IToken::TOKEN_NAME_LITERAL : return $token;
			case IToken::TOKEN_NUMBER_LITERAL :
			case IToken::TOKEN_STRING_LITERAL : return $this->_composeValue($token);
			case IToken::TOKEN_EXPRESSION_GROUP : return $this->_resolveExpressionGroup($token);
			default : throw new \ErrorException($type);
		}
	}

	private function _resolveExpression(IListToken $expr) : IToken {
		$tokens = $expr->getChildren();
		$len = count($tokens);

		if ($len === 0) throw new \ErrorException();

		$prev = $this->_resolveExpressionOperand($tokens[0]);

		for ($i = 1; $i < $len; $i += 1) {
			$token = $tokens[$i];
			$type = $token->getType();

			switch ($type) {
				case IToken::TOKEN_OPERATOR :
					$next = $this->_resolveExpressionOperand($tokens[++$i]);
					$prev = $this->_composeBinaryOperation($token, $prev, $next);
					break;

				case IToken::TOKEN_ACCESS_GROUP :
					$prev = $this->_composeAccess($prev, $token);
					break;

				case IToken::TOKEN_CALL_GROUP :
					$prev = $this->_composeCall($prev, $token);
					break;

				default : throw new \ErrorException($type);
			}
		}

		return $prev;
	}

	private function _resolveExpressionGroup(IGroupToken $group) : IToken {
		$token = $this->_resolveExpression($group->getChild());

		if ($token instanceof IOperationToken) {
			$op = $token->getOperator();

			$token = $this->_factory->produce('binaryOperation', [
				$this->_produceOperator(
					$op->getChars(),
					$this->_getOperatorPrecedence('grp'),
					$this->_getOperatorAssociativity('grp')
				),
				$token->getOperandAt(IOperationToken::OPERAND_BINARY_BEFORE),
				$token->getOperandAt(IOperationToken::OPERAND_BINARY_AFTER)
			]);
		}

		return $token;
	}

	private function _resolveExpressionList(IListToken $list) : IToken {
		$children = [];

		foreach ($list->getChildren() as $child) $children[] = $this->_resolveExpression($child);

		return $this->_factory->produce('expressionList', $children);
	}


	public function transform(IToken $token) : IToken {
		$type = $token->getType();

		switch($type) {
			case IToken::TOKEN_EXPRESSION_GROUP :
			case IToken::TOKEN_ACCESS_GROUP :
				$token = $token->getChild();
			case IToken::TOKEN_EXPRESSION :
				return $this->_resolveExpression($token);


			case IToken::TOKEN_CALL_GROUP :
				$token = $token->getChild();
			case IToken::TOKEN_EXPRESSION_LIST :
				return $this->_resolveExpressionList($token);

			case IToken::TOKEN_NAME_LITERAL :
			case IToken::TOKEN_OPERATOR :
				return $token;

			case IToken::TOKEN_NUMBER_LITERAL :
			case IToken::TOKEN_STRING_LITERAL :
				return $this->_composeValue($token);

			default : throw new \ErrorException(sprintf(
				'EXPR invalid target "%s"',
				$token->getChars()
			));
		}
	}
}
