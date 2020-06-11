<?php

namespace ash\token;



interface IBranchToken
extends IToken
{

	public function getTest() : IToken;

	public function getNumBranches() : int;

	public function getBranchAt(int $index) : IToken;
}
