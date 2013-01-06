<?php
class Util_String{

	const UTF8 = 'utf';
	const GBK = 'gbk';

	const UUID_CLEAR_VER = 15;

	/**
	 * UUID constant that sets the version bit for generated UUIDs (`01000000`).
	 */
	const UUID_VERSION_4 = 64;

	/**
	 * Clears relevant bits of variant byte (`00111111`).
	 */
	const UUID_CLEAR_VAR = 63;

	/**
	 * The RFC 4122 variant (`10000000`).
	 */
	const UUID_VAR_RFC = 128;

	/**
	 * base64_encode
	 */
	const ENCODE_BASE_64 = 1;

	public static $_source;

	public static function substr($string, $start, $length, $charset = self::UTF8, $dot = false) {
		switch (strtolower($charset)) {
			case self::GBK:
				$string = self::substrForGbk($string, $start, $length, $dot);
				break;
			default:
				$string = self::substrForUtf8($string, $start, $length, $dot);
				break;
		}
		return $string;
	}

	public static function substrForUtf8($string,$start,$length = null,$dot = false){
		$strlen = strlen($string);
		$length = $length?$length:(int)$strlen;
		$substr = '';
		$chinese = $word = 0;
		for ($i = 0, $j = 0; $i < (int) $start; $i++) {
			if (0xa0 < ord(substr($string, $j, 1))) {
				$chinese++;
				$j += 2;
			} else {
				$word++;
			}
			$j++;
		}
		$start = $word + 3 * $chinese;
		for ($i = $start, $j = $start; $i < $start + $length; $i++) {
			if (0xa0 < ord(substr($string, $j, 1))) {
				$substr .= substr($string, $j, 3);
				$j += 2;
			} else {
				$substr .= substr($string, $j, 1);
			}
			$j++;
		}
		(strlen($substr) < $strlen) && $dot && $substr .= "...";
		return $substr;
	}

	public static function substrForGbk($string,$start,$length = null,$dot = false){
		if (empty($string) || !is_int($start) || ($length && !is_int($length))) {
			return '';
		}
		$strlen = strlen($string);
		$length = $length ? $length : $strlen;
		$substr = '';
		$chinese = $word = 0;
		for ($i = 0, $j = 0; $i < $start; $i++) {
			if (0xa0 < ord(substr($string, $j, 1))) {
				$chinese++;
				$j++;
			} else {
				$word++;
			}
			$j++;
		}
		$start = $word + 2 * $chinese;
		for ($i = $start, $j = $start; $i < $start + $length; $i++) {
			if (0xa0 < ord(substr($string, $j, 1))) {
				$substr .= substr($string, $j, 2);
				$j++;
			} else {
				$substr .= substr($string, $j, 1);
			}
			$j++;
		}
		(strlen($substr) < $strlen) && $dot && $substr .= "...";
		return $substr;
	}

	public static function strlen($string, $charset = self::UTF8) {
		$len = strlen($string);
		$i = $count = 0;
		$charset = strtolower(substr($charset, 0, 3));
		while ($i < $len) {
			if (ord($string[$i]) <= 129)
				$i++;
			else
				switch ($charset) {
					case 'utf':
						$i += 3;
						break;
					default:
						$i += 2;
						break;
				}
			$count++;
		}
		return $count;
	}

	public static function varToString($input, $indent = '') {
		switch (gettype($input)) {
			case 'string':
				return "'" . str_replace(array("\\", "'"), array("\\\\", "\\'"), $input) . "'";
			case 'array':
				$output = "";
				foreach ($input as $key => $value) {
					if($output!='') $output .= ",\r\n";
					$output .= $indent . "\t" . self::varToString($key, $indent . "\t") . ' => ' . self::varToString(
						$value, $indent . "\t");
				}
				$output = "array(\r\n".$output.$indent . ')';
				return $output;
			case 'boolean':
				return $input ? 'true' : 'false';
			case 'NULL':
				return 'NULL';
			case 'integer':
			case 'double':
			case 'float':
				return "'" . (string) $input . "'";
		}
		return 'NULL';
	}

	public static function random($bytes, array $options = array()) {
		$defaults = array(
			'encode' => null,
			'source' => null
		);
		$options += $defaults;
		$source = is_callable($options['source']) ? $options['source'] : static::_source();
		$result = $source($bytes);
		if ($options['encode'] != static::ENCODE_BASE_64) {
			return $result;
		}
		return strtr(rtrim(base64_encode($result), '='), '+', '.');
	}

	protected static function _source() {
		switch (true) {
			case isset(static::$_source):
				return static::$_source;
			case is_readable('/dev/urandom') && $fp = fopen('/dev/urandom', 'rb'):
				return static::$_source = function($bytes) use (&$fp) {
					return fread($fp, $bytes);
				};
			case class_exists('COM', false):
				try {
					$com = new COM('CAPICOM.Utilities.1');
					return static::$_source = function($bytes) use ($com) {
						return base64_decode($com->GetRandom($bytes, 0));
					};
				} catch (Exception $e) {
				}
			default:
				return static::$_source = function($bytes) {
					$rand = '';
					for ($i = 0; $i < $bytes; $i++) {
						$rand .= mt_rand(0, 255);
					}
					return $rand;
				};
		}
	}

	public static function hash($string, array $options = array()) {
		$defaults = array(
			'type' => 'sha512',
			'salt' => false,
			'key' => false,
			'raw' => false
		);
		$options += $defaults;

		if ($options['salt']) {
			$string = $options['salt'] . $string;
		}
		if ($options['key']) {
			return hash_hmac($options['type'], $string, $options['key'], $options['raw']);
		}
		return hash($options['type'], $string, $options['raw']);
	}

	public static function uuid() {
		$uuid = static::random(16);
		$uuid[6] = chr(ord($uuid[6]) & static::UUID_CLEAR_VER | static::UUID_VERSION_4);
		$uuid[8] = chr(ord($uuid[8]) & static::UUID_CLEAR_VAR | static::UUID_VAR_RFC);

		return join('-', array(
			bin2hex(substr($uuid, 0, 4)),
			bin2hex(substr($uuid, 4, 2)),
			bin2hex(substr($uuid, 6, 2)),
			bin2hex(substr($uuid, 8, 2)),
			bin2hex(substr($uuid, 10, 6))
		));
	}

	public function charset($fContents, $from, $to) {
		$from = strtoupper($from) == 'UTF8'? 'utf-8':$from;
		$to = strtoupper($to) == 'UTF8'? 'utf-8':$to;
		if (strtoupper($from) === strtoupper($to) || empty($fContents) || (is_scalar($fContents) && !is_string($fContents))) {
			return $fContents;
		} 
		if (is_string($fContents)) {
			if (function_exists('mb_convert_encoding')) {
				return mb_convert_encoding ($fContents, $to, $from);
			} elseif (function_exists('iconv')) {
				return iconv($from, $to, $fContents);
			} else {
				return $fContents;
			} 
		} elseif (is_array($fContents)) {
			foreach ($fContents as $key => $val) {
				$_key = auto_charset($key, $from, $to);
				$fContents[$_key] = auto_charset($val, $from, $to);
				if ($key != $_key)
					unset($fContents[$key]);
			} 
			return $fContents;
		} else {
			return $fContents;
		} 
	}
}



?>