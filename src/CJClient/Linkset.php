<?php
namespace CJClient;

/**
 * Represents any C+J object which contains links.
 *
 * These include Items and Collections.
 */
class Linkset extends Fetchable {
  /**
   * @var array
   *   An array of links, keyed by their rels.
   */
  protected $linkmap;

  /**
   * @see \CJClient\CJObject::raw()
   */
  public function raw() {
    $raw = parent::raw();
    if ($this->links()) {
      $raw['links'] = array();
      foreach ($this->links() as $link) {
        $raw['links'][] = $link->raw();
      }
    }
    else {
      unset($raw['links']);
    }
    return $raw;
  }

  /**
   * Follows the links identified by the specified relations.
   *
   * @param array $rels
   *   An array of link relation strings.
   *
   * @throws CJException
   *
   * @return array
   *   An array of Collection objects representing the targets of each link.
   *   These requests may not have completed, so you must call wait() on
   *   each to use the completed results. 
   */
  public function followLinks(array $rels = NULL) {
    if (!isset($rels)) {
      $rels = $this->rels();
    }
    elseif (array_diff($rels, $this->rels())) {
      throw new CJException("A link with the specified relation does not exist in this LinkSet");
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
