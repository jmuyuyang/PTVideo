<?php
/**
 * 
 * @yuyang 获取豆瓣信息
 */

class Util_DoubanInfo{
	private $_url = 'http://api.douban.com/v2/';
	private $_initInfo;
	public $info;
	private $_type = array('book', 'movie', 'music');

	function __construct($type,$imdb){
		$this->_url .= $this->_type[$type]."/imdb/".$imdb;
		$this->_initInfo = json_decode(@file_get_contents($this->_url));
	}

	function getInfo(){
		if(!$this->_initInfo) return false;
		$this->info['title'] = $this->_initInfo->title;
		$this->info['summary'] = $this->_initInfo->summary;
		$attrs = (array)$this->_initInfo->attrs;
		foreach($attrs as $attr => $val){
			$this->info[$attr] = $val;
		}
		$this->_getRate();
	}
	
	function getTitle(){
		return $this->_initInfo->title;	
	}

	function getFormatInfo(){
		$content = "";
		$content .= "\n[导演]:&nbsp";
		foreach($this -> info['director'] as $director) {
			$content .= $director . ",";
		} 
		$content .= "\n[演员]:&nbsp";
		foreach($this -> info['cast'] as $cast) {
			$content .= $cast . ",";
		} 
		$content .= "\n[类型]:&nbsp";
		foreach($this -> info['movie_type'] as $movie_type) {
			$content .= $movie_type . ",";
		} 
		$content .= "\n[国别]:&nbsp";
		foreach($this -> info['country'] as $country) {
			$content .= $country . ",";
		} 
		$content .= "\n[简介]:<blockquote>" . $this -> info['summary'] . "</blockquote>";
		$content .= "\n[豆瓣排名]:&nbsp" . $this -> info['rating'];
		$content .= "\n[豆瓣评分]:&nbsp" . $this -> info['avg'];
		return $content;
	}

	private function _getRate(){
		$this->info['rating'] = $this->_initInfo->rating->numRaters;
		$this->info['avg'] = $this->_initInfo->rating->average;
	}
}

?>