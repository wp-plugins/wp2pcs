<?php

// 获取目录下的文件列表，注意，参数$path末尾最好不要带/
function get_files_in_dir($path){
	set_time_limit(0); // 延长执行时间，防止读取失败
	ini_set('memory_limit','200M'); // 扩大内存限制，防止读取文件溢出
	if(!file_exists($path) || !is_dir($path)){
		return null;
	}
	$dir = opendir($path);
	global $file_list;// 这个地方貌似有漏洞，因为之前没有声明过这个参数，这样做是否合理？
	// 经过验证，确实会遇到这个问题，即如果我两次使用get_files_in_dir函数，那么第一次中保存的$file_list将仍然存在，所以，在第一次使用完get_files_in_dir函数之后，一定要先把$file_list清空才可以。
	while($file = readdir($dir)){
		if($file == '.' || $file == '..')continue;
		$file_list[] = $path.'/'.$file;
		if(is_dir($path.'/'.$file)){
			get_files_in_dir($path.'/'.$file);
		}
	};
	closedir($dir);
	return $file_list;
}
// 为了上面这个函数准备的参数清空。
function get_files_in_dir_reset(){
	global $file_list;
	$file_list = array();
}

// 打包某一个目录，打包的包括它的子目录
function zip_files_in_dir($zip_dir_path,$zip_file_path){
	// 适用于所有路径，和下面的zip_files_in_dirs不同
	set_time_limit(0); // 延长执行时间，防止读取失败
	//ini_set('max_execution_time', 1000);
	ini_set('memory_limit','200M'); // 扩大内存限制，防止读取文件溢出
	if(!file_exists($zip_dir_path) || !is_dir($zip_dir_path)){
		return null;
	}
	if(file_exists($zip_file_path)){
		unlink($zip_file_path);
	}
	$zip = new ZipArchive();
	if($zip->open($zip_file_path,ZIPARCHIVE::CREATE)!==TRUE){
		return false;
	}
	// 获取这个目录下的所有文件
	$files = get_files_in_dir($zip_dir_path);
	//print_r($files);
	if(!empty($files))foreach($files as $file){
		$file_rename = str_replace($zip_dir_path,'',$file);
		if(is_dir($file)){
			$zip->addEmptyDir($file_rename);
		}elseif(is_file($file)){
			$zip->addFile($file,$file_rename);
		}
	}
	$zip->close();//关闭
	return $zip_file_path;
}

// 打包指定目录列表中的文件
function zip_files_in_dirs($zip_local_paths = array(),$zip_file_path){
	// 只适用于ABSPATH开头的路径
	if(empty($zip_local_paths)){
		return null;
	}
	set_time_limit(0);
	ini_set('memory_limit','200M');
	date_default_timezone_set("PRC");
	if(file_exists($zip_file_path)){
		unlink($zip_file_path);
	}
	$zip = new ZipArchive();
	if($zip->open($zip_file_path,ZIPARCHIVE::CREATE)!==TRUE){
		return false;
	}
	foreach($zip_local_paths as $zip_local_path){
		$zip_local_path = trim($zip_local_path);
		$zip_local_path = str_replace('{year}',date('Y'),$zip_local_path);
		$zip_local_path = str_replace('{month}',date('m'),$zip_local_path);
		$zip_local_path = str_replace('{day}',date('d'),$zip_local_path);
		if(!file_exists($zip_local_path) || !is_dir($zip_local_path) || !is_file($zip_local_path)){
			continue;
		}
		$files = get_files_in_dir($zip_local_path);
		if(!empty($files))foreach($files as $file){
			$file_rename = str_replace(ABSPATH,'',$file);
			if(is_dir($file)){
				$zip->addEmptyDir($file_rename);
			}elseif(is_file($file)){
				$zip->addFile($file,$file_rename);
			}
		}
	}
	$zip->close();//关闭
	return $zip_file_path;
}