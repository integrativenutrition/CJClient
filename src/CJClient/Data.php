<?php
namespace CJClient;

/**
 * Represents a Collection+JSON Data object.
 */
class Data extends CJObject implements \ArrayAccess {
  protected $map;

  public function offsetExists($offset) {
    $this->map();
    return array_key_exists($offset, $this->map);
  }

  public function offsetGet($offset) {
    return $this->value($offset);
  }

  public function offsetSet($offset, $value) {
    $this->setValue($offset, $value);
  }

  public function offsetUnset($offset) {
    $this->map();
    unset($this->map[$offset]);
  }

  /*
   * Builds a map of the raw data, keyed by name.
   */
  protected function map() {
    if (!isset($this->map)) {
      foreach ($this->raw as $datum) {
        $this->map[$datum['name']] = $datum;
      }
    }
    return $this->map;
  }

  /**
   * Get the names of each element in the data array.
   *
   * @return array
   *   An array containing the name keys of each item in the data array.
   */
  public function names() {
    return array_keys($this->map());
  }

  /**
   * Returns the value for a given name, if any.
   *
   * @param string $name
   *   The name of the datum whose prompt should be returned.
   *
   * @return string|bool
   *   The value string, or FALSE if not found.
   */
  public function value($name) {
    return isset($this->map()[$name]['value']) ? $this->map()[$name]['value'] : FALSE;
  }

  /**
   * Sets the value of a key in this data object.
   *
   * @param string $name
   *   The key to be set. This name must already exist in the data object.
   * @param string $value
   *   The value to set.
   */
  public function setValue($name, $value) {
    $this->map();
    if (!isset($this->map[$name])) {
      throw new CJException('The specified data key does not exist');
    }
    $this->map[$name]['value'] = $value;
  }

  /**
   * @see \CJClient\CJObject::raw()
   */
   public function raw() {
    $return = array();
    foreach ($this->map() as $name => $data) {
      $return[] = array('name' => $name) + $data;
    }
    return $return;
  }

  /**
   * Returns the prompt for a given name, if any.
   *
   * @param string $name
   *   The name of the datum whose prompt should be returned.
   *
   * @return string|bool
   *   The prompt string, or FALSE if not found.
   */
  public function prompt($name) {
    $this->map();
    return isset($this->map[$name]['prompt']) ? $this->map[$name]['prompt'] : FALSE;
  }

  /**
   * Tests whether these data match a set of conditions.
   *
   * Compares the data to a set of key-value pairs.
   *
   * @param array $conditions
   *   The conditions to match against.  A set of key value pairs.
   * @param $match_all
   *   TRUE to require that the data match ALL conditions. FALSE to match ANY.
   *
   * @return boolean
   *   TRUE if the data match, false otherwise.
   */
  public function match($conditions, $match_all = TRUE) {
    foreach ($conditions as $name => $value) {
      if ($match_all xor $this->value($name) === $value) {
        return !$match_all;
      }
    }
    return $match_all;
  }

}