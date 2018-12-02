<?php

namespace ash\token;



abstract class AGroupToken
implements IGroupToken, IStreamToken
{

	private $_factory;

	private $_target;
	private $_depth;


	public function __construct(ITokenFactory $factory) {
		$this->_factory = $factory;

		$this->_target = null;
		$this->_depth = 0;
	}


	final protected function _getTarget() : IToken {
		if (is_null($this->_target)) $this->_target = $this->_factory->produce($this->_getTargetName());

		return $this->_target;
	}


	public function getChars() : string {
		return
			$this->_getLeadingSymbol() .
			$this->_getTarget()->getChars() .
			$this->_getTrailingSymbol();
	}


	abstract protected function _getTargetName() : string;

	abstract protected function _getLeadingSymbol() : string;

	abstract protected function _getTrailingSymbol() : string;


	public function getChild() : IToken {
		return $this->_getTarget();
	}


	public function append(string $char) : bool {
		$depth =& $this->_depth;

		if ($depth < 0) return false;

		if ($char === $this->_getLeadingSymbol()) {
			$depth += 1;

			if ($depth === 1) return true;
		}
		else if ($char === $this->_getTrailingSymbol()) $depth -= 1;

		if ($depth > 0) return $this->_getTarget()->append($char);

		$depth = -1;

		return true;
	}


	public function getProjection() : array {
		return [
			'type' => $this->getType(),
			'data' => $this->_getTarget()->getProjection()
		];
	}
}
