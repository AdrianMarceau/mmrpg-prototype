<?php

	/*

		Single File PHP Gallery 4.1.1 (SFPG)

		See EULA in readme.txt for commercial use

		See readme.txt for configuration
		Released: 20-August-2011
		http://sye.dk/sfpg/
		by Kenny Svalgaard

	*/

	error_reporting(0);
	
	//	----------- CONFIGURATION START ------------

	define("GALLERY_ROOT", "./");
	define("DATA_ROOT", "./_sfpg_data/");
	define("SECURITY_PHRASE", "What if I don't want to?");

	define("DIR_NAME_FILE", "_name.txt");
	define("DIR_IMAGE_FILE", "_image.jpg");
	define("DIR_DESC_FILE", "_desc.txt");
	define("DIR_SORT_REVERSE", FALSE);
	define("DIR_SORT_BY_TIME", FALSE);
	$dir_exclude = array("_sfpg_data", "_sfpg_icons");

	define("SHOW_IMAGE_EXT", FALSE);
	define("IMAGE_SORT_REVERSE", FALSE);
	define("IMAGE_SORT_BY_TIME", FALSE);
	define("ROTATE_IMAGES", TRUE);
	define("IMAGE_JPEG_QUALITY", 90);

	define("SHOW_FILES", TRUE);
	define("SHOW_FILE_EXT", TRUE);
	define("FILE_IN_NEW_WINDOW", TRUE);
	define("FILE_THUMB_EXT", ".jpg");
	define("FILE_SORT_REVERSE", FALSE);
	define("FILE_SORT_BY_TIME", FALSE);
	$file_exclude = array();
	$file_ext_exclude = array(".php", ".txt");
	$file_ext_thumbs = array();

	define("LINK_BACK", "");
	define("CHARSET", "iso-8859-1");
	define("DATE_FORMAT", "Y-m-d h:i:s");
	define("DESC_EXT", ".txt");
	define("SORT_DIVIDER", "--");
	define("SORT_NATURAL", TRUE);
	define("FONT_SIZE", 12);
	define("UNDERSCORE_AS_SPACE", TRUE);
	define("NL_TO_BR", FALSE);
	define("SHOW_EXIF_INFO", TRUE);
	
	define("THUMB_MAX_WIDTH", 160);
	define("THUMB_MAX_HEIGHT", 120);
	define("THUMB_ENLARGE", FALSE);
	define("THUMB_JPEG_QUALITY", 75);

	define("USE_PREVIEW", FALSE);
	define("PREVIEW_MAX_WIDTH", 600);
	define("PREVIEW_MAX_HEIGHT", 400);
	define("PREVIEW_ENLARGE", FALSE);
	define("PREVIEW_JPEG_QUALITY", 75);
	
	define("WATERMARK", "");

	define("INFO_BOX_WIDTH", 250);
	define("MENU_BOX_HEIGHT", 70);
	define("NAV_BAR_HEIGHT", 25);

	define("THUMB_BORDER_WIDTH", 1);
	define("THUMB_MARGIN", 10);
	define("THUMB_BOX_MARGIN", 7);
	define("THUMB_BOX_EXTRA_HEIGHT", 14);
	define("THUMB_CHARS_MAX", 20);

	define("FULLIMG_BORDER_WIDTH", 5);
	define("NAVI_CHARS_MAX", 100);

	define("OVERLAY_OPACITY", 90);
	define("FADE_FRAME_PER_SEC", 30);
	define("FADE_DURATION_MS", 300);
	define("LOAD_FADE_GRACE", 500);

	define("TEXT_GALLERY_NAME", "Mega Man RPG Images");
	define("TEXT_HOME", "Home");
	define("TEXT_CLOSE_IMG_VIEW", "Close Image");
	define("TEXT_ACTUAL_SIZE", "Actual Size");
	define("TEXT_FULLRES", "Full resolution");
	define("TEXT_PREVIOUS", "<< Previous");
	define("TEXT_NEXT", "Next >>");
	define("TEXT_INFO", "Information");
	define("TEXT_DOWNLOAD", "Download full-size image");
	define("TEXT_NO_IMAGES", "No Images in gallery");
	define("TEXT_DATE", "Date");
	define("TEXT_FILESIZE", "File size");
	define("TEXT_IMAGESIZE", "Full Image");
	define("TEXT_DISPLAYED_IMAGE", "Displayed Image");
	define("TEXT_DIR_NAME", "Gallery Name");
	define("TEXT_IMAGE_NAME", "Image Name");
	define("TEXT_FILE_NAME", "File Name");
	define("TEXT_DIRS", "Sub galleries");
	define("TEXT_IMAGES", "Images");
	define("TEXT_IMAGE_NUMBER", "Image number");
	define("TEXT_FILES", "Files");
	define("TEXT_DESCRIPTION", "Description");
	define("TEXT_DIRECT_LINK_GALLERY", "Direct link to Gallery");
	define("TEXT_DIRECT_LINK_IMAGE", "Direct link to Image");
	define("TEXT_NO_PREVIEW_FILE", "No Preview for file");
	define("TEXT_IMAGE_LOADING", "Image Loading ");
	define("TEXT_LINKS", "Links");
	define("TEXT_NOT_SCALED", "Not Scaled");
	define("TEXT_LINK_BACK", "Back to my site");
	define("TEXT_THIS_IS_FULL", "Full");
	define("TEXT_THIS_IS_PREVIEW", "Preview");
	define("TEXT_SCALED_TO", "Scaled to");
	define("TEXT_YES", "Yes");
	define("TEXT_NO", "No");
	define("TEXT_EXIF_DATE", "EXIF Date");
	define("TEXT_EXIF_CAMERA", "Camera");
	define("TEXT_EXIF_ISO", "ISO");
	define("TEXT_EXIF_SHUTTER", "Shutter Speed");
	define("TEXT_EXIF_APERTURE", "Aperture");
	define("TEXT_EXIF_FOCAL", "Focal Length");
	define("TEXT_EXIF_FLASH", "Flash fired");
	define("TEXT_EXIF_MISSING", "No EXIF informatin in image");

	$color_body_back = "#000000";
	$color_body_text = "#aaaaaa";
	$color_body_link = "#b0b0b0";
	$color_body_hover = "#ffffff";

	$color_thumb_border = "#606060";
	$color_fullimg_border = "#ffffff";

	$color_dir_box_border = "#505050";
	$color_dir_box_back = "#000000";
	$color_dir_box_text = "#aaaaaa";
	$color_dir_hover = "#ffffff";
	$color_dir_hover_text = "#000000";

	$color_img_box_border = "#505050";
	$color_img_box_back = "#202020";
	$color_img_box_text = "#aaaaaa";
	$color_img_hover = "#ffffff";
	$color_img_hover_text = "#000000";

	$color_file_box_border = "#404040";
	$color_file_box_back = "#101010";
	$color_file_box_text = "#aaaaaa";
	$color_file_hover = "#ffffff";
	$color_file_hover_text = "#000000";

	$color_button_border = "#808080";
	$color_button_back = "#000000";
	$color_button_text = "#aaaaaa";
	$color_button_border_off = "#505050";
	$color_button_back_off = "#000000";
	$color_button_text_off = "#505050";
	$color_button_hover = "#ffffff";
	$color_button_hover_text = "#000000";
	$color_button_on = "#aaaaaa";
	$color_button_text_on = "#000000";

	$color_overlay = "#000000";
	$color_menu_hover = "#ffffff";

	//	----------- CONFIGURATION END ------------


	function sfpg_array_sort(&$arr, &$arr_time, $sort_by_time, $sort_reverse)
	{
		if ($sort_by_time)
		{
			if ($sort_reverse)
			{
				array_multisort ($arr_time, SORT_DESC, SORT_NUMERIC, $arr);
			}
			else
			{
				array_multisort ($arr_time, SORT_ASC, SORT_NUMERIC, $arr);
			}
		}
		else
		{
			if (SORT_NATURAL)
			{
				natcasesort ($arr);
				if ($sort_reverse)
				{
					array_reverse ($arr);
				}
			}
			else
			{
				if ($sort_reverse)
				{
					rsort ($arr);
				}
				else
				{
					sort ($arr);
				}
			}
		}
	}


	function sfpg_file_size($size)
	{
		$sizename = array("Bytes", "KB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB");
		return ($size ? round($size/pow(1024, ($i = floor(log($size, 1024)))), 2) . " " . $sizename[$i] : "0 Bytes");
	}


	function sfpg_base64url_encode($plain)
	{
		$base64 = base64_encode($plain);
		$base64url = strtr($base64, "+/", "-_");
		return rtrim($base64url, "=");
	}


	function sfpg_base64url_decode($base64url)
	{
		$base64 = strtr($base64url, "-_", "+/");
		$plain = base64_decode($base64);
		return ($plain);
	}


	function sfpg_url_string($dir = "", $img = "")
	{
		$res = $dir . "*" . $img . "*";
		return sfpg_base64url_encode($res . md5($res . SECURITY_PHRASE));
	}


	function str_to_script($str)
	{
		return str_replace("\r", "", str_replace("\n", "", str_replace("\"", "\\\"", str_replace("'", "\'", (NL_TO_BR ? nl2br($str) : $str)))));
	}


	function sfpg_display_name($name, $show_ext)
	{
		$break_pos = strpos($name, SORT_DIVIDER);
		if ($break_pos !== FALSE)
		{
			$display_name = substr($name, $break_pos + strlen(SORT_DIVIDER));
		}
		else
		{
			$display_name = $name;
		}
		if (UNDERSCORE_AS_SPACE)
		{
			$display_name = str_replace("_", " ", $display_name);
		}
		if (!$show_ext)
		{
			$display_name = substr($display_name, 0, strrpos($display_name, "."));
		}
		return $display_name;
	}


	function sfpg_ext($file)
	{
		return strtolower(substr($file, strrpos($file, ".")));
	}


	function sfpg_image_type($file)
	{
		$type = sfpg_ext($file);
		if (($type == ".jpg") or ($type == ".jpeg"))
		{
			return "jpeg";
		}
		elseif ($type == ".png")
		{
			return "png";
		}
		elseif ($type == ".gif")
		{
			return "gif";
		}
		return FALSE;
	}


	function sfpg_get_dir($dir)
	{
		global $dir_exclude, $file_exclude, $file_ext_exclude;
		$dirs = array();
		$dirs_time = array();
		$images = array();
		$images_time = array();
		$files = array();
		$files_time = array();
		$directory_handle = opendir(GALLERY_ROOT . $dir);
		if ($directory_handle != FALSE)
		{
			while($var = readdir($directory_handle))
			{
				if (is_dir(GALLERY_ROOT . $dir . $var))
				{
					if	(($var != ".") and ($var != "..") and !in_array(strtolower($var), $dir_exclude))
					{
						$dirs[] = $var;
						if (DIR_SORT_BY_TIME)
						{
							$dirs_time[] = filemtime(GALLERY_ROOT . $dir . $var . "/.");
						}
					}
				}
				elseif (sfpg_image_type($var))
				{
					if ($var != DIR_IMAGE_FILE)
					{
						$images[] = $var;
						if (IMAGE_SORT_BY_TIME)
						{
							$images_time[] = filemtime(GALLERY_ROOT . $dir . $var);
						}
					}
				}
				elseif (SHOW_FILES)
				{
					if (!in_array(strtolower($var), $file_exclude) and !((strrpos($var, ".") !== FALSE) and in_array(sfpg_ext($var), $file_ext_exclude)))
					{
						$files[] = $var;
						if (FILE_SORT_BY_TIME)
						{
							$files_time[] = filemtime(GALLERY_ROOT . $dir . $var);
						}
					}
				}
			}
			if (SHOW_FILES)
			{
				foreach ($files as $val)
				{
					$fti = array_search($val . FILE_THUMB_EXT, $images);
					if ($fti !== FALSE)
					{
						array_splice($images, $fti, 1);
						array_splice($images_time, $fti, 1);
					}
				}
			}
			sfpg_array_sort($dirs, $dirs_time, DIR_SORT_BY_TIME, DIR_SORT_REVERSE);
			sfpg_array_sort($images, $images_time, IMAGE_SORT_BY_TIME, IMAGE_SORT_REVERSE);
			sfpg_array_sort($files, $files_time, FILE_SORT_BY_TIME, FILE_SORT_REVERSE);
			return array($dirs, $images, $files);
		}
		else
		{
			header("Location: " . $_SERVER["PHP_SELF"]);
			exit;
		}
	}


	function sfpg_image($image_dir, $image_file, $func, $download=FALSE)
	{
		$image_path_file = DATA_ROOT . $func . "/" . $image_dir . $image_file;
		$image_type = sfpg_image_type($image_path_file);

		if ($func == "image")
		{
			if (!file_exists($image_path_file))
			{
				$image_path_file = GALLERY_ROOT . $image_dir . $image_file;
			}
			if ($download)
			{
				header("Content-Type: application/octet-stream");
				header("Content-Disposition: attachment; filename=\"" . $image_file . "\"");
			}
			else
			{
				header("Content-Type: image/" . $image_type);
				header("Content-Disposition: filename=\"" . $image_file . "\"");
			}
			readfile($image_path_file);
			exit;
		}

		if (($func == "thumb") or ($func == "preview"))
		{
			if (file_exists($image_path_file))
			{
				header("Content-Type: image/" . $image_type);
				header("Content-Disposition: filename=\"" . $func . "_" . $image_file . "\"");
				readfile($image_path_file);
				exit;
			}
			else
			{
				if($func == "thumb")
				{
					$max_width = THUMB_MAX_WIDTH;
					$max_height = THUMB_MAX_HEIGHT;
					$enlarge = THUMB_ENLARGE;
					$jpeg_quality = THUMB_JPEG_QUALITY;
					$source_img = GALLERY_ROOT . $image_dir . $image_file;
				}
				else
				{
					$max_width = PREVIEW_MAX_WIDTH;
					$max_height = PREVIEW_MAX_HEIGHT;
					$enlarge = PREVIEW_ENLARGE;
					$jpeg_quality = PREVIEW_JPEG_QUALITY;
					$source_img = DATA_ROOT . "image/" . $image_dir . $image_file;
					if (!file_exists($source_img))
					{
						$source_img = GALLERY_ROOT . $image_dir . $image_file;
					}
				}

				if (!$image = imagecreatefromstring(file_get_contents($source_img)))
				{
					exit;
				}

				if (($func == "thumb") and ($image_dir != "_sfpg_icons/"))
				{
					$image_changed = FALSE;
					if (!is_dir(DATA_ROOT . "info/" . $image_dir))
					{
						mkdir(DATA_ROOT . "info/" . $image_dir, 0777, TRUE);
					}
					$exif_info = "";
					if (function_exists("read_exif_data"))
					{
						if (SHOW_EXIF_INFO)
						{
							$exif_data = exif_read_data(GALLERY_ROOT . $image_dir . $image_file, "IFD0");
							if ($exif_data !== FALSE)
							{
								$exif_info .= TEXT_EXIF_DATE . ": " . $exif_data["DateTimeOriginal"] ."<br>";
								$exif_info .= TEXT_EXIF_CAMERA . ": " . $exif_data["Model"] ."<br>";
								$exif_info .= TEXT_EXIF_ISO . ": ";
								if(isset($exif_data["ISOSpeedRatings"]))
								{
									$exif_info .= $exif_data["ISOSpeedRatings"];
								}
								else
								{
									$exif_info .= "n/a";
								}
								$exif_info .= "<br>";
								
								$exif_info .= TEXT_EXIF_SHUTTER . ": ";
								if(isset($exif_data["ExposureTime"]))
								{
									$exif_ExposureTime=create_function('','return '.$exif_data["ExposureTime"].';');
									$exp_time = $exif_ExposureTime();
									if ($exp_time > 0.25)
									{
										$exif_info .= $exp_time;
									}
									else
									{
										$exif_info .= $exif_data["ExposureTime"];
									}
									$exif_info .= "s";
									
								}
								else
								{
									$exif_info .= "n/a";
								}
								$exif_info .= "<br>";

								$exif_info .= TEXT_EXIF_APERTURE . ": ";
								if(isset($exif_data["FNumber"]))
								{
									$exif_FNumber=create_function('','return number_format(round('.$exif_data["FNumber"].',1),1);');
									$exif_info .= "f".$exif_FNumber();
								}
								else
								{
									$exif_info .= "n/a";
								}
								$exif_info .= "<br>";

								$exif_info .= TEXT_EXIF_FOCAL . ": ";
								if(isset($exif_data["FocalLength"]))
								{
									$exif_FocalLength=create_function('','return number_format(round('.$exif_data["FocalLength"].',1),1);');
									$exif_info .= $exif_FocalLength();
								}
								else
								{
									$exif_info .= "n/a";
								}
								$exif_info .= "mm<br>";
								
								$exif_info .= TEXT_EXIF_FLASH . ": ";
								if(isset($exif_data["Flash"]))
								{
									$exif_info .= (($exif_data["Flash"] & 1) ? TEXT_YES : TEXT_NO);
								}
								else
								{
									$exif_info .= "n/a";
								}
								$exif_info .= "<br>";
							}
							else
							{
								$exif_info .= TEXT_EXIF_MISSING . "<br>";
							}
						}

						if (ROTATE_IMAGES and isset($exif_data["Orientation"]))
						{
							$image_width = imagesx($image);
							$image_height = imagesy($image);

							switch ($exif_data["Orientation"])
							{
								case 2 :
								{
									$rotate = @imagecreatetruecolor($image_width, $image_height);
									imagecopyresampled($rotate, $image, 0, 0, $image_width-1, 0, $image_width, $image_height, -$image_width, $image_height);
									imagedestroy($image);
									$image_changed = TRUE;
									break;
								}
								case 3 :
								{
									$rotate = imagerotate($image, 180, 0);
									imagedestroy($image);
									$image_changed = TRUE;
									break;
								}
								case 4 :
								{
									$rotate = @imagecreatetruecolor($image_width, $image_height);
									imagecopyresampled($rotate, $image, 0, 0, 0, $image_height-1, $image_width, $image_height, $image_width, -$image_height);
									imagedestroy($image);
									$image_changed = TRUE;
									break;
								}
								case 5 :
								{
									$rotate = imagerotate($image, 270, 0);
									imagedestroy($image);
									$image = $rotate;
									$rotate = @imagecreatetruecolor($image_height, $image_width);
									imagecopyresampled($rotate, $image, 0, 0, 0, $image_width-1, $image_height, $image_width, $image_height, -$image_width);
									$image_changed = TRUE;
									break;
								}
								case 6 :
								{
									$rotate = imagerotate($image, 270, 0);
									imagedestroy($image);
									$image_changed = TRUE;
									break;
								}
								case 7 :
								{
									$rotate = imagerotate($image, 90, 0);
									imagedestroy($image);
									$image = $rotate;
									$rotate = @imagecreatetruecolor($image_height, $image_width);
									imagecopyresampled($rotate, $image, 0, 0, 0, $image_width-1, $image_height, $image_width, $image_height, -$image_width);
									$image_changed = TRUE;
									break;
								}
								case 8 :
								{
									$rotate = imagerotate($image, 90, 0);
									imagedestroy($image);
									$image_changed = TRUE;
									break;
								}
								default: $rotate = $image;
							}
							$image = $rotate;
						}
					}
					
					if (WATERMARK)
					{
						$wm_file = GALLERY_ROOT . "_sfpg_icons/" . WATERMARK;
						if (file_exists($wm_file))
						{
							if ($watermark = imagecreatefromstring(file_get_contents($wm_file)))
							{
								$image_width = imagesx($image);
								$image_height = imagesy($image);
								$ww = imagesx($watermark);
								$wh = imagesy($watermark);
								imagecopy($image, $watermark, $image_width-$ww, $image_height-$wh, 0, 0, $ww, $wh);
								imagedestroy($watermark);
								$image_changed = TRUE;
							}
						}
					}

					if ($image_changed)
					{
						if (!is_dir(DATA_ROOT . "image/" . $image_dir))
						{
							mkdir(DATA_ROOT . "image/" . $image_dir, 0777, TRUE);
						}
						$new_full_img = DATA_ROOT . "image/" . $image_dir . $image_file;
						if ($image_type == "jpeg")
						{
							imagejpeg($image, $new_full_img, IMAGE_JPEG_QUALITY);
						}
						elseif ($image_type == "png")
						{
							imagepng($image, $new_full_img);
						}
						elseif ($image_type == "gif")
						{
							imagegif($image, $new_full_img);
						}
					}
					
					$fp = fopen(DATA_ROOT . "info/" . $image_dir . $image_file . ".sfpg", "w");
					fwrite($fp, date(DATE_FORMAT, filemtime(GALLERY_ROOT . $image_dir . $image_file)) . "|" . sfpg_file_size(filesize(GALLERY_ROOT . $image_dir . $image_file)) . "|" . imagesx($image) . "|" . imagesy($image) . "|" . $exif_info);
					fclose($fp);
				}

				$image_width = imagesx($image);
				$image_height = imagesy($image);
				if (($image_width < $max_width) and ($image_height < $max_height) and !$enlarge)
				{
					$new_img_height = $image_height;
					$new_img_width = $image_width;
				}
				else
				{
					$aspect_x = $image_width / $max_width;
					$aspect_y = $image_height / $max_height;
					if ($aspect_x > $aspect_y)
					{
						$new_img_width = $max_width;
						$new_img_height = $image_height / $aspect_x;
					}
					else
					{
						$new_img_height = $max_height;
						$new_img_width = $image_width / $aspect_y;
					}
				}
				$new_image = imagecreatetruecolor($new_img_width, $new_img_height);
				imagecopyresampled($new_image, $image, 0, 0, 0, 0, $new_img_width, $new_img_height, imagesx($image), imagesy($image));
				imagedestroy($image);
				if (!is_dir(DATA_ROOT . $func . "/" . $image_dir))
				{
					mkdir(DATA_ROOT . $func . "/" . $image_dir, 0777, TRUE);
				}
				header("Content-type: image/" . $image_type);
				header("Content-Disposition: filename=\"" . $func . "_" . $image_file . "\"");
				if ($image_type == "jpeg")
				{
					imagejpeg($new_image, NULL, $jpeg_quality);
					imagejpeg($new_image, $image_path_file, $jpeg_quality);
				}
				elseif ($image_type == "png")
				{
					imagepng($new_image);
					imagepng($new_image, $image_path_file);
				}
				elseif ($image_type == "gif")
				{
					imagegif($new_image);
					imagegif($new_image, $image_path_file);
				}
				imagedestroy($new_image);
			}
		}
	}

	function sfpg_dir_info($directory, $initial=TRUE)
	{
		list($dirs, $images, $files) = sfpg_get_dir($directory);
		if ($initial)
		{
			$info = count($dirs) . "|" . count($images) . "|" . count($files) . "|" . date(DATE_FORMAT, filemtime(GALLERY_ROOT . GALLERY . ".")) . "|";
		}
		else
		{
			$info = "";
		}
		if ((DIR_IMAGE_FILE) and file_exists(GALLERY_ROOT . $directory . DIR_IMAGE_FILE))
		{
			return $info . sfpg_url_string($directory, DIR_IMAGE_FILE);
		}
		if (isset($images[0]))
		{
			return $info . sfpg_url_string($directory, $images[0]);
		}
		else
		{
			foreach ($dirs as $subdir)
			{
				$subresult = sfpg_dir_info($directory . $subdir . "/", FALSE);
				if ($subresult != "")
				{
					return $info . $subresult;
				}
			}
		}
		return $info;
	}


	function sfpg_set_dir_info($directory)
	{
		if (!is_dir(DATA_ROOT . "info/" . $directory))
		{
			mkdir(DATA_ROOT . "info/" . $directory, 0777, TRUE);
		}
		if ($fp = fopen(DATA_ROOT . "info/" . $directory . "_info.sfpg", "w"))
		{
			fwrite($fp, sfpg_dir_info($directory));
			fclose($fp);
		}
	}


	function sfpg_javascript()
	{
		global $dirs, $images, $files, $file_ext_thumbs;

		echo "<script language=\"JavaScript\" TYPE=\"text/javascript\">
		<!--

		var phpSelf = '" . $_SERVER["PHP_SELF"] . "';

		var navLink = [];
		var navName = [];

		var dirLink = [];
		var dirThumb = [];
		var dirName = [];
		var dirInfo = [];

		var imgLink = [];
		var imgName = [];
		var imgInfo = [];

		var fileLink = [];
		var fileThumb = [];
		var fileName = [];
		var fileInfo = [];

		var imageSpace = 50;

		var waitSpin = ['&bull;-----', '-&bull;----', '--&bull;---', '---&bull;--', '----&bull;-', '-----&bull;'];
		var waitSpinNr = 0;
		var waitSpinSpeed = 100;

		var graceMaxRun = Math.ceil(".LOAD_FADE_GRACE." / waitSpinSpeed);

		var showInfo = ".(((isset($_GET["info"])) and ($_GET["info"]=='1')) ? "true" : "false").";
		var actualSize = false;
		var fullImgLoaded = false;
		var imageLargerThanViewport = false;

		var index = false;
		var preloadImg = new Image();
		var preloaded = -1;
		var preloadedFull = -1;

		var viewportWidth;
		var viewportHeight;

		var imgFullWidth;
		var imgFullHeight;


		function getViewport()
		{
			if (typeof window.innerWidth != 'undefined')
			{
				viewportWidth = window.innerWidth,
				viewportHeight = window.innerHeight
			}
			else if (typeof document.documentElement != 'undefined' && typeof document.documentElement.clientWidth != 'undefined' && document.documentElement.clientWidth != 0)
			{
				viewportWidth = document.documentElement.clientWidth,
				viewportHeight = document.documentElement.clientHeight
			}
			else
			{
				viewportWidth = document.getElementsByTagName('body')[0].clientWidth,
				viewportHeight = document.getElementsByTagName('body')[0].clientHeight
			}
			if (showInfo)
			{
				viewportWidth -= (".INFO_BOX_WIDTH." + 12);
			}
			viewportHeight -= ".MENU_BOX_HEIGHT.";
			if (viewportHeight < 0) viewportHeight = 20;
		}


		function initDisplay()
		{
			getViewport();
			if (index)
			{
				resizeImage();
			}
			document.getElementById('box_image').style.width = viewportWidth;
			document.getElementById('box_image').style.height = viewportHeight;
			document.getElementById('box_wait').style.width = viewportWidth;
			document.getElementById('box_wait').style.height = viewportHeight;
			document.getElementById('box_gallery').style.width = viewportWidth;
			document.getElementById('box_gallery').style.height = viewportHeight;
			document.getElementById('box_info').style.height = viewportHeight-20;
			showMenu();
		}


		function resizeImage()
		{
			var availX, availY, aspectX, aspectY, newImgX, newImgY;
			availX = viewportWidth - imageSpace;
			availY = viewportHeight - imageSpace;
			if (availX < " . THUMB_MAX_WIDTH . ")
			{
				availX = " . THUMB_MAX_WIDTH . ";
			}
			if (availY < " . THUMB_MAX_HEIGHT . ")
			{
				availY = " . THUMB_MAX_HEIGHT . ";
			}
			if ((imgFullWidth > availX) || (imgFullHeight > availY))
			{
				imageLargerThanViewport = true;
			}
			else
			{
				imageLargerThanViewport = false;
			}
			if (!actualSize && ((imgFullWidth > availX) || (imgFullHeight > availY)))
			{
				aspectX = imgFullWidth / availX;
				aspectY = imgFullHeight / availY;
				if (aspectX > aspectY)
				{
					newImgX = availX;
					newImgY = Math.round(imgFullHeight / aspectX);
				}
				else
				{
					newImgX = Math.round(imgFullWidth / aspectY);
					newImgY = availY;
				}
				document.getElementById('img_resize').innerHTML = newImgX + ' x ' + newImgY;
			}
			else
			{
				newImgX = imgFullWidth;
				newImgY = imgFullHeight;
				document.getElementById('img_resize').innerHTML = '" . str_to_script(TEXT_NOT_SCALED) . "';
			}
			document.getElementById('img_size').innerHTML = imgFullWidth + ' x ' + imgFullHeight;
			document.getElementById('full').width = newImgX;
			document.getElementById('full').height = newImgY;
		}


		function fullSize()
		{
			if (actualSize == true)
			{
				actualSize = false;
				initDisplay();
			}
			else
			{
				actualSize = true;
				initDisplay();
			}
		}


		function showMenu()
		{
			if (imgLink.length > 0)
			{
				menu = '<span onmouseover=\"this.className=\'sfpg_button_hover\';\" onmouseout=\"this.className=\'sfpg_button\';\" class=\"sfpg_button\" onclick=\"cycleImg(-1)\">" . str_to_script(TEXT_PREVIOUS) . "</span>';
				menu += '<span onmouseover=\"this.className=\'sfpg_button_hover\';\" onmouseout=\"this.className=\'sfpg_button\';\" class=\"sfpg_button\" onclick=\"cycleImg(1)\">" . str_to_script(TEXT_NEXT) . "</span>';
			}
			else
			{
				menu = '<span class=\"sfpg_button_disabled\">" . str_to_script(TEXT_PREVIOUS) . "</span>';
				menu += '<span class=\"sfpg_button_disabled\">" . str_to_script(TEXT_NEXT) . "</span>';
			}

			if (showInfo)
			{
				menu += '<span onmouseover=\"this.className=\'sfpg_button_hover\';\" onmouseout=\"this.className=\'sfpg_button_on\';\" onclick=\"toggleInfo(showInfo);\" class=\"sfpg_button_on\">" . str_to_script(TEXT_INFO) . "</span>';
			}
			else
			{
				menu += '<span onmouseover=\"this.className=\'sfpg_button_hover\';\" onmouseout=\"this.className=\'sfpg_button\';\" onclick=\"toggleInfo(showInfo);\" class=\"sfpg_button\">" . str_to_script(TEXT_INFO) . "</span>';
			}

			if (index && imageLargerThanViewport)
			{
				if (actualSize)
				{
					menu += '<span onmouseover=\"this.className=\'sfpg_button_hover\';\" onmouseout=\"this.className=\'sfpg_button_on\';\" class=\"sfpg_button_on\" onclick=\"fullSize()\">" . str_to_script(TEXT_ACTUAL_SIZE) . "</span>';
				}
				else
				{
					menu += '<span onmouseover=\"this.className=\'sfpg_button_hover\';\" onmouseout=\"this.className=\'sfpg_button\';\" class=\"sfpg_button\" onclick=\"fullSize()\">" . str_to_script(TEXT_ACTUAL_SIZE) . "</span>';
				}
			}
			else
			{
				menu += '<span class=\"sfpg_button_disabled\">" . str_to_script(TEXT_ACTUAL_SIZE) . "</span>';
			}


			";
			if (USE_PREVIEW)
			{
				echo "
				if (index)
				{
					if (fullImgLoaded)
					{
						menu += '<span onmouseover=\"this.className=\'sfpg_button_hover\';\" onmouseout=\"this.className=\'sfpg_button_on\';\" class=\"sfpg_button_on\" onclick=\"openImageView('+index+', false)\">".str_to_script(TEXT_FULLRES)."</span>';
					}
					else
					{
						menu += '<span onmouseover=\"this.className=\'sfpg_button_hover\';\" onmouseout=\"this.className=\'sfpg_button\';\" class=\"sfpg_button\" onclick=\"openImageView('+index+', true)\">".str_to_script(TEXT_FULLRES)."</span>';
					}
				}
				else
				{
					menu += '<span class=\"sfpg_button_disabled\">" . str_to_script(TEXT_FULLRES) . "</span>';
				}
				";
			}
			echo "
			if (index)
			{
				menu += '<span onmouseover=\"this.className=\'sfpg_button_hover\';\" onmouseout=\"this.className=\'sfpg_button\';\" class=\"sfpg_button\" onclick=\"closeImageView()\">" . str_to_script(TEXT_CLOSE_IMG_VIEW) . "</span>';
			}
			else
			{
				menu += '<span class=\"sfpg_button_disabled\">" . str_to_script(TEXT_CLOSE_IMG_VIEW) . "</span>';
			}
			";
			if (LINK_BACK)
			{
				echo "menu += '<span onmouseover=\"this.className=\'sfpg_button_hover\';\" onmouseout=\"this.className=\'sfpg_button\';\" class=\"sfpg_button\" onclick=\"window.location=\'".LINK_BACK."\'\">".TEXT_LINK_BACK."</span>';
				";
			}
			echo "menu += '<span onmouseover=\"this.className=\'sfpg_button_hover\';\" onmouseout=\"this.className=\'sfpg_button\';\" class=\"sfpg_button\" onclick=\"window.location=\'http://megamanpoweredup.net/rpg2k11/\'\">Mega Man RPG Prototype</span>';
			document.getElementById('div_menu').innerHTML = menu;
		}


		function openImageView(imgId, full)
		{
			if (!index)
			{
				document.getElementById('box_overlay').style.visibility='visible';
				setOpacity('box_overlay', " . OVERLAY_OPACITY . ");
			}
			index = imgId;
			fillInfo('img', index);
			setOpacity('full', 0);
			document.getElementById('wait').style.visibility='visible';
			document.getElementById('box_wait').style.visibility='visible';
			document.getElementById('box_image').style.visibility='visible';
			preloadImage(index, full);
			actualSize = false;
			fullImgLoaded = full;
			showMenu();
			showImage(0);
		}


		function preloadImage(imgId, full)
		{
			if ((preloaded != imgId) || (preloadedFull != full))
			{
				preloadImg = new Image();
				if ((full == 1) || (".(USE_PREVIEW ? "false" : "true")."))
				{
					preloadImg.src = phpSelf+'?cmd=image&sfpg='+imgLink[imgId];
					preloadedFull = 1;
				}
				else
				{
					preloadImg.src = phpSelf+'?cmd=preview&sfpg='+imgLink[imgId];
					preloadedFull = 0;
				}
				preloaded = imgId;
			}
		}


		function fillInfo(type, id)
		{
			if (!index || (type == 'img'))
			{
				var info='<div class=\"thumbimgbox\">';
				if (type == 'dir')
				{
					if (dirThumb[id] != '')
					{
						info += '<img class=\"thumb\" alt=\"\" src=\"'+phpSelf+'?cmd=thumb&sfpg='+dirThumb[id]+'\">';
					}
					else
					{
						info += '<br><br>".str_to_script(TEXT_NO_IMAGES)."';
					}
					info += '</div>';
					info += '<strong>".str_to_script(TEXT_DIR_NAME)."</strong><br><div class=\"sfpg_info_text\">'+dirName[id] + '</div><br>';
					var splint = dirInfo[id].split('|');
					info += '<strong>".str_to_script(TEXT_INFO)."</strong><br><div class=\"sfpg_info_text\">';
					info += '".str_to_script(TEXT_DATE).": '+splint[0]+'<br>';
					info += '".str_to_script(TEXT_DIRS).": '+splint[1]+'<br>';
					info += '".str_to_script(TEXT_IMAGES).": '+splint[2]+'<br>';";
					if (SHOW_FILES)
					{
						echo "
						info += '".str_to_script(TEXT_FILES).": '+splint[3]+'<br>';";
					}
					echo "
					info += '</div><br>';
					info += '<strong>".str_to_script(TEXT_DESCRIPTION)."</strong><br><div class=\"sfpg_info_text\">'+splint[4]+'<br></div><br>';
					info += '<strong>".str_to_script(TEXT_LINKS)."</strong><br><a href=\"'+phpSelf+'?sfpg='+dirLink[id]+'\">".str_to_script(TEXT_DIRECT_LINK_GALLERY)."</a><br><br>';
				}
				else if (type == 'img')
				{
					info += '<img class=\"thumb\" alt=\"\" src=\"'+phpSelf+'?cmd=thumb&sfpg='+imgLink[id]+'\">';
					info += '</div>';
					var splint = imgInfo[id].split('|');
					info += '<strong>".str_to_script(TEXT_IMAGE_NAME)."</strong><br><div class=\"sfpg_info_text\">'+imgName[id] + '</div><br>';
					info += '<strong>".str_to_script(TEXT_INFO)."</strong><br><div class=\"sfpg_info_text\">';
					info += '".str_to_script(TEXT_DATE).": '+splint[0]+'<br>';
					info += '".str_to_script(TEXT_IMAGESIZE).": '+splint[2]+' x '+splint[3]+'<br>';
					info += '".str_to_script(TEXT_DISPLAYED_IMAGE).": <span id=\"img_size\"></span> (';
					if (fullImgLoaded || ".(USE_PREVIEW ? "false" : "true").")
					{
						info += '".str_to_script(TEXT_THIS_IS_FULL)."';
					}
					else
					{
						info += '".str_to_script(TEXT_THIS_IS_PREVIEW)."';
					}
					info += ')<br>';
					info += '".str_to_script(TEXT_SCALED_TO).": <span id=\"img_resize\"></span><br>';
					info += '".str_to_script(TEXT_FILESIZE).": '+splint[1]+'<br>'+splint[4];
					info += '".str_to_script(TEXT_IMAGE_NUMBER).": '+id+' / '+(imgLink.length-1)+'<br>';
					info += '</div><br>';
					info += '<strong>".str_to_script(TEXT_DESCRIPTION)."</strong><br><div class=\"sfpg_info_text\">'+splint[5]+'<br></div><br>';
					info += '<strong>".str_to_script(TEXT_LINKS)."</strong><br>';
					info += '<a href=\"'+phpSelf+'?sfpg='+imgLink[id]+'\">".str_to_script(TEXT_DIRECT_LINK_IMAGE)."</a><br>';
					".(TEXT_DOWNLOAD ? "info += '<a href=\"'+phpSelf+'?cmd=dl&sfpg='+imgLink[id]+'\">".str_to_script(TEXT_DOWNLOAD)."</a><br><br>';" : "")."
				}
				else if (type == 'file')
				{
					if (fileThumb[id] != '')
					{
						info += '<img class=\"thumb\" alt=\"\" src=\"'+phpSelf+'?cmd=thumb&sfpg='+fileThumb[id]+'\">';
					}
					else
					{
						info += '<br><br>".str_to_script(TEXT_NO_PREVIEW_FILE)."<br>';
					}
					info += '</div>';
					info += '<strong>".str_to_script(TEXT_FILE_NAME)."</strong><br><div class=\"sfpg_info_text\">'+fileName[id]+'</div><br>';
					var splint = fileInfo[id].split('|');
					info += '<strong>".str_to_script(TEXT_INFO)."</strong><br><div class=\"sfpg_info_text\">';
					info += '".str_to_script(TEXT_DATE).": '+splint[0]+'<br>';
					info += '".str_to_script(TEXT_FILESIZE).": '+splint[1]+'<br>';
					info += '</div><br>';
					info += '<strong>".str_to_script(TEXT_DESCRIPTION)."</strong><br><div class=\"sfpg_info_text\">'+splint[2]+'<br></div><br>';
				}
				document.getElementById('box_inner_info').innerHTML = info;
			}
		}


		function toggleInfo(status)
		{
			if (status)
			{
				document.getElementById('box_info').style.visibility='hidden';
			}
			else
			{
				setOpacity('box_info', 0);
				document.getElementById('box_info').style.visibility='visible';
				fadeOpacity('box_info', 0,	100, " . FADE_DURATION_MS . ");
			}
			showInfo = !status;
			initDisplay();
		}


		function openGallery(id, type)
		{
			var link;
			if (type == 'nav')
			{
				link=navLink[id];
			}
			else
			{
				link=dirLink[id];
			}

			var opt='';
			if (showInfo)
			{
				opt = '&info=1';
			}
			window.location	= phpSelf+'?sfpg='+link+opt;
		}


		function openFile(id)
		{
			if (".(FILE_IN_NEW_WINDOW ? "true" : "false").")
			{
				window.open(phpSelf+'?cmd=file&sfpg='+fileLink[id]);
			}
			else
			{
				window.location	= phpSelf+'?cmd=file&sfpg='+fileLink[id];
			}
		}


		function nextImage(direction)
		{
			var nextIndex;
			if (!index)
			{
				if (direction > 0)
				{
					return 1;
				}
				else
				{
					return (imgLink.length - 1);
				}
			}
			var nextImg = index + direction;
			if (nextImg > imgLink.length - 1)
			{
				nextImg = 1;
			}
			if (nextImg < 1)
			{
				nextImg = imgLink.length - 1;
			}
			return nextImg;
		}


		function cycleImg(direction)
		{
			openImageView(nextImage(direction), false);
		}


		function showImage(graceRun)
		{
			if (graceRun < graceMaxRun)
			{
				if (preloadImg.complete || graceRun != 0)
				{
					if (graceRun == 0)
					{
						document.getElementById('full').src = preloadImg.src;
						imgFullWidth = preloadImg.width;
						imgFullHeight = preloadImg.height;
						fillInfo('img', index);
						initDisplay();
						preloadImage(nextImage(1),0);
					}
					graceRun++;
				}
				if (waitSpinNr >= waitSpin.length)
				{
					waitSpinNr = 0;
				}
				document.getElementById('wait').innerHTML = '<div class=\"loading\">".str_to_script(TEXT_IMAGE_LOADING)."' + waitSpin[waitSpinNr] + '</div>';
				waitSpinNr++;
				if (index)
				{
					setTimeout ('showImage(' + graceRun + ')', waitSpinSpeed);
				}
			}
			else
			{
				document.getElementById('wait').style.visibility='hidden';
				fadeOpacity('full', 0, 100, " . FADE_DURATION_MS . ");
			}
		}


		function closeImageView()
		{
			document.getElementById('box_wait').style.visibility='hidden';
			document.getElementById('wait').style.visibility='hidden';
			document.getElementById('box_image').style.visibility='hidden';
			index = false;
			showMenu();
			fadeOpacity('box_overlay', " . OVERLAY_OPACITY . ", 0, " . FADE_DURATION_MS . ");
			document.getElementById('full').width = 1;
			document.getElementById('full').height = 1;
			document.getElementById('full').src = '';
			fillInfo('dir', 0);
		}


		function setOpacity(id, opacity)
		{
			var element = document.getElementById(id).style;
			element.opacity = (opacity / 100);	// std
			element.MozOpacity = (opacity / 100);	// firefox
			element.filter = 'alpha(opacity=' + opacity + ')';	// IE
			element.KhtmlOpacity = (opacity / 100);	// Mac
		}


		function fadeOpacity(id, opacityStart, opacityEnd, msToFade)
		{
			if (msToFade > 0)
			{
				var frames = Math.round((msToFade / 1000) * ".FADE_FRAME_PER_SEC.");
				var msPerFrame = Math.round(msToFade / frames);
				var opacityPerFrame = (opacityEnd - opacityStart) / frames;
				var opacity = opacityStart;
				for (frame = 1; frame <= frames; frame++)
				{
					setTimeout('setOpacity(\'' + id + '\',' + opacity + ')',(frame * msPerFrame));
					opacity += opacityPerFrame;
				}
				if (opacityEnd == 0)
				{
					setTimeout('document.getElementById(\'' + id + '\').style.visibility=\'hidden\'',((frames+1) * msPerFrame));
				}
				else
				{
					setTimeout('setOpacity(\'' + id + '\',' + opacityEnd + ')',((frames+1) * msPerFrame));
				}
			}
			else
			{
				setOpacity(id, opacityEnd);
				if (opacityEnd == 0)
				{
					document.getElementById(id).style.visibility='hidden';
				}
			}
		}


		function thumbDisplayName(name)
		{
			dispName = name.substring(0,".THUMB_CHARS_MAX.");
			if (name.length > ".THUMB_CHARS_MAX.")
			{
				dispName += '...';
			}
			return dispName;
		}


		function addElement(elementNumber, type)
		{
			var newdiv = document.createElement('div');
			newdiv.className = 'thumbbox';
			if (type == 'dir')
			{
				content = '<div onclick=\"openGallery('+elementNumber+')\" onmouseover=\"this.className=\'innerboxdir_hover\'; fillInfo(\'dir\', '+elementNumber+')\" onmouseout=\"this.className=\'innerboxdir\'; fillInfo(\'dir\', 0)\" class=\"innerboxdir\"><div class=\"thumbimgbox\">';
				if (dirThumb[elementNumber] != '')
				{
					content += '<img class=\"thumb\" alt=\"\" src=\"'+phpSelf+'?cmd=thumb&sfpg='+dirThumb[elementNumber]+'\">';
				}
				else
				{
					content += '<br><br>".str_to_script(TEXT_NO_IMAGES)."';
				}
				content += '</div>';
				". (THUMB_CHARS_MAX ? "content += '['+thumbDisplayName(dirName[elementNumber])+']';" : "")."
				content += '</div>';
			}
			else if (type == 'img')
			{
				content = '<div onclick=\"openImageView('+elementNumber+', false)\" onmouseover=\"this.className=\'innerboximg_hover\'; fillInfo(\'img\', '+elementNumber+')\" onmouseout=\"this.className=\'innerboximg\'; fillInfo(\'dir\', 0)\" class=\"innerboximg\"><div class=\"thumbimgbox\"><img class=\"thumb\" alt=\"\" src=\"'+phpSelf+'?cmd=thumb&sfpg='+imgLink[elementNumber]+'\"></div>';
				". (THUMB_CHARS_MAX ? "content += thumbDisplayName(imgName[elementNumber]);" : "")."
				content += '</div>';
			}
			else if (type == 'file')
			{
				content = '<div onclick=\"openFile('+elementNumber+')\" onmouseover=\"this.className=\'innerboxfile_hover\'; fillInfo(\'file\', '+elementNumber+')\" onmouseout=\"this.className=\'innerboxfile\'; fillInfo(\'dir\', 0)\" class=\"innerboxfile\"><div class=\"thumbimgbox\">';
				if (fileThumb[elementNumber] != '')
				{
					content += '<img class=\"thumb\" alt=\"\" src=\"'+phpSelf+'?cmd=thumb&sfpg='+fileThumb[elementNumber]+'\">';
				}
				else
				{
					content += '<br><br>".str_to_script(TEXT_NO_PREVIEW_FILE)."';
				}
				content += '</div>';
				". (THUMB_CHARS_MAX ? "content += thumbDisplayName(fileName[elementNumber]);" : "")."
				content += '</div>';
			}
			newdiv.innerHTML = content;
			var boxC = document.getElementById('box_gallery');
			boxC.appendChild(newdiv);
		}


		function showGallery(initOpenImage)
		{
			initDisplay();
			if (initOpenImage)
			{
				openImageView(initOpenImage, false);
			}
			else
			{
				fillInfo('dir', 0);
			}

			if (showInfo)
			{
				toggleInfo(false);
			}

			var navLinks = '';
			for (i = 1; i < navLink.length; i++)
			{
				if (navLink[i] != '')
				{
					navLinks += '<span onmouseover=\"this.className=\'sfpg_button_hover\';\" onmouseout=\"this.className=\'sfpg_button_nav\';\" class=\"sfpg_button_nav\" onclick=\"openGallery('+i+', \'nav\')\">'+navName[i]+'</span>';
				}
				else
				{
					navLinks += navName[i];
				}
			}
			document.getElementById('navi').innerHTML = navLinks;

			for (i = 1; i < dirLink.length; i++)
			{
				addElement(i, 'dir');
			}

			for (i = 1; i < imgLink.length; i++)
			{
				addElement(i, 'img');
			}
			
			for (i = 1; i < fileLink.length; i++)
			{
				addElement(i, 'file');
			}
		}

		\n\n";

		echo "navLink[1] = '" . sfpg_url_string('') . "';\n";
		echo "navName[1] = '" . str_to_script(TEXT_HOME) . "';\n\n";
		
		$links = explode("/", GALLERY);
		$gal_dirs = "";
		if (GALLERY and is_array($links))
		{
			for ($i = 0; $i < count($links); $i++)
			{
				if ($links[$i])
				{
					$gal_dirs .= $links[$i] . "/";
					$display_name = @file(GALLERY_ROOT . $gal_dirs . DIR_NAME_FILE);
					if ($display_name)
					{
						$display_name = trim($display_name[0]);
					}
					else
					{
						$display_name = sfpg_display_name($links[$i], TRUE);
					}
					$a_names[] = $display_name;
					$a_links[] = $gal_dirs;
				}
			}
			$link_disp_lenght = strlen(TEXT_HOME) + 4;
			$start_link = count($a_names)-1;
			for($i = count($a_names)-1; $i >= 0; $i--)
			{
				$link_disp_lenght += strlen($a_names[$i]) + 5;
				if ($link_disp_lenght < NAVI_CHARS_MAX)
				{
					$start_link = $i;
				}
			}
			$i = 2;
			for ($link_nr = $start_link; $link_nr < count($a_links); $link_nr++)
			{
				if(($start_link > 0) and ($link_nr == $start_link))
				{
					echo "navLink[".$i."] = '';\n";
					echo "navName[".$i."] = '" . str_to_script(" ... ") . "';\n\n";
					$i++;
				}
				else
				{
					echo "navLink[".$i."] = '';\n";
					echo "navName[".$i."] = '" . str_to_script(" > ") . "';\n\n";
					$i++;
				}
				echo "navLink[".$i."] = '" . sfpg_url_string($a_links[$link_nr]) . "';\n";
				echo "navName[".$i."] = '" . str_to_script($a_names[$link_nr]) . "';\n\n";
				$i++;
			}
			echo "dirLink[0] = '" . sfpg_url_string($a_links[count($a_links)-1]) . "';\n";
			echo "dirName[0] = '" . str_to_script((count($a_links) == 0 ? TEXT_HOME : $a_names[count($a_links)-1])) . "';\n";
		}
		else
		{
			echo "dirLink[0] = '" . sfpg_url_string("") . "';\n";
			echo "dirName[0] = '" . str_to_script(TEXT_HOME) . "';\n";
		}
		
		if (!file_exists(DATA_ROOT . "info/" . GALLERY . "_info.sfpg"))
		{
			sfpg_set_dir_info(GALLERY);
		}

		$filed = explode("|", file_get_contents(DATA_ROOT . "info/" . GALLERY . "_info.sfpg"));
		if ((count($dirs) != $filed[0]) or (count($images) != $filed[1]) or (count($files) != $filed[2]))
		{
			sfpg_set_dir_info(GALLERY);
			$filed = explode("|", file_get_contents(DATA_ROOT . "info/" . GALLERY . "_info.sfpg"));
		}
		echo "dirThumb[0] = '" . $filed[4] . "';\n";
		echo "dirInfo[0] = '" . str_to_script($filed[3]."|".$filed[0]."|".$filed[1]."|".$filed[2]."|".@file_get_contents(GALLERY_ROOT . GALLERY . DIR_DESC_FILE)) . "';\n\n";
		
		$item = 1;
		foreach ($dirs as $val)
		{
			$display_name = @file(GALLERY_ROOT . GALLERY . $val . "/" . DIR_NAME_FILE);
			if ($display_name)
			{
				$display_name = trim($display_name[0]);
			}
			else
			{
				$display_name = sfpg_display_name($val, TRUE);
			}
			echo "dirName[" . ($item) . "] = '" . str_to_script($display_name) . "';\n";
			echo "dirLink[" . ($item) . "] = '" . sfpg_url_string((GALLERY . $val . "/")) . "';\n";
			if (!file_exists(DATA_ROOT . "info/" . GALLERY . $val . "/_info.sfpg"))
			{
				sfpg_set_dir_info(GALLERY . $val . "/");
			}
			$filed = explode("|", file_get_contents(DATA_ROOT . "info/" . GALLERY . $val . "/_info.sfpg"));
			echo "dirThumb[" . ($item) . "] = '" . $filed[4] . "';\n";
			echo "dirInfo[" . ($item) . "] = '" . str_to_script($filed[3]."|".$filed[0]."|".$filed[1]."|".$filed[2]."|".@file_get_contents(GALLERY_ROOT . GALLERY . $val . "/" . DIR_DESC_FILE)) . "';\n\n";
			$item++;
		}

		$img_direct_link = FALSE;
		$item = 1;
		foreach ($images as $val)
		{
			if ($val == IMAGE)
			{
				$img_direct_link = ($item);
			}
			echo "imgLink[" . ($item) . "] = '" . sfpg_url_string(GALLERY, $val) . "';\n";
			$img_name = sfpg_display_name($val, SHOW_IMAGE_EXT);
			echo "imgName[" . ($item) . "] = '" . str_to_script($img_name) . "';\n";
			echo "imgInfo[" . ($item) . "] = '" . str_to_script(@file_get_contents(DATA_ROOT . "info/" . GALLERY . $val . ".sfpg")."|".@file_get_contents(GALLERY_ROOT . GALLERY . $val . DESC_EXT))."';\n\n";
			$item++;
		}
		if ($img_direct_link)
		{
			define("OPEN_IMAGE_ON_LOAD", $img_direct_link);
		}
		else
		{
			define("OPEN_IMAGE_ON_LOAD", FALSE);
		}


		$item = 1;
		foreach ($files as $val)
		{
			$ext = sfpg_ext($val);
			echo "fileLink[" . ($item) . "] = '" . sfpg_url_string(GALLERY, $val) . "';\n";
			if (FILE_THUMB_EXT and file_exists(GALLERY_ROOT . GALLERY . $val . FILE_THUMB_EXT))
			{
				echo "fileThumb[" . ($item) . "] = '" . sfpg_url_string(GALLERY, $val . FILE_THUMB_EXT) . "';\n";
			}
			elseif (isset($file_ext_thumbs[$ext]))
			{
				echo "fileThumb[" . ($item) . "] = '" . sfpg_url_string("_sfpg_icons/", $file_ext_thumbs[$ext]) . "';\n";
			}
			else
			{
				echo "fileThumb[" . ($item) . "] = '';\n";
			}
			echo "fileName[" . ($item) . "] = '" . str_to_script(sfpg_display_name($val, SHOW_FILE_EXT)) . "';\n";
			if (!file_exists(DATA_ROOT . "info/" . GALLERY . $val . ".sfpg"))
			{
				$fp = fopen(DATA_ROOT . "info/" . GALLERY . $val . ".sfpg", "w");
				fwrite($fp, date(DATE_FORMAT, filemtime(GALLERY_ROOT . GALLERY . $val)) . "|" . sfpg_file_size(filesize(GALLERY_ROOT . GALLERY . $val)));
				fclose($fp);
			}
			echo "fileInfo[" . ($item) . "] = '" . str_to_script(@file_get_contents(DATA_ROOT . "info/" . GALLERY . $val . ".sfpg") . "|" . @file_get_contents(GALLERY_ROOT . GALLERY . $val . DESC_EXT)) . "';\n\n";
			$item++;
		}

		echo "
		//-->
		</script>";
	}


	$get_set = FALSE;
	if (isset($_GET["sfpg"]))
	{
		$get = explode("*", sfpg_base64url_decode($_GET["sfpg"]));
		if ((md5($get[0] . "*" . $get[1] . "*" . SECURITY_PHRASE) === $get[2]) and (strpos($get[0] . $get[1], "..") === FALSE))
		{
			define("GALLERY", $get[0]);
			define("IMAGE", $get[1]);
			$get_set = TRUE;
		}
	}
	if (!$get_set)
	{
		define("GALLERY", "");
		define("IMAGE", "");
	}

	if (isset($_GET["cmd"]))
	{
	
		if ($_GET["cmd"] == "css")
		{
			header("Content-type: text/css");
			echo "

			img
			{
				-ms-interpolation-mode : bicubic;
			}

			body.sfpg
			{
				background : $color_body_back;
				color: $color_body_text;
				font-family: Arial, Helvetica, sans-serif;
				font-size: ".FONT_SIZE."px;
				font-weight: normal;
				margin:0px;
				padding:0px;
				overflow:hidden;
			}

			body.sfpg a:active, body.sfpg a:link, body.sfpg a:visited, body.sfpg a:focus
			{
				color : $color_body_link;
				text-decoration : none;
			}

			body.sfpg a:hover
			{
				color : $color_body_hover;
				text-decoration : none;
			}

			table
			{
				font-size: ".FONT_SIZE."px;
				height:100%;
				width:100%;
			}

			table.info td
			{
				padding : 10px;
				vertical-align : top;
			}

			table.sfpg_disp
			{
				text-align : center;
				padding : 0px;
			}

			table.sfpg_disp td.menu
			{
				background : #000000;
				border-top : 1px solid #303030;
				vertical-align : middle;
				white-space: nowrap;
			}

			table.sfpg_disp td.navi
			{
				height: ".NAV_BAR_HEIGHT."px;
				background : #202020;
				border-top : 1px solid #303030;
				vertical-align : middle;
				white-space: nowrap;
			}

			table.sfpg_disp td.mid
			{
				vertical-align : middle;
			}

			.sfpg_info_text, .loading
			{
				background : #000000;
				border : 1px solid #606060;
				color : #aaaaaa;
				padding : 1px 4px 1px 4px;
				width : 200px;
			}
			
			.loading
			{
				padding : 20px 20px 20px 20px;
				margin-right: auto;
				margin-left: auto;
			}
			
			.sfpg_button, .sfpg_button_hover, .sfpg_button_on, .sfpg_button_nav, .sfpg_button_disabled
			{
				cursor : pointer;
				background : $color_button_back;
				border : 1px solid $color_button_border;
				color : $color_button_text;
				padding : 0px 5px 0px 5px;
				margin : 0px 5px 0px 5px;
				white-space: nowrap;
			}

			.sfpg_button_hover
			{
				background : $color_button_hover;
				color : $color_button_hover_text;
			}

			.sfpg_button_on
			{
				background : $color_button_on;
				color : $color_button_text_on;
			}

			.sfpg_button_disabled
			{
				cursor : default;
				border : 1px solid $color_button_border_off;
				background : $color_button_back_off;
				color : $color_button_text_off;
			}

			.sfpg_button_nav
			{
				border : 1px solid #404040;
				background:#101010;
				color:#808080;
			}

			.thumbbox
			{
				vertical-align : top;
				display:-moz-inline-stack;
				display:inline-block;
				zoom:1;
				*display:inline;
				width: " . ((2 * (THUMB_BORDER_WIDTH + THUMB_MARGIN + THUMB_BOX_MARGIN)) + THUMB_MAX_WIDTH + 2) . "px;
				height: " . ((2 * (THUMB_BORDER_WIDTH + THUMB_MARGIN + THUMB_BOX_MARGIN)) + THUMB_MAX_HEIGHT + 2 + THUMB_BOX_EXTRA_HEIGHT) . "px;
				margin: 0px;
				padding: 0px;
			}

			.thumbimgbox
			{
				width: " . ((2 * (THUMB_BORDER_WIDTH + THUMB_MARGIN)) + THUMB_MAX_WIDTH) . "px;
				height: " . ((THUMB_BORDER_WIDTH * 2) + THUMB_MARGIN + THUMB_MAX_HEIGHT + 6) . "px;
				margin: 0px;
				padding: 0px;
			}
			
			.innerboxdir, .innerboximg, .innerboxfile, .innerboxdir_hover, .innerboximg_hover, .innerboxfile_hover
			{
				cursor:pointer;
				margin: " . THUMB_BOX_MARGIN . "px;
				padding: 0px;
				width: " . ((2 * (THUMB_BORDER_WIDTH + THUMB_MARGIN)) + THUMB_MAX_WIDTH + 2) . "px;
				height: " . ((2 * (THUMB_BORDER_WIDTH + THUMB_MARGIN)) + THUMB_MAX_HEIGHT + 2 + THUMB_BOX_EXTRA_HEIGHT) . "px;
			}

			.innerboxdir, .innerboxdir_hover
			{
				border: 1px solid $color_dir_box_border;
				border-radius: 0.5em;
				-moz-border-radius: 0.5em;
				-webkit-border-radius: 0.5em;
				background-overflow: clip;
				background : $color_dir_box_back;
				color : $color_dir_box_text;
			}

			.innerboximg, .innerboximg_hover
			{
				border: 1px solid $color_img_box_border;
				border-radius: 0.5em;
				-moz-border-radius: 0.5em;
				-webkit-border-radius: 0.5em;
				background-overflow: clip;
				background : $color_img_box_back;
				color : $color_img_box_text;
			}

			.innerboxfile, .innerboxfile_hover
			{
				border: 1px solid $color_file_box_border;
				background : $color_file_box_back;
				color : $color_file_box_text;
			}

			.innerboxdir_hover
			{
				background : $color_dir_hover;
				color : $color_dir_hover_text;
			}

			.innerboximg_hover
			{
				background : $color_img_hover;
				color : $color_img_hover_text;
			}

			.innerboxfile_hover
			{
				background : $color_file_hover;
				color : $color_file_hover_text;
			}

			.full_image
			{
				cursor:pointer;
				border : ".FULLIMG_BORDER_WIDTH."px solid $color_fullimg_border;
			}

			.thumb
			{
				margin: " . THUMB_MARGIN . "px " . THUMB_MARGIN . "px 5px " . THUMB_MARGIN . "px;
				border : ".THUMB_BORDER_WIDTH."px solid $color_thumb_border;
				border-radius: 0.5em;
				-moz-border-radius: 0.5em;
				-webkit-border-radius: 0.5em;
				background-overflow: clip;
			}

			.box_image
			{
				position:absolute;
				bottom:".MENU_BOX_HEIGHT."px;
				right:0;
				z-index:1020;
				overflow:auto;
				visibility:hidden;
				text-align : center;
			}

			.box_wait
			{
				position:absolute;
				bottom:".MENU_BOX_HEIGHT."px;
				right:0;
				z-index:1015;
				overflow:auto;
				visibility:hidden;
				text-align : center;
			}

			.box_navi
			{
				position:absolute;
				bottom:0;
				left:0;
				height:".MENU_BOX_HEIGHT."px;
				width:100%;
				z-index:1120;
				overflow:hidden;
				text-align : center;
			}

			.box_info
			{
				position:absolute;
				top:10px;
				left:10px;
				width:".INFO_BOX_WIDTH."px;
				z-index:1040;
				visibility:hidden;
				overflow:auto;
				border : 1px solid #404040;
				background: #101010;
			}

			.box_overlay
			{
				position:absolute;
				bottom:".MENU_BOX_HEIGHT."px;
				left:0;
				height:100%;
				width:100%;
				z-index:1010;
				overflow:hidden;
				visibility:hidden;
				background:$color_overlay;
			}

			.box_gallery
			{
				text-align:center;
				position:absolute;
				top:0;
				right:0;
				z-index:1000;
				overflow:auto;
			}
			";
			exit;
		}


		if ($_GET["cmd"] == "thumb")
		{
			sfpg_image(GALLERY, IMAGE, "thumb");
			exit;
		}


		if ($_GET["cmd"] == "preview")
		{
			if (USE_PREVIEW)
			{
				sfpg_image(GALLERY, IMAGE, "preview");
			}
			exit;
		}


		if ($_GET["cmd"] == "image")
		{
			sfpg_image(GALLERY, IMAGE, "image");
			exit;
		}


		if (($_GET["cmd"] == "dl") and TEXT_DOWNLOAD)
		{
			sfpg_image(GALLERY, IMAGE, "image", TRUE);
			exit;
		}


		if ($_GET["cmd"] == "file")
		{
			header("Location: " . GALLERY_ROOT . GALLERY . IMAGE);
			exit;
		}

	}

	list($dirs, $images, $files) = sfpg_get_dir(GALLERY);

	echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\"><html><head>" .
	"<link rel=\"stylesheet\" type=\"text/css\" href=\"" . $_SERVER["PHP_SELF"] . "?cmd=css\">" .
	"<meta http-equiv=\"Content-Type\" content=\"text/html;charset=" . CHARSET . "\"><title>" . TEXT_GALLERY_NAME . "</title>";
	sfpg_javascript();
	echo "</head><body onresize='initDisplay()' onload='showGallery(".(OPEN_IMAGE_ON_LOAD ? OPEN_IMAGE_ON_LOAD : "false").")' class=\"sfpg\">" .

	"<div id=\"box_navi\" class=\"box_navi\">" .
		"<table class=\"sfpg_disp\" cellspacing=\"0\">" .
			"<tr><td class=\"navi\">" .
				"<div id=\"navi\"></div>" .
			"</td></tr>" .
			"<tr><td class=\"menu\">" .
				"<div id=\"div_menu\"></div>" .
			"</td></tr>" .
		"</table>" .
	"</div>" .

	"<div id=\"box_image\" class=\"box_image\">" .
		"<table class=\"sfpg_disp\" cellspacing=\"0\">" .
			"<tr><td class=\"mid\">" .
				"<img alt=\"\" src=\"\" id=\"full\" class=\"full_image\" onclick=\"closeImageView()\">" .
			"</td></tr>" .
		"</table>" .
	"</div>" .

	"<div id=\"box_wait\" class=\"box_wait\">" .
		"<table class=\"sfpg_disp\" cellspacing=\"0\">" .
			"<tr><td class=\"mid\">" .
				"<div id=\"wait\"></div>" .
			"</td></tr>" .
		"</table>" .
	"</div>" .

	"<div id=\"box_info\" class=\"box_info\">" .
		"<table class=\"info\" cellspacing=\"0\">" .
			"<tr><td>" .
				"<div id=\"box_inner_info\"></div>" .
			"</td></tr>" .
		"</table>" .
	"</div>" .

	"<div id=\"box_gallery\" class=\"box_gallery\"></div>" .

	"<div id=\"box_overlay\" class=\"box_overlay\"></div>" .

	"</body></html>";

?>