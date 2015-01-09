<?php
namespace CJClient;

use GuzzleHttp\Message\FutureResponse;

/**
 * @class
 *
 * Represents a Collection+JSON Collection.
 */
class Collection extends Linkset {
  /**
   * Waits for pending requests for a list of Collections.
   *
   * @param array $collections
   *   An array of Collection objects to wait for.
   *
   * @return array
   *   An array of status codes for each of the requests.
   */
  public static function waitForAll(array $collections) {
    $statuses = array();
    foreach ($fetchables as $fetchable) {
    	$statuses[] = $fetchable->wait()->status();
    }    
    return $statuses;
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
   * Creates a new item in this Collection.
   *
   * This method issues a POST to the href of the collection.
   *
   * @todo If this collection specifies a template, than the keys of the posted
   * data must match the keys specified in the template.
   *
   * @param Template $template
   *   A completed template object containing a representation of the item.
   *
   * @return \CJClient\Collection
   *   A Collection object representing the newly created Item. Note that this
   *   Collection must be fetched before it will be fully populated. 
   */
  public function create(Template $template) {
    $target = new Collection('');
    //$target->setClient($this->client());
    $post = array('template' => $template->raw());
    $target->response = $this->client()->post($this->href() . '/', array(/*'exceptions' => FALSE,*/  'json' => $post, 'future' => TRUE));
    $target->response->then(
        function($rsp) use ($target) {
          echo "Success";
          $target->setHref($rsp->getHeader('Location'));
          $target->status = $rsp->getStatusCode();
        },
        function($ex) use ($target) {
          echo "Failure\n";
          $target->status = $ex->getCode();
        }
    );
    return $target;
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
   * Get the version of this Collection.
   *
   * @return number
   *   The Collection+JSON version of this representation.
   */
  public function version() {
    return $this->raw['version'];
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