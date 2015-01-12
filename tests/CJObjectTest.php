<?php
use CJClient\CJObject;

class CJObjecTest extends PHPUnit_Framework_TestCase {
   public function testRaw() {
     $data = array(
       'A' => array(
     	   'b' => 'foo',
         'C' => 'Bar',
       ),
       'd' => 'baz',
     );
     $normalData = array(
       'a' => array(
     	   'b' => 'foo',
         'c' => 'Bar',
       ),
       'd' => 'baz',
     );
     $object = new CJObject($data, NULL);
     $raw = $object->raw();
     $this->assertEquals($normalData, $raw, 'Raw output equals normalized input');
   }
 