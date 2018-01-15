<?php

namespace Nucleus\View;

class DebugExtension extends \Twig_Extension
{


	public function getFunctions()
	{
		return [
			new \Twig_SimpleFunction('d', [$this, 'dump']),
			new \Twig_SimpleFunction('dd', [$this, 'dumpAndDie'])
		];
	}

	public function dump($var)
	{
		return d($var);
	}

	public function dumpAndDie($var)
	{
		return dd($var);
	}
}
