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
     * @return rpg_object
     */
    public function rpg_object(){

        // Update the session keys for this object
        $this->session_key = 'OBJECTS';
        $this->session_token = 'object_token';
        $this->session_id = 'object_id';
        $this->class = 'object';
        $this->multi = 'objects';

    }



}
?>