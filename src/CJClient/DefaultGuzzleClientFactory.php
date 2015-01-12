<?php
namespace CJClient;

use GuzzleHttp\Client;

/**
 * @class
 *
 * Factory to create a default guzzle Client object.
 */
class DefaultGuzzleClientFactory implements GuzzleClientFactory {
  /**
   * @see \CJClient\GuzzleClientFactory::createClient()
   */
  public function createClient() {
    $settings = array(
    	'redirect.strict' => TRUE, // Necessary for POSTs to work with redirects.
    	'defaults' => array(
    	  'headers' => array(
    	    'Content-Type' => 'application/vnd.collection+json',
    	    'Accept' => 'application/vnd.collection+json',
    	  ),
    	  'proxy' => 'http://localhost:8888',
      ),
    );
    $client = new Client($settings);
    return $client;
  }
}