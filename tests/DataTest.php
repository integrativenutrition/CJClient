<?php
use CJClient\Data;
use CJClient\CJException;
class DataTest extends PHPUnit_Framework_TestCase {
  protected $testData = array(
  	array(
  	  'name' => 'foo',
  	  'prompt' => 'Foo Prompt',
  	  'value' => 'fooValue',
    ),
    array(
  	  'name' => 'bar',
      'value' => 'barValue',
    ),
    array(
      'name' => 'baz',
      'prompt' => 'Baz Prompt',
    ),
  );

  public function testNames() {
    $data = new Data($this->testData, NULL);
    $this->assertEquals(array('foo', 'bar', 'baz'), $data->names(), 'The data names are correct.');
  }

  public function testValue() {
    $data = new Data($this->testData, NULL);
    $this->assertEquals('fooValue', $data->value('foo'), 'A value is correct');
    $this->assertSame(FALSE, $data->value('baz'), 'A missing value is correctly reported as false');
  }

  public function testPrompt() {
    $data = new Data($this->testData, NULL);
    $this->assertEquals('Foo Prompt', $data->prompt('foo'), 'A prompt is correct');
    $this->assertSame(FALSE, $data->prompt('bar'), 'A missing prompt is correctly reported as false');
  }

  public function testRaw() {
    $data = new Data($this->testData, NULL);
    $this->assertEquals($this->testData, $data->raw(), 'The raw data is correct');
  }

  public function testSetValue() {
    $data = new Data($this->testData, NULL);
    $exceptionThrown = FALSE;
    try {
      $data->setValue('diggle', 'diggleValue');
    }
    catch (CJException $e) {
      $exceptionThrown = TRUE;
    }
    $this->assertTrue($exceptionThrown, 'An exception is thrown when trying to set a nonexistent data key.');
    $data->setValue('foo', 'NewFooValue');
    $this->assertEquals('NewFooValue', $data->value('foo'), 'The new value is set correctly');
    $raw = $data->raw();
    foreach ($raw as $values) {
      if ($values['name'] == 'foo') {
        $this->assertEquals('NewFooValue', $values['value'], 'The newly set value is reflected in the raw data.');
        break;
      }
    }
  }

  public function testMatch() {
    $data = new Data($this->testData, NULL);
    $cond1 = array('foo' => 'fooValue');
    $this->assertTrue($data->match($cond1), 'A single condition matches correctly.');
    $cond2 = array('foo' => 'fooValue', 'bar' => 'barValue');
    $this->assertTrue($data->match($cond2), 'A pair of conditions match.');
    $this->assertTrue($data->match($cond2, FALSE), 'A pair of conditions ORd match');
    $cond3 = array('foo' => 'fooValue', 'bar' => 'notBarValue');
    $this->assertFalse($data->match($cond3), 'A pair of conditions with one wrong does not match.');
    $this->assertTrue($data->match($cond3, FALSE), 'A pair of conditions ORd match even when one is wrong.');
  }
}
  
