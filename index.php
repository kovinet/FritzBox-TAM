<?php
declare(strict_types=1);

require_once './vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

use function FritzBox\getServiceData;
use function FritzBox\getStateVars;
use function FritzBox\soapCall;
use function FritzBox\soapClient;
use function FritzBox\getHTTPSContent;

require_once('fritzbox.php');


//user and password
//- user is optional for default setup; simply set any name
//- otherwise use FritzBox-User with read/write access
$user = getenv('FRITZBOX_USERNAME');
$pass = getenv('FRITZBOX_PASSWORD');

//fritz!box soap server
//$base_uri = "http://192.168.0.254:49000/tr64desc.xml";
$base_uri = "https://192.168.0.254:49443";

//description of services
$desc = "tr64desc.xml";

//function signatures, variables and its data types
$scpd = "x_tamSCPD.xml";
$action = "GetList";

try
{
    //receive service description
    $service = getServiceData($base_uri, $desc, $scpd);

    if ($service === false)
    {
        throw new \RuntimeException("no service found in ". $scpd);
    }

    #print_r($service);

    //set user and password
    $service['login'] = $user;
    $service['password'] = $pass;

    //receive variables and its data types belonging to action
    /*
    $stateVars = getStateVars($base_uri, $service, $action);

    if ($stateVars === false)
    {
        throw new Exception("no state variables belonging to $action");
    }
    print_r($stateVars);exit;
    */

    //create soap client
    $client = soapClient($service);

    //execute action published by service
    $url = null;
    $arg = new \stdClass();
    $arg->NewIndex = 0;
    $list = soapCall($client, $action);

    echo $list;

    $action = 'GetMessageList';
    //result equals to
    $listURL = soapCall($client, $action,
        new SoapParam(1, 'NewIndex'));

    $parts = parse_url($listURL);
    parse_str($parts['query'], $query);
    $sid = $query['sid'];

    echo 'TAMList URL: ' . $listURL . PHP_EOL;

    $TAMList = getHTTPSContent($listURL);

    if (empty($TAMList)) {
        throw new \RuntimeException('No messages found.');
    }



    echo "sid: " . $sid . PHP_EOL;

    $xml = simplexml_load_string($TAMList);
    if ($xml === false) {
        echo "Failed loading XML: ";
        foreach(libxml_get_errors() as $error) {
            echo "<br>", $error->message;
        }
    } else {
        $i = 0;
        foreach ($xml->Message as $message) {
            $i++;
            echo $message->Path . PHP_EOL;
            $file = getHTTPSContent($base_uri . $message->Path . '&sid=' . $sid);
            file_put_contents('file' . $i . '.wav', $file);
        }
    }


    echo PHP_EOL;

    //var_dump($list);
    //var_dump($url);

    /*

    $action = "GetGenericDectEntry";

    for($i = 0; $i < $noOfTelephones; $i++)
    {
        $result = soapCall($client, $action,
                        new SoapParam((int)$i, 'NewIndex'));

        $line = ($result['NewActive'] != 0) ? "busy" : "open";

        echo "name(". $result['NewName'] .") id(". $result['NewID'] .") line(". $line .")" . PHP_EOL;
    }
    */
}
catch(Exception $e)
{
    echo $e->__toString() . PHP_EOL;
}
