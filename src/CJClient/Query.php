<?php
namespace CJClient;

class Query extends Fetchable {
  protected $data;

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
   * @see \CJClient\CJObject::setRaw()
   */
  public function setRaw($raw) {
    parent::setRaw($raw);
    unset($this->data);
  }

  /**
   * Get the prompt.
   *
   * @return string|boolean
   *   The prompt, or FALSE if none exists.
   */
  public function prompt() {
    return isset($this->raw['prompt']) ? $this->raw['prompt'] : FALSE;
  }

  /**
   * Get the name.
   *
   * @return string|boolean
   *   The name, or FALSE if none exists.
   */
  public function name() {
    return isset($this->raw['name']) ? $this->raw['name'] : FALSE;
  }

  /**
   * Get the rel.
   *
   * @return string|boolean
   *   The rel, or FALSE if none exists.
   */
  public function rel() {
    return isset($this->raw['rel']) ? $this->raw['rel'] : FALSE;
  }

  /**
   * Execute a query given a set of parameters.
   *
   * @throws CJException
   * @return \CJClient\Collection
   */
  public function execute() {
    return $this->prepareQuery()->fetch();
  }

  /**
   * Prepare the url for this query.
   *
   * @return \CJClient\Href
   *   The Fetchable href.
   */
  protected function prepareQuery() {
    $data = $this->data();
    $query = array();
    foreach ($data->names() as $name) {
      $value = trim($data->value($name));
      if (!empty($value)) {
        $query[$name] = $value;
      }
    }
    $url = $this->href();
    if (!empty($query)) {
      $url .= '?' . http_build_query($query);
    }
    $fetcher = new Href($url);
    $fetcher->setClient($this->client());
    return $fetcher;
  }

  /**
   * Synchronous query execution.
   *
   * @throws CJException
   *   If there is an error executing the query.
   *
   *
   * @return \CJClient\Collection>
   */
  public function mustExecute() {
    return $this->prepareQuery()->mustFetch();    
  }
}