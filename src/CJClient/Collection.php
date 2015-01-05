<?php
namespace CJClient;

class Collection extends Linkset {

  public function __construct($href) {
    parent::__construct(array('href' => $href), NULL);
  }

  public function template() {
    return isset($this->raw['template']) ? new Template($this->raw['template']) : FALSE;
  }

  /**
   * Wait for the current request (if any) to complete.
   * 
   * @return number
   *   The status code of the request, or zero if no request active.
   */
  public function wait() {
    try {
      if (isset($this->response) && $this->response instanceof FutureResponse) {
        $this->response->wait();
      }
    } catch(\Exception $e) {
      $this->status = $e->getCode();
    }
    return isset($this->status) ? $this->status : 0;
  }

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

  public function version() {
    return $this->_property('version');
  }
}