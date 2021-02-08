# FritzBox TAM API PHP Client 
Access your Fritz!Box ANSWERING MACHINE via PHP SOAP API

App downloads audio recordings from answering machine (TAM) via SOAP API.

* AVM resources:

https://avm.de/service/schnittstellen/  
https://avm.de/fileadmin/user_upload/Global/Service/Schnittstellen/AVM_TR-064_first_steps.pdf  
https://avm.de/fileadmin/user_upload/Global/Service/Schnittstellen/x_tam.pdf  

This is just a working test case how to download answering machine messages, it is not meant to be used as a library.

Inspired by (forked from) https://github.com/geki-yaba/FritzBoxPHP]FritzBoxPHP

# Install and run

 1. run `composer install`
 2. copy .env.example to .env and edit hostname, username and password
 3. run `php index.php` (all audio files are stored in ./storage folder)
