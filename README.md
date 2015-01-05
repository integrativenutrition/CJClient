Colleciton+JSON Client for PHP
==============================

Basic Usage
-----------
Fetch the collection at the entry point of an API:
```
$collection = new Collection("http://my.api.com/entrypoint")->fetch();
$code = $colleciton->wait();
if ($code != 200) {
  throw new Exception('Error');
}
```

Follow a link with a specified relation:
```
$rel = 'children';
$collection1 = $collection->link('children')->fetch();
$code = $collection1->wait();
```

to be continued...
