<?php
namespace CJClient;

class Data extends CJObject {
  protected $map;

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
    $this->map();
    return isset($this->map[$name]['value']) ? $this->map[$name]['value'] : FALSE;
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