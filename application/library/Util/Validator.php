<?php
/*
* @yuyang
*/
class util_Validator{
	static private $_rules;
	static private $_options = array();

	public static function init() {
		$alnum = '[A-Fa-f0-9]';
		$class = get_called_class();

		static::$_rules = array(
			'alphaNumeric' => '/^[\p{Ll}\p{Lm}\p{Lo}\p{Lt}\p{Lu}\p{Nd}]+$/mu',
			'blank'        => '/[^\\s]/',
			'creditCard'   => array(
				'amex'     => '/^3[4|7]\\d{13}$/',
				'bankcard' => '/^56(10\\d\\d|022[1-5])\\d{10}$/',
				'diners'   => '/^(?:3(0[0-5]|[68]\\d)\\d{11})|(?:5[1-5]\\d{14})$/',
				'disc'     => '/^(?:6011|650\\d)\\d{12}$/',
				'electron' => '/^(?:417500|4917\\d{2}|4913\\d{2})\\d{10}$/',
				'enroute'  => '/^2(?:014|149)\\d{11}$/',
				'jcb'      => '/^(3\\d{4}|2100|1800)\\d{11}$/',
				'maestro'  => '/^(?:5020|6\\d{3})\\d{12}$/',
				'mc'       => '/^5[1-5]\\d{14}$/',
				'solo'     => '/^(6334[5-9][0-9]|6767[0-9]{2})\\d{10}(\\d{2,3})?$/',
				'switch'   => '/^(?:49(03(0[2-9]|3[5-9])|11(0[1-2]|7[4-9]|8[1-2])|36[0-9]{2})' .
				              '\\d{10}(\\d{2,3})?)|(?:564182\\d{10}(\\d{2,3})?)|(6(3(33[0-4]' .
				              '[0-9])|759[0-9]{2})\\d{10}(\\d{2,3})?)$/',
				'visa'     => '/^4\\d{12}(\\d{3})?$/',
				'voyager'  => '/^8699[0-9]{11}$/',
				'fast'     => '/^(?:4[0-9]{12}(?:[0-9]{3})?|5[1-5][0-9]{14}|6011[0-9]{12}|3' .
				              '(?:0[0-5]|[68][0-9])[0-9]{11}|3[47][0-9]{13})$/'
			),
			'ip' => function($value, array $options = array()) {
				$options += array('flags' => array());
				return (boolean) filter_var($value, FILTER_VALIDATE_IP, $options);
			},
			'money'        => array(
				'right'    => '/^(?!0,?\d)(?:\d{1,3}(?:([, .])\d{3})?(?:\1\d{3})*|(?:\d+))' .
				              '((?!\1)[,.]\d{2})?(?<!\x{00a2})\p{Sc}?$/u',
				'left'     => '/^(?!\x{00a2})\p{Sc}?(?!0,?\d)(?:\d{1,3}(?:([, .])\d{3})?' .
				              '(?:\1\d{3})*|(?:\d+))((?!\1)[,.]\d{2})?$/u'
			),
			'notEmpty'     => '/[^\s]+/m',
			'phone'        => '/^\+?[0-9\(\)\-]{10,20}$/',
			'postalCode'   => '/(^|\A\b)[A-Z0-9\s\-]{5,}($|\b\z)/i',
			'regex'        => '/^(?:([^[:alpha:]\\\\{<\[\(])(.+)(?:\1))|(?:{(.+)})|(?:<(.+)>)|' .
			                  '(?:\[(.+)\])|(?:\((.+)\))[gimsxu]*$/',
			'time'         => '%^((0?[1-9]|1[012])(:[0-5]\d){0,2}([AP]M|[ap]m))$|^([01]\d|2[0-3])' .
			                  '(:[0-5]\d){0,2}$%',
			'boolean'      => function($value) {
				$bool = is_bool($value);
				$filter = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
				return ($bool || $filter !== null || empty($value));
			},
			'decimal' => function($value, array $options = array()) {
				if (isset($options['precision'])) {
					$precision = strlen($value) - strrpos($value, '.') - 1;

					if ($precision !== (int) $options['precision']) {
						return false;
					}
				}
				return (filter_var($value, FILTER_VALIDATE_FLOAT, FILTER_NULL_ON_FAILURE) !== null);
			},
			'inList' => function($value, $options) {
				$options += array('list' => array());
				return in_array($value, $options['list']);
			},
			'lengthBetween' => function($value, $options) {
				$length = strlen($value);
				$options += array('min' => 1, 'max' => 255);
				return ($length >= $options['min'] && $length <= $options['max']);
			},
			'luhn' => function($value) {
				if (empty($value) || !is_string($value)) {
					return false;
				}
				$sum = 0;
				$length = strlen($value);

				for ($position = 1 - ($length % 2); $position < $length; $position += 2) {
					$sum += $value[$position];
				}
				for ($position = ($length % 2); $position < $length; $position += 2) {
					$number = $value[$position] * 2;
					$sum += ($number < 10) ? $number : $number - 9;
				}
				return ($sum % 10 == 0);
			},
			'numeric' => function($value) {
				return is_numeric($value);
			},
			'inRange' => function($value, $options) {
				$defaults = array('upper' => null, 'lower' => null);
				$options += $defaults;

				if (!is_numeric($value)) {
					return false;
				}
				switch (true) {
					case (!is_null($options['upper']) && !is_null($options['lower'])):
						return ($value > $options['lower'] && $value < $options['upper']);
					case (!is_null($options['upper'])):
						return ($value < $options['upper']);
					case (!is_null($options['lower'])):
						return ($value > $options['lower']);
				}
				return is_finite($value);
			},
			'uuid' => "/^{$alnum}{8}-{$alnum}{4}-{$alnum}{4}-{$alnum}{4}-{$alnum}{12}$/",
			'email' => function($value) {
				return filter_var($value, FILTER_VALIDATE_EMAIL);
			},
			'url' => function($value, array $options = array()) {
				$options += array('flags' => array());
				return (boolean) filter_var($value, FILTER_VALIDATE_URL, $options);
			}
		);
	}

	public static function __callStatic($method, $args = array()) {
		if (!isset($args[0])) {
			return false;
		}
		$args = array_filter($args);
		preg_match("/^is([A-Z])([A-Za-z0-9]+)$/", $method,$rule);
		$rules = array();
		$rules[0] = strtolower($rule[1]).$rule[2];
		$rules['message'] = $args[1]?:'Invalid Check';
		return static::check($args[0],$rules);
	}

	public static function add($name,$rule = null,$options = null){
		if(!is_array($name)){
			$name = array($name => $rule);
		}
		static::$_rules = array_merge(static::$_rules,$name);
		if (!empty($options)) {
			if(!static::$_options[$name]) static::$_options[$name] = array();
			$options = array_combine(array_keys($name),array_fill(0,count($name),$options));
			static::$_options = array_merge(static::$_options, $options);
		}
	}

	public static function check($value,$rules){
		$rules= is_string($rules)? array($rules,'message' => 'Invalid Check'):$rules;
		$rules = is_array(current($rules))? $rules : array($rules);
		foreach($rules as $item => $rule){
			$rule = $rule + array('message'=>'Invalid Check');
			$defaultOptions = static::$_options[$rule[0]]?:array();
			$options = $rule['options']?$rule['options'] + $defaultsOptions:$defaultOptions;
			if(!static::rule($rule,$value[$item],$options)) return $rule['message'];
		}
		return NULL;
	}

	public static function rule($rule,$value,$options){
		if(!isset(static::$_rules[$rule[0]])){
			throw new Exception('the $rule[0] is Invalid rule'); 
		}
		$ruleCheck = $rule[0];
		$ruleCheck = is_array($ruleCheck)?:array($ruleCheck);
		foreach($ruleCheck as $rule){
			if(static::_checkFormats($rule,$value,$options)){
				return true;
			}
		}
		return false;
	}

	public static function _checkFormats($rule,$value,$options){
		if(is_string($ruleCheck = static::$_rules[$rule])){
			return preg_match($ruleCheck,$value);
		}
		if(is_object($ruleCheck = static::$_rules[$rule])){
			return $ruleCheck($value,$options);
		}
	}

}





?>