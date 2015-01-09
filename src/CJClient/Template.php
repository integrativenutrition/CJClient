<?php 
namespace CJClient;

class Template extends CJObject {
  protected $data;

  /**
   * Gets the Data object in this template.
   */
  public function data() {
    if (!isset($this->data)) {
      $this->data = isset($this->raw['data']) ? new Data($this->raw['data'], $this) : FALSE;
    }
    return $this->data;
  }

  /**
   * @see \CJClient\CJObject::raw()
   */
  public function raw() {
    $raw = parent::raw();
    if ($this->data()) {
      $raw['data'] = $this->data()->raw();
    }
    return $raw;
  }
}