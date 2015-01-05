<?php
namespace CJClient;

class Linkset extends Fetchable {
  protected $linkmap;

  public function followLinks(array $rels = NULL) {
    if (!isset($rels)) {
      $rels = $this->rels();
    }
    elseif (array_diff($rels, $this->rels())) {
      throw new CJException("Relations not found");
    }
    $cols = array();
    foreach ($rels as $rel) {
      $cols[$rel] = $this->link($rel)->fetch();
    }
    return $cols;
  }

  /**
   * Returns a map of all links, keyed by their relation.
   *
   * @return array
   *   An array of Link objects.
   */
  public function links() {
    if (!isset($this->linkmap)) {
      $this->linkmap = array();
      if (isset($this->raw['links'])) {
        foreach ($this->raw['links'] as $link) {
          $this->linkmap[$link['rel']] = new Link($link, $this);
        }
      }
    }
    return $this->linkmap;    
  }

  /**
   * Get the link relations for all links in this set.
   *
   * @return array
   *   An array of strings representing the link relations.
   */
  public function rels() {
    $this->links();
    return array_keys($this->linkmap);
  }

  /**
   * Returns the link for a specified relation.
   *
   * @param string $rel
   *   The link relation to search for.
   *
   * @return Link|boolean
   *   The link object, if found. FALSE otherwise.
   */
  public function link($rel) {
    $this->links();
    return isset($this->linkmap[$rel]) ? $this->linkmap[$rel] : FALSE;
  }
}
