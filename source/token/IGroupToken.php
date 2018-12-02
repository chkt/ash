<?php

namespace ash\token;



interface IGroupToken
extends IToken
{

	public function getChild() : IToken;
}
