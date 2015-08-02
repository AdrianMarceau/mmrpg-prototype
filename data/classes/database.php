<?php
// Define the plutocms_database() class
class plutocms_database {

  // Define the private variables
  private $LINK = false;
  private $CACHE = array();
  // Define the public variables
  public $CONNECT = true;
  public $HOST;
  public $USERNAME;
  public $PASSWORD;
  public $CHARSET;
  public $NAME;
  public $MYSQL_RESULT;
  public $INDEX;
  public $DEBUG;


  /*
   * CONSTRUCTOR
   */

  // Define the constructor for the class
  public function __construct(){
    // Collect the initializer arguments
    $this->HOST = MMRPG_CONFIG_DBHOST;
    $this->USERNAME = MMRPG_CONFIG_DBUSERNAME;
    $this->PASSWORD = MMRPG_CONFIG_DBPASSWORD;
    $this->CHARSET = MMRPG_CONFIG_DBCHARSET;
    $this->NAME = MMRPG_CONFIG_DBNAME;
  	// First initialize the database connection
    $this->CONNECT = $this->db_connect();
    if ($this->CONNECT === false){ $this->CONNECT = false; return $this->CONNECT; }
    // Now select the currently active database
    //$this->CONNECT = $this->db_select();
    //if ($this->CONNECT === false){ $this->CONNECT = false; return $this->CONNECT; }
    // Set the names and character set
    $this->CONNECT = $this->query("SET NAMES {$this->CHARSET};");
    if ($this->CONNECT === false){ $this->CONNECT = false; return $this->CONNECT; }
    // Clear any links or whatever this function does not
    $this->CONNECT = $this->clear();
    if ($this->CONNECT === false){ $this->CONNECT = false; return $this->CONNECT; }
  }

  // Define the constructor for the class
  public function __destruct(){
    // Empty the database cache
    $this->CACHE = array();
    // Clear any current links
    $this->clear();
    // Close the database connection
    $this->db_close();
  }

  /*
   * CONNECT / DISCONNECT FUNCTIONS
   */

  // Define the error handler for when the database goes bye bye
  private function critical_error($message){
    if (MMRPG_CONFIG_IS_LIVE){
      $file = fopen(MMRPG_CONFIG_ROOTDIR.'_logs/mmrpg-database-errors_'.date('Y-m-d').'.txt', 'a');
      fwrite($file, date('Y-m-d @ H:i:s').' ('.$_SERVER['REMOTE_ADDR'].') : '.$message."\r\n\r\n");
      fclose($file);
    } else {
      echo('<pre style="display: block; clear: both; float: none; background-color: #f2f2f2; color: #292929; text-shadow: 0 0 0 transparent; white-space: normal; padding: 10px; text-align: left;">'.$message.'</pre>');
    }
    return false;
  }

  // Define the private function for initializing the database connection
  private function db_connect(){
  	// Clear any leftover data
    $this->clear();
    // Attempt to open the connection to the MySQL database
	if (!isset($this->LINK) || $this->LINK === false){ $this->LINK = new mysqli($this->HOST, $this->USERNAME, $this->PASSWORD, $this->NAME);	}
    // If the connection was not successful, return false
    if ($this->LINK === false){
      if (MMRPG_CONFIG_IS_LIVE && !MMRPG_CONFIG_ADMIN_MODE){ $this->critical_error("<strong>plutocms_database::db_connect</strong> : Critical error! Unable to connect to the database &lt;".("{$this->USERNAME}:******@")."{$this->HOST}&gt;!<br />[MySQL Error ".mysqli_errno($this->LINK)."] : &quot;".htmlentities(mysqli_error($this->LINK), ENT_QUOTES, 'UTF-8', true)."&quot;"); }
      else { $this->critical_error("<strong>plutocms_database::db_connect</strong> : Critical error! Unable to connect to the database &lt;".("{$this->USERNAME}:{$this->PASSWORD}@")."{$this->HOST}&gt;!<br />[MySQL Error ".mysqli_errno()."] : &quot;".htmlentities(mysqli_errno($this->LINK), ENT_QUOTES, 'UTF-8', true)."&quot;"); }
      return false;
    }
    // Set the character set, if possible
    //if (function_exists('mysqli_set_charset')) { mysqli_set_charset($this->LINK, $this->CHARSET); }
    //else { mysqli_query($this->LINK, "SET NAMES 'utf8'");  }
    // Return true
    return true;
  }

  // Define the private function for closing the database connection
  private function db_close(){
  	// Close the open connection to the database
    if (isset($this->LINK) && $this->LINK != false){ $close = mysqli_close($this->LINK); }
  	else { $close = true; }
    // If the closing was not successful, return false
    if ($close === false){
      $this->critical_error("<strong>plutocms_database::db_close</strong> : Critical error! Unable to close the database connection for host &lt;{$this->HOST}&gt;!<br />[MySQL Error ".mysqli_errno($this->LINK)."] : &quot;".mysqli_errno($this->LINK)."&quot;");
      return false;
    }
    // Return true
    return true;
  }

  // Define the private function for selecting the database
  private function db_select(){
  	// Attempt to select the database by name
    $select = mysqli_select_db($this->LINK, $this->NAME);
    // If the select was not successful, return false
    if ($select === false){
      $this->critical_error("<strong>plutocms_database::db_select</strong> : Critical error! Unable to select the database &lt;{$this->NAME}&gt;!<br />[MySQL Error ".mysqli_errno($this->LINK)."] : &quot;".mysqli_errno($this->LINK)."&quot;");
      return false;
    }
    // Return true
    return true;
  }

  /*
   * DATABASE QUERY FUNCTIONS
   */

  // Define the function for querying the database
  public function query($query_string, &$affected_rows = 0){

    // Execute the query against the database
    $this->MYSQL_RESULT = mysqli_query($this->LINK, $query_string);
    // If a result was not found, produce an error message and return false
    if ($this->MYSQL_RESULT === false){
      if (MMRPG_CONFIG_DEBUG_MODE || MMRPG_CONFIG_ADMIN_MODE){ $this->critical_error("[[plutocms_database::query]] : Unable to run the requested query. ".mysqli_errno($this->LINK).". The query was &laquo;".htmlentities(preg_replace('/\s+/', ' ', $query_string), ENT_QUOTES, 'UTF-8')."&raquo;."); }
      else { $this->critical_error("[[plutocms_database::query]] : Unable to run the requested query. ".mysqli_errno($this->LINK)."."); }
      return false;
    }

    // Populate the affected rows, if any
    $affected_rows = mysqli_affected_rows($this->LINK);

    // Return the results if there are any
    if (is_resource($this->MYSQL_RESULT) && mysqli_num_rows($this->LINK, $this->MYSQL_RESULT) > 0){
      return $this->MYSQL_RESULT;
    } else {
      return true;
    }

  }

  // Define the function for clearing the results
  public function clear(){
  	// Attempt to release the MySQL result
  	if (is_resource($this->MYSQL_RESULT)){
  	  mysqli_free_result($this->LINK, $this->MYSQL_RESULT);
  	}
    // Return true
    return true;
  }

  // Define a function for selecting a single row as an array
  public function get_array($query_string){
  	// Ensure this is a string
    if (empty($query_string) || !is_string($query_string)) { return false; }
    // Define the md5 of this query string
    $temp_query_hash = 'get_array_'.md5(preg_replace('/\s+/', ' ', $query_string));
    $temp_query_cacheable = preg_match('/mmrpg_index_/i', $query_string) ? true : false;
    // Check if there's a chached copy of this data and decode if so
    if (!empty($this->CACHE[$temp_query_hash])){
      // Collect and decode the results and return that
      $result_array = $this->CACHE[$temp_query_hash]; //json_decode($this->CACHE[$temp_query_hash], true);
      return $result_array;
    }
    // Run the query against the database
    $this->query($query_string);
    // If the result is empty NULL or empty, return false
    if (!$this->MYSQL_RESULT || mysqli_num_rows($this->MYSQL_RESULT) < 1) { return false; }
    // Otherwise, pull an array from the result
    $result_array = mysqli_fetch_array($this->MYSQL_RESULT, MYSQL_ASSOC);
    // Free the results of the query
    $this->clear();
    // Check to see if this is a cacheable result, and encode if so
    if ($temp_query_cacheable){
      // Serialize and cache the result before we return it
      $this->CACHE[$temp_query_hash] = $result_array; //json_encode($result_array);
    }
    // Now return the resulting array
    return $result_array;
  }
  // Define a function for selecting a single row as an object (converted array)
  public function get_object($query_string){
    // Ensure this is a string
    if (empty($query_string) || !is_string($query_string)) { return false; }
    // Now return the resulting array, casted as an object
    return (object)($this->get_array($query_string));
  }

  // Define a function for selecting a list of rows as arrays
  public function get_array_list($query_string, $index = false, &$record_count = 0){
    // Ensure this is a string
    if (empty($query_string) || !is_string($query_string)) { return false; }
    // Ensure the $index is a string, else set it to false
    if ($index) { $index = is_string($index) ? trim($index) : false; }
    // Define the md5 of this query string
    $temp_query_hash = 'get_array_list_'.md5(preg_replace('/\s+/', ' ', $query_string));
    $temp_query_cacheable = preg_match('/mmrpg_index_/i', $query_string) ? true : false;
    // Check if there's a chached copy of this data and decode if so
    if (!empty($this->CACHE[$temp_query_hash])){
      // Collect and decode the results and return that
      $array_list = $this->CACHE[$temp_query_hash]; //json_decode($this->CACHE[$temp_query_hash], true);
      return $array_list;
    }
    // Run the query against the database
    $this->query($query_string);
    // If the result is empty NULL or empty, return false
    if (!$this->MYSQL_RESULT || mysqli_num_rows($this->MYSQL_RESULT) < 1) { return false; }
    // Create the list array to hold all the rows
    $array_list = array();
    // Now loop through the result rows, pulling associative arrays
    while ($result_array = mysqli_fetch_array($this->MYSQL_RESULT, MYSQL_ASSOC)){
      // If there was an index defined, assign the array to a specific key in the list
      if ($index) { $array_list[$result_array[$index]] = $result_array; }
      // Otherwise, append the array to the end of the list
      else { $array_list[] = $result_array; }
    }
    // Free the results of the query
    $this->clear();
    // Check to see if this is a cacheable result, and encode if so
    if ($temp_query_cacheable){
      // Encode and cache the result before we return it
      $this->CACHE[$temp_query_hash] = $array_list; //json_encode($array_list);
    }
    // Update the $record_count variable
    $record_count = is_array($array_list) ? count($array_list) : 0;
    // Now return the resulting array
    return $array_list;
  }
  // Define a function for selecting a list of rows as a objects (converted arrays)
  public function get_object_list($query_string, $index = false, &$record_count = 0){
    // Ensure this is a string
    if (empty($query_string) || !is_string($query_string)) { return false; }
    // Ensure the $index is a string, else set it to false
    if ($index) { $index = is_string($index) ? trim($index) : false; }
    // Pull the object list
    $object_list = $this->get_array_list($query_string, $index);
    // Loop through and convert all arrays to objects
    if (is_array($object_list)){
    	foreach ($object_list AS $key => $array){
    		$object_list[$key] = (object)($array);
    	}
    }
    // Update the $record_count variable
    $record_count = is_array($object_list) ? count($object_list) : 0;
    // Now return the resulting object list, casted from arrays
    return $object_list;
  }
  // Define a function for pulling a single value from a database
  public function get_value($query_string, $field_name = 'value'){
    // Ensure this is a string
    if (empty($query_string) || !is_string($query_string)) { return false; }
    // Define the md5 of this query string
    $temp_query_hash = 'get_value_'.md5($query_string);
    $temp_query_cacheable = preg_match('/mmrpg_index_/i', $query_string) ? true : false;
    // Check if there's a chached copy of this data and decode if so
    if (!empty($this->CACHE[$temp_query_hash])){
      // Collect and decode the results and return that
      $result_array = $this->CACHE[$temp_query_hash]; //json_decode($this->CACHE[$temp_query_hash], true);
      return $result_array;
    }
    // Run the query against the database
    $this->query($query_string);
    // If the result is empty NULL or empty, return false
    if (!$this->MYSQL_RESULT || mysqli_num_rows($this->MYSQL_RESULT) < 1) { return false; }
    // Otherwise, pull an array from the result
    $result_array = mysqli_fetch_array($this->MYSQL_RESULT, MYSQL_ASSOC);
    // Free the results of the query
    $this->clear();
    // Check to see if this is a cacheable result, and encode if so
    if ($temp_query_cacheable){
      // Encode and cache the result before we return it
      $this->CACHE[$temp_query_hash] = $result_array; //json_encode($result_array, true);
    }
    // Now return the resulting array
    return isset($result_array[$field_name]) ? $result_array[$field_name] : false;
  }

  // Define a function for inserting a record into the database
  public function insert($table_name, $insert_data){
    // Ensure proper data types have been received
    if (empty($table_name) || !is_string($table_name)) { return false; }
    if (empty($insert_data) || (!is_array($insert_data) && !is_string($insert_data))) { return false; }
    // Create the $insert_fields and $insert_values arrays
    $insert_fields = array();
    $insert_values = array();
    // Initialize in insert string
    $insert_string = '';
    // If the insert_data was an array
    if (is_array($insert_data)){
      // Loop through the $insert_data array and separate the keys/values
      foreach ($insert_data AS $field => $value)
      {
        // Skip fields that aren't named or have empty keys
        if (empty($field) || !is_string($field)) { continue; }
        // Otherwise, add to the insert_field and the insert_value lists
        $insert_fields[] = $field;
        $insert_values[] = "'".str_replace("'", "\'", $value)."'";
      }
      // Implode into an the insert strings
      $insert_string = "(".implode(', ', $insert_fields).") VALUES (".implode(', ', $insert_values).")";
    }
    // Else, if the $insert_data is a string
    elseif (is_string($insert_data)){
    	// Add this preformatted value to the insert string
      $insert_string = $insert_data;
    }
    // Create the insert query to run against the database
    $insert_query = "INSERT INTO {$table_name} {$insert_string}";
    // Execute the insert query against the database
    $affected_rows = 0;
    $this->query($insert_query, $affected_rows);
    // If success, return the affected number of rows
    if ($this->MYSQL_RESULT !== false){ $this->clear(); return $affected_rows; }
    else { $this->clear(); return false; }
  }

  // Define a function for updating a record in the database
  public function update($table_name, $update_data, $condition_data){
    // Ensure proper data types have been received
    if (empty($table_name) || !is_string($table_name)) { return false; }
    if (empty($update_data) || (!is_array($update_data) && !is_string($update_data))) { return false; }
    if (empty($condition_data) || (!is_array($condition_data) && !is_string($condition_data))) { return false; }
    // Initialize the update string
    $update_string = '';
    // If the update_data is an array object
    if (is_array($update_data)){
      // Create the update blocks array
      $update_blocks = array();
      // Loop through the $update_data array and separate the keys/values
      $find = "'"; $replace = "\'";
      foreach ($update_data AS $field => $value){
        // Skip fields that aren't named or have empty keys
        if (empty($field) || !is_string($field)) { continue; }
        // Otherwise, add to the update_blocks list
        $update_blocks[] = "$field = '".str_replace($find, $replace, $value)."'";
        }
      // Clear the update data to free memory
      unset($update_data, $field, $value);
      // Implode into an update string
      $update_string = implode(', ', $update_blocks);
      unset($update_blocks);
      }
    // Else, if the $update_data is a string
    elseif (is_string($update_data)){
      // Add this preformatted value to the update string
      $update_string = $update_data;
      // Clear the update data to free memory
      unset($update_data);
      }
    // Initialize the condition string
    $condition_string = '';
    // If the condition_data is an array object
    if (is_array($condition_data)){
      // Create the condition blocks array
      $condition_blocks = array();
      // Loop through the $condition_data array and separate the keys/values
      foreach ($condition_data AS $field => $value){
        // Skip fields that aren't named or have empty keys
        if (empty($field) || !is_string($field)) { continue; }
        // Otherwise, add to the condition_blocks list
        $condition_blocks[] = "$field = '".str_replace("'", "\'", $value)."'";
        }
      // Clear the condition data to free memory
      unset($condition_data, $field, $value);
      // Implode into an condition string
      $condition_string = implode(' AND ', $condition_blocks);
      unset($condition_blocks);
      }
    elseif (is_string($condition_data)){
      // Add this preformatted value to the condition string
      $condition_string = $condition_data;
      // Clear the condition data to free memory
      unset($condition_data);
      }
    // Now put together the update query to run against the database
    $update_query = "UPDATE {$table_name} SET {$update_string} WHERE {$condition_string}";
    unset($update_string, $condition_string);
    // Execute the update query against the database
    $affected_rows = 0;
    $this->query($update_query, $affected_rows);
    // If success, return the affected number of rows
    if ($this->MYSQL_RESULT !== false){ $this->clear(); return $affected_rows; }
    else { $this->clear(); return false; }
  }

  // Define a function for deleting a record (or records) from the database
  public function delete($table_name, $condition_data){
    // Ensure proper data types have been received
    if (empty($table_name) || !is_string($table_name)) { return false; }
    if (empty($condition_data) || (!is_array($condition_data) && !is_string($condition_data))) { return false; }
    // Initialize the condition string
    $condition_string = '';
    // If the condition_data is an array object
    if (is_array($condition_data)){
      // Create the condition blocks array
      $condition_blocks = array();
      // Loop through the $condition_data array and separate the keys/values
      foreach ($condition_data AS $field => $value){
        // Skip fields that aren't named or have empty keys
        if (empty($field) || !is_string($field)) { continue; }
        // Otherwise, add to the condition_blocks list
        $condition_blocks[] = "$field = '".str_replace("'", "\'", $value)."'";
      }
      // Implode into an condition string
      $condition_string = implode(' AND ', $condition_blocks);
    }
    elseif (is_string($condition_data)){
      // Add this preformatted value to the condition string
      $condition_string = $condition_data;
    }
    // Now put together the delete query to run against the database
    $delete_query = "DELETE FROM {$table_name} WHERE {$condition_string}";
    // Execute the delete query against the database
    $affected_rows = 0;
    $this->query($delete_query, $affected_rows);
    // If success, return the affected number of rows
    if ($this->MYSQL_RESULT !== false){ $this->clear(); return $affected_rows; }
    else { $this->clear(); return false; }
  }

  // Define a function for pulling the list of database tables
  public function table_list(){
    // Run the SHOW TABLES query against the database
    $this->query("SHOW TABLES");
    // If the result is empty NULL or empty, return false
    if (!$this->MYSQL_RESULT || mysqli_num_rows($this->LINK, $this->MYSQL_RESULT) < 1) { return false; }
    // Create the array to hold all table names
    $all_tables = array();
    // Loop through the result and add the names to the array
    while ($row = mysqli_fetch_row($this->LINK, $this->MYSQL_RESULT)){
      if (!isset($row[0]) || empty($row[0])){ continue; }
      $all_tables[] = $row[0];
    }
    // Free the results of the query
    $this->clear();
    // Now return the resulting array of table names
    return $all_tables;

  }

  // Define a function for checking if a database table exists
  public function table_exists($table_name){
    // First collect all tables from the database into an array
    $all_tables = $this->table_list();
    // Return true
    return in_array($table_name, $all_tables);
  }

  // Define a function for collection the maximum field value of a given table
  public function max_value($table_name, $field_name, $condition_data = false){
    // Ensure proper data types have been received
    if (empty($table_name) || !is_string($table_name)) { return false; }
    if (empty($field_name) || !is_string($field_name)) { return false; }
    if ($condition_data != false && (!is_array($condition_data) && !is_string($condition_data))) { return false; }
    // Initialize the condition string
    $condition_string = '';
    // If the condition_data is an array object
    if (is_array($condition_data)){
      // Create the condition blocks array
      $condition_blocks = array();
      // Loop through the $condition_data array and separate the keys/values
      foreach ($condition_data AS $field => $value){
        // Skip fields that aren't named or have empty keys
        if (empty($field) || !is_string($field)) { continue; }
        // Otherwise, add to the condition_blocks list
        $condition_blocks[] = "$field = '".str_replace("'", "\'", $value)."'";
      }
      // Implode into an condition string
      $condition_string = "WHERE ".implode(' AND ', $condition_blocks);
    }
    elseif (is_string($condition_data)){
      // Add this preformatted value to the condition string
      $condition_string = "WHERE ".$condition_data;
    }
    // Pull the max valued array from the database
    $max_array = $this->get_array("SELECT MAX({$field_name}) as max_value FROM {$table_name} {$condition_string} ORDER BY {$field_name} DESC LIMIT 1");
    // Return the value for the $max_array
    return !empty($max_array['max_value']) ? $max_array['max_value'] : 0;
  }

  // Define a function for collection the minimum field value of a given table
  public function min_value($table_name, $field_name, $condition_data = false){
    // Ensure proper data types have been received
    if (empty($table_name) || !is_string($table_name)) { return false; }
    if (empty($field_name) || !is_string($field_name)) { return false; }
    if ($condition_data != false && (!is_array($condition_data) && !is_string($condition_data))) { return false; }
    // Initialize the condition string
    $condition_string = '';
    // If the condition_data is an array object
    if (is_array($condition_data)){
      // Create the condition blocks array
      $condition_blocks = array();
      // Loop through the $condition_data array and separate the keys/values
      foreach ($condition_data AS $field => $value){
        // Skip fields that aren't named or have empty keys
        if (empty($field) || !is_string($field)) { continue; }
        // Otherwise, add to the condition_blocks list
        $condition_blocks[] = "$field = '".str_replace("'", "\'", $value)."'";
      }
      // Implode into an condition string
      $condition_string = "WHERE ".implode(' AND ', $condition_blocks);
    }
    elseif (is_string($condition_data)){
      // Add this preformatted value to the condition string
      $condition_string = "WHERE ".$condition_data;
    }
    // Pull the min valued array from the database
    $min_array = $this->get_array("SELECT MIN({$field_name}) as min_value FROM {$table_name} {$condition_string} ORDER BY {$field_name} ASC LIMIT 1");
    // Return the value for the $min_array
    return $min_array['min_value'];
  }

  // Define a public function for resetting the auto increment of a table
  public function reset_table($table_name, $auto_increment = false, $order_by = false){
    // Ensure proper data types have been received
    if (empty($table_name) || !is_string($table_name)) { return false; }
    if (empty($auto_increment) && empty($order_by)) { return false; }
    // Execute the alter table queries against the database and collect the successes or failures
    if (is_numeric($auto_increment)){
    	$this->query("ALTER TABLE {$table_name} AUTO_INCREMENT = {$auto_increment}");
      $success1 = $this->MYSQL_RESULT ? 1 : 0;
      $this->clear();
    }
    if (is_string($order_by)){
      $this->query("ALTER TABLE {$table_name} ORDER BY {$order_by}");
      $success2 = $this->MYSQL_RESULT ? 1 : 0;
      $this->clear();
    }
    return ($success1 + $success2);
  }

}

?>