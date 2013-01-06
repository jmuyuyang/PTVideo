<?php
class Util_Mongo{
	public static function id($id = NULL){
		return new MongoId($id);
	}

	public static function date($date){
		return new MongoDate($date);
	}

	public static function timeStamp($sec = NULL,$inc = 0){
		if (empty($sec)) $sec = time();
        return new MongoTimestamp($sec, $inc);
	}

	public static function regex($regex){
		return new MongoRegex($regex);
	}
}