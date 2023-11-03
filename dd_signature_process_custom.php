<?php

session_start();

		$nextMemberNo = $_SESSION['image_token'];
		//$domain = $_SESSION['domain'];

/**
* HTML5 Signature Pad Software
* Copyright (c) 2010 - 2013 Dream Designs (www.dreamdesignsweb.com)
* Under GPL (ww.dreamdesignsweb.com/license) licenses.
* All scripts & codes in this file was created by Dream Designs @ www.dreamdesignsweb.com
* ----------------------------------------------------
* Last Mod: Sep 11 2013 | version 1.4
*/
/**
* -------------------------------
* Customizable Settings
* -------------------------------
*/
$dd_savePath=str_replace('\\','/',dirname(__FILE__))."/images/custom_sigs";
$prependSig=$nextMemberNo;
/**
* -------------------------------
* End of customizable settings
* -------------------------------
*/
/**
* Attempt to create the needed save dir if not exist
*/
if(!is_dir($dd_savePath)) {
	@mkdir($dd_savePath,0777);
	@chmod($dd_savePath,0777);
}
//
$action=strip_tags(trim($_POST['action']));
$fileName=strip_tags(trim($_POST['file_name']));
$width=strip_tags(trim($_POST['width']));
$height=strip_tags(trim($_POST['height']));
$download=(isset($_POST['download']) ? strip_tags(trim($_POST['download'])) : strip_tags(trim($_GET['download'])));
/**
* @desc 
*/
class createSignature {
	private $img, $file, $dest;
	
	public function __construct($i,$d) {
		$this->file=$i;
		$this->dest=$d;
	}
	
	public function makePNG($w,$h) {
		$this->img=@imagecreatefrompng($this->file);
		//
		imagealphablending($this->img, true); 	// setting alpha blending on
		imagesavealpha($this->img, true); 		// save alphablending setting (important)
		//
		$canvas=self::makeCanvas('png',$w,$h);
		// Let's get our dims for original image
		$trueSize=getimagesize($this->file);
		//
		imagecopyresampled($canvas,$this->img,0,0,0,0,$w,$h,$trueSize[0],$trueSize[1]);
		//
		if(imagepng($canvas,$this->dest)) {
			@chmod($this->dest,0777);
			@imagedestroy($this->img);
			@imagedestroy($canvas);
			return true;
		}else{
			return false;
		}
	}
	
	private function makeCanvas($t,$w,$h) {
		switch($t) {
			case "png":
				$canvas=imagecreatetruecolor($w,$h);
				imagecolortransparent($canvas, imagecolorallocatealpha($canvas, 0, 0, 0, 127));
				imagealphablending($canvas, false);	// setting alpha blending on
				imagesavealpha($canvas, true);		// save alphablending setting (important)
				break;
			default:
		}
		//
		return $canvas;
	}
}
/**
* If download flag, then we're downloading a created signature image
*/
if($download!="") {
	//
	$downloadFile="{$dd_savePath}/{$download}";
	//
	$ctype="application/force-download";
	header("Pragma: public");
	header("Expires: 0");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header("Content-Type: $ctype");
	header("Content-Length: ".filesize($downloadFile));
	header("Content-Disposition: attachment; filename=\"".basename($downloadFile)."\"");
	header("Accept-Range: bytes");
	set_time_limit(0);
	readfile("{$downloadFile}");
	header("Connection: close");
	
/**
* Else we're building a new signature image
*/
}else{
	$data=$_POST['data'];
	$data=str_replace(' ','+',$data);	// convert any whitespace
	/**
	* Now let's decode it so it's recognized as an img
	*/
	$decodeData=@base64_decode($data);
	/**
	* Finally, save her
	*/
	$file="{$dd_savePath}/".($action=="sig" ? "{$prependSig}{$fileName}" : "{$prependTxt}{$fileName}");
	if(@file_put_contents($file, $decodeData)) {
		@chmod($file,0777);
		/**
		* If we've saved a signature, let's replicate it to proper size
		* since our signature object is bigger (in real dimensions) than what we want
		* See our css+html notes
		*/
		switch($action) {
			case "sig":
				$newFile=$dd_savePath.'/tmp_'.$fileName;
				$newSig=new createSignature($file,$newFile);
				$result=$newSig->makePNG($width,$height);
				if($result==true) {
					// Finally, we want to delete the original and replace it with the new resized one
					@unlink($file);
					$finalName=str_replace('tmp_',$prependSig,$newFile);
					@rename($newFile,$finalName);
					//
					$finalName=substr(strrchr($finalName,'/'),1);
					echo "success|{$finalName}";
				}else{
					echo "failed";
				}
				break;
			default:
				echo "success";
		}
		
	}else{
		echo "{$file}";
	}
}
?>
