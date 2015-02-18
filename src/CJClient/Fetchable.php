<?php
namespace CJClient;
use GuzzleHttp\Client;
use GuzzleHttp\Message\FutureResponse;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Message\Request;

/**
 * @class
 *
 * Represents a Collection+JSON object with a defined href.
 */
class Fetchable extends CJObject {
  protected $client;
  protected $response;
  protected $status = 0;

  /**
   * Construct a new Fetchable.
   *
   * @throws CJException
   * @see CJObject::__construct
   */
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
    if (!isset($this->client)) {
      $this->client = CJClient::getInstance()->createGuzzleClient();
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
   * Initiate an asynchronous http request to the href of this object.
   *
   * @param string $method
   *   The request method ('get', 'post' or 'put')
   * @param array $options
   *   The guzzle options for this request.
   *
   * @return \CJClient\Collection
   *   The collection returned. Since this request is asynchronous, you
   *   must call the collections wait() method to wait for the Request
   *   to complete before attempting to use the collection.
   */
  public function request($method, $options) {
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
    $target->response = $target->client()->{$method}($target->href(), $options);
    $target->response->then(
        function($rsp) use ($target, $method) {
          if ($method == 'post' && $location = $rsp->getHeader('Location')) {
            $target->setHref($location);
          }
          try {
            // If we get a json response, then parse it, but don't panic if not.
            $json = $rsp->json();
            if (isset($json['collection'])) {
              $target->raw = $json['collection'];
            }
          } catch(\Exception $e) {} 
          $target->status = $rsp->getStatusCode();
        },
        function($ex) use ($target) {
          $target->status = $ex->getCode();
          $target->response = $ex->getResponse();
        }
    );
    return $target;
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
    return $this->request('get', array('future' => TRUE));
  }

  /**
   * Synchronous fetch.
   *
   * This method fetches the resource at the href, and throws an exception if
   * the return status is not 200.
   *
   * @throws CJException
   * @return Collection
   *   The Collection object containing the resource representation.
   */
  public function mustFetch() {
    $collection = $this->fetch()->wait();
    if ($collection->status() != 200) {
      throw new CJException('Failed to fetch resource at ' . $collection->href() . ', Status=' . $collection->status());
    }
    return $collection;
  }

  /**
   * Gets the http status of the last request.
   *
   * @return number
   *   The http status of the last request.
   */
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

  public function response() {
    return $this->response;
  }

}
