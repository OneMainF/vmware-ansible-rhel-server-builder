<?php

##section:Variables
$hInventory;
$hHostVars;
$sThisHost = "";

require("zinc_db_sysmgt.php");

##section:Main
#################################################################################################
##stmt:Open DB
openDBSysMgt();

##if:Host set?
if (isset($_GET["host"])) {
	##stmt:Yes, get value and clean it
	$sThisHost = $_GET["host"];
	$aTemp = explode(".", $sThisHost);
	$sThisHost = $aTemp[0];
	$sThisHost = preg_replace("/[^A-Za-z0-9\-]/", "", $sThisHost);

	##stmt:Query for server info
	$sSQL  = "select serverid, vmname, status, access, sdescription, roles from servers ";
	$sSQL .= "where status != 'decommissioned' and status != 'outofservice' and serverid = :serverid ";
	$sSQL .= "and roles IS NOT NULL order by roles, serverid";
	$dbResData = $oDBConnSysMgt->prepare($sSQL);
	$dbResData->bindValue(":serverid", $sThisHost, PDO::PARAM_STR);
}
else {
	##else:No host
	##stmt:Get all servers
	$sSQL  = "select serverid, vmname, status, access, sdescription, roles from servers ";
	$sSQL .= "where status != 'decommissioned' and status != 'outofservice' and roles IS NOT NULL order by roles, serverid";
	$dbResData = $oDBConnSysMgt->prepare($sSQL);
} ##endif

##stmt:Get data
$dbResData->setFetchMode(PDO::FETCH_ASSOC);
$dbResData->execute();

##if:Have rows?
if ($dbResData->rowCount() > 0) {
	##stmt:Yes, get info

	##loop:Get rows
	while ($aRow = $dbResData->fetch()) {
		##stmt:Get next row
		$sThisHost = strtolower($aRow["serverid"]) . ".CONFIGDOMAIN";

		##if:Inventory exist?
		if (!isset($hInventory)) {
			##stmt:No, initialize
			$hInventory[$aRow["roles"]] = Array();
		} ##endif

		##if:Key exist?
		if (array_key_exists($aRow["roles"], $hInventory)) {
			##stmt:Yes, append hash
			array_push($hInventory[$aRow["roles"]], $sThisHost);
		}
		else {
			##else:Does not exist
			##stmt:Create hash
			$hInventory[$aRow["roles"]] = Array();
			array_push($hInventory[$aRow["roles"]], $sThisHost);
		} ##endif

		##stmt:Build hostvars
		$hHostVars[$sThisHost] = array(
							"vmname" => $aRow["vmname"],
							"status" => $aRow["status"],
							"access" => $aRow["access"],
							"description" => $aRow["sdescription"]
	
						);
 
	} ##endloop

	##stmt:Append host vars
	$hInventory["_meta"] = array( "hostvars" => $hHostVars);
}
else {
	##else:No results
	##stmt:Send 404
	header("HTTP/1.0 404 Not Found");
	closeDBSysMgt();
	exit;
} ##endif

##stmt:Output JSON
print(json_encode($hInventory));

##subsec:Cleanup
closeDBSysMgt();

?>
