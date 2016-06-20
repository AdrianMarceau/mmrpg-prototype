<?
/**
 * Mega Man RPG Object
 * <p>The base class for all objects in the Mega Man RPG Prototype.</p>
 */
class rpg_object {

    // Define global class variables
    public $index = array();
    public $flags = array();
    public $counters = array();
    public $values = array();
    public $history = array();
    public $session_key = '';
    public $session_token = '';
    public $session_id = '';
    public $class = '';
    public $multi = '';

    /**
     * Create a new RPG object
     * @return  rpg_object
     */
    public function rpg_object(){

        // Update the session keys for this object
        $this->session_key = 'OBJECTS';
        $this->session_token = 'object_token';
        $this->session_id = 'object_id';
        $this->class = 'object';
        $this->multi = 'objects';

    }

    /**
     * Get any object property by name without the prefix
     * @param   string      $name       Name of the object property
     * @return  mixed
     */
    public function __get($name){

        // If the property exists verbetim, return now
        if (isset($this->$name)){ return $this->$name; }

        // Otherwise, define the actual property name
        $prop_name = $this->class.'_'.$name;

        // Return the property if it exists, else null
        if (isset($this->$prop_name)){ return $this->$prop_name; }
        else { return null; }

    }

    /**
     * Set any object property by name and value without the prefix
     * @param   string      $name       Name of the object property
     * @param   mixed       $value      Value for the object property
     */
    public function __set($name, $value){

        // Determine the real property name, verbetim or otherwise
        if (isset($this->$name)){ $prop_name = $name; }
        else { $prop_name = $this->class.'_'.$name; }

        // Update the object property with provided value
        $this->$prop_name = $value;

    }

    /**
     * Get or set this object's value
     * @param   string      $name       Name of the object property
     * @param   mixed       $value      Value of the object property (optional)
     * @return  mixed
     */
    public function value($key, $value = null){

        // Define the prop name
        $prop_name = $this->class.'_'.$key;

        // If value was not provided, return current
        if ($value !== null){ $this->$prop_name = $value; }
        // Otherwsie set the property value if it exists
        elseif (isset($this->$prop_name)){ return $this->$prop_name; }
        // Else return null
        else { return null; }

    }

    /**
     * Get or set this object's string RPG class
     * @param   string      $value      String object class (optional)
     * @return  string
     */
    public function rpg_class($value = null){
        return $this->value('class', $value);
    }

    /**
     * Get or set this object's string RPG subclass
     * @param   string      $value      String object subclass (optional)
     * @return  string
     */
    public function rpg_subclass($value = null){
        return $this->value('subclass', $value);
    }

    /**
     * Get or set this object's integer key
     * @param   integer     $value      Integer object key (optional)
     * @return  integer
     */
    public function key($value = null){
        return $this->value('key', $value);
    }

    /**
     * Get or set this object's integer ID
     * @param   integer     $value      Integer object ID (optional)
     * @return  integer
     */
    public function id($value = null){
        return $this->value('id', $value);
    }

    /**
     * Get or set this object's string number
     * @param   string      $value      String object number (optional)
     * @return  string
     */
    public function number($value = null){
        return $this->value('number', $value);
    }

    /**
     * Get or set this object's string token
     * @param   string      $value      String object token (optional)
     * @return  string
     */
    public function token($value = null){
        return $this->value('token', $value);
    }

    /**
     * Get or set this object's string name
     * @param   string      $value      String object name (optional)
     * @return  string
     */
    public function name($value = null){
        return $this->value('name', $value);
    }
    public function base_name($value = null){
        return $this->value('base_name', $value);
    }

    /**
     * Get or set this object's string image
     * @param   string      $value      String object image (optional)
     * @return  string
     */
    public function image($value = null){
        return $this->value('image', $value);
    }
    public function base_image($value = null){
        return $this->value('base_image', $value);
    }

    /**
     * Get or set this object's string image size
     * @param   integer      $value      Integer object image size (optional)
     * @return  string
     */
    public function image_size($value = null){
        return $this->value('image_size', $value);
    }
    public function base_image_size($value = null){
        return $this->value('base_image_size', $value);
    }

    /**
     * Get or set this object's string image editor
     * @param   integer      $value      Integer object image editor (optional)
     * @return  string
     */
    public function image_editor($value = null){
        return $this->value('image_editor', $value);
    }
    public function base_image_editor($value = null){
        return $this->value('base_image_editor', $value);
    }

    /**
     * Get or set this object's string image overlay
     * @param   array       $values      Array object image overlay (optional)
     * @return  string
     */
    public function image_overlay($values = null){
        return $this->value('image_overlay', $values);
    }
    public function base_image_overlay($values = null){
        return $this->value('base_image_overlay', $values);
    }

    /**
     * Get or set this object's string type
     * @param   string      $value      String object type (optional)
     * @return  string
     */
    public function type($value = null){
        return $this->value('type', $value);
    }
    public function base_type($value = null){
        return $this->value('base_type', $value);
    }

    /**
     * Get or set this object's string type2
     * @param   string      $value      String object type2 (optional)
     * @return  string
     */
    public function type2($value = null){
        return $this->value('type2', $value);
    }
    public function base_type2($value = null){
        return $this->value('base_type2', $value);
    }

    /**
     * Get or set this object's string frame
     * @param   string      $value      String object frame (optional)
     * @return  string
     */
    public function frame($value = null){
        return $this->value('frame', $value);
    }

    /**
     * Get or set this object's string frame offset
     * @param   array       $values      Array object frame offset (optional)
     * @return  string
     */
    public function frame_offset($values = null){
        return $this->value('frame_offset', $values);
    }

    /**
     * Get or set this object's string frame classes
     * @param   string      $values      String object frame classes (optional)
     * @return  string
     */
    public function frame_classes($values = null){
        return $this->value('frame_classes', $value);
    }

    /**
     * Get or set this object's string frame styles
     * @param   string      $values      String object frame styles (optional)
     * @return  string
     */
    public function frame_styles($values = null){
        return $this->value('frame_styles', $value);
    }





}
?>