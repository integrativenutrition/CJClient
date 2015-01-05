<?php
namespace CJClient;
use GuzzleHttp\Client;
use GuzzleHttp\Message\FutureResponse;
use Guzzle\Http\Exception\RequestException;

/**
 * @class
 *
 * Represents a Collection+JSON object with a defined href.
 */
class Fetchable extends CJObject {
  protected $client;
  protected $response;

  public static function waitForAll(array $fetchables) {
    $statuses = array();
    foreach ($fetchables as $fetchable) {
    	$statuses[] = $fetchable->wait();
    }    
    return $statuses;
  }

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
  public function getClient() {
    // @todo There should be a mechanism to inject a client with
    // global default options.
    if (!isset($client)) {
      $client = new Client();
      $client->setDefaultOption('headers/Content-Type', 'application/vnd.collection+json');
      $client->setDefaultOption('headers/Accept', 'application/vnd.collection+json');
      $client->setDefaultOption('proxy', 'http://localhost:8888');
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
  public function setClient(\Guzzle\Http\Client $client) {
    $this->client = $client;
  }

  public function fetch($target = NULL) {
    //if (!isset($target)) {
      $target = new Collection($this->href());
      // Pass in our client.
      $target->setClient($this->getClient());
    //}
    //else {
    //  $target->setHref($this->href());
    //}
    $target->response = $this->getClient()->get($this->href(), array('future' => TRUE));
    $target->response->then(
        function($rsp) use ($target) {
          $raw = $rsp->json();
          $target->raw = $raw['collection'];
          $target->status = $rsp->getStatusCode();
        },
        function($ex) use ($target) {
          $target->status = $ex->getCode();
        }
    );
    return $target;
  }

  public function status() {
    return isset($this->status) ? $this->status : 0;
  }

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
