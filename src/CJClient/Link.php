<?php
namespace CJClient;

class Link extends Fetchable {
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
   * Get the render.
   *
   * @return string|boolean
   *   The render, or FALSE if none exists.
   */
  public function render() {
    return isset($this->raw['render']) ? $this->raw['render'] : FALSE;
  }
}
