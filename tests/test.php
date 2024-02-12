<?php

use SwHawk\BgaPhpUnit\BGATestCase;

function clienttranslate($text)
{
	return $text;
}

class test extends BGATestCase
{

	protected string $gameClassName = FiveRealms::class;

	public function __construct(?string $name = null, array $data = [], $dataName = '')
	{
		\Table::$projectDir = __DIR__ . "/../src/";
		parent::__construct($name, $data, $dataName);
	}

	public function test1()
	{

		$this->assertTrue(true);
	}
}
