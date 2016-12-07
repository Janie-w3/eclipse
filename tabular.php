<?php
/*$hexstr = "b04db7dc531edd2250b709d44a83de01bf4bae21";
$binstr = hex2bin($hexstr);
$hexstr2 = bin2hex($binstr);*/
$binstr = time().rand(1, 100000);


$mysqli = mysqli_connect("localhost", "root", "", "jmesh");
if (mysqli_connect_errno($mysqli)) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
}

$query = "SELECT * FROM devices WHERE connected = '1' AND last_heard >= DATE_SUB(NOW(), INTERVAL 1 MINUTE)";
$result = mysqli_query($mysqli, $query);
$rowcount=mysqli_num_rows($result);
$connected_values = array(0,1);
$connected = $connected_values[rand(0,1)];
$role_values = array(1,2,3);
$role = $role_values[rand(0,2)];
echo $role;
//$query = "INSERT INTO devices(uuid, role, connected) VALUES('$binstr', '1', 'true')";
/*$query = "INSERT INTO devices(uuid, role, connected) VALUES('$binstr', '$role', '$connected')";
$result = mysqli_query($mysqli, $query);*/
//echo $query;
if($rowcount == 0)
{
	$query = "INSERT INTO devices(uuid, role, connected) VALUES('$binstr', '$role', '$connected')";
	$result = mysqli_query($mysqli, $query);
	print_r($result);
}
