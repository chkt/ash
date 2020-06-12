<?php

namespace ash\token;



interface IBranchToken
extends IListToken
{

	const BRANCH_INDEX_SELF = -1;


	public function getBranchIndex($value) : int;
}
