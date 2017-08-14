<?php

//client
$options = [
	'location' 	=> 'localhost/week8/wee8.php'
	'uri'		=> 'localhost/week8'];

$client = new SoapClient(NULL, $options);

echo $client->getMessage() . "\n";
echo $client->addNumbers(3, 5) . "\n";

//WiP