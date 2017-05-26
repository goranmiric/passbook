Passbook
================================================================================
Passbook module provides integration to Passbook API.

Installation
--------------------------------------------------------------------------------
  - cd $DRUPAL
  - composer update
  - composer require "eo/passbook:dev-master"
    
Configuration
--------------------------------------------------------------------------------
  - Go to the nodule config page "/admin/config/passbook/passbook-settings",
   and configure the Passbook Settings.
   
   - P12 File Path
     - To register a pass type identifier, do the following:
       - In <a href="https://developer.apple.com/account/#/welcome">Certificates, Identifiers & Profiles</a>, select "Certificates, Identifiers & Profiles".
       - Then under "Identifiers" select "Pass Type IDs".
       - Then click (+) and create a new one (Enter the description and pass type identifier, and click Submit).
       - Then under "Certificates" click "Production".
       - Then click (+) and select "Pass Type ID Certificate", and click "Continue".
       - For "Pass Type ID" select previously created Pass Type ID, and click "Continue".
       - Follow instruction from the page and create a CSR file and click "Continue".
       - Select previously created ".certSigningRequest" file and click "Continue".
       - Then click "Download" to download ".cer" file.
     - Once you have downloaded the Apple iPhone certificate from Apple, export it to the P12 certificate format.
       - On your Mac go to the folder Applications > Utilities and open Keychain Access.
       - If you have not already added the certificate to Keychain, select File > Import. Then navigate to the certificate file (the .cer file) you obtained from Apple.
       - Select the Keys category in Keychain Access.
       - Select the private key associated with your Pass Type ID Certificate. The private key is identified by the Pass Type ID Developer: public certificate that is paired with it.
       - Select File > Export Items.
       - Save your key in the Personal Information Exchange (.p12) file format.
       - You will be prompted to create a password that is used when you attempt to import this key on another computer.
       
       
API documentation
--------------------------------------------------------------------------------
  - http://eymengunay.github.io/php-passbook/api/