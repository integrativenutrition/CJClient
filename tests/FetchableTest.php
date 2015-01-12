<?php
use CJClient\Fetchable;
use GuzzleHttp\Subscriber\Mock;
use GuzzleHttp\Message\Response;
use GuzzleHttp\Stream\Stream;
use GuzzleHttp\Subscriber\History;
class FetchableTest extends PHPUnit_Framework_TestCase {
  protected $href = "http://api.test.com";
  protected $jsonData = array(
  	'collection' => array(
  	  'href' => 'http://api.test.com/canonical',
    ),
  );

  /**
   * @expectedException CJClient\CJException
   */
  public function testMissingHref() {
    $data = array('foo' => 'bar');
    $fetchable = new Fetchable($data, NULL);
  } 

  public function testHref() {
    $data = array('href' => $this->href);
    $fetchable = new Fetchable($data, NULL);
    $href = $fetchable->href();
    $this->assertEquals($this->href, $href, 'The href was initialized correctly');
  }

  public function testInitialStatus() {
    $data = array('href' => $this->href);
    $fetchable = new Fetchable($data, NULL);
    $this->assertSame(0, $fetchable->status(), "The initial status is 0");
  }

  public function testFetch() {
    $data = array('href' => $this->href);
    $fetchable = new Fetchable($data, NULL);
    $client = $fetchable->client();

    // Create a successful response
    $r1 = new Response(200);
    $r1->addHeader('Content-Type', 'application/vnd.collection+json');
    $r1->setBody(Stream::factory(json_encode($this->jsonData)));
    // And a server error.
    $r2 = new Response(503);
    $mock = new Mock(array($r1, $r2));
    $client->getEmitter()->attach($mock);
    // Keep track of our requests.
    $history = new History();
    $client->getEmitter()->attach($history);

    // Make a request with a good response.
    $col = $fetchable->fetch()->wait();
    $lastRequest = $history->getLastRequest();
    $this->assertEquals('application/vnd.collection+json', $lastRequest->getHeader('Content-Type'), 'The content type header was set properly on the request.');
    $this->assertEquals('application/vnd.collection+json', $lastRequest->getHeader('Accept'), 'The Accept header was set properly on the request.');
    $this->assertEquals(200, $col->status(), 'The fetched collection has the correct status');
    $this->assertEquals($this->jsonData['collection'], $col->raw(), 'The fetched collecition has the correct data.');
    $this->assertEquals("OK", $col->response()->getReasonPhrase());

    // Make a request with a bad response.
    $col = $fetchable->fetch()->wait();
    $this->assertEquals(503, $col->status(), 'The fetched collection has the correct error status');
    $this->assertEquals("Service Unavailable", $col->response()->getReasonPhrase());
  }
}