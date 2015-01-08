<?php
namespace CJClient;

class Item extends Fetchable {
  /**
   * Get the data for this item, if any.
   *
   * @return Data|bool
   *   The Data object in this item, or FALSE if none exists.
   */
  public function data() {
    return isset($this->raw['data']) ? new Data($this->raw['data'], $this) : $this;
  }
}
