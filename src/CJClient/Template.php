<?php 
namespace CJClient;

class Template extends CJObject {
  protected $data;
  // When set, contains an alternate href to use for update (PUT) ops. This
  // allows updating of an item within a collection using the item's href,
  // not the Collection's (for cases where the collection was obtained
  // via a query.
  protected $updateHref = NULL;

  /**
   * Set this template to submit a create operation.
   *
   * @return \CJClient\Template
   *   This template object, for chaining.
   */
  public function setModeCreate() {
    $this->updateHref = NULL;
    return $this;
  }

  /**
   * Set this template to submit an update operation.
   *
   * @param Item $item
   *   When specified, use the href of this item for the update request. This
   *   allows updating of an item within a collection using the item's href,
   *   not the Collection's (for cases where the collection was obtained
   *   via a query).
   *   
   *   NOTE: If omitted, and the colleiton contains only a single item, the href
   *   of that item will be used.
   *
   * @return \CJClient\Template
   *   This template object, for chaining.
   */
  public function setModeUpdate(Item $item = NULL) {
    if (!isset($this->parent)) {
      throw new CJException('Must have a parent collection to update');
    }
    if (isset($item)) {
      // If an item was specified, use its href.
      $this->updateHref = $item->href();
    }
    elseif (count($this->parent->items()) == 1) {
      // If the collection contains only a single item, use that href.
      $this->updateHref = current($this->parent->items())->href();
    }
    else {
      // Use the href of the collection.
      $this->updateHref = $this->parent->href();
    }
    return $this;
  }

  /**
   * Determine whether this template will submit an update or create operation.
   * 
   * @return string
   *   Either 'update' or 'create'.
   */
  public function getMode() {
    return isset($this->updateHref) ? 'update' : 'create';
  }
 
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

  /**
   * Submit this template asynchronously.
   *
   * The method is determined by the last call to setMethodPost() or
   * setMethodPut().
   *
   * The template MUST have a parent collection.
   *
   * This is an asynchronous request - the result will contain a promise.
   *
   * @return \CJClient\Collection
   *   The collection object which is the result of the request.
   *
   * @throws CJException
   *   If the template has no parent collection.
   */
  public function submit() {
    if (empty($this->parent) || !($this->parent instanceof Collection)) {
      throw new CJException('Cannot submit a template without a parent collection.');
    }

    $post = array('template' => $this->raw());
    $method = isset($this->updateHref) ? 'put' : 'post';
    $href = isset($this->updateHref) ? $this->updateHref : $this->parent->href();
    $target = new Collection($href);
    $options = array(
    	'exceptions' => FALSE,
      'json' => $post,
      'future' => TRUE,
    );
    return $target->request($method, $options);
  }

  /**
   * Submit this template synchronously.
   *
   * This method submits the template, and throws an exception if
   * the return status is not 200 (update) or 201 (create).
   *
   * @return Collection
   *   The Collection object containing the resource representation.
   *
   * @throws CJException
   *   In the event of a network error.
   *
   * @see Template::submit()
   */
  public function mustSubmit() {
    $collection = $this->submit()->wait();
    $target_status = isset($this->updateHref) ? 200 : 201;
    if ($collection->status() != $target_status) {
      $operation = $this->method == 'post' ? 'create' : 'update';
      throw new CJException('Failed to ' . $operation . ' resource at ' . $collection->href() . ', Status=' . $collection->status());
    }
    return $collection;
  }
}