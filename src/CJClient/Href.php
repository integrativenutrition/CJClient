<?php
namespace CJClient;

/**
 * @class
 *
 * A fetchable URL.
 */
class Href extends Fetchable {
  public function __construct($href) {
    parent::__construct(array('href' => $href), NULL);
  }
}
