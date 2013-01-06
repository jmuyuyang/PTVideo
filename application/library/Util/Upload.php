<?php
class Util_Upload {
	private static $_config = array();
	private $_hashType = 'md5';
	private $_error = '';
	private $_uploadFileInfo;
	private $_isUpload = false;
	private $_dateFormat = 'Ymd'; 

	// max size
	public $isImage = false;
	public $maxSize = -1; 
	// allow upload types
	public $allowTypes = array(); 
	// save in sub
	public $autoSub = false;
	public $subType = 'date';
	public $hashLevel = 1; // 
	public $savePath = ''; 
	// if replace the same name file
	public $uploadReplace = false; 
	// file named rule
	public $saveRule = '';
	
	// thumb config
	public $autoSize = 'false';
	public $thumbDir = null;
	public $thumbSize = array();
	public $thumbExt = 'png';
	public $thumbPrefix = 'thumb_';

	public static function config($config) {
		self::$_config = $config;
	} 

	public function setConfig($item, $val) {
		$this -> $item = $val;
	}

	public function setup(){
		foreach(self::$_config as $item => $val){
			$this->setConfig($item,$val);
		}
	} 

	public function save() {
		$file = $this -> _uploadFileInfo;
		$fileName = $file['savePath'].$file['saveName'].".".$file['extension'];
		if (!$this -> uploadReplace && is_file($fileName)) {
			$this -> _error = '文件已经存在！' . $fileName;
			return false;
		} 
		if (!move_uploaded_file($file['tmp_name'], $fileName)) {
			$this -> _error = '文件上传保存错误！';
			return false;
		} 
		$this -> _isUpload = true;
		$this->_uploadFileInfo['uploadFile'] = $file['saveName'].".".$file['extension'];
		unset($this -> _uploadFileInfo['tmp_name']);
		return true;
	} 

	/**
	 * Upload File
	 */
	public function upload($file) {
		if (!$file) return false;
		$this->setup();
		$savePath = $this -> savePath; 
		// check upload dir
		$ifDir = $this -> _checkDir($savePath);
		if (!$ifDir) return false; 
		// filter upload
		if (!empty($file['name'])) {
			$file['savePath'] = $savePath;
			$file['extension'] = $this -> _getExt($file['name']);
			$file['saveName'] = $this -> _getSaveName($file); 
			// check upload file
			if (!$this -> _check($file)) return false;
			$file['ifImg'] = $this -> isImage;
			if (!function_exists($this -> hashType)) {
				$fun = $this -> _hashType;
				$file['hash'] = $fun($file['savePath'] . $file['saveName']);
			} 
		}
		$this -> _uploadFileInfo = $file;
		return $this;
	}

	public function adapter($discribe,$size) {
		$this->thumbPrefix = $discribe;
		$this -> thumbSize = $size;
		return $this;
	} 

	public function saveThumb() {
		$path = $this->thumbDir?:$this -> savePath;
		$file = $this -> getUploadFileInfo();
		if ($file['ifImg']) {
			$fileName = $this->_isUpload ?$file['savePath'].$file['uploadFile']:$file['tmp_name'];
			$this->thumbExt = $this->thumbExt?:$file['extension'];
			$thumbFile = $file['saveName'].".".$this->thumbExt;
			$this->_uploadFileInfo['thumbName'] = $this->thumbPrefix."_".$thumbFile;
			$thumbName = $path.$this->_uploadFileInfo['thumbName'];
			$orginSize = getimagesize($fileName);
			$size = $this -> autoSize?$this -> _autoSize($orginSize,$this->thumbSize):$this->thumbSize;
			$this -> createImgThumb($fileName, $thumbName, $size,$file['extension']); 
		}
	} 

	/**
	 * 生成缩略图文件
	 */
	public function createImgThumb($img, $newFile, $size,$fileExt) {
		if (is_file($img)) {
			$cursize = getimagesize($img);
			$dst = imagecreatetruecolor ($size[0], $size[1]);
			$types = array('jpg' => array('imagecreatefromjpeg', 'imagejpeg'),
				'jpeg' => array('imagecreatefromjpeg', 'imagejpeg'),
				'png' => array('imagecreatefrompng', 'imagepng'),
				'gif' => array('imagecreatefromgif', 'imagegif')); 
			// header("content-type: image/jpeg");
			$func = $types[$fileExt][0];
			$src = $func($img);
			imagecopyresampled($dst, $src, 0, 0, 0, 0, $size[0], $size[1], $cursize[0], $cursize[1]);
			$func = $types[$this->thumbExt][1];
			$func($dst, $newFile);
			return true;
		} 
		return false;
	} 
	
	public function getUploadFileInfo() {
		return $this -> _uploadFileInfo;
	} 

	public function errors() {
		return $this -> _error;
	} 

	protected function error($errorNo) {
		switch ($errorNo) {
			case 1:
			case 2:
				$this -> _error = '上传文件超过限定大小';
				break;
			case 3:
				$this -> _error = '文件只有部分被上传';
				break;
			case 4:
				$this -> _error = '没有文件被上传';
				break;
			case 6:
				$this -> _error = '找不到临时文件夹';
				break;
			case 7:
				$this -> _error = '文件写入失败';
				break;
			default:
				$this -> _error = '未知上传错误！';
		} 
		return ;
	} 

	/**
	 * +----------------------------------------------------------
	 * 根据上传文件命名规则取得保存文件名
	 * 
	 * @access private 
	 * @param string $filename 数据
	 * +----------------------------------------------------------
	 * @return string +----------------------------------------------------------
	 */
	private function _getSaveName($filename) {
		$rule = $this -> saveRule?:'time';
		if (is_callable($rule)) {
			$saveName = $rule();
		} else $saveName = time();
		if ($this -> autoSub) {
			// 使用子目录保存文件
			$saveName = $this -> _getSubName($filename) . '/' . $saveName;
		} 
		return $saveName;
	} 

	/**
	 * +----------------------------------------------------------
	 * 获取子目录的名称
	 * +----------------------------------------------------------
	 * 
	 * @access private 
	 * @param array $file 上传的文件信息
	 * @return string +----------------------------------------------------------
	 */
	private function _getSubName($file) {
		$subType = $this -> subType;
		if (is_callable($subType)) {
			$dir = $subType();
		} else $dir = date($this -> _dateFormat, time());
		if (!is_dir($file['savePath'] . $dir)) {
			mkdir($file['savePath'] . $dir);
		} 
		return $dir;
	} 

	private function _getExt($filename) {
		$pathinfo = pathinfo($filename);
		return $pathinfo['extension'];
	} 

	private function _checkDir($dir) {
		if (!is_dir($dir)) {
			if (is_dir(base64_decode($dir))) {
				$savePath = base64_decode($dir);
			} else {
				if (!mkdir($dir)) {
					$this -> _error = '上传目录' . $savePath . '不存在';
					return false;
				} 
			} 
		} else {
			if (!is_writeable($dir)) {
				$this -> _error = '上传目录' . $savePath . '不可写';
				return false;
			} 
		} 
		return true;
	} 

	/**
	 * +----------------------------------------------------------
	 * 检查上传的文件
	 * +----------------------------------------------------------
	 * 
	 * @access private 
	 * @param array $file 文件信息
	 * +----------------------------------------------------------
	 * @return boolean +----------------------------------------------------------
	 */
	private function _check($file) {
		if ($file['error'] !== 0) {
			$this -> error($file['error']);
			return false;
		} 
		if (!$this -> _checkSize($file['size'])) {
			$this -> _error = '上传文件超过限定大小！';
			return false;
		} 
		if (!$this -> _checkExt($file['extension'], $file['tmp_name'])) {
			$this -> _error = '上传文件类型不允许';
			return false;
		} 
		if (!$this -> _checkUpload($file['tmp_name'])) {
			$this -> _error = '非法上传文件！';
			return false;
		} 
		return true;
	} 

	private function _checkExt($type, $tmpName) {
		$type = strtolower($type);
		$this -> isImage = false;
		if (!empty($this -> allowTypes)) {
			if (in_array($type, array('jpg', 'gif', 'bmp', 'png', 'jpeg'))) {
				$this -> isImage = true;
				if (false === getimagesize($tmpName)) return false;
			} 
			return in_array($type, $this -> allowTypes);
		} 
		return true;
	} 

	private function _checkSize($size) {
		return !($size > $this -> maxSize) || (-1 == $this -> maxSize);
	} 

	private function _checkUpload($filename) {
		return is_uploaded_file($filename);
	} 

	private function _autoSize($orginSize,$size) {
		$ratio = $orginSize[0] / $orginSize[1];
		if ($orginSize[0] > $size[0] || $orginSize[1] > $size[1]) {
			$size[1] = $size[0] / $ratio;
		} 
		return $size;
	} 
} 

?>