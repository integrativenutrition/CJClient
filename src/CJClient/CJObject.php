<?php
namespace CJClient;

class CJObject {
  protected $raw;
  protected $parent;

  /**
   * Ensure that the array keys in the raw data are all lowercase.
   *
   * @param array $array
   *   The array whose keys should be normalized.
   * @return array
   *   The same array with normalized keys. 
   */
  protected static function normalizeArrayKeys($array) {
    if (!is_array($array)) {
      return $array;
    }
    $result = array();
    foreach ($array as $key => $value) {
      $result[strtolower($key)] = static::normalizeArrayKeys($value);
    }
    return $result;
  }

  /**
   * Create a new Collection+JSON object.
   *
   * @param array $raw
   *   The raw data from the request.
   * @param CJObject $parent
   *   The parent object of which this is a property (if any).
   */
  public function __construct($raw, $parent) {
    $this->raw = static::normalizeArrayKeys($raw);
    $this->parent = $parent;
  }

  /**
   * Get the raw representation of this object.
   *
   * @return array
   *   A PHP associative array containing the raw representation of this
   *   object, suitable for conversion to JSON.
   */
  public function raw() {
    return $this->raw;
  }

  /**
   * Set the raw data for this object.
   *
   * @param array $raw
   *   The raw data as received from the server and converted to an array.
   */
  public function setRaw($raw) {
    $this->raw = $raw;
  }
}
