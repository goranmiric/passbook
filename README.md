Passbook
================================================================================
Passbook module provides integration to Passbook API.

Installation
--------------------------------------------------------------------------------
  - Download "eymengunay/php-passbook" library from github (inside or outside of drupal root):
    git clone git@github.com:eymengunay/php-passbook.git
  
  - Execute "composer install" inside passbook directory

  - Add "passbook_dir" path variable to settings.php, e.g.:
    $settings['passbook_dir'] = DRUPAL_ROOT . '/../passbook';
    
Configuration
--------------------------------------------------------------------------------
  - Go to the nodule config page "/admin/config/passbook/passbook-settings",
   and configure the Passbook Settings.
   
API documentation
--------------------------------------------------------------------------------
  - http://eymengunay.github.io/php-passbook/api/
   
Usage example
--------------------------------------------------------------------------------
```php
<?php
use Passbook\Pass\Field;
use Passbook\Pass\Barcode;
use Passbook\Pass\Structure;
use Passbook\Type\EventTicket;

// Initialize service.
$passbook = \Drupal::service('passbook.manager');

// Create an event ticket.
$pass = new EventTicket("1254785", "The Beat Goes On");
$pass->setBackgroundColor('rgb(60, 65, 76)');
$pass->setLogoText('Apple Inc.');

// Create pass structure.
$structure = new Structure();

// Add primary field.
$primary = new Field('event', 'The Beat Goes On');
$primary->setLabel('Event');
$structure->addPrimaryField($primary);

// Add secondary field.
$secondary = new Field('location', 'Moscone West');
$secondary->setLabel('Location');
$structure->addSecondaryField($secondary);

// Add auxiliary field.
$auxiliary = new Field('datetime', '2013-04-15 @10:25');
$auxiliary->setLabel('Date & Time');
$structure->addAuxiliaryField($auxiliary);

// Set pass structure.
$pass->setStructure($structure);

// Add barcode.
$barcode = new Barcode(Barcode::TYPE_QR, 'barcodeMessage');
$pass->setBarcode($barcode);

$passbook->create($pass);
````
