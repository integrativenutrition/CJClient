<?php
namespace CJClient;

use GuzzleHttp\Message\FutureResponse;
use GuzzleHttp\Message\Response;

/**
 * @class
 *
 * Represents a Collection+JSON Collection.
 */
class Collection extends Linkset {
  protected $template;
  protected $queries;
  protected $items;
  protected $links;

  /**
   * Waits for pending requests for a list of Collections.
   *
   * @param array $collections
   *   An array of Collection objects to wait for.
   */
  public static function waitForAll(array $collections) {
    foreach ($collections as $collection) {
    	$collection->wait();
    }    
  }

  public function __construct($href) {
    parent::__construct(array('href' => $href), NULL);
  }

  /**
   * Gets the template for this Collection.
   *
   * @return Template|bool
   *   The Template object, if one exists, or FALSE otherwise.
   */
  public function template() {
    if (!isset($this->template)) {
      $this->template = isset($this->raw['template']) ? new Template($this->raw['template'], $this) : FALSE;
    }
    return $this->template;
  }

  /**
   * Wait for the current request (if any) to complete.
   * 
   * @return Collection
   *   This Collection object, suitable for chaining.
   */
  public function wait() {
    try {
      if (isset($this->response) && $this->response instanceof FutureResponse) {
        $this->response->wait();
      }
    } catch(\Exception $e) {
      $this->status = $e->getCode();
    }
    return $this;
  }

  /**
   * Get all queries, keyed by their link relation.
   *
   * @return array
   *   An array of all query templates, keyed by their rel.
   */
  public function queries() {
    if (!isset($this->querymap)) {
      $this->querymap = array();
      if (isset($this->raw['queries'])) {
        foreach ($this->raw['queries'] as $rawquery) {
          $query = new Query($rawquery, $this);
          $this->querymap[$query->rel()] = $query;
        }
      }
    }
    return $this->querymap;
  }

  /**
   * Returns a clone of the query template (if any).
   *
   * @param string $rel
   *   The rel of the query template to clone.
   * @return Query|bool
   *   A clone of the matching query template, or FALSE if none exists.
   */
  public function query($rel) {
    $queries = $this->queries();
    if (isset($queries[$rel])) {
      return clone $queries[$rel];
    }
    else {
      return FALSE;
    }
  }

  /**
   * Get the items present in this collection.
   *
   * These may (optionally) be limited by a set of conditions.
   *
   * @param array $conditions
   *   Optional. A set of key value pairs. If specified, Only items which
   *   contain matching data elements will be returned.
   * @param bool $match_all
   *   If TRUE (default), all conditions must be met. Otherwise, any.
   *   
   * @return array
   *   An array of Item objects which match the conditions.
   */
  public function items($conditions = array(), $match_all = TRUE) {
    if (!isset($this->items)) {
      $this->items = array();
      if (isset($this->raw['items'])) {
        foreach ($this->raw['items'] as $rawitem) {
          $this->items[] = new Item($rawitem, $this);
        }
      }
    }
    if ($conditions) {
      $items = array();
      foreach ($this->items as $item) {
        if ($item->data()->match($conditions, $match_all)) {
          $items[] = $item;
        }
      }
      return $items;
    }
    else {
      return $this->items;
    }
  }

  /**
   * Set the items array.
   *
   * @param array $items
   *   An array of Item objects to set.
   *
   * @return Collection
   *   This collection object.
   */
  public function setItems(array $items) {
    foreach ($items as $item) {
      if (!$item instanceof Item) {
        throw new CJException('Attempt to set a non-item.');
      }
    }
    $this->items = $items;
    return $this;
  }

  /**
   * Filter out items which do not match a set of conditions.
   *
   * @param array $conditions
   *   Optional. A set of key value pairs. If specified, Only items which
   *   contain matching data elements will be returned.
   * @param bool $match_all
   *   If TRUE (default), all conditions must be met. Otherwise, any.
   *
   * @return Collection
   *   This collection object.
   */
  public function filterItems($conditions, $match_all = TRUE) {
    $this->setItems($this->items($conditions, $match_all));
    return $this;
  }


  /**
   * Get the version of this Collection.
   *
   * @return number
   *   The Collection+JSON version of this representation.
   */
  public function version() {
    return $this->raw['version'];
  }

  /**
   * @see \CJClient\CJObject::setRaw()
   */
  public function setRaw($raw) {
    $this->raw = $raw;
    unset($this->items);
    unset($this->queries);
    unset($this->links);
    unset($this->items);
  }

  /**
   * @see \CJClient\Linkset::raw()
   */
  public function raw() {
    $raw = parent::raw();
    if ($this->template()) {
      $raw['template'] = $this->template()->raw();
    }
    if ($this->items()) {
      $raw['items'] = array();
      foreach ($this->items() as $item) {
        $raw['items'][] = $item->raw();
      }
    }
    else {
      unset($raw['items']);
    }
    if ($this->queries()) {
      $raw['queries'] = array();
      foreach ($this->queries() as $query) {
        $raw['queries'][] = $query->raw();
      }
    }
    else {
      unset($raw['queries']);
    }
    return $raw;
  }



}