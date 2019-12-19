<?

// Define the class that will act as the wrapper for all image utility functions
class cms_image {

    // Define the public variables
    public $MESSAGES;
    public $FILETYPES;

    // Define the class constructor
    public function cms_image(){

        // Initialize the MESSAGES stack if there are any
        $this->MESSAGES = array();

        // Initialize all the FILETYPES to be used in the script
        $this->filetypes(array(
            array('JPG', 'image/jpeg', 'jpg'),
            array('GIF', 'image/gif', 'gif'),
            array('PNG', 'image/png', 'png'),
            array('BMP', 'image/bmp', 'bmp'),
            array('TIFF', 'image/tiff', 'tiff'),
            array('ICO', 'image/x-icon', 'ico'),
            array('WBMP', 'image/vnd.wap.wbmp', 'wbmp'),
            array('JP2', 'image/jp2', 'jp2'),
            array('SWF', 'application/x-shockwave-flash', 'swf'),
            array('PSD', 'application/octet-stream', 'psd'),
            array('PDF', 'application/pdf', 'pdf'),
            array('DOC', 'application/msword', 'doc'),
            array('ZIP', 'application/zip', 'zip'),
            array('RAR', 'application/rar', 'rar'),
            array('AVI', 'video/x-msvideo', 'avi'),
            array('MOV', 'video/quicktime', 'mov'),
            array('MP4', 'video/mp4', 'mp4'),
            array('MPEG', 'video/mpeg', 'mpeg'),
            array('MPG', 'video/x-flv', 'mpg'),
            array('FLV', 'video/mpeg', 'flv'),
            array('MP3', 'audio/mp3', 'mp3'),
            array('WAV', 'audio/wav', 'wav'),
            array('OGG', 'application/ogg', 'ogg'),
            array('MID', 'audio/midi', 'mid'),
            array('MP2', 'audio/mpeg', 'mp2'),
            array('TXT', 'text/plain', 'txt'),
            array('TEXT', 'text/plain', 'text'),
            array('RTF', 'text/richtext', 'rtf'),
            array('HTML', 'text/html', 'html'),
            array('SHTML', 'text/html', 'shtml'),
            array('XML', 'text/xml', 'xml'),
            array('PHP', 'text/php', 'php'),
            array('CSS', 'text/css', 'css'),
            array('JS', 'text/javascript', 'js'),
            array('LOG', 'text/plain', 'log'),
            array('ASP', 'text/asp', 'asp'),
            array('XLS', 'application/vnd.ms-excel', 'xls'),
            array('UNKNOWN', 'unknown/not-supported', '')
            ));
    }

    /*
     * FILE ACCESS/MANIPULATION FEATURES
     */

    // Define a function for pulling or defining file types
    public function filetypes(){
        // Collect any arguments passed to the function
        $args = func_get_args();
        $args_count = is_array($args) ? count($args) : 0;
        // If there were no arguments provided, return the entire FILETYPES array
        if (!$args_count){
            return $this->FILETYPES;
        }
        // Else if there was a single array provided, loop through collecting types
        elseif ($args_count == 1 && is_array($args[0])){
            // Loop through each entry and add the type to the FILETYPES array
            foreach ($args[0] AS $type){
                // Pull the details of the item
                $type['ID'] = isset($type[0]) ? $type[0] : '';
                $type['MIME'] = isset($type[1]) ? $type[1] : '';
                $type['EXTENSION'] = isset($type[2]) ? $type[2] : '';
                // If the type ID is empty, continue
                if (empty($type['ID'])) { continue; }
                // Add this type to the FILETYPES array
                $this->FILETYPES[$type['ID']] = $type;
            }
        }
        // Else if there are 1-3 arguments of string, string, and string types
        elseif ($args_count >= 1 && $args_count <= 3){
            // Pull the details of the item
            $type = array();
            $type['ID'] = isset($type[0]) ? $type[0] : '';
            $type['MIME'] = isset($type[1]) ? $type[1] : '';
            $type['EXTENSION'] = isset($type[2]) ? $type[2] : '';
            // If the type ID is empty, break
            if (empty($type['ID'])){
                $this->message("[[cms_image::filetypes]] : A file type identifier was not provided.", CMS_IMAGE_ERROR);
                return false;
            }
            // Add this type to the FILETYPES array
            $this->FILETYPES[$type['ID']] = $type;
        }
        // Otherwise, this is an invalid call and should return false
        else {
            $this->message("[[cms_image::filetypes]] : An invalid number of arguments was provided.", CMS_IMAGE_NOTICE);
            return false;
        }
    }

    // Define a function for determining a given file's type
    public function filetype($file_path, $file_name = ''){
        // If the file path is empty, skip
        if (empty($file_path)){
            $this->message("[[cms_image::filetype]] : No file path was specified.", CMS_IMAGE_ERROR);
            return false;
        }
        // Check to make sure the file exists
        elseif (!file_exists($file_path)){
            $this->message("[[cms_image::filetype]] : The file path <<{$file_path}>> does not exist.", CMS_IMAGE_ERROR);
            return false;
        }
        // Check to make sure the file is NOT a directory
        elseif (is_dir($file_path)){
            $this->message("[[cms_image::filetype]] : The file path <<{$file_path}>> is not a file.", CMS_IMAGE_ERROR);
            return false;
        }
        // Check if the filetype is an image
        if (list($width, $height, $type) = @getimagesize($file_path)){
            if ($type == IMAGETYPE_JPEG) { return $this->FILETYPES['JPG']; }
            elseif ($type == IMAGETYPE_GIF) { return $this->FILETYPES['GIF']; }
            elseif ($type == IMAGETYPE_PNG) { return $this->FILETYPES['PNG']; }
            elseif ($type == IMAGETYPE_BMP) { return $this->FILETYPES['BMP']; }
            elseif ($type == IMAGETYPE_WBMP) { return $this->FILETYPES['WBMP']; }
            elseif (defined('IMAGETYPE_ICO') && $type == IMAGETYPE_ICO) { return $this->FILETYPES['ICO']; }
            elseif ($type == IMAGETYPE_TIFF_II || $type == IMAGETYPE_TIFF_MM) { return $this->FILETYPES['TIFF']; }
            elseif ($type == IMAGETYPE_JPEG2000) { return $this->FILETYPES['JP2']; }
            elseif ($type == IMAGETYPE_SWF) { return $this->FILETYPES['SWF']; }
            elseif ($type == IMAGETYPE_PSD) { return $this->FILETYPES['PSD']; }
        }
        // Convert the filename to lowercase for extension analyzing
        preg_match('#/?([^/\\\]+)$#i', $file_path, $matches);
        $file_name = !empty($file_name) ? strtolower($file_name) : strtolower($matches[1]);
        // Check if document-type
        if (preg_match('#(.txt)$#i', $file_name)) { return $this->FILETYPES['TXT']; }
        elseif (preg_match('#(.rtf|.rt)$#i', $file_name)) { return $this->FILETYPES['RTF']; }
        elseif (preg_match('#(.doc)$#i', $file_name)) { return $this->FILETYPES['DOC']; }
        elseif (preg_match('#(.pdf)$#i', $file_name)) { return $this->FILETYPES['PDF']; }
        elseif (preg_match('#(.htm|.html|.htmlx)$#i', $file_name)) { return $this->FILETYPES['HTML']; }
        elseif (preg_match('#(.shtml|.shtm|.htmls)$#i', $file_name)) { return $this->FILETYPES['SHTML']; }
        elseif (preg_match('#(.css)$#i', $file_name)) { return $this->FILETYPES['CSS']; }
        elseif (preg_match('#(.js)$#i', $file_name)) { return $this->FILETYPES['JS']; }
        elseif (preg_match('#(.text)$#i', $file_name)) { return $this->FILETYPES['TEXT']; }
        elseif (preg_match('#(.xml)$#i', $file_name)) { return $this->FILETYPES['XML']; }
        elseif (preg_match('#(.log)$#i', $file_name)) { return $this->FILETYPES['LOG']; }
        elseif (preg_match('#(.php)$#i', $file_name)) { return $this->FILETYPES['PHP']; }
        elseif (preg_match('#(.asp)$#i', $file_name)) { return $this->FILETYPES['ASP']; }
        elseif (preg_match('#(.xls)$#i', $file_name)) { return $this->FILETYPES['XLS']; }
        // Check if uncaught image type
        elseif (preg_match('#(.jpg|.jpeg)$#i', $file_name)) { return $this->FILETYPES['JPG']; }
        elseif (preg_match('#(.gif)$#i', $file_name)) { return $this->FILETYPES['GIF']; }
        elseif (preg_match('#(.png)$#i', $file_name)) { return $this->FILETYPES['PNG']; }
        elseif (preg_match('#(.bmp)$#i', $file_name)) { return $this->FILETYPES['BMP']; }
        elseif (preg_match('#(.wbmp)$#i', $file_name)) { return $this->FILETYPES['WBMP']; }
        elseif (preg_match('#(.ico)$#i', $file_name)) { return $this->FILETYPES['ICO']; }
        elseif (preg_match('#(.tiff|.tif)$#i', $file_name)) { return $this->FILETYPES['TIFF']; }
        elseif (preg_match('#(.jp2|.jpg2|.jpeg2)$#i', $file_name)) { return $this->FILETYPES['JP2']; }
        elseif (preg_match('#(.swf)$#i', $file_name)) { return $this->FILETYPES['SWF']; }
        elseif (preg_match('#(.psd)$#i', $file_name)) { return $this->FILETYPES['PSD']; }
        // Check if audio-type
        elseif (preg_match('#(.mp3)$#i', $file_name)) { return $this->FILETYPES['MP3']; }
        elseif (preg_match('#(.wav)$#i', $file_name)) { return $this->FILETYPES['WAV']; }
        elseif (preg_match('#(.ogg)$#i', $file_name)) { return $this->FILETYPES['OGG']; }
        elseif (preg_match('#(.mid|.midi)$#i', $file_name)) { return $this->FILETYPES['MIDI']; }
        elseif (preg_match('#(.mp2)$#i', $file_name)) { return $this->FILETYPES['MP2']; }
        // Check if video type
        elseif (preg_match('#(.avi)$#i', $file_name)) { return $this->FILETYPES['AVI']; }
        elseif (preg_match('#(.mov)$#i', $file_name)) { return $this->FILETYPES['MOV']; }
        elseif (preg_match('#(.mpg)$#i', $file_name)) { return $this->FILETYPES['MPG']; }
        elseif (preg_match('#(.mpeg)$#i', $file_name)) { return $this->FILETYPES['MPEG']; }
        elseif (preg_match('#(.mp4)$#i', $file_name)) { return $this->FILETYPES['MP4']; }
        elseif (preg_match('#(.flv)$#i', $file_name)) { return $this->FILETYPES['FLV']; }
        // Check a few random others
        elseif (preg_match('#(.zip)$#i', $file_name)) { return $this->FILETYPES['ZIP']; }
        elseif (preg_match('#(.rar)$#i', $file_name)) { return $this->FILETYPES['RAR']; }
        // Otherwise, return 'unknown'
        else { return $this->FILETYPES['UNKNOWN']; }
    }

    /*
     * COLOUR / GRAPHIC FUNCTIONS
     */

    // Define a function for converting a HEX value to an RGB array
    public function colour_hex2rgb($src_value){
        return $this->color_hex2rgb($src_value);
    }
    public function color_hex2rgb($src_value){
        if (!is_string($src_value)) { $this->message("[[cms_image::color_hex2rgb]] : Source value is not a string.", CMS_IMAGE_ERROR); return false; }
        $src_value = str_replace('#','', trim($src_value));
        if (!eregi("^[0-9ABCDEFabcdef]+$", $src_value)) { $this->message("[[cms_image::color_hex2rgb]] : Source value is not in HEX format as it contains illegal characters <<{$src_value}>>.", CMS_IMAGE_ERROR); return false; }
        if (strlen($src_value) == 3) { $groups = 1; }
        elseif (strlen($src_value) == 6) { $groups = 2; }
        else { $this->message("[[cms_image::color_hex2rgb]] : Source value is not in HEX format as it contains an invalid number of digits <<{$src_value}>>.", CMS_IMAGE_ERROR); return false; }
        $dst_value = array();
        $dst_value[0] = $dst_value['r'] = $dst_value['red'] = hexdec(substr($src_value, 0,1*$groups));
        $dst_value[1] = $dst_value['g'] = $dst_value['green'] = hexdec(substr($src_value, 1*$groups,1*$groups));
        $dst_value[2] = $dst_value['b'] = $dst_value['blue'] = hexdec(substr($src_value, 2*$groups,1*$groups));
        return $dst_value;
    }

    // Define a function for converting an RGB array to a HEX value
    public function colour_rgb2hex($src_value){
        return $this->color_rgb2hex($src_value);
    }
    public function color_rgb2hex($src_value){
        if (!is_array($src_value)) { $this->message("[[cms_image::color_rgb2hex]] : Source value is not an array.", CMS_IMAGE_ERROR); return false; }
        if (count($src_value) % 3 != 0) { $this->message("[[cms_image::color_rgb2hex]] : Source value contains an invalid number of arguments <<".implode(',',$src_value).">>.", CMS_IMAGE_ERROR); return false; }
        $src_value[0] = $src_value['r'] = $src_value['red'] = isset($src_value['red']) ? $src_value['red'] : (isset($src_value['r']) ? $src_value['r'] : (isset($src_value[0]) ? $src_value[0] : 0));
        $src_value[1] = $src_value['g'] = $src_value['green'] = isset($src_value['green']) ? $src_value['green'] : (isset($src_value['g']) ? $src_value['g'] : (isset($src_value[1]) ? $src_value[1] : 0));
        $src_value[2] = $src_value['b'] = $src_value['blue'] = isset($src_value['blue']) ? $src_value['blue'] : (isset($src_value['b']) ? $src_value['b'] : (isset($src_value[2]) ? $src_value[2] : 0));
        $dst_value = '#';
        $dst_value .= str_pad(dechex($src_value['red']), 2, '0', STR_PAD_LEFT);
        $dst_value .= str_pad(dechex($src_value['green']), 2, '0', STR_PAD_LEFT);
        $dst_value .= str_pad(dechex($src_value['blue']), 2, '0', STR_PAD_LEFT);
        $dst_value = strtoupper($dst_value);
        return $dst_value;
    }

    // Define a function for lightening the perceived brightness of a colour (HEX or RGB)
    public function colour_lighten($colour, $percent){
        return $this->color_lighten($colour, $percent);
    }
    public function color_lighten($color, $percent){
        // Verify the percent value and the variable type
        if (!is_numeric($percent)) { $this->message("[[cms_image::color_lighten]] : Percentage to lighten is not numeric.", CMS_IMAGE_ERROR); return false; }
        if (!is_array($color) && !is_string($color)) { $this->message("[[cms_image::color_lighten]] : Source color value is not in a recognized format.", CMS_IMAGE_ERROR); return false; }
        if ($percent == 0) { return $color; }
        // If this is a string, make sure it's a HEX value
        if (is_string($color)){
            $rgb = $this->color_hex2rgb($color);
            if (!isset($rgb['red']) || !isset($rgb['green']) || !isset($rgb['blue'])) { $this->message("[[cms_image::color_lighten]] : Source color value is not in a recognized format (incorrectly assumed HEX format).", CMS_IMAGE_ERROR); return false; }
            $return_format = 'HEX';
        }
        // If this is an array, make sure it contains the necessary RGB values
        elseif (is_array($color)){
            if (count($color) % 3 != 0) { $this->message("[[cms_image::color_lighten]] : Source color value is not in a recognized format (incorrectly assumed RGB format).", CMS_IMAGE_ERROR); return false; }
            $rgb = $color;
            $rgb[0] = $rgb['r'] = $rgb['red'] = isset($rgb['red']) ? $rgb['red'] : (isset($rgb['r']) ? $rgb['r'] : (isset($rgb[0]) ? $rgb[0] : 0));
            $rgb[1] = $rgb['g'] = $rgb['green'] = isset($rgb['green']) ? $rgb['green'] : (isset($rgb['g']) ? $rgb['g'] : (isset($rgb[1]) ? $rgb[1] : 0));
            $rgb[2] = $rgb['b'] = $rgb['blue'] = isset($rgb['blue']) ? $rgb['blue'] : (isset($rgb['b']) ? $rgb['b'] : (isset($rgb[2]) ? $rgb[2] : 0));
            if (!isset($rgb['red']) || !isset($rgb['green']) || !isset($rgb['blue'])) { $this->message("[[cms_image::color_lighten]] : Source color value is not in a recognized format (incorrectly assumed RGB format).", CMS_IMAGE_ERROR); return false; }
            $return_format = 'RGB';
        }
        // Create the new RGB values by multiplying each by the percent
        $red = round($rgb['red'] * (1+($percent/100)));
        $blue = round($rgb['blue'] * (1+($percent/100)));
        $green = round($rgb['green'] * (1+($percent/100)));
        // Collect the overflow form any value over 255
        $overflow = 0;
        if ($red > 255) { $overflow += $red - 255; $red = 255; }
        elseif ($red < 0) { $overflow -= $red * -1; $red = 0; }
        if ($blue > 255) { $overflow += $blue - 255; $blue = 255; }
        elseif ($blue < 0) { $overflow -= $blue * -1; $blue = 0; }
        if ($green > 255) { $overflow += $green - 255; $green = 255; }
        elseif ($green < 0) { $overflow -= $green * -1; $green = 0; }
        // Distribute the overflow evenly to the other colours
        if ($overflow > 0){
            $overflow_each = round($overflow / 3);
            $red += $overflow_each;
            $blue += $overflow_each;
            $green += $overflow_each;
        }
        // And now re-cap them at 0-255 in case any went over
        if ($red > 255) { $red = 255; }
        elseif ($red < 0) { $red = 0; }
        if ($blue > 255) { $blue = 255; }
        elseif ($blue < 0) { $blue = 0; }
        if ($green > 255) { $green = 255; }
        elseif ($green < 0) { $green = 0; }
        // Return the value in the format it was submit
        if ($return_format == 'HEX'){
            $return_color = $this->color_rgb2hex(array($red, $green, $blue));
            return $return_color;
        }
        elseif ($return_format == 'RGB'){
            $return_color = array();
            $return_color[0] = $return_color['r'] = $return_color['red'] = $red;
            $return_color[1] = $return_color['g'] = $return_color['green'] = $green;
            $return_color[2] = $return_color['b'] = $return_color['blue'] = $blue;
            return $return_color;
        }
    }

    // Define a function for darkening the perceived brightness of a colour (HEX or RGB)
    public function colour_darken($colour, $percent){
        return $this->color_darken($colour, $percent);
    }
    public function color_darken($color, $percent){
        // Verify the percent value and the variable type
        if (!is_numeric($percent)) { $this->message("[[cms_image::color_darken]] : Percentage to lighten is not numeric.", CMS_IMAGE_ERROR); return false; }
        if (!is_array($color) && !is_string($color)) { $this->message("[[cms_image::color_darken]] : Source color value is not in a recognized format.", CMS_IMAGE_ERROR); return false; }
        if ($percent == 0) { return $color; }
            // If this is a string, make sure it's a HEX value
        if (is_string($color)){
            $rgb = $this->color_hex2rgb($color);
            if (!isset($rgb['red']) || !isset($rgb['green']) || !isset($rgb['blue'])) { $this->message("[[cms_image::color_darken]] : Source color value is not in a recognized format (incorrectly assumed HEX format).", CMS_IMAGE_ERROR); return false; }
            $return_format = 'HEX';
        }
        // If this is an array, make sure it contains the necessary RGB values
        elseif (is_array($color)){
            if (count($color) % 3 != 0) { $this->message("[[cms_image::color_darken]] : Source color value is not in a recognized format (incorrectly assumed RGB format).", CMS_IMAGE_ERROR); return false; }
            $rgb = $color;
            $rgb[0] = $rgb['r'] = $rgb['red'] = isset($rgb['red']) ? $rgb['red'] : (isset($rgb['r']) ? $rgb['r'] : (isset($rgb[0]) ? $rgb[0] : 0));
            $rgb[1] = $rgb['g'] = $rgb['green'] = isset($rgb['green']) ? $rgb['green'] : (isset($rgb['g']) ? $rgb['g'] : (isset($rgb[1]) ? $rgb[1] : 0));
            $rgb[2] = $rgb['b'] = $rgb['blue'] = isset($rgb['blue']) ? $rgb['blue'] : (isset($rgb['b']) ? $rgb['b'] : (isset($rgb[2]) ? $rgb[2] : 0));
            if (!isset($rgb['red']) || !isset($rgb['green']) || !isset($rgb['blue'])) { $this->message("[[cms_image::color_darken]] : Source color value is not in a recognized format (incorrectly assumed RGB format).", CMS_IMAGE_ERROR); return false; }
            $return_format = 'RGB';
        }
        // Create the new RGB values by multiplying each by the percent
        $red = round($rgb['red'] * (1-($percent/100)));
        $blue = round($rgb['blue'] * (1-($percent/100)));
        $green = round($rgb['green'] * (1-($percent/100)));
        // Collect the overflow form any value over 255
        $overflow = 0;
        if ($red > 255) { $overflow += $red - 255; $red = 255; }
        elseif ($red < 0) { $overflow -= $red * -1; $red = 0; }
        if ($blue > 255) { $overflow += $blue - 255; $blue = 255; }
        elseif ($blue < 0) { $overflow -= $blue * -1; $blue = 0; }
        if ($green > 255) { $overflow += $green - 255; $green = 255; }
        elseif ($green < 0) { $overflow -= $green * -1; $green = 0; }
        // Distribute the overflow evenly to the other colours
        if ($overflow > 0){
            $overflow_each = round($overflow / 3);
            $red += $overflow_each;
            $blue += $overflow_each;
            $green += $overflow_each;
        }
        // And now re-cap them at 0-255 in case any went over
        if ($red > 255) { $red = 255; }
        elseif ($red < 0) { $red = 0; }
        if ($blue > 255) { $blue = 255; }
        elseif ($blue < 0) { $blue = 0; }
        if ($green > 255) { $green = 255; }
        elseif ($green < 0) { $green = 0; }
        // Return the value in the format it was submit
        if ($return_format == 'HEX'){
            $return_color = $this->color_rgb2hex(array($red, $green, $blue));
            return $return_color;
        }
        elseif ($return_format == 'RGB'){
            $return_color = array();
            $return_color[0] = $return_color['r'] = $return_color['red'] = $red;
            $return_color[1] = $return_color['g'] = $return_color['green'] = $green;
            $return_color[2] = $return_color['b'] = $return_color['blue'] = $blue;
            return $return_color;
        }
    }

    // Define a function for inverting a colour
    public function colour_invert($colour){
        return $this->color_invert($colour);
    }
    public function color_invert($color){
        // Verify the color variable type
        if (!is_array($color) && !is_string($color)) { $this->message("[[cms_image::color_invert]] : Source color value is not in a recognized format.", CMS_IMAGE_ERROR); return false; }
        // If this is a string, make sure it's a HEX value
        if (is_string($color)){
            $rgb = $this->color_hex2rgb($color);
            if (!isset($rgb['red']) || !isset($rgb['green']) || !isset($rgb['blue'])) { $this->message("[[cms_image::color_invert]] : Source color value is not in a recognized format (incorrectly assumed HEX format).", CMS_IMAGE_ERROR); return false; }
            $return_format = 'HEX';
        }
        // If this is an array, make sure it contains the necessary RGB values
        elseif (is_array($color)){
            if (count($color) % 3 != 0) { return false; }
            $rgb = $color;
            $rgb[0] = $rgb['r'] = $rgb['red'] = isset($rgb['red']) ? $rgb['red'] : (isset($rgb['r']) ? $rgb['r'] : (isset($rgb[0]) ? $rgb[0] : 0));
            $rgb[1] = $rgb['g'] = $rgb['green'] = isset($rgb['green']) ? $rgb['green'] : (isset($rgb['g']) ? $rgb['g'] : (isset($rgb[1]) ? $rgb[1] : 0));
            $rgb[2] = $rgb['b'] = $rgb['blue'] = isset($rgb['blue']) ? $rgb['blue'] : (isset($rgb['b']) ? $rgb['b'] : (isset($rgb[2]) ? $rgb[2] : 0));
            if (!isset($rgb['red']) || !isset($rgb['green']) || !isset($rgb['blue'])) { $this->message("[[cms_image::color_invert]] : Source color value is not in a recognized format (incorrectly assumed RGB format).", CMS_IMAGE_ERROR); return false; }
            $return_format = 'RGB';
        }
        // Create the new RGB values by subtracting the difference from 255
        $red = 255 - $rgb['red'];
        $blue = 255 - $rgb['blue'];
        $green = 255 - $rgb['green'];
        // Return the value in the format it was submit
        if ($return_format == 'HEX'){
            $return_color = $this->color_rgb2hex(array($red, $green, $blue));
            return $return_color;
        }
        elseif ($return_format == 'RGB'){
            $return_color = array();
            $return_color[0] = $return_color['r'] = $return_color['red'] = $red;
            $return_color[1] = $return_color['g'] = $return_color['green'] = $green;
            $return_color[2] = $return_color['b'] = $return_color['blue'] = $blue;
            return $return_color;
        }
    }

    // Define a function for creating a new image based on an existing source
    public function image_create($source_path, $export_path, $export_type = '', $export_width = '', $export_height = '', $options = array(), $filters = array()){
        // Attempt to increase the memory limit
        @ini_set("memory_limit", '500M');
        // Define allowable values for mandatory fields
        $allowed_types = array('JPG', 'PNG', 'GIF');
        // Ensure the $source_path exists
        if (empty($source_path)){ $this->message("[[cms_image::image_create]] : Source image path was not provided.", CMS_IMAGE_ERROR); return false; }
        elseif (!file_exists($source_path)){ $this->message("[[cms_image::image_create]] : Source image path <<{$source_path}>> does not exist.", CMS_IMAGE_ERROR); return false; }
        // Ensure the $export_path exists
        @preg_match('#^(.+?)([^/\\\]+)$#i', $export_path, $matches);
        if (empty($export_path)){ $this->message("[[cms_image::image_create]] : Export image path was not provided.", CMS_IMAGE_ERROR); return false; }
        elseif (!empty($matches[1]) && !file_exists($matches[1])){ $this->message("[[cms_image::image_create]] : Export image path <<{$matches[1]}>> does not exist.", CMS_IMAGE_ERROR); return false; }
        // Collect the file type for the source file
        $source_type = $this->filetype($source_path);
        if (!in_array($source_type['ID'], array('JPG','PNG','GIF'))){ $this->message("[[cms_image::image_create]] : Provided source file type <<{$source_type['ID']}>> was not a valid image format.", CMS_IMAGE_ERROR); return false; }
        // Collect/reformat the file type for the export file
        if (!empty($export_type) && is_string($export_type)){ $export_type = isset($this->FILETYPES[$export_type]) ? $this->FILETYPES[$export_type] : ''; }
        elseif (!empty($export_type) && is_array($export_type)){ $id = isset($export_type['ID']) ? $export_type['ID'] : $export_type[0]; $export_type = isset($this->FILETYPES[$id]) ? $this->FILETYPES[$id] : '';  }
        if (!$export_type) { $export_type = $source_type; }
        // Collect the source image dimensions
        list($source_width, $source_height) = getimagesize($source_path);
        // Ensure the options are in array format, else define default
        if (!is_array($options)){ $options = array(); }
        // Ensure the filters are in array format, else define default
        if (!is_array($filters)){ $filters = array(); }
        // Define any option defaults if not set
        $options['crop'] = isset($options['crop']) ? ($options['crop'] ? true : false) : true;
        $options['enlarge'] = isset($options['enlarge']) ? ($options['enlarge'] ? true : false) : true;
        $options['background'] = isset($options['background']) && preg_match('/^#?([a-z0-9]{6})$/i', $options['background']) ? '#'.trim($options['background'], '#') : false;
        $options['halign'] = isset($options['halign']) && is_numeric($options['halign']) ? $options['halign'] : CMS_IMAGE_ALIGN_CENTER;
        $options['valign'] = isset($options['valign']) && is_numeric($options['valign']) ? $options['valign'] : CMS_IMAGE_ALIGN_MIDDLE;
        $options['maxwidth'] = isset($options['maxwidth']) && is_numeric($options['maxwidth']) && $options['maxwidth'] > 0 ? $options['maxwidth'] : false;
        $options['maxheight'] = isset($options['maxheight']) && is_numeric($options['maxheight']) && $options['maxheight'] > 0 ? $options['maxheight'] : false;
        // Collect and reformat any filters into associative arrays
        foreach ($filters AS $key => $filterinfo):
            $filter = array();
            $filter['type'] = isset($filterinfo['type']) ? $filterinfo['type'] : (isset($filterinfo[0]) ? $filterinfo[0] : false);
            $filter['arg1'] = isset($filterinfo['arg1']) ? $filterinfo['arg1'] : (isset($filterinfo[1]) ? $filterinfo[1] : false);
            $filter['arg2'] = isset($filterinfo['arg2']) ? $filterinfo['arg2'] : (isset($filterinfo[2]) ? $filterinfo[2] : false);
            $filter['arg3'] = isset($filterinfo['arg3']) ? $filterinfo['arg3'] : (isset($filterinfo[3]) ? $filterinfo[3] : false);
            $filter['arg4'] = isset($filterinfo['arg4']) ? $filterinfo['arg4'] : (isset($filterinfo[4]) ? $filterinfo[4] : false);
            if ($filter['type'] !== false) { $filters[$key] = $filter; }
        endforeach;
        // Collect or define export image dimensions, defining any missing values
        $export_width = !empty($export_width) && is_numeric($export_width) ? $export_width : '';
        $export_height = !empty($export_height) && is_numeric($export_height) ? $export_height : '';
        if (!$export_width && !$export_height){ $export_width = $source_width; $export_height = $source_height; }
        elseif ($export_width && !$export_height){ $export_height = $this->image_autoheight($source_width, $source_height, $export_width); }
        elseif (!$export_width && $export_height){ $export_width = $this->image_autowidth($source_width, $source_height, $export_height); }
        // If enlarge is set to FALSE, check to ensure this image will not be stretched
        if (!$options['enlarge']){
            if (is_numeric($export_width) && ($export_width > $source_width)){
                $export_width = $source_width;
                $export_height = $this->image_autoheight($source_width, $source_height, $export_width);
            }
            if (is_numeric($export_height) && ($export_height > $source_height)){
                $export_height = $source_height;
                $export_width = $this->image_autowidth($source_width, $source_height, $export_height);
            }
        }
        // If there is a maxwidth or maxheight defined, respect them
        if (!empty($options['maxwidth']) && $export_width > $options['maxwidth']){
            $export_width = $options['maxwidth'];
            $export_height = $this->image_autoheight($source_width, $source_height, $export_width);
        }
        if (!empty($options['maxheight']) && $export_height > $options['maxheight']){
            $export_height = $options['maxheight'];
            $export_width = $this->image_autowidth($source_width, $source_height, $export_height);
        }
        // Create backup variables for the source and export width and height
        $org_source_width = $source_width;
        $org_source_height = $source_height;
        $org_export_width = $export_width;
        $org_export_height = $export_height;
        // Define the source and export X and Y coordinance
        $source_x = $source_y = 0;
        $export_x = $export_y = 0;
        // Determine the aspect ratios for the source and export images
        $source_aspect = $source_width / $source_height;
        $export_aspect = $export_width / $export_height;
        // If enlargement is NOT enabled, prevent stretching of smaller images
        if (!$options['enlarge']){
            // If both the $export_width and $export_height are greater than the $source_width and $source_height
            if ($export_width > $source_width && $export_height > $source_height){
                $org_export_width= $export_width = $source_width;
                $org_export_height= $export_height = $source_height;
            }
            // If the $export_width is greater than the $source_width
            elseif ($export_width > $source_width){
                $org_export_width = $export_height = $this->image_autoheight($export_width, $export_height, $source_width);
                $org_export_height= $export_width = $source_width;
            }
            // If the $export_height is great than the $source_height
            elseif ($export_height > $source_height){
                $org_export_width = $export_width = $this->image_autowidth($export_width, $export_height, $source_height);
                $org_export_height= $export_height = $source_height;
            }
        }
        // If cropping was requested and the two images are different aspects, define the new resolution coordinants
        if ($options['crop'] && $source_aspect != $export_aspect){
            // If the $source_aspect is greater than $export_aspect (source is wider)
            if ($source_aspect > $export_aspect){
                // Leave the $source_height the same
                $source_height = $source_height;
                // Scale the $source_width proportionaly to the $export_width
                $source_width = ceil(($export_width * $org_source_height) / $export_height);
                // Leave the $source_y the same
                $source_y = $source_y;
                // Fix the halign if out of range
                if ($options['halign'] > 100 || $options['halign'] < -100){ $options['halign'] = $options['halign'] % 100; }
                if ($options['halign'] < 0){ $options['halign'] = 100 + $options['halign']; }
                // If the valign if at zero, keep it at the left (0%), otherwise, move it right proportionally
                if ($options['halign'] == 0){ $source_x = 0; }
                elseif ($options['halign'] > 0){ $source_x = ceil(($org_source_width / (100/$options['halign'])) - ($source_width / (100/$options['halign']))); }
            }
            // If the $source_aspect is less than the $export_aspect (source is taller)
            elseif ($source_aspect < $export_aspect){
                // Leave the $source_width the same
                $source_width = $source_width;
                // Scale the $source_height proportionaly to the $export_height
                $source_height = ceil(($org_source_width * $export_height) / $export_width);
                // Leave the $source_x the same
                $source_x = $source_x;
                // Fix the valign if out of range
                if ($options['valign'] > 100 || $options['valign'] < -100){ $options['valign'] = $options['valign'] % 100; }
                if ($options['valign'] < 0){ $options['valign'] = 100 + $options['valign']; }
                // If the valign if at zero, keep it at the top (0%), otherwise, move it down proportionally
                if ($options['valign'] == 0){ $source_y = 0; }
                elseif ($options['valign'] > 0){ $source_y = ceil(($org_source_height / (100/$options['valign'])) - ($source_height / (100/$options['valign']))); }
            }
        }
        // If cropping was NOT requested and the two images are different aspects, define the new resolution coordinants
        if (!$options['crop'] && $source_aspect != $export_aspect){
            // If the $source_aspect is greater than $export_aspect (source is wider)
            if ($source_aspect > $export_aspect){
                // Leave the $export_x the same
                $export_x = $export_x;
                // Leave the $source_width the same
                $source_width = $source_width;
                // Generate the approximate height of the fitted image
                $fit_width = $export_width;
                $fit_height = $this->image_autoheight($source_width, $source_height, $export_width);
                // Fix the valign if out of range
                if ($options['valign'] > 100 || $options['valign'] < -100){ $options['valign'] = $options['valign'] % 100; }
                if ($options['valign'] < 0){ $options['valign'] = 100 + $options['valign']; }
                // If the valign if at zero, keep it at the top (0%), otherwise, move it down proportionally
                if ($options['valign'] == 0){ $export_y = 0; }
                elseif ($options['valign'] > 0){ $export_y = round(($export_height / (100/$options['valign'])) - ($fit_height / (100/$options['valign']))); }
                // Update the $export_height to that of the $fit_height
                $export_height = $fit_height;
            }
            // If the $source_aspect is less than the $export_aspect (source is taller)
            elseif ($source_aspect < $export_aspect){
                // Leave the $export_y the same
                $export_y = $export_y;
                // Leave the $source_height the same
                $source_height = $source_height;
                // Generate the approximate width of the fitted image
                $fit_height = $export_height;
                $fit_width = $this->image_autowidth($source_width, $source_height, $export_height);
                // Fix the halign if out of range
                if ($options['halign'] > 100 || $options['halign'] < -100){ $options['halign'] = $options['halign'] % 100; }
                if ($options['halign'] < 0){ $options['halign'] = 100 + $options['halign']; }
                // If the valign if at zero, keep it at the left (0%), otherwise, move it right proportionally
                if ($options['halign'] == 0){ $export_x = 0; }
                elseif ($options['halign'] > 0){ $export_x = round(($export_width / (100/$options['halign'])) - ($fit_width / (100/$options['halign']))); }
                // Update the $export_width to that of the $fit_width
                $export_width = $fit_width;
            }
        }
        // Create the image link object based on source type
        if ($source_type['ID'] == 'JPG') { $source_link = @imagecreatefromjpeg($source_path); }
        elseif ($source_type['ID'] == 'PNG') { $source_link = @imagecreatefrompng($source_path); @imagealphablending($source_link, true); }
        elseif ($source_type['ID'] == 'GIF') { $source_link = @imagecreatefromgif($source_path); }
        // If there were any filters set, apply them to the image link
        if (!empty($filters)): foreach($filters AS $key => $filterinfo):
            if (defined('IMG_FILTER_COLORIZE') && $filterinfo['type'] == IMG_FILTER_COLORIZE):
                @imagefilter($source_link, $filterinfo['type'], $filterinfo['arg1'], $filterinfo['arg2'], $filterinfo['arg3']);
            elseif (defined('IMG_FILTER_PIXELATE') && $filterinfo['type'] == IMG_FILTER_PIXELATE):
                @imagefilter($source_link, $filterinfo['type'], $filterinfo['arg1'], $filterinfo['arg2']);
            elseif (defined('IMG_FILTER_SMOOTH') && $filterinfo['type'] == IMG_FILTER_SMOOTH):
                @imagefilter($source_link, $filterinfo['type'], $filterinfo['arg1']);
            elseif (defined('IMG_FILTER_CONTRAST') && $filterinfo['type'] == IMG_FILTER_CONTRAST):
                @imagefilter($source_link, $filterinfo['type'], $filterinfo['arg1']);
            elseif (defined('IMG_FILTER_BRIGHTNESS') && $filterinfo['type'] == IMG_FILTER_BRIGHTNESS):
                @imagefilter($source_link, $filterinfo['type'], $filterinfo['arg1']);
            elseif (defined('IMG_FILTER_ALPHA') && $filterinfo['type'] == IMG_FILTER_ALPHA):
                $source_link = self::image_setopacity($source_link, $filterinfo['arg1']);
            else:
                @imagefilter($source_link, $filterinfo['type']);
            endif;
        endforeach; endif;
        // Create a true colour image container with the export with and height
        $export_object = imagecreatetruecolor($org_export_width, $org_export_height);
        // Fill a image with a background rectangle
        if ($options['background'] !== false){
            $background_rgb = $this->colour_hex2rgb($options['background']);
            $background_colour = imagecolorallocate($export_object, $background_rgb['red'], $background_rgb['green'], $background_rgb['blue']);
            imagefilledrectangle($export_object, 0, 0, $org_export_width, $org_export_height, $background_colour);
        }
        // If this is a PNG/GIF to PNG/GIF conversion, attempt to preserve transparency
        if ($options['background'] === false && ($export_type['ID'] == 'PNG' || $export_type['ID'] == 'GIF')):
            // Disable alpha blending on the export object
            @imagealphablending($export_object, false);
            // Enable alpha saving on the export object
            @imagesavealpha($export_object, true);
            // Now fill the export background will the transparent colour (assuming there is one)
            $trans_colour = imagecolorallocatealpha($export_object, 0, 0, 0, 127);
            @imagefill($export_object, 0, 0, $trans_colour);
            // If this is being exported as a GIF
            if (($source_type['ID'] == 'PNG' || $source_type['ID'] == 'GIF') && $export_type['ID'] == 'GIF'):
                // Collect the original transparent colour from the GIF or PNG
                $transparent_index = $source_type['ID'] == 'GIF' ? @imagecolortransparent($source_link) : @imagecolorsforindex($source_link, @imagecolorat($source_link, 0, 0));
                // Define the transparency colour for the export GIF
                if ($transparent_index >= 0) { $transparency_colour = @imagecolorsforindex($source_link, $transparent_index); }
                else { $transparency_colour = array('red' => 0, 'green' => 0, 'blue' => 0); }
                // Create the new transparency_index2 for the export and allocate it
                $transparent_index2 = @imagecolorallocate($export_object, $transparency_colour['red'], $transparency_colour['green'], $transparency_colour['blue']);
                // Copy the pallet from the source image to the export object
                @imagepalettecopy($source_link, $export_object);
                // Fill the background with the transparent index
                @imagefill($export_object, 0, 0, $transparent_index2);
                // Set the transparent colour on the destination image
                @imagecolortransparent($export_object, $transparent_index2);
                // Convert the export object to a pallet-based image
                @imagetruecolortopalette($export_object, true, 256);
            endif;
        endif;
        // Now, finally, copy the source image over into the export object
        @imagecopyresampled($export_object, $source_link, $export_x, $export_y, $source_x, $source_y, $export_width, $export_height, $source_width, $source_height);
        // Attempt to destroy the image objects from memory
        @imagedestroy($source_link);
        unset($source_link);
        // And now to export the image in the requested format
        if ($export_type['ID'] == 'JPG') { $success = imagejpeg($export_object, $export_path, 100); }
        elseif ($export_type['ID'] == 'PNG') { $success = imagepng($export_object, $export_path, 0); }
        elseif ($export_type['ID'] == 'GIF') { $success = imagegif($export_object, $export_path); }
        // Attempt to destroy the image objects from memory
        @imagedestroy($export_object);
        unset($export_object);
        // Attempt to destroy any other objects that might be taking extra memory
        unset($options, $filters);
        // Return the success flag on completion
        return $success;
    }

    // Define a function for generating an image index
    public function image_index($token, $config, $attributes = array(), $options = array()){
        // Define allowable values for mandatory fields
        $allowed_types = array('JPG', 'PNG', 'GIF');
        // Ensure the token is a valid string without spaces
        if (!is_string($token)){ $this->message("[[cms_image::image_index]] : Provided token is not a string.", CMS_IMAGE_ERROR); return false; }
        elseif (strstr($token, ' ')){ $this->message("[[cms_image::image_index]] : Provided token contains illegal spaces.", CMS_IMAGE_ERROR); return false; }
        // Ensure necessary fields are included in the CONFIG
        if (!isset($config['ROOTDIR']) || !is_string($config['ROOTDIR'])){ $this->message("[[cms_image::image_index]] : Configuration field <<ROOTDIR>> was not provided.", CMS_IMAGE_ERROR); return false; }
        elseif (!isset($config['ROOTURL']) || !is_string($config['ROOTURL'])){ $this->message("[[cms_image::image_index]] : Configuration field <<ROOTURL>> was not provided.", CMS_IMAGE_ERROR); return false; }
        elseif (!isset($config['INDEX']) || !is_array($config['INDEX'])){ $this->message("[[cms_image::image_index]] : Configuration field <<INDEX>> was not provided.", CMS_IMAGE_ERROR); return false; }
        // Ensure the attributes are in array format, else define default array()
        if (!is_array($attributes)){ $this->message("[[cms_image::image_index]] : Provided attributes were not in array format.", CMS_IMAGE_WARNING); $attributes = array(); }
        // Ensure the options are in array format, else define default
        if (!is_array($options)){ $this->message("[[cms_image::image_index]] : Provided options were not in array format.", CMS_IMAGE_WARNING); $options = array(); }
        // Define any attribute defaults if not set
        $attributes['alt'] = isset($attributes['alt']) ? $attributes['alt'] : '';
        // Define any option defaults if not set
        $options['width'] = $this->filter_to_default((isset($options['width']) ? $options['width'] : true), array(true, false));
        $options['height'] = $this->filter_to_default((isset($options['height']) ? $options['height'] : true), array(true, false));
        // Define the image index container to populate
        $index = array();
        // Loop through all the config INDEX fields
        foreach ($config['INDEX'] AS $key => $imageinfo){
            // Compensate for missing fields by providing defaults
            $imageinfo['width'] = isset($imageinfo['width']) ? $imageinfo['width'] : 'auto';
            $imageinfo['height'] = isset($imageinfo['height']) ? $imageinfo['height'] : 'auto';
            $imageinfo['type'] = $this->filter_to_default((isset($imageinfo['type']) ? $imageinfo['type'] : ''), $allowed_types);
            $imageinfo['before'] = isset($imageinfo['before']) ? $imageinfo['before'] : '';
            $imageinfo['after'] = isset($imageinfo['after']) ? $imageinfo['after'] : '';
            $imageinfo['options'] = isset($imageinfo['options']) ? $imageinfo['options'] : false;
            $imageinfo['filters'] = isset($imageinfo['filters']) ? $imageinfo['filters'] : false;
            // Attempt to fix configuration logic errors
            if ($imageinfo['width'] == 'auto' && $imageinfo['height'] == 'auto') { $imageinfo['width'] = $imageinfo['height'] = 'full'; }
            if ($imageinfo['width'] == 'full' && is_numeric($imageinfo['height'])) { $imageinfo['width'] == 'auto'; }
            elseif ($imageinfo['height'] == 'full' && is_numeric($imageinfo['width'])) { $imageinfo['height'] == 'auto'; }
            // Define the container for this particular image index
            $this_index = array();
            // Define the image index fields
            $this_index['token'] = $key;
            $this_index['filters'] = $imageinfo['filters'];
            $this_index['width'] = $imageinfo['width'];
            $this_index['height'] = $imageinfo['height'];
            $this_index['xwidth'] = false;
            $this_index['xheight'] = false;
            $this_index['options'] = array();
            $this_index['size'] = '';
            $this_index['markup'] = '';
            $this_index['type'] = isset($this->FILETYPES[$imageinfo['type']]) ? $this->FILETYPES[$imageinfo['type']] : $this->FILETYPES['UNKNOWN'];
            $this_index['name'] = "{$imageinfo['before']}{$token}{$imageinfo['after']}.{$this_index['type']['EXTENSION']}";
            $this_index['dir'] = "{$config['ROOTDIR']}{$this_index['name']}";
            $this_index['url'] = "{$config['ROOTURL']}{$this_index['name']}";
            $this_index['exists'] = file_exists($this_index['dir']);
            // If the image exists, update the xwidth and xheight
            if ($this_index['exists']){ list($this_index['xwidth'], $this_index['xheight']) = getimagesize($this_index['dir']); }
            // Update the size text field
            if (is_numeric($imageinfo['width']) && is_numeric($imageinfo['height'])){ $this_index['size'] = "{$imageinfo['width']}px by {$imageinfo['height']}px"; }
            elseif ($imageinfo['width'] == 'full' && $imageinfo['height'] == 'full'){ $this_index['size'] = 'Fullsize'; }
            elseif ($this_index['xwidth'] && $this_index['xheight']){ $this_index['size'] = "{$this_index['xwidth']}px by {$this_index['xheight']}px"; }
            else { $this_index['size'] = 'Auto'; }
            // Collect and reformat any options into associative arrays
            if (is_array($imageinfo['options'])): foreach ($imageinfo['options'] AS $option => $value):
                if (is_string($option)) { $this_index['options'][$option] = $value; }
            endforeach; endif;
            if (is_array($options)): foreach ($options AS $option => $value):
                if (is_string($option)) { $options[$option] = $value; }
            endforeach; endif;
            // Create the image markup based on all collected information
            $this_index['markup'] .= "<img ";
            $this_index['markup'] .= "src=\"{$this_index['url']}\" ";
            if ($options['width'] && !isset($attributes['width'])) { $this_index['markup'] .= "width=\"".(is_numeric($this_index['width']) ? $this_index['width'] : '')."\" "; }
            if ($options['height'] && !isset($attributes['height'])) { $this_index['markup'] .= "height=\"".(is_numeric($this_index['height']) ? $this_index['height'] : '')."\" "; }
            foreach ($attributes AS $name => $value){ $this_index['markup'] .= "{$name}=\"{$value}\" "; }
            $this_index['markup'] .= "/>";
            // Add this index to the overall image index
            $index[$key] = $this_index;
        }
        // Return the completed image index
        return $index;
    }

    // Define a function for automatically exporting an image based on an imageindex
    public function image_export($source_path, $image_index, $source_filename = false, $delete_existing = true){
        // Define allowable values for mandatory fields
        $allowed_types = array('JPG', 'PNG', 'GIF');
        // Ensure the source_path is a valid string without spaces
        if (!is_string($source_path)){ $this->message("[[cms_image::image_export]] : Provided source path is not a string.", CMS_IMAGE_ERROR); return false; }
        elseif (!file_exists($source_path) || is_dir($source_path)){ $this->message("[[cms_image::image_export]] : Provided source path does not point to a valid image resource.", CMS_IMAGE_ERROR); return false; }
        // Attempt to determine the filename if it was not provided
        if (!is_string($source_filename) || empty($source_filename)){
            preg_match('/([^\/])+$/i', str_replace('\\', '/', $source_path), $matches);
            $source_filename = is_array($matches) ? $matches[0] : false;
        }
        // Collect the filetype for this image file
        $source_type = $this->filetype($source_path, $source_filename);
        // Ensure the source file is of the correct type
        if (!in_array($source_type['ID'], $allowed_types)){ $this->message("[[cms_image::image_export]] : Provided source image is not a valid file type - expected ".implode(' or ', $allowed_types)." but found {$source_type['ID']}.", CMS_IMAGE_ERROR); return false; }
        // Attempt to collect the source width & height
        list($source_width, $source_height) = getimagesize($source_path);
        // Ensure the collected width and height are numeric
        if (!is_numeric($source_width) || !is_numeric($source_height)) { $this->message("[[cms_image::image_export]] : The source image&apos;s width and height could not be defined.", CMS_IMAGE_ERROR); return false; }
        // Ensure the image_index is a valid array with at least one element
        if (!is_array($image_index) || empty($image_index)){ $this->message("[[cms_image::image_export]] : Provided image index is not a valid array.", CMS_IMAGE_ERROR); return false; }
        // Create the success counter to count the number of successful exports
        $success = 0;
        // Now loop through each image in the index and attempt to export it
        foreach ($image_index AS $token => $imageinfo){
            // If the destination path exists, either delete or backup the existing file
            if (file_exists($imageinfo['dir']) && $delete_existing){ @unlink($imageinfo['dir']); }
            elseif (file_exists($imageinfo['dir']) && !$delete_existing){ @rename($imageinfo['dir'], $imageinfo['dir'].'.bak.'.time()); }
            // Decide what the final export sizes should be
            if ($imageinfo['width'] == 'full'){ $imageinfo['width'] = $source_width; }
            if ($imageinfo['height'] == 'full'){ $imageinfo['height'] = $source_height; }
            $export_width = is_numeric($imageinfo['width']) ? $imageinfo['width'] : $this->image_autowidth($source_width, $source_height, $imageinfo['height']);
            $export_height = is_numeric($imageinfo['height']) ? $imageinfo['height'] : $this->image_autoheight($source_width, $source_height, $imageinfo['width']);
            // Now attempt to export the image with the provided info
            $exported = $this->image_create($source_path, $imageinfo['dir'], $imageinfo['type'], $export_width, $export_height, $imageinfo['options'], $imageinfo['filters']);
            // If the export was successful, increment the success counter
            if ($exported){ $success++; }
        }
        // Return the success counter
        return $success;
    }

    // Define a function for automatically determining width given all other values
    public function image_autowidth($source_width, $source_height, $new_height){
        // Ensure all width/height values are numeric
        if (!is_numeric($source_width) || !is_numeric($source_height) || !is_numeric($new_height)){
            $this->message("[[cms_image::image_autowidth]] : One or more arguments were of invalid type ({$source_width}, {$source_height}, {$new_height}).", CMS_IMAGE_ERROR);
            return false;
        }
        // Calculate the new width using the same aspect ratio
        $new_width = ceil(($source_width * $new_height) / $source_height);
        // Return the new width
        return $new_width;
    }

    // Define a function for automatically determining height given all other values
    public function image_autoheight($source_width, $source_height, $new_width){
        // Ensure all width/height values are numeric
        if (!is_numeric($source_width) || !is_numeric($source_height) || !is_numeric($new_width)){
            $this->message("[[cms_image::image_autoheight]] : One or more arguments were of invalid type ({$source_width}, {$source_height}, {$new_width}).", CMS_IMAGE_ERROR);
            return false;
        }
        // Calculate the new height using the same aspect ratio
        $new_height = ceil(($source_height * $new_width) / $source_width);
        // Return the new height
        return $new_height;
    }

    // Define a function for altering (reducing) the transparency of a an image resource object
    // Via: https://stackoverflow.com/questions/14468405/change-the-opacity-of-an-image-in-php
    function image_setopacity( $imageSrc, $opacity )
    {
        $width  = imagesx( $imageSrc );
        $height = imagesy( $imageSrc );

        // Duplicate image and convert to TrueColor
        $imageDst = imagecreatetruecolor( $width, $height );
        imagealphablending( $imageDst, false );
        imagefill( $imageDst, 0, 0, imagecolortransparent( $imageDst ));
        imagecopy( $imageDst, $imageSrc, 0, 0, 0, 0, $width, $height );

        // Set new opacity to each pixel
        for ( $x = 0; $x < $width; ++$x )
            for ( $y = 0; $y < $height; ++$y ) {
                $pixelColor = imagecolorat( $imageDst, $x, $y );
                $pixelOpacity = 127 - (( $pixelColor >> 24 ) & 0xFF );
                if ( $pixelOpacity > 0 ) {
                    $pixelOpacity = $pixelOpacity * $opacity;
                    $pixelColor = ( $pixelColor & 0xFFFFFF ) | ( (int)round( 127 - $pixelOpacity ) << 24 );
                    imagesetpixel( $imageDst, $x, $y, $pixelColor );
                }
            }

        return $imageDst;
    }

    /*
     * STATUS MESSAGE FUNCTIONS
     */

    // Define some functions for creating/adding/collecting status messages to/from the stack
    public function message(){
        // Collect any arguments passed to the function
        $args = func_get_args();
        $args_count = is_array($args) ? count($args) : 0;
        // If there were no arguments provided, return the entire MESSAGE stack
        if (!$args_count){
            return $this->MESSAGES;
        }
        // Else if there was a single array provided, loop through collecting messages
        elseif ($args_count == 1 && is_array($args[0])){
            // Loop through each entry and add the item to the MESSAGE stack
            foreach ($args[0] AS $message){
                // Pull the details of the item
                $message['text'] = isset($message[0]) ? $message[0] : '';
                $message['type'] = isset($message[1]) ? $message[1] : CMS_IMAGE_DEFAULT;
                // If the message text is empty, continue
                if (empty($message['text'])) { continue; }
                // Add this message to the end of the MESSAGE stack
                $this->MESSAGES[] = array('text' => $message['text'], 'type' => $message['type']);
                // Update the SESSION variable
                $_SESSION['CMS_IMAGE']['MESSAGES'] = $this->MESSAGES;
            }
        }
        // Else if there are 1-3 arguments of string, string, and boolean types
        elseif ($args_count >= 1 && $args_count <= 3){
            // Pull the details of the item
            $message = array();
            $message['text'] = isset($args[0]) ? $args[0] : '';
            $message['type'] = isset($args[1]) ? $args[1] : CMS_IMAGE_DEFAULT;
            // If the message text is empty, continue
            if (empty($message['text'])) { return false; }
            // Add this message to the end of the MESSAGE stack
            $this->MESSAGES[] = array('text' => $message['text'], 'type' => $message['type']);
            // Update the SESSION variable
            $_SESSION['CMS_IMAGE']['MESSAGES'] = $this->MESSAGES;
        }
        // Otherwise, this is an invalid call and should return false
        else {
            $this->message("[[cms_image::message]] : An invalid set of arguments were passed.", CMS_IMAGE_ERROR);
            return false;
        }
    }
    public function messages(){
        return $this->message();
    }
    // Define a function for pulling all status messages from the stack in a list format
    public function message_list($clear_stack = true, $list_id = 'messagestack', $item_class = 'message'){
        // Define the list container variable
        $message_list = '';
        // Define the opening tags for the message list
        $message_list .= "<ul id=\"{$list_id}\">\r\n";
        // Loop through each message and add it as a list item
        foreach ($this->MESSAGES AS $messageinfo){
            // Parse out quick-tags
            $messageinfo['text'] = $this->message_parse($messageinfo['text']);
            // Add a new list item for this message
            $message_list .= "<li class=\"{$item_class} status_{$messageinfo['type']}\">{$messageinfo['text']}</li>\r\n";
        }
        // Define the closing tags for the message list
        $message_list .= "</ul>\r\n";
        // If requested, clear the message stack
        if ($clear_stack){
            $_SESSION['CMS_IMAGE']['MESSAGES'] = $this->MESSAGES = array();
        }
        // Return the completed list markup
        return $message_list;
    }
    // Define a function for pulling all status messages from the stack in JSON format
    public function message_json($clear_stack = true){
        // Define the container array variable
        $message_list = array();
        // Loop through each message and add it as an array element
        foreach ($this->MESSAGES AS $messageinfo){
            // Parse out quick-tags
            $messageinfo['text'] = $this->message_parse($messageinfo['text']);
            // Add a new array element for this message
            $message_list[] = array('type' => $messageinfo['type'], 'text' => $messageinfo['text']);
        }
        // Encode ythe entire message array as JSON
        $message_list = $this->json($message_list);
        // If requested, clear the message stack
        if ($clear_stack){
            $_SESSION['CMS_IMAGE']['MESSAGES'] = $this->MESSAGES = array();
        }
        // Return the completed list array
        return $message_list;
    }
    // Define a function for parsing message quick text
    public function message_parse($message_text){
        // Parse out quick-tags
        $message_text = preg_replace('/\[\[(.*?)\]\]/i', '<strong>$1</strong>', $message_text);
        $message_text = preg_replace('/<<(.*?)>>/i', '<em>$1</em>', $message_text);
        $message_text = preg_replace('/\+\+(.*?)\+\+/i', '<span style="font-size:120%;">$1</span>', $message_text);
        $message_text = preg_replace('/--(.*?)--/i', '<span style="font-size:80%;">$1</span>', $message_text);
        // Return the result
        return $message_text;
    }
    // Define a function for attaching message stacks to the stackanchor (if one is defined)
    public function message_insert($message_stack, $content_body, $fallback = ''){
        // Ensure the $fallback variable is valid
        $fallback = !empty($fallback) && is_string($fallback) ? strtoupper($fallback) : 'PREPEND';
        if (!in_array($fallback, array('PREPEND', 'APPEND'))){ $fallback = 'PREPEND'; }
        // Define the pattern to match for the stack anchor and the final markup
        $stack_pattern = '#<span class="stackanchor">([^<>]*?)</span>#i';
        $stack_markup = '<span class="stackanchor"></span>';
        // Check if a stackanchor exists in the in the content_body
        if (preg_match($stack_pattern, $content_body)){
            // Replace all matches with the final markup, taking note of how MANY matches there were
            $replace_count = 0;
            $content_body = preg_replace($stack_pattern, $stack_markup, $content_body, -1, $replace_count);
            // Now replace all matches with nothing EXCEPT THE LAST
            $content_body = preg_replace($stack_pattern, '', $content_body, ($replace_count - 1));
            // Now replace the only exists match left with the stack anchor and messages
            $content_body = preg_replace($stack_pattern, $stack_markup."\r\n".$message_stack, $content_body);
        }
        // Otherwise, simply prepend/append the message stack to the content body
        elseif ($fallback == 'PREPEND'){
            $content_body = $message_stack."\r\n".$content_body;
        }
        elseif ($fallback == 'APPEND'){
            $content_body = $content_body."\r\n".$message_stack;
        }
        // And now return the new content_body
        return $content_body;
    }

}

// Define some IMAGE ALIGNMENT constants for use within the class
define('CMS_IMAGE_ALIGN_TOP', 0);
define('CMS_IMAGE_ALIGN_MIDDLE', 50);
define('CMS_IMAGE_ALIGN_BOTTOM', 100);
define('CMS_IMAGE_ALIGN_LEFT', 0);
define('CMS_IMAGE_ALIGN_CENTER', 50);
define('CMS_IMAGE_ALIGN_RIGHT', 100);

// Define some STATUS MESSAGE constants for use within the class
define('CMS_IMAGE_ERROR', 'error');
define('CMS_IMAGE_SUCCESS', 'success');
define('CMS_IMAGE_NOTICE', 'notice');
define('CMS_IMAGE_WARNING', 'warning');
define('CMS_IMAGE_LOADING', 'loading');
define('CMS_IMAGE_ALERT', 'alert');
define('CMS_IMAGE_DEFAULT', 'default');
define('CMS_IMAGE_UNKNOWN', 'unknown');

// Define IMAGE FILTER constants for things that aren't native
if (!defined('IMG_FILTER_ALPHA')){ define('IMG_FILTER_ALPHA', 'IMG_FILTER_ALPHA'); }


?>