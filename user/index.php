<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!-- 
	A basic user stats page. 
	URL Params:
		u - the user.
		rg_order - the way to order records in the regions table.
		sys_order - the way to order the records in the systems table.
		db - the database being used. Use 'TravelMappingDev' for in-development systems. 
-->
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<style type="text/css">
body, html {
  margin:0;
  border:0;
  padding:0;
  height:100%;
  max-height:100%;
  overflow: hidden;
  font-size:9pt;
  background-color:#EEEEFF;
}

#body {
position: fixed;
left: 0px;
top: 80px;
bottom: 0px;
width: 100%;
overflow:auto;
padding: 20px;
}

table.nmptable {
font-size:8pt;
border: 1px solid black;
border-spacing: 0px;
margin-left: auto;
margin-right: auto;
background-color:white;
}

table.nmptable  td, th {
border: solid black;
border-width: 1px;
}

table.nmptable2 td, th {
border-width: 0px;
}

table.nmptable tr td {
text-align:right;
}

table.pthtable {
font-size:10pt;
border: 1px solid black;
border-spacing: 0px;
margin-left: auto;
margin-right: auto;
background-color:white;
}

table.pthtable  td, th {
border: solid black;
border-width: 1px;
}

table.pthtable tr td {
text-align:left;
}

table.gratable {
font-size:10pt;
border: 1px solid black;
border-spacing: 0px;
margin-left: auto;
margin-right: auto;
width: 50%;
background-color:white;
}

table.gratable  td, th {
border: solid black;
border-width: 1px;
}

table.gratable tr td {
text-align:left;
}

table.gratable tr:hover td {
	background-color: #CCCCCC;
}
</style>
<title>
	<?php
		$user = "null";
		$rg_order = "region ascending";
		$sys_order = "countryCode DESC";

		if (array_key_exists("u",$_GET)) {
			$user = $_GET['u'];
		}

		if (array_key_exists("rg_order",$_GET)) {
			$rg_order = $_GET['rg_order'];
		}

		if (array_key_exists("sys_order",$_GET)) {
			$sys_order = $_GET['sys_order'];
		}

		echo "Traveler Stats for ".$user;

		$dbname = "TravelMapping";
		if (array_key_exists("db",$_GET)) {
		  $dbname = $_GET['db'];
		}

		// establish connection to db: mysql_ interface is deprecated, should learn new options
		$db = new mysqli("localhost","travmap","clinch",$dbname) or die("Failed to connect to database");

		# functions from http://stackoverflow.com/questions/834303/startswith-and-endswith-functions-in-php
		function startsWith($haystack, $needle) {
		    // search backwards starting from haystack length characters from the end
			return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
		}
		function endsWith($haystack, $needle) {
		    // search forward starting from end minus needle length characters
		    return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== FALSE);
		}

		function colorScale($percent) {

		}
	?>
</title>
</head>
<body>
	<div id="header">
	<form id="userselect">
		<label>User: </label>
		<input type="text" name="u" form="userselect" value="<?php echo $user ?>">
		<input type="submit">
	</form>
	<h1>Traveler Stats for <?php echo $user; ?>:</h1>
	</div>
	<div id="body">
		<h2>Stats by Region</h2>
		<table class="gratable" id="regionsTable">
			<thead>
				<tr>
					<th colspan="5">Clinched Mileage by Region:</th>
				</tr>
				<tr>
					<th><a href="?<?php echo "u=".$user."&sys_order=".$sys_order."&rg_order=region" ?>">Region</a></th>
					<th><a href="?<?php echo "u=".$user."&sys_order=".$sys_order."&rg_order=clinchedMileage desc" ?>">Clinched Mileage</a></th>
					<th><a href="?<?php echo "u=".$user."&sys_order=".$sys_order."&rg_order=totalMileage desc" ?>">Overall Mileage</a></th>
					<th><a href="?<?php echo "u=".$user."&sys_order=".$sys_order."&rg_order=percentage" ?>">Percent Clinched</a></th>
					<th>Map</th>
				</tr>
			</thead>
			<tbody>
				<?php
					$sql_command = "SELECT o.region, co.mileage as clinchedMileage, o.mileage as totalMileage FROM overallMileageByRegion AS o INNER JOIN clinchedOverallMileageByRegion AS co ON co.region = o.region WHERE co.traveler = '".$user."' ORDER BY ";
					if (array_key_exists("rg_order", $_GET) && strlen($_GET["rg_order"]) > 0) {
						if (!strcmp($_GET['rg_order'], "percentage")) {
							$sql_command .= "1 - clinchedMileage / totalMileage";
						} else {
							$sql_command .= $_GET["rg_order"];
						}
				    } else {
				    	$sql_command .= "o.region";
				    }
					echo "<!-- SQL: ".$sql_command."-->";
					$res = $db->query($sql_command);
					while ($row = $res->fetch_assoc()) {
						$percent = round($row['clinchedMileage'] / $row['totalMileage'] * 100.0, 3);
				        echo "<tr onClick=\"window.document.location='user/region.php?u=".$user."&rg=".$row['region']."'\"><td>".$row['region']."</td><td>".$row['clinchedMileage']."</td><td>".$row['totalMileage']."</td><td>".$percent."%</td><td><a href=\"/hbtest/mapview.php?u=".$user."&rg=".$row['region']."\">Map</a></td></tr>";
				    }
			        $res->free();
				?>
				<tr><td colspan="5">*Regions with no mileage not shown</td>
			</tbody>
		</table>
		<h2>Stats by System</h2>
		<table class="gratable" id="systemsTable">
			<thead>
				<tr>
					<th colspan="7">Clinched Mileage by System</th>
				</tr>
				<tr>
					<th><a href="?<?php echo "u=".$user."&sys_order=countryCode&rg_order=".$rg_order ?>">Country</a></th>
					<th><a href="?<?php echo "u=".$user."&sys_order=systemName&rg_order=".$rg_order ?>">System Code</a></th>
					<th><a href="?<?php echo "u=".$user."&sys_order=fullName&rg_order=".$rg_order ?>">System Name</a></th>
					<th><a href="?<?php echo "u=".$user."&sys_order=clinchedMileage desc&rg_order=".$rg_order ?>">Clinched Mileage</a></th>
					<th><a href="?<?php echo "u=".$user."&sys_order=totalMileage desc&rg_order=".$rg_order ?>">Total Mileage</a></th>
					<th><a href="?<?php echo "u=".$user."&sys_order=percentage desc&rg_order=".$rg_order ?>">Percent</a></th>
					<th>Map</th>
				</tr>
			</thead>
			<tbody>
				<?php 
					$sql_command = "SELECT sys.countryCode, sys.systemName, sys.fullName, r.root, COALESCE(ROUND(SUM(cr.mileage), 2),0) AS clinchedMileage, COALESCE(ROUND(SUM(r.mileage), 2), 0) AS totalMileage, COALESCE(ROUND(SUM(cr.mileage) / SUM(r.mileage) * 100, 3), 0) AS percentage FROM systems as sys INNER JOIN routes AS r ON r.systemName = sys.systemName LEFT JOIN clinchedRoutes AS cr ON cr.route = r.root AND cr.traveler = '".$user."' GROUP BY r.systemName";
					$sql_command .= " ORDER BY ".$sys_order.", sys.tier, sys.systemName;";
					echo "<!-- SQL: ".$sql_command."-->";
					$res = $db->query($sql_command);
					while ($row = $res->fetch_assoc()) {
						echo "<tr onClick=\"window.document.location='user/system.php?u=".$user."&sys=".$row['systemName']."'\"><td>".$row['countryCode']."</td><td>".$row['systemName']."</td><td>".$row['fullName']."</td><td>".$row['clinchedMileage']."</td><td>".$row['totalMileage']."</td><td>".$row['percentage']."%</td><td><a href=\"/hbtest/mapview.php?u=".$user."&sys=".$row['systemName']."\">Map</a></td></tr>";
					}
					$res->free();
				?>
			</tbody>
		</table>
	</div>
</body>