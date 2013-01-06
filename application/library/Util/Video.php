<?php
class Util_Video{
	public static function movie_ffmpeg($movie, $imgname,$dir) {
		if (extension_loaded('ffmpeg')) {
		// 判断ffmpeg是否载入
			$mov = new ffmpeg_movie($movie); //视频的路径     
			$ff_frame = $mov -> getFrame(2);
			$gd_image = $ff_frame -> toGDImage();
			$img = $dir . $imgname . 'jpg'; //要生成图片的绝对路径     
			imagejpeg($gd_image, $img); //创建jpg图像     
			imagedestroy($gd_image); //销毁一图像 
		} else {
			return false;
		}	 
	} 

	public static function movie_mplayer($file,$dir,$frame) {
		if (is_file($dir . $file)) {
			$info = system("/home/vod/vod/mvpic " . $file . " " . $frame, $return);
			$movieinfo = explode('/', $info);
			return $movieinfo;
		} 
		return false;
	}

	public static function fileRename($newfile,$newname = NULL,$prefix = NULL){
		$ext_l = strrpos($newfile, ".");
		$ext = strtolower(substr($newfile, $ext_l + 1, strlen($newfile) - ($ext_l + 1)));
		$filename = str_replace("." . $ext, "", $newfile);
		if ($prefix) {
			if ($newname) $filename = $newname . $prefix . '.' . $ext;
			else $filename = $filename . $prefix . '.' . $ext;
		} 
		return $filename;
	}

	public static function movefile($orginFile,$base,$type,$ifCreate = 1){
		if($ifCreate) {
			$newFile = time();
			$dir = system(APPLICATION_PATH."/shell/moveVideo.sh ".$orginFile." ".$base." ".$newFile." ".$type,$output);
			return array($dir,$newFile);
		}else {
			system(APPLICATION_PATH."/shell/moveVideo.sh ".$orginFile." ".$base,$output);
			return;
		}
	}

	public static function fileInfo($fileDir) {
		if ($fileDir != '/../') {
			$dir = opendir($fileDir);
			$fid = 1;
			$fileinfo = array();
			while ($file = readdir($dir)) {
				if ($file != '..' && $file != '.') {
					$path = $fileDir . $file . '/';
					$path = iconv('gb2312', 'utf-8', $path);
					if (is_dir($fileDir . $file)) {
						$filename = iconv('gb2312', 'utf-8', $file);
						$fileinfo[$fid]['filename'] = $filename;
						$fileinfo[$fid]['filesize'] = 0; 
					} else {
						$filename = iconv('gb2312', 'utf-8', $file);
						$filesize = filesize($fileDir . $file) / 1000;
						$fileinfo[$fid]['filename'] = $filename;
	 					$fileinfo[$fid]['filesize'] = $filesize;
					} 
					$fid++;
				} 
			} 
		} 
		closedir($dir);
		return $fileinfo; 
	}

	public static function getVfInfo($file, $index) {
	// $index为读取文件多少行
		$fp = fopen($file, 'r');
		$i = 1;
		while (!feof($fp)) {
			if ($i == $index) {
				$fileinfo = trim(fgets($fp));
				break;
			} else fgets($fp);
			$i++;
		} 
		return $fileinfo;
	} 
}