<?php

namespace Nucleus\View;

class DebugExtension extends \Twig_Extension
{


	public function getFunctions()
	{
		return [
			new \Twig_SimpleFunction('d', [$this, 'dump']),
			new \Twig_SimpleFunction('dd', [$this, 'dumpAndDie']),
			new \Twig_SimpleFunction('ddd', [$this, 'dBug'])
		];
	}

	/**
	 * Dump variable to screen
	 * @param $var
	 * @return string
	 */
	public function dump($var)
	{
		return d($var);
	}

	/**
	 * Dump variable and cease execution
	 * @param $var
	 * @return string
	 */
	public function dumpAndDie($var)
	{
		return dd($var);
	}

	/**
	 * Use dBug to output variables -- right now this only works with scalar variables
	 * @param $var
	 * @return string
	 */
	public function dBug($var)
	{
		return ddd($var);
	}
}
