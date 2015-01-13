Colleciton+JSON Client for PHP
==============================

Basic Usage
-----------
Fetch the collection at the entry point of an API:
```
$collection = new Collection("http://my.api.com/entrypoint")->fetch()->wait();
if ($collection->status() != 200) {
  throw new Exception('Error');
}
```

Follow a link with a specified relation
```
$rel = 'children';
$collection1 = $collection->link('children')->fetch()->wait();
```

Create a new item in a collection.
```
if ($template = $collection->template()) {
  // Clone the template to create new data.
  $submit = clone $template;
  // Will throw an exception if the template data has no item named 'foo'
  $submit->data()->setValue('foo', 'Foo Value');
  $newItem = $collection->create($submit)->wait();
  // Create will return a 201 status code if successful.
  if ($newItem->status() != 201) {
    throw new Exception('Not created.');
  }
  // The new item will have the href set correctly, but we'll need to fetch
  // the data if we want to use it.
  $newItem->fetch()->wait();
```

Find items in a collection.
Given the following json:
```
"items": [
   { 
     "href": "http://my.api.com/collection/1", 
     "data": [ 
       {"name" => "foo", "value" => "foo value"}
     ]
   },
   { 
     "href": "http://my.api.com/collection/22", 
     "data": [ 
       {"name" => "foo", "value" => "foo value"},
       {"name" => "bar", "value" => "bar value"}
     ]
   }
]
```
The following code will search for items: 
```
$conditions = array(
  'foo' => 'foo value',
  'bar' => 'bar value',
);

// Returns all items which match all of the conditions.
// (just item #2 above)
$items = $collection->items($conditions);

// Returns all items which match any of the conditions.
// (both items #1 and items #2)
$items = $collection->items($conditions, FALSE);
```
