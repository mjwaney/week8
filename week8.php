<?php
require_once("SQL.php");
function __autoload($SQL)
{
	require_once "../week7/week7.php";
}

// <!-- · JSON -->
// <!-- o Bouw je applicatie van vorige week verder uit met een webservice om JSON-data uit 1 of meerdere tabellen te tonen op het scherm met json_encode() -->

// <!-- o Experimenteer ook met alle options voor json_encode zoals op bladzijde 181 staan van het boek -->
// get the HTTP method, path and body of the request
$method = $_SERVER['REQUEST_METHOD'];
$request = explode('/', trim($_SERVER['PATH_INFO'],'/'));
$input = json_decode(file_get_contents('php://input'),true);
 
// connect to the mysql database
$link = mysqli_connect('localhost', 'root', '', 'week7');
mysqli_set_charset($link,'utf8');
 
// retrieve the table and key from the path
$table = preg_replace('/[^a-z0-9_]+/i','',array_shift($request));
$key = array_shift($request)+0;
 
// escape the columns and values from the input object
$columns = preg_replace('/[^a-z0-9_]+/i','',array_keys($input));
$values = array_map(function ($value) use ($link) {
  if ($value===null) return null;
  return mysqli_real_escape_string($link,(string)$value);
},array_values($input));
 
// build the SET part of the SQL command
$set = '';
for ($i=0;$i<count($columns);$i++) {
  $set.=($i>0?',':'').'`'.$columns[$i].'`=';
  $set.=($values[$i]===null?'NULL':'"'.$values[$i].'"');
}
 
// create SQL based on HTTP method
switch ($method) {
  case 'GET':
    $sql = "select * from `$table`".($key?" WHERE id=$key":''); break;
  case 'PUT':
    $sql = "update `$table` set $set where id=$key"; break;
  case 'POST':
    $sql = "insert into `$table` set $set"; break;
  case 'DELETE':
    $sql = "delete `$table` where id=$key"; break;
}
 
// excecute SQL statement
$result = mysqli_query($link,$sql);
 
// die if SQL statement failed
// if (!$result) {
//   http_response_code(404);
//   die(mysqli_error());
// }
 
// print results, insert id or affected row count
if ($method == 'GET') {
  if (!$key) echo '[';
  for ($i=0;$i<mysqli_num_rows($result);$i++) {
    echo ($i>0?',':'').json_encode(mysqli_fetch_object($result));
  }
  if (!$key) echo ']';
} elseif ($method == 'POST') {
  echo mysqli_insert_id($link);
} else {
  echo mysqli_affected_rows($link);
}
 
// close mysql connection
mysqli_close($link);


// <!-- o Maak 1 simpele klasse die de JsonSerializable implementeert en data uit je database in JSON formaat weergeeft wanneer de json_encode wordt uitgevoerd met een object van de klasse. -->

class JsonSQL implements JsonSerializable {

	public function jsonSerialize()
	{
		$db = new PDO( 'mysql:host=localhost;dbname=week7', 'root', '' );	
		$stmt = $db->query("SELECT * FROM player");
		$stmt->execute();
		// $result = array();
		$count = $stmt->rowCount();
		while($count > 0)
		{
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			$count--;
		}

		return $result;
	}
}
$jsql = new JsonSQL;
$json = json_encode($jsql, JSON_PRETTY_PRINT);
echo $json;

// <!-- o Pas de json_decode functie op bovenstaande pagina’s, waarbij je 1 keer de tweede argument (bij assoc) op true zet en bij de andere op false (default) -->

var_dump(json_decode($json, TRUE));
var_dump(json_decode($json, FALSE));

// <!-- o Maak expres een fout in JSON zodat je de fout kan uitlezen met de json_last_error en json_last_error_msg functies -->

$json2[] = "{'Question': 'How do quotes work?'}";

foreach($json2 as $j)
{
	json_decode($j);

	switch(json_last_error())
	{
		case JSON_ERROR_NONE:
			echo ' - No errors';
			break;
		case JSON_ERROR_DEPTH:
			echo ' - Maximum stack depth exceeded';
			break;
		case JSON_ERROR_STATE_MISMATCH:
			echo ' - Underflow or the modes mismatch';
			break;
		case JSON_ERROR_CTRL_CHAR:
			echo ' - Unexpected control character found';
			break;
		case JSON_ERROR_SYNTAX:
			echo ' - Syntax error, malformed JSON';
			break;
		case JSON_ERROR_UTF8:
			echo ' - Malformed UTF-8 characters, possibly incorrectly encoded';
			break;
		default:
			echo ' - Unknown error';
			break;
    	}
}

// <!-- · DateTime -->
// <!-- o Experimenteer met 2 verschillende datetime-zones; in je php.ini bij date.timezone en via date_default_timezone_set. Toon aan dat je met beide timezones andere resultaat krijgt op het scherm. -->

$date = date('Y-m-d H:i:s');
echo "<br> $date"; //Europe/Amsterdam
$date2 =  date_default_timezone_set("UTC");

$script_tz = date_default_timezone_get();

if (strcmp($script_tz, ini_get('date.timezone'))){
	echo 'Script timezone differs from ini-set timezone.';
} else {
	echo 'Script timezone and ini-set timezone match.';
}
// <!-- o Haal datum/tijden op uit je database en geef deze mee aan een DateTime object via setDate()-methode. Roep vervolgens de format()-methode aan om de datum/tijd op het scherm te tonen. -->

// <!-- o Haal datum/tijden op uit je database en geef deze mee aan een DateTimeImmutable object; beschrijf het verschil met DateTime. Roep vervolgens de format()-methode aan om de datum/tijd op het scherm te tonen; pas meerdere . -->

$sql = new SQL();
$res = $sql->query("SELECT  date from savegame");

echo "<br>";
foreach($res as $r)
{
	echo "$r <br>";
	$dateDB = new DateTime($r);
	$dateDB->setDate();
	$dateDBF = date_format($dateDB, 'Y-m-d');
	echo $dateDBF;

	$dateDB2 = DateTimeImmutable::createFromFormat("Y-m-d", $r);
	var_dump($dateDB2);
	echo $dateDB2->format("Y/m/d");
}

// <!-- o Pas de date(), mktime(), strtotime() en time() functies toe in combinatie met het DateTime object. Roep vervolgens de format()-methode aan om de datum/tijd op het scherm te tonen en experimenteer met verschillende formaten -->

echo "<br>";
echo date('l \t\h\e jS', $dateDBF);
echo "<br>";
echo date("j, n, Y", $dateDBF);

echo "<br>";
echo mktime($dateDBF);

echo "<br>";
echo strtotime($dateDBF);

echo "<br>";
echo gmdate("Y-m-d\TH", time() - strtotime($dateDBF));

// <!-- o Pas ook de microtime() functie toe -->

echo "<br>";
echo microtime($dateDBF);

// <!-- o Voeg 1 dag en 8 uur toe aan een datum/tijd dmv de add()-methode -->
echo "<br>";
echo date_format($dateDB, 'Y-m-d\TH');
// $newtime = strtotime("+32 hours", strtotime($dateDBF));
echo "<br>";
// echo gmdate("Y-m-d\TH", $newtime);
$newdate = $dateDB->add(new DateInterval('PT32H'));
echo date_format($newdate, 'Y-m-d\TH');

// <!-- o Vergelijk 2 datum/tijden met elkaar -->

$d1 = new DateTime('2017-07-04');
$d2 = new DateTime('2017-08-18');
$d3 = $d1->diff($d2);
echo $d3->format('%R%a days');

// <!-- o Vraag de unix-timestamp op van een datum/tijd -->
echo "<br>";
echo $d1->getTimeStamp();

// <!-- o Pas een datum/tijd aan via de modify()-methode -->

$dMod = new DateTime('2017-07-04');
echo "<br>";
echo $dMod->format('Y-m-d');
echo "<br>";
$dMod->modify('+5 days');
echo $dMod->format('Y-m-d');

// <!-- · XML -->
// <!-- o Maak zelf een XML-bestand met meerdere XML-nodes of pak een bestaande van internet (Bijv bij https://msdn.microsoft.com/en-us/library/ms762271(v=vs.85).aspx ) -->

// <!-- o Laad de XML-bestand in met behulp van Simple XML -->

if(file_exists('music.xml'))
{
    $xml = simplexml_load_file('music.xml');
}
// <!-- o Loop met for/foreach door de XML-data heen en laat de waardes van enkele child nodes en de attributen zien -->

echo "<br>";
foreach($xml->instrument as $instrument)
{
	echo (string)$instrument->name . "<br>";
	echo (string)$instrument->description . "<br><br>";
}

// <!-- o Voeg een node child en attribuut toe bij alle rijen -->
foreach($xml->instrument as $i)
{
	$i->addChild('build', 'wooden');
	$i->addAttribute('type', 'music');
}

// <!-- o Toon de XML nu op het scherm met asXML() -->
echo $xml->asXML();

// <!-- o Doe bovenstaande 4 stappen ook met DomDocument. Sla het bestand ook op als een nieuw XML-bestand. -->

$doc = new DOMDocument();
$doc->load('music.xml');
$instruments = $doc->getElementsByTagName('instrument');

foreach($instruments as $i)
{
	// var_dump($i->nodeValue);
}

foreach($instruments as $i)
{
	$build = $doc->createElement('build', 'steel');
	$doc->appendChild($build);
	// echo $i->nodeValue . "<br>";
}
var_dump($doc);
echo $doc->saveXML();

// <!-- o Maak een XML parser met xml_parser_create via de procedural manier en/of de object georiënteerde manier. Zie https://php-and-symfony.matthiasnoback.nl/2012/04/php-create-an-object-oriented-xml-parser-using-the-built-in-xml_-functions/ voor een tutorial. -->

xml_set_object($parser, $this);

class ObjectOrientedXmlParser
{
    private $parser;

    public function __construct($encoding = 'UTF-8')
    {
        $this->parser = xml_parser_create($encoding);

        xml_set_object($this->parser, $this);
        xml_set_element_handler($this->parser, 'startElement', 'endElement');
        xml_set_character_data_handler($this->parser, 'cdata');
    }

    public function parse($data, $final)
    {
        return xml_parse($this->parser, $data, $final);
    }

    public function startElement($parser, $name, array $attributes)
    {
        var_dump(func_get_args());
    }

    public function cdata($parser, $cdata)
    {
        var_dump(func_get_args());
    }

    public function endElement($parser, $name)
    {
        var_dump(func_get_args());
    }

    public function __destruct()
    {
        if (is_resource($this->parser)) {
            xml_parser_free($this->parser);
        }
    }
}

$parser = new ObjectOrientedXmlParser;
$parser->parse($xml);


// <!-- · SOAP -->
// <!-- o Maak de SOAP-server en SOAP-client uit het boek -->

//server
class MySoapServer
{
	public function getMessage()
	{
		return 'Hello, World!';
	}
	public function addNumbers($num1, $num2)
	{
		return $num1 + $num2;
	}
}

$options = ['uri'		=> 'localhost/week8'];
$server = new SoapServer(NULL, $options);
$server->setClass('MySoapServer');
$server->handler();


// <!-- · REST -->
// <!-- o Maak een REST webservice welke via GET en ( POST of PUT of DELETE ) te benaderen is.  -->