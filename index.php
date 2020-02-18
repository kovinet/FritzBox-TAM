<?php
declare(strict_types=1);

require_once './vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

use function FritzBox\getServiceData;
use function FritzBox\soapCall;
use function FritzBox\soapClient;

require_once('fritzbox.php');



# user and password
# - user is optional for default setup; simply set any name
# - otherwise use FritzBox-User with read/write access
$user = getenv('FRITZBOX_USERNAME');
$pass = getenv('FRITZBOX_PASSWORD');

# fritz!box soap server
//$base_uri = "http://192.168.0.254:49000/tr64desc.xml";
$base_uri = "https://192.168.0.254:49443";

# description of services
$desc = "tr64desc.xml";

# function signatures, variables and its data types
$scpd = "x_tamSCPD.xml";

# function to execute
$action = "GetMessageList";



try
{
    # receive service description
    $service = getServiceData($base_uri, $desc, $scpd);

    if ($service === false)
    {
        throw new Exception("no service found in ". $scpd);
    }

    #print_r($service);

    # set user and password
    $service['login'] = $user;
    $service['password'] = $pass;

    # receive variables and its data types belonging to action
    #$stateVars = \FritzBox\getStateVars($base_uri, $service, $action);

    #if ($stateVars === false)
    #{
    #    throw new Exception("no state variables belonging to $action");
    #}

    #print_r($stateVars);

    # create soap client
    $client = soapClient($service);

    # execute action published by service
    $noOfTelephones = (int)soapCall($client, $action);

    echo "no of dect telephones: ". $noOfTelephones . PHP_EOL;

    $action = "GetGenericDectEntry";

    for($i = 0; $i < $noOfTelephones; $i++)
    {
        $result = soapCall($client, $action,
                        new SoapParam((int)$i, 'NewIndex'));

        $line = ($result['NewActive'] != 0) ? "busy" : "open";

        echo "name(". $result['NewName'] .") id(". $result['NewID'] .") line(". $line .")" . PHP_EOL;
    }
}
catch(Exception $e)
{
    echo $e->__toString() . PHP_EOL;
}
