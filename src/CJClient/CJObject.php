<?php
namespace CJClient;

class CJObject {
  protected $raw;
  protected $parent;

  public function __construct($raw, $parent) {
    $this->raw = $raw;
    $this->parent = $parent;
  }

  protected function _property($name, $class = NULL) {
    if (isset($this->raw[$name])) {
      return isset($class) ? new $class($this->raw[$name]) : $this->raw[$name];
    }
    return FALSE;
  }
}
