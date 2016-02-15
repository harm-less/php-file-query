<?php

namespace FQ\Utils;

use FQ\Dirs\Dir;

class Dirs {
	/**
	 * @param Dir[] $array1
	 * @param Dir[] $array2
	 * @return bool Returns true if the array contains equal dirs in the same order. Otherwise returns false.
	 */
	public static function equalDirs($array1, $array2) {
		if (count($array1) !== count($array2)) return false;
		foreach ($array1 as $index => $dir) {
			if ($array2[$index] !== $dir) {
				return false;
			}
		}
		return true;
	}
}