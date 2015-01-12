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
   * @param Template $template
   *   The filled in query template.
   * @throws CJException
   * @return \CJClient\Collection
   */
  public function execute() {
    $data = $this->data();
    foreach ($data->names() as $name) {
      $value = trim($data->value($name));
      if (!empty($value)) {
        $query[] = "$name=$value";
      }
    }
    $fetcher = new Href($this->href() . '?' . implode('&', $query));
    $fetcher->setClient($this->client());
    return $fetcher->fetch();
  }
}