<?php
namespace CJClient;

class Href extends Fetchable {
  public function __construct($href) {
    parent::__construct(array('href' => $href));
  }
}