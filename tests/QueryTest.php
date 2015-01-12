<?php
use CJClient\Query;
use GuzzleHttp\Subscriber\Mock;
use GuzzleHttp\Subscriber\History;
use GuzzleHttp\Message\Response;
use GuzzleHttp\Stream\Stream;
class QueryTest extends PHPUnit_Framework_TestCase {
  protected $rawQuery = array(
  	'href' => "http://x.y.z",
    'rel' => "my-test-query",
    'prompt' => 'My Test Query',
    'data' => array(
      array(
  	    'name' => 'foo',
        'prompt' => 'Foo',
      ),
      array(
  	    'name' => 'bar',
        'prompt' => 'Bar',
      ),
    ),
  );

  public function testPrompt() {
    $query = new Query($this->rawQuery, NULL);
    $this->assertEquals('My Test Query', $query->prompt(), 'The prompt is correct.');
  }

  public function testRel() {
    $query = new Query($this->rawQuery, NULL);
    $this->assertEquals('my-test-query', $query->rel(), 'The rel is correct.');
  }

  public function testRaw() {
    $query = new Query($this->rawQuery, NULL);
    $this->assertEquals($this->rawQuery, $query->raw(), 'The raw representation is correct.');
  }

  public function testExecute() {
    $query = new Query($this->rawQuery, NULL);
    $client = $query->client();

    // Create a successful response
    $r1 = new Response(200);
    $r1->addHeader('Content-Type', 'application/vnd.collection+json');
    $r1->setBody(Stream::factory(json_encode(array('collection' => array('href' => '')))));
    // And a server error.
    $r2 = new Response(503);
    $mock = new Mock(array($r1, $r2));
    $client->getEmitter()->attach($mock);
    // Keep track of our requests.
    $history = new History();
    $client->getEmitter()->attach($history);

    // Make a request with a good response.
    $query->data()->setValue('foo', 'fooval');
    $query->execute()->wait();
    $lastRequest = $history->getLastRequest();
    $this->assertEquals(1, count($lastRequest->getQuery()->count()), 'The querystring has the correct number of params.');
    $this->assertEquals('fooval', $lastRequest->getQuery()->get('foo'), 'The value of the query param is correct');
  }
}
