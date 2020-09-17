# Collections Manager

PHP based Collections Manager

## Installation

```shell script
composer require ialopezg/collections
```

## Requirements

* PHP 5.6+

## Features

* Set item
* Update item
* Remove item
* Lookup for an item
    - By key name when single lookup
    - By dot notation when nested lookup
* Data cache for quick access
* Access to firs level object by invoking its name in the class, by example: ``` echo $collection->itemName;```. Protected properties cannot be invoked.
* Merging of classes are derivative from ```CollectionInterface```.
* Access to its items like associative arrays.

## Usage

```php
use ialopezg\Libraries\Collection;

// class derived from Collection class
class ItemCollection extends Collection { }

$collection = new ItemCollection([]);
// get the value, if not found return default value
echo $collection->get('item_name', 'initial value');
// changing item_name's value
$collection->set('item_name', 'item value');
// print current value
if ($collection->has('item_name')) {
    echo $collection->get('item_name');
}
// remove item_name property
$collection->remove('item_name');

echo $collection->count();
```

Please, check [examples](examples) directory for more details of usage or run:
```shell script
### from linux bash
./server.sh
``` 
or
```shell script
### from windows bash
server.bat
``` 

## License

This project is under the MIT license. For more information see [LICENSE](LICENSE).
