<?php
class SWSPlugin {
	public function __construct(){

	}
	public static function wp_above_version($ver) {
		global $wp_version;
		if (version_compare($wp_version, $ver, '>=')) {
			return true;
		}
		return false;
	}
}