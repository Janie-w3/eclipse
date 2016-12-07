<?php

function setTrace($data, $die = 1)
{
	print '<pre>';
	print_r($data);
	print '</pre>';

	if($die) die;
}

class Network {
	public $nodes = array();
	public $links = array();
	
	function addNode(Node $node)
	{
		$this->nodes[] = $node;
	}
	
	function addLink(Link $link)
	{
		$this->links[] = $link;
	}
}

class Node {
	public $id;
}

class Link {
	public $source;
	public $target;
}

function get_role_array()
{
	$mysqli = mysqli_connect("localhost", "root", "", "jmesh");
	if (mysqli_connect_errno($mysqli)) {
		echo "Failed to connect to MySQL: " . mysqli_connect_error();
	}
	
	$query = "SELECT * from roles";
	$result = mysqli_query($mysqli, $query);
	
	while($r = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
		$roles[$r["id"]] = $r["name"];
	}
	mysqli_close($mysqli);
	return $roles;
}
$roles = get_role_array();

$network = new Network;
//$n1 = new Node;
//$n1->id = 5;
//$network->addNode($n1);

$mysqli = mysqli_connect("localhost", "root", "", "jmesh");
if (mysqli_connect_errno($mysqli)) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
}

$query = "SELECT * FROM devices WHERE connected = '1' AND last_heard >= DATE_SUB(NOW(), INTERVAL 1 MINUTE)";
$query = "SELECT * FROM devices WHERE connected = '1'";
//$query = "SELECT * FROM devices";
$result = mysqli_query($mysqli, $query) or die(mysqli_error($mysqli));
//setTrace($result);
while ($line = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
	$n = new Node;
	//$n->id = bin2hex($line["uuid"]);
	$n->id = $line["uuid"];
	$index = $line["role"];
	$n->role = $roles[$index];
	$network->addNode($n);
}

$query = "SELECT * FROM links WHERE last_heard >= DATE_SUB(NOW(), INTERVAL 1 MINUTE)";
$query = "SELECT * FROM links";
$result = mysqli_query($mysqli, $query);
while ($line = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
	$l = new Link;
	//$l->source = bin2hex($line["source"]);
	$l->source = $line["source"];
	$l->target = $line["target"];
	//$l->target = bin2hex($line["target"]);
	$network->addLink($l);
}

echo json_encode($network, JSON_NUMERIC_CHECK);

mysqli_close($mysqli);
?>
