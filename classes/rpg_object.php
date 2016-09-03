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
     * Refresh this object with data from the session
     */
    public function refresh(){
        $session_key = $this->session_key;
        $session_id = $this->session_id;
        // Now load the object data from the session or index
        $load_function = $this->class.'_load';
        $this->{$load_function}($this->{$this->session_id}, $this->{$this->session_token});
        // Update the session variable
        $this->update_session();
    }

    /**
     * Update session data for this object
     */
    public function update_session(){
        // Don't actually update this object
    }

    /**
     * Get the session data for a given object type by ID
     * @param string $session_key
     * @param int $object_id
     * @return array
     */
    public function get_session_object($session_key, $object_id){
        if (isset($_SESSION[$session_key][$object_id])){ return $_SESSION[$session_key][$object_id]; }
        else { return false; }
    }

    /**
     * Get the session data for a given player by ID
     * @param string $session_key
     * @param int $object_id
     * @return array
     */
    public function get_session_player($player_id){
        return $this->get_session_object('PLAYERS', $player_id);
    }

    /**
     * Get the session data for a given robot by ID
     * @param string $session_key
     * @param int $object_id
     * @return array
     */
    public function get_session_robot($robot_id){
        return $this->get_session_object('ROBOTS', $robot_id);
    }

    /**
     * Get the session data for a given ability by ID
     * @param string $session_key
     * @param int $object_id
     * @return array
     */
    public function get_session_ability($ability_id){
        return $this->get_session_object('ABILITIES', $ability_id);
    }

    /**
     * Get the session data for a given attachment by ID
     * @param string $session_key
     * @param int $object_id
     * @return array
     */
    public function get_session_attachment($attachment_id){
        return $this->get_session_object('ATTACHMENTS', $attachment_id);
    }

    /**
     * Get the session data for a given item by ID
     * @param string $session_key
     * @param int $object_id
     * @return array
     */
    public function get_session_item($item_id){
        return $this->get_session_object('ITEMS', $item_id);
    }

    /**
     * Set one of this object's internal flags by token
     * @param string $token
     * @param string $token2 (optional)
     * @param string $token3 (optional)
     * @param bool $flag
     */
    public function set_flag($token, $flag){
        $session_key = $this->session_key;
        $session_id = $this->session_id;
        $args = func_get_args();
        $flag = array_pop($args) ? 1 : 0;
        $token = array_shift($args);
        if (count($args) == 2){
            $token2 = array_shift($args);
            $token3 = array_shift($args);
            $this->flags[$token][$token2][$token3] = $flag;
            $_SESSION[$session_key][$this->$session_id]['flags'][$token][$token2][$token3] = $flag;
        } elseif (count($args) == 1){
            $token2 = array_shift($args);
            $this->flags[$token][$token2] = $flag;
            $_SESSION[$session_key][$this->$session_id]['flags'][$token][$token2] = $flag;
        } else {
            $this->flags[$token] = $flag;
            $_SESSION[$session_key][$this->$session_id]['flags'][$token] = $flag;
        }
    }

    /**
     * Unset one of this object's internal flags by token
     * @param string $token
     * @param string $token2 (optional)
     * @param string $token3 (optional)
     */
    public function unset_flag($token){
        $session_key = $this->session_key;
        $session_id = $this->session_id;
        $args = func_get_args();
        $token = array_shift($args);
        if (count($args) == 2){
            $token2 = array_shift($args);
            $token3 = array_shift($args);
            unset($this->flags[$token][$token2][$token3]);
            unset($_SESSION[$session_key][$this->$session_id]['flags'][$token][$token2][$token3]);
        } elseif (count($args) == 1){
            $token2 = array_shift($args);
            unset($this->flags[$token][$token2]);
            unset($_SESSION[$session_key][$this->$session_id]['flags'][$token][$token2]);
        } else {
            unset($this->flags[$token]);
            unset($_SESSION[$session_key][$this->$session_id]['flags'][$token]);
        }
    }

    /**
     * Get one of this object's internal flags by token
     * @param string $token
     * @param string $token2 (optional)
     * @param string $token3 (optional)
     * @return bool
     */
    public function get_flag($token){
        $session_key = $this->session_key;
        $session_id = $this->session_id;
        $args = func_get_args();
        $token = array_shift($args);
        if (count($args) == 2){
            $token2 = array_shift($args);
            $token3 = array_shift($args);
            if (isset($_SESSION[$session_key][$this->$session_id]['flags'][$token][$token2][$token3])){ $this->flags[$token][$token2][$token3] = $_SESSION[$session_key][$this->$session_id]['flags'][$token][$token2][$token3] ? true : false; }
            if (isset($this->flags[$token][$token2][$token3])){ return $this->flags[$token][$token2][$token3] ? true : false; }
            else { return false; }
        } elseif (count($args) == 1){
            $token2 = array_shift($args);
            if (isset($_SESSION[$session_key][$this->$session_id]['flags'][$token][$token2])){ $this->flags[$token][$token2] = $_SESSION[$session_key][$this->$session_id]['flags'][$token][$token2] ? true : false; }
            if (isset($this->flags[$token][$token2])){ return $this->flags[$token][$token2] ? true : false; }
            else { return false; }
        } else {
            if (isset($_SESSION[$session_key][$this->$session_id]['flags'][$token])){ $this->flags[$token] = $_SESSION[$session_key][$this->$session_id]['flags'][$token] ? true : false; }
            if (isset($this->flags[$token])){ return $this->flags[$token] ? true : false; }
            else { return false; }
        }
    }

    /**
     * Check if this object has a specific flag by token
     * @param string $token
     * @param string $token2 (optional)
     * @param string $token3 (optional)
     * @return bool
     */
    public function has_flag($token){
        $session_key = $this->session_key;
        $session_id = $this->session_id;
        $args = func_get_args();
        $token = array_shift($args);
        if (count($args) == 2){
            $token2 = array_shift($args);
            $token3 = array_shift($args);
            if (isset($_SESSION[$session_key][$this->$session_id]['flags'][$token][$token2][$token3])){ $this->flags[$token][$token2][$token3] = $_SESSION[$session_key][$this->$session_id]['flags'][$token][$token2][$token3]; }
            if (isset($this->flags[$token][$token2][$token3])){ return true; }
            else { return false; }
        } elseif (count($args) == 1){
            $token2 = array_shift($args);
            if (isset($_SESSION[$session_key][$this->$session_id]['flags'][$token][$token2])){ $this->flags[$token][$token2] = $_SESSION[$session_key][$this->$session_id]['flags'][$token][$token2]; }
            if (isset($this->flags[$token][$token2])){ return true; }
            else { return false; }
        } else {
            if (isset($_SESSION[$session_key][$this->$session_id]['flags'][$token])){ $this->flags[$token] = $_SESSION[$session_key][$this->$session_id]['flags'][$token]; }
            if (isset($this->flags[$token])){ return true; }
            else { return false; }
        }
    }

    /**
     * Set one of this object's internal counters by token
     * @param string $token
     * @param string $token2 (optional)
     * @param string $token3 (optional)
     * @param int $counter
     */
    public function set_counter($token, $counter){
        $session_key = $this->session_key;
        $session_id = $this->session_id;
        $args = func_get_args();
        $counter = array_pop($args);
        $token = array_shift($args);
        if (count($args) == 2){
            $token2 = array_shift($args);
            $token3 = array_shift($args);
            $this->counters[$token][$token2][$token3] = $counter;
            $_SESSION[$session_key][$this->$session_id]['counters'][$token][$token2][$token3] = $counter;
        } elseif (count($args) == 1){
            $token2 = array_shift($args);
            $this->counters[$token][$token2] = $counter;
            $_SESSION[$session_key][$this->$session_id]['counters'][$token][$token2] = $counter;
        } else {
            $this->counters[$token] = $counter;
            $_SESSION[$session_key][$this->$session_id]['counters'][$token] = $counter;
        }
    }

    /**
     * Increase one of this object's internal counters by one or a custom amount
     * @param string $token
     * @param string $token2 (optional)
     * @param string $token3 (optional)
     * @param int $amount (optional)
     */
    public function increase_counter($token, $amount){
        $args = func_get_args();
        $amount = array_pop($args);
        $token = array_shift($args);
        if (count($args) == 2){
            $token2 = array_shift($args);
            $token3 = array_shift($args);
            $counter = $this->get_counter($token, $token2, $token3) + $amount;
            $this->set_counter($token, $token2, $token3, $counter);
        } elseif (count($args) == 1){
            $token2 = array_shift($args);
            $counter = $this->get_counter($token, $token2) + $amount;
            $this->set_counter($token, $token2, $counter);
        } else {
            $counter = $this->get_counter($token) + $amount;
            $this->set_counter($token, $counter);
        }
    }

    /**
     * Increase one of this object's internal counters by one
     * @param string $token
     * @param string $token2 (optional)
     * @param string $token3 (optional)
     */
    public function inc_counter($token){
        $args = func_get_args();
        $args[] = 1;
        return call_user_func_array(array($this, 'increase_counter'), $args);
    }

    /**
     * Decrease one of this object's internal counters by one or a custom amount
     * @param string $token
     * @param string $token2 (optional)
     * @param string $token3 (optional)
     * @param int $amount (optional)
     */
    public function decrease_counter($token, $amount){
        $args = func_get_args();
        $amount = array_pop($args);
        $token = array_shift($args);
        if (count($args) == 2){
            $token2 = array_shift($args);
            $token3 = array_shift($args);
            $counter = $this->get_counter($token, $token2, $token3) - $amount;
            $this->set_counter($token, $token2, $token3, $counter);
        } elseif (count($args) == 1){
            $token2 = array_shift($args);
            $counter = $this->get_counter($token, $token2) - $amount;
            $this->set_counter($token, $token2, $counter);
        } else {
            $counter = $this->get_counter($token) - $amount;
            $this->set_counter($token, $counter);
        }
    }

    /**
     * Decrease one of this object's internal counters by one
     * @param string $token
     * @param string $token2 (optional)
     * @param string $token3 (optional)
     */
    public function dec_counter($token){
        $args = func_get_args();
        $args[] = 1;
        return call_user_func_array(array($this, 'decrease_counter'), $args);
    }


    /**
     * Unset one of this object's internal counters by token
     * @param string $token
     * @param string $token2 (optional)
     * @param string $token3 (optional)
     */
    public function unset_counter($token){
        $session_key = $this->session_key;
        $session_id = $this->session_id;
        $args = func_get_args();
        $token = array_shift($args);
        if (count($args) == 2){
            $token2 = array_shift($args);
            $token3 = array_shift($args);
            unset($this->counters[$token][$token2][$token3]);
            unset($_SESSION[$session_key][$this->$session_id]['counters'][$token][$token2][$token3]);
        } elseif (count($args) == 1){
            $token2 = array_shift($args);
            unset($this->counters[$token][$token2]);
            unset($_SESSION[$session_key][$this->$session_id]['counters'][$token][$token2]);
        } else {
            unset($this->counters[$token]);
            unset($_SESSION[$session_key][$this->$session_id]['counters'][$token]);
        }
    }

    /**
     * Get one of this object's internal counters by token
     * @param string $token
     * @param string $token2 (optional)
     * @param string $token3 (optional)
     * @return integer
     */
    public function get_counter($token){
        $session_key = $this->session_key;
        $session_id = $this->session_id;
        $args = func_get_args();
        $token = array_shift($args);
        if (count($args) == 2){
            $token2 = array_shift($args);
            $token3 = array_shift($args);
            if (isset($this->counters[$token][$token2][$token3])){ return $this->counters[$token][$token2][$token3]; }
            else { return 0; }
        } elseif (count($args) == 1){
            $token2 = array_shift($args);
            if (isset($this->counters[$token][$token2])){ return $this->counters[$token][$token2]; }
            else { return 0; }
        } else {
            if (isset($this->counters[$token])){ return $this->counters[$token]; }
            else { return 0; }
        }
    }

    /**
     * Check if this object has a specific counter by token
     * @param string $token
     * @param string $token2 (optional)
     * @param string $token3 (optional)
     * @return bool
     */
    public function has_counter($token){
        $session_key = $this->session_key;
        $session_id = $this->session_id;
        $args = func_get_args();
        $token = array_shift($args);
        if (count($args) == 2){
            $token2 = array_shift($args);
            $token3 = array_shift($args);
            if (isset($_SESSION[$session_key][$this->$session_id]['counters'][$token][$token2][$token3])){ $this->counters[$token][$token2][$token3] = $_SESSION[$session_key][$this->$session_id]['counters'][$token][$token2][$token3]; }
            if (isset($this->counters[$token][$token2][$token3])){ return true; }
            else { return false; }
        } elseif (count($args) == 1){
            $token2 = array_shift($args);
            if (isset($_SESSION[$session_key][$this->$session_id]['counters'][$token][$token2])){ $this->counters[$token][$token2] = $_SESSION[$session_key][$this->$session_id]['counters'][$token][$token2]; }
            if (isset($this->counters[$token][$token2])){ return true; }
            else { return false; }
        } else {
            if (isset($_SESSION[$session_key][$this->$session_id]['counters'][$token])){ $this->counters[$token] = $_SESSION[$session_key][$this->$session_id]['counters'][$token]; }
            if (isset($this->counters[$token])){ return true; }
            else { return false; }
        }
    }

    /**
     * Set one of this object's internal values by token
     * @param string $token
     * @param string $token2 (optional)
     * @param string $token3 (optional)
     * @param mixed $value
     */
    public function set_value($token, $value){
        $session_key = $this->session_key;
        $session_id = $this->session_id;
        $args = func_get_args();
        $value = array_pop($args);
        $token = array_shift($args);
        if (count($args) == 2){
            $token2 = array_shift($args);
            $token3 = array_shift($args);
            $this->values[$token][$token2][$token3] = $value;
            $_SESSION[$session_key][$this->$session_id]['values'][$token][$token2][$token3] = $value;
        } elseif (count($args) == 1){
            $token2 = array_shift($args);
            $this->values[$token][$token2] = $value;
            $_SESSION[$session_key][$this->$session_id]['values'][$token][$token2] = $value;
        } else {
            $this->values[$token] = $value;
            $_SESSION[$session_key][$this->$session_id]['values'][$token] = $value;
        }
    }

    /**
     * Unset one of this object's internal values by token
     * @param string $token
     * @param string $token2 (optional)
     * @param string $token3 (optional)
     */
    public function unset_value($token){
        $session_key = $this->session_key;
        $session_id = $this->session_id;
        $args = func_get_args();
        $token = array_shift($args);
        if (count($args) == 2){
            $token2 = array_shift($args);
            $token3 = array_shift($args);
            unset($this->values[$token][$token2][$token3]);
            unset($_SESSION[$session_key][$this->$session_id]['values'][$token][$token2][$token3]);
        } elseif (count($args) == 1){
            $token2 = array_shift($args);
            unset($this->values[$token][$token2]);
            unset($_SESSION[$session_key][$this->$session_id]['values'][$token][$token2]);
        } else {
            unset($this->values[$token]);
            unset($_SESSION[$session_key][$this->$session_id]['values'][$token]);
        }
    }

    /**
     * Get one of this object's internal values by token
     * @param string $token
     * @param string $token2 (optional)
     * @param string $token3 (optional)
     * @return mixed
     */
    public function get_value($token){
        $session_key = $this->session_key;
        $session_id = $this->session_id;
        $args = func_get_args();
        $token = array_shift($args);
        if (count($args) == 2){
            $token2 = array_shift($args);
            $token3 = array_shift($args);
            if (isset($this->values[$token][$token2][$token3])){ return $this->values[$token][$token2][$token3]; }
            else { return false; }
        } elseif (count($args) == 1){
            $token2 = array_shift($args);
            if (isset($this->values[$token][$token2])){ return $this->values[$token][$token2]; }
            else { return false; }
        } else {
            if (isset($this->values[$token])){ return $this->values[$token]; }
            else { return false; }
        }
    }

    /**
     * Check if this object has a specific value by token
     * @param string $token
     * @param string $token2 (optional)
     * @param string $token3 (optional)
     * @return bool
     */
    public function has_value($token){
        $session_key = $this->session_key;
        $session_id = $this->session_id;
        $args = func_get_args();
        $token = array_shift($args);
        if (count($args) == 2){
            $token2 = array_shift($args);
            $token3 = array_shift($args);
            if (isset($_SESSION[$session_key][$this->$session_id]['values'][$token][$token2][$token3])){ $this->values[$token][$token2][$token3] = $_SESSION[$session_key][$this->$session_id]['values'][$token][$token2][$token3]; }
            if (isset($this->values[$token][$token2][$token3])){ return true; }
            else { return false; }
        } elseif (count($args) == 1){
            $token2 = array_shift($args);
            if (isset($_SESSION[$session_key][$this->$session_id]['values'][$token][$token2])){ $this->values[$token][$token2] = $_SESSION[$session_key][$this->$session_id]['values'][$token][$token2]; }
            if (isset($this->values[$token][$token2])){ return true; }
            else { return false; }
        } else {
            if (isset($_SESSION[$session_key][$this->$session_id]['values'][$token])){ $this->values[$token] = $_SESSION[$session_key][$this->$session_id]['values'][$token]; }
            if (isset($this->values[$token])){ return true; }
            else { return false; }
        }
    }

    /**
     * Add a new string to one of this object's history arrays
     * @param string $token
     * @param string $token2 (optional)
     * @param string $token3 (optional)
     * @param mixed $value
     */
    public function add_history($token, $value){
        $session_key = $this->session_key;
        $session_id = $this->session_id;
        $args = func_get_args();
        if (count($args) == 4){
            $this->history[$args[0]][$args[1]][$args[2]][] = $args[3];
            $_SESSION[$session_key][$this->$session_id]['history'][$args[0]][$args[1]][$args[2]][] = $args[3];
        } elseif (count($args) == 3){
            $this->history[$args[0]][$args[1]][] = $args[2];
            $_SESSION[$session_key][$this->$session_id]['history'][$args[0]][$args[1]][] = $args[2];
        } else {
            $this->history[$args[0]][] = $args[1];
            $_SESSION[$session_key][$this->$session_id]['history'][$args[0]][] = $args[1];
        }
    }

    /**
     * Get one of this object's history arrays
     * @param string $token
     * @param string $token2 (optional)
     * @param string $token3 (optional)
     * @return array
     */
    public function get_history($token){
        $session_key = $this->session_key;
        $session_id = $this->session_id;
        $args = func_get_args();
        if (count($args) == 3){
            if (isset($_SESSION[$session_key][$this->$session_id]['history'][$args[0]][$args[1]][$args[2]])){ $this->history[$args[0]][$args[1]][$args[2]] = $_SESSION[$session_key][$this->$session_id]['history'][$args[0]][$args[1]][$args[2]]; }
            if (isset($this->history[$args[0]][$args[1]][$args[2]])){ return $this->history[$args[0]][$args[1]][$args[2]]; }
            else { return false; }
        } elseif (count($args) == 2){
            if (isset($_SESSION[$session_key][$this->$session_id]['history'][$args[0]][$args[1]])){ $this->history[$args[0]][$args[1]] = $_SESSION[$session_key][$this->$session_id]['history'][$args[0]][$args[1]]; }
            if (isset($this->history[$args[0]][$args[1]])){ return $this->history[$args[0]][$args[1]]; }
            else { return false; }
        } else {
            if (isset($_SESSION[$session_key][$this->$session_id]['history'][$args[0]])){ $this->history[$args[0]] = $_SESSION[$session_key][$this->$session_id]['history'][$args[0]]; }
            if (isset($this->history[$args[0]])){ return $this->history[$args[0]]; }
            else { return false; }
        }
    }

    /**
     * Set the value of an internal object variable and update the session
     * @param mixed $key
     * @param mixed $key2 (optional)
     * @param mixed $key3 (optional)
     * @param string $value
     */
    public function set_info($key, $value){
        $session_key = $this->session_key;
        $session_id = $this->session_id;

        $args = func_get_args();
        $value = array_pop($args);
        $key = array_shift($args);
        if (!isset($this->$key)){ return false; }
        else { $key_array = &$this->$key; }

        if (count($args) == 2){
            $key2 = array_shift($args);
            $key3 = array_shift($args);
            $key_array[$key2][$key3] = $value;
            $_SESSION[$session_key][$this->$session_id][$key][$key2][$key3] = $value;
            /* echo("\$_SESSION[$session_key]".
                "[".print_r($this->$session_id, true)."]".
                "[".print_r($key, true)."]".
                "[".print_r($key2, true)."]".
                "[".print_r($key3, true)."]".
                " = ".
                print_r($_SESSION[$session_key][$this->$session_id][$key][$key2][$key3], true).
                ";"); */
        } elseif (count($args) == 1){
            $key2 = array_shift($args);
            $key_array[$key2] = $value;
            $_SESSION[$session_key][$this->$session_id][$key][$key2] = $value;
            /* echo("\$_SESSION[$session_key]".
                "[".print_r($this->$session_id, true)."]".
                "[".print_r($key, true)."]".
                "[".print_r($key2, true)."] = ".
                print_r($_SESSION[$session_key][$this->$session_id][$key][$key2], true).
                ";"); */
        } else {
            $key_array = $value;
            $_SESSION[$session_key][$this->$session_id][$key] = $value;
            /* echo("\$_SESSION[$session_key]".
                "[".print_r($this->$session_id, true)."]".
                "[".print_r($key, true)."] = ".
                print_r($_SESSION[$session_key][$this->$session_id][$key], true).
                ";"); */
        }
    }

    /**
     * Unset the value of an internal object variable and remove from session
     * @param mixed $key
     * @param mixed $key2 (optional)
     * @param mixed $key3 (optional)
     */
    public function unset_info($key){
        $session_key = $this->session_key;
        $session_id = $this->session_id;
        $args = func_get_args();
        $key = array_shift($args);
        if (!isset($this->$key)){ return; }
        else { $key_array = &$this->$key; }
        if (count($args) == 2){
            $key2 = array_shift($args);
            $key3 = array_shift($args);
            unset($key_array[$key2][$key3]);
            unset($_SESSION[$session_key][$this->$session_id][$key][$key2][$key3]);
        } elseif (count($args) == 1){
            $key2 = array_shift($args);
            unset($key_array[$key2]);
            unset($_SESSION[$session_key][$this->$session_id][$key][$key2]);
        } else {
            unset($key_array);
            unset($_SESSION[$session_key][$this->$session_id][$key]);
        }
    }

    /**
     * Get the value of an internal object variable matching its session data
     * @param mixed $key
     * @param mixed $key2 (optional)
     * @param mixed $key3 (optional)
     * @return mixed
     */
    public function get_info($key){
        $session_key = $this->session_key;
        $session_id = $this->session_id;
        $args = func_get_args();
        $key = array_shift($args);
        if (!isset($this->$key)){ return false; }
        else { $key_array = &$this->$key; }
        if (count($args) == 2){
            $key2 = array_shift($args);
            $key3 = array_shift($args);
            if (isset($_SESSION[$session_key][$this->$session_id][$key][$key2][$key3])){ $key_array[$key2][$key3] = $_SESSION[$session_key][$this->$session_id][$key][$key2][$key3]; }
            if (isset($key_array[$key2][$key3])){ return $key_array[$key2][$key3]; }
            else { return false; }
        } elseif (count($args) == 1){
            $key2 = array_shift($args);
            if (isset($_SESSION[$session_key][$this->$session_id][$key][$key2])){ $key_array[$key2] = $_SESSION[$session_key][$this->$session_id][$key][$key2]; }
            if (isset($key_array[$key2])){ return $key_array[$key2]; }
            else { return false; }
        } else {
            if (isset($_SESSION[$session_key][$this->$session_id][$key])){ $key_array = $_SESSION[$session_key][$this->$session_id][$key]; }
            if (isset($key_array)){ return $key_array; }
            else { return false; }
        }
    }



    // -- COMMON ATTRIBUTE FUNCTIONS -- //

    /**
     * Return the numeric ID for this object
     * @return [type] [description]
     */
    public function get_id(){
        $id_field = $this->class.'_id';
        $id_value = $this->get_info($id_field);
        return !empty($id_value) ? intval($id_value) : 0;
    }

    public function get_token(){
        $token_field = $this->class.'_token';
        $token_value = $this->get_info($token_field);
        return !empty($token_value) ? intval($token_value) : $this->class;
    }

    public function get_string(){
        $this_id = $this->get_id();
        $this_token = $this->get_token();
        return $this_id.'_'.$this_token;
    }

    public function get_lookup(){
        $id_field = $this->class.'_id';
        $id_value = $this->get_info($id_field);
        $token_field = $this->class.'_token';
        $token_value = $this->get_info($token_field);
        return array($id_field => $id_value, $token_field => $token_value);
    }

}