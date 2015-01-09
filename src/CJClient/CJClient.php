<?php
namespace CJClient;

/**
 * @class
 *
 * Main CJClient Runtime.
 */
class CJClient {
  /**
   * @var CJClient
   *   The singleton instance of the CJClient runtime.
   */
  private static $instance;

  /**
   * Get the singleton instance.
   *
   * @return \CJClient\CJClient
   *   The CJClient object for this request.
   */
  public static function getInstance() {
    if (!isset(static::$instance)) {
      static::$instance = new CJClient();
    }
    return static::$instance;
  }

  /**
   * @var GuzzleClientFactory
   *   The factory that will be used to create Guzzle client objects.
   */
  protected $guzzleClientFactory;
  
  /**
   * Construct a new CJClient object.
   */
  private function __construct() {
  	$this->guzzleClientFactory = new DefaultGuzzleClientFactory();
  }
  
  /**
   * Creates a guzzle client.
   *
   * @return Guzzlehttp\Client
   *   A Guzzle client which can be used for all requests.
   */
  public function createGuzzleClient() {
    return $this->guzzleClientFactory->createClient();
  }

  /**
   * Set the factory used to create Guzzle clients.
   *
   * @param GuzzleClientFactory $factory
   *   A class which implements the createClient() method.
   */
  public function setGuzzleClientFactory(GuzzleClientFactory $factory) {
    $this->guzzleClientFactory = $factory;
  }
}