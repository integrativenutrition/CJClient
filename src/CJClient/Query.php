<?php
namespace CJClient;

class Query extends Fetchable {

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
   * Get the data for this item, if any.
   *
   * @return Data|bool
   *   The Data object in this item, or FALSE if none exists.
   */
  public function data() {
    return isset($this->raw['data']) ? new Data($this->raw['data']) : $this;
  }
  
  /**
   * Execute a query given a set of parameters.
   *
   * @param unknown $template
   * @throws CJException
   * @return \CJClient\Collection
   */
  public function execute($data) {
    foreach ($data as $key => $value) {
      if (!$this->data($key)) {
        throw new CJException('Invalid key in query template.');
      }
      $query[] = "$key=$valye";
    }
    $fetcher = new Href($this->href() . '?' . implode('&', $query));
    return $fetcher->fetch();
  }
}