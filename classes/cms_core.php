<?
// Define the class that will act as the cms core wrapper
class cms_core {

    // Define a function for printing out an array's contents using path-like syntax
    public static function array_to_paths($value, $base = '', $short = false){

        $echo = '';
        $path = trim($base, '/.');
        if ($short && !empty($path)){
            $path = implode('/', array_slice(explode('/', $path), -1));
        }
        if (is_numeric($value)){
            $number = $value;
            $echo .= $path.' = '.$number.'';
        } elseif (is_string($value)){
            $string = $value;
            $echo .= $path.' = \''.$string.'\'';
        } elseif (is_bool($value)){
            $bool = $value ? 1 : 0;
            $echo .= $path.' = '.$bool.'';
        } elseif (is_array($value)){
            $array = $value;
            foreach ($array AS $key => $val){
                $subpath = $path.'/'.$key;
                if (is_array($val)){ $echo .= trim($subpath, '/').'/'.PHP_EOL; }
                $echo .= self::array_to_paths($val, $subpath, $short);
                if (empty($base)){ $echo .= PHP_EOL; }
            }
        } elseif (empty($value)){
            $echo .= $path;
        }
        $echo = trim($echo).PHP_EOL;
        return $echo;

    }

}
?>