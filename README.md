Passbook
================================================================================
Drupal 8 passbook integration.

Requirements
--------------------------------------------------------------------------------
  - You need to have valid ssl certificate, otherwise you will not be able to download passbook package using.

Installation
--------------------------------------------------------------------------------
  - cd $DRUPAL
  - composer update
  - composer require "composer require gorrax/passbook"
    
Configuration
--------------------------------------------------------------------------------
  - Enable "Passbook" module.
  - If you will use default passbook webservice, enable "passbook_webservice" module too.
  - Go to the module config page "/admin/config/passbook/passbook-settings", and configure the Passbook Settings.
  - All the needed information you will find at the same page under "Documentation" section.
  
How to use it
--------------------------------------------------------------------------------
  - Go to "/admin/structure/passbook" and create a new passbook type entity.
  - Add fields. You should use "Passbook" fields and and "Image: Logo" and "Image: Thumbnail" fields.
  - When you are adding passbook fields, on field setting page you will see "Passbook layout" for the passbook type,
  so you know where that field will appear.
  - Then you need to configure "Display" (Default is user for passbook package) for the passbook type.
  - If you hide label it will not be added to passbook package, etc.
  - Passbook package will be created/updated when you create/update passbook entity.
  
API documentation
--------------------------------------------------------------------------------
  - http://eymengunay.github.io/php-passbook/api/