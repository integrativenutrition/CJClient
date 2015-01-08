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
    return isset($this->raw['template']) ? new Template($this->raw['template']) : FALSE;
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
   * @param array $data
   *   An array of data values to be posted.
   *
   * @return \CJClient\Collection
   *   A Collection object representing the newly created Item. Note that this
   *   Collection must be fetched before it will be fully populated. 
   */
  public function create($data) {
    $target = new Collection($this->href());
    $target->response = $this->getClient()->post($this->href(), array('json' => $data, 'future' => TRUE));
    $target->response->then(
        function($rsp) use ($target) {
          $target->setHref($rsp->getHeader('Location'));
          $target->status = $rsp->getStatusCode();
        },
        function($ex) use ($target) {
          $target->status = $ex->getCode();
        }
    );
    return $target;
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
    $items = array();
    if (isset($this->raw['items'])) {
      foreach ($this->raw['items'] as $rawitem) {
        $item = new Item($rawitem, $this);
        if (!$conditions || $item->data()->match($conditions, $match_all)) {
          $items[] = $item;
        }
      }
    }
 
    return $items;
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
}