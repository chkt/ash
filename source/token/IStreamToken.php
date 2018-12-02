<?php

namespace ash\token;



interface IStreamToken
extends IToken
{

	public function append(string $char) : bool;
}
