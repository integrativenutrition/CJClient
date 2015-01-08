<?php
namespace CJClient;
use GuzzleHttp\Client;
use GuzzleHttp\Message\FutureResponse;
use GuzzleHttp\Exception\RequestException;

/**
 * @class
 *
 * Represents a Collection+JSON object with a defined href.
 */
class Fetchable extends CJObject {
  protected $client;
  protected $response;
  protected $status = 0;

  public function __construct($raw, $parent) {
    if (!isset($raw['href'])) {
      throw new CJException("Missing href.");
    }
    parent::__construct($raw, $parent);
  }

  /**
   * Get the Guzzle client object used to make requests.
   *
   * @return \GuzzleHttp\Client
   *   The Client object to use.
   */
  public function client() {
    // @todo There should be a mechanism to inject a client with
    // global default options.
    if (!isset($client)) {
      $client = new Client();
      $client->setDefaultOption('headers/Content-Type', 'application/vnd.collection+json');
      $client->setDefaultOption('headers/Accept', 'application/vnd.collection+json');
      $this->client = $client;
    }
    return $this->client;
  }

  /**
   * Set the Guzzle client object used to make requests.
   *
   * @param \GuzzleHttp\Client $client
   *   The Client object to use.
   */
  public function setClient(\GuzzleHttp\Client $client) {
    $this->client = $client;
  }

  /**
   * Retrieve the collection located at the href of this Fetchable.
   *
   * If this fetchable is a Collection, the contents are refreshed
   * with the data from the href. Otherwise, a new Collection is
   * created which contains the data from the href.
   *
   * This is an asynchronous operation. If you want to block until
   * the network request is complete, follow this with a call to
   * Collection::wait().
   *
   * @return Collection
   *   A Collection object which will contain the data from this
   *   href once the request completes.
   */
  public function fetch() {
    if ($this instanceof Collection) {
      $target = $this;
    }
    else {
      $target = new Collection($this->href());
      // Pass along our client.
      $target->setClient($this->client());
    }
    // Reset the status.
    $target->status = 0;
    // Initiate the request.
    $target->response = $target->client()->get($target->href(), array('future' => TRUE));
    $target->response->then(
        // On a successful response, set the raw data and response status.
        function($rsp) use ($target) {
          $raw = $rsp->json();
          $target->raw = $raw['collection'];
          $target->status = $rsp->getStatusCode();
        },
        // On an error, just set the status.
        function($ex) use ($target) {
          $target->status = $ex->getCode();
        }
    );
    return $target;
  }

  public function status() {
    return isset($this->status) ? $this->status : 0;
  }


  /**
   * Get the href for this item.
   *
   * @return string
   *   The href.
   */
  public function href() {
    return $this->raw['href'];
  }

  /**
   * Sets the href property of this fetchable.
   *
   * @param string $href
   *   The href to set, must be a valid URL.
   */
  public function setHref($href) {
    if (!isset($this->raw)) {
      $this->raw = array();
    }
    $this->raw['href'] = $href;
  }

}
