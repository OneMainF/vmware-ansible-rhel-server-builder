<?php

##Licensed to the Apache Software Foundation (ASF) under one
##or more contributor license agreements.  See the NOTICE file
##distributed with this work for additional information
##regarding copyright ownership.  The ASF licenses this file
##to you under the Apache License, Version 2.0 (the
##"License"); you may not use this file except in compliance
##with the License.  You may obtain a copy of the License at
##
##    http://www.apache.org/licenses/LICENSE-2.0
##
##Unless required by applicable law or agreed to in writing, software
##distributed under the License is distributed on an "AS IS" BASIS,
##WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
##See the License for the specific language governing permissions and
##limitations under the License.

##extdep:zinc_zinc_db_sysmgt.php
require("zinc_db_sysmgt.php");

$sThisVMUser = "VMORCHESTRATORUSER";
$sThisVMPasswd = "VMORPASSWD";
$sESXiHost = "VCENTERHOST";

##if:Host set?
if (isset($_GET["host"])) {
	##stmt:Yes, get value
	$sThisHost = $_GET["host"];

	##if:Did we get a host?
	if ($sThisHost == "") {
		##stmt:No, tell user and exit
		$hRes["result"] = "1";
		$hRes["message"] = "Invalid hostname";
		print(json_encode($hRes));
		exit;
	} ##endif
}
else {
	##else:Not set
	##stmt:Tell user and exit
        $hRes["result"] = "1";
        $hRes["message"] = "Invalid hostname";
	print(json_encode($hRes));
        exit;
} ##endif

##stmt:Open DB
openDBSysMgt();

##stmt:Query for server info
$sSQL = "select * from servers where vmname = :hostname";
$dbResData = $oDBConnSysMgt->prepare($sSQL);
$dbResData->bindValue(":hostname", $sThisHost, PDO::PARAM_STR);
$dbResData->setFetchMode(PDO::FETCH_ASSOC);
$dbResData->execute();

##if:Have rows?
if ($dbResData->rowCount() == 1) {
	##stmt:Yes, get info
	$aRow = $dbResData->fetch();
	$sThisVM = strtolower($aRow["vmname"]);
	$sThisIP = $aRow["ipaddress"];

	##if:Have an IP?
	if ($sThisIP != "") {
		##stmt:Yes, parse if
		$aTemp = explode(".", $sThisIP);
		$sThisNetwork = $aTemp[0] . "." . $aTemp[1] . "." . $aTemp[2] . ".0";

		##stmt:Build command string
		$sCommand = "/opt/hs/bin/changeVMNetwork.py " . $sThisVM . " " . $sThisNetwork . " " . $sThisVMUser . " " . $sThisVMPasswd . "  " . $sESXiHost . " > /dev/null 2>&1 ";

		##stmt:Execute command
		$sOutput = system($sCommand, $iReturn);

		##if:Command executed successfully?
		if ($iReturn == 0) {
			##stmt:Yes, tell user
			$hRes["result"] = "0";
			$hRes["message"] = "Network successfully changed";
		}
		else {
			##else:Command failed
			##stmt:Tell user
			$hRes["result"] = "1";
			$hRes["message"] = "Failed to change network - " . $iReturn;
			$hRes["debug"] = $sOutput;
		} ##endif
	}
	else {
		##else:No IP
		##stmt:Tell user and exit
		$hRes["result"] = "1";
		$hRes["message"] = "Failed to find IP for " . $sThisVM;
	} ##endif
}
else {
	##else:Invalid rows
	##stmt:Tell user and exit
	$hRes["result"] = "1";
	$hRes["message"] = "Invalid vmname";
} ##endif

##stmt:Output JSON
print(json_encode($hRes));

##stmt:Close DB
closeDBSysMgt();

?>
