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

    /**
     * Get or set this object's string class
     * @param   string      $value      String object class (optional)
     * @return  string
     */
    public function class($value = null){
        return $this->value('class', $value);
    }





}
?>