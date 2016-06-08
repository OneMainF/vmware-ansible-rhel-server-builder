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

##section:Variables
$sThisHost = "";
$sThisIP = "";
$sThisHDSize = 0;
$sThisDataStore = "";
$sThisCPU = 0;
$sThisRAM = 0;
$sThisGuest = "";
$sThisNote = "";
$sThisESXHost = "";
$sThisISO = "";
$sThisLocation = "";
$sThisStatus = "";
$sThisAccess = "";
$sThisBackupType = "";
$sThisSecscription = "";
$sThisAssignGroup = "";
$sThisOSVersion = "";
$sThisTemplate = "";
$sThisVMUser = "VMORCHESTRATORUSER";
$sThisVMPasswd = "VMORPASSWD";
$sThisVCenterHost = "VCENTERHOST";
$hOut;

require("zinc_db_sysmgt.php");

##section:Main
#################################################################################################

##if:Host set?
if (isset($_POST["host"])) {
	##stmt:Yes, get value and clean it
	$sThisHost = strtoupper($_POST["host"]);
	$sThisHost = preg_replace("/[^A-Za-z0-9\-]/", "", $sThisHost);
}
else {
	##else:No host
	##stmt:Output JSON
	$hOut["status"] = 1;
	$hOut["message"] = "Invalid hostname";
	print(json_encode($hOut));
	exit;
} ##endif

##if:OS version set?
if (isset($_POST["osversion"])) {
	##stmt:Yes, get value and clean it
	$sThisOSVersion = $_POST["osversion"];

	##if:RHEL6?
	if ($sThisOSVersion == "rhel6") {
		##stmt:Yes, set guest
		$sThisGuest = "rhel6_64Guest";
		$sThisNote = "Red Hat Enterprise Linux 6";
		$sThisISO = "ISODATASTORENAME/RHEL6KICKSTARTISO";
	}
	else if ($sThisOSVersion == "rhel7") {
		##elseif:RHEL7
		##stmt:Setup vars
		$sThisGuest = "rhel7_64Guest";
		$sThisNote = "Red Hat Enterprise Linux 7";
		$sThisISO = "ISODATASTORENAME/RHEL7KICKSTARTISO";
	}
	else {
		##else:Invalid OS
		##stmt:Tell user and exit
		$hOut["status"] = 1;
		$hOut["message"] = "Invalid OS Version";
		print(json_encode($hOut));
		exit;
	} ##endif
}
else {
	##else:No OS version
	##stmt:Output JSON
	$hOut["status"] = 1;
	$hOut["message"] = "Invalid OS Version";
	print(json_encode($hOut));
	exit;
} ##endif

##if:IP address set?
if (isset($_POST["ipaddress"])) {
	##stmt:Yes, get value and clean it
	$sThisIP = $_POST["ipaddress"];

	##if:Valid IP?
	if (!preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\z/', $sThisIP)) {
		##stmt:No, tell user and quit
		$hOut["status"] = 1;
		$hOut["message"] = "Invalid IP";
		print(json_encode($hOut));
		exit;
	} ##endif
}
else {
	##else:No IP adderess
	##stmt:Output JSON
	$hOut["status"] = 1;
	$hOut["message"] = "Invalid IP Address";
	print(json_encode($hOut));
	exit;
} ##endif

##if:Cluster set?
if (isset($_POST["cluster"])) {
	##stmt:Yes, get value and clean it
	$sThisCluster = $_POST["cluster"];

	##if:Valid cluster?
	if (($sThisCluster != "CLUSTER1") && ($sThisCluster != "CLUSTER2")) {
		##stmt:No, tell user and quit
		$hOut["status"] = 1;
		$hOut["message"] = "Invalid cluster";
		print(json_encode($hOut));
		exit;
	} ##endif

	##stmt:Get a host from the cluster
	$sCommand = "/opt/hs/bin/getHostsFromCluster.py \"$sThisCluster\" \"$sThisVMUser\" \"$sThisVMPasswd\" \"$sThisVCenterHost\" | sort -R | head -1";
	exec($sCommand, $aOutput, $iReturn);

	##if:Command executed successfully?
	if ($iReturn == 0) {
		##stmt:Yes, tell user
		$sThisESXHost = $aOutput[0];

		##if:Did we ger an ESXi host?
		if ($sThisESXHost == "") {
			##stmt:No, tell user and exit
			$hOut["result"] = "1";
			$hOut["message"] = "Failed to find a host in the " . $sThisCluster . " cluster";
			print(json_encode($hOut));
			exit;
		} ##endif
	}
	else {
		##else:Command failed
		##stmt:Tell user
		$hOut["result"] = "1";
		$hOut["message"] = "Failed to find a host in the " . $sThisCluster . " cluster";
		print(json_encode($hOut));
		exit;
	} ##endif
}
else {
	##else:No IP adderess
	##stmt:Output JSON
	$hOut["status"] = 1;
	$hOut["message"] = "Invalid cluster";
	print(json_encode($hOut));
	exit;
} ##endif

##if:HD Size set?
if (isset($_POST["hdsize"])) {
	##stmt:Yes, get value and clean it
	$sThisHDSize = abs($_POST["hdsize"]);

	##if:Valid size?
	if (($sThisHDSize > 500) || ($sThisHDSize < 50)) {
		##stmt:No, tell user
		$hOut["status"] = 1;
		$hOut["message"] = "Invalid HD Size";
		print(json_encode($hOut));
		exit;
	} ##endif
}
else {
	##else:No HD Size
	##stmt:Output JSON
	$hOut["status"] = 1;
	$hOut["message"] = "Invalid HD Size";
	print(json_encode($hOut));
	exit;
} ##endif

##if:CPU set?
if (isset($_POST["cpu"])) {
	##stmt:Yes, get value and clean it
	$sThisCPU = abs($_POST["cpu"]);

	##if:Valid cpu?
	if (($sThisCPU < 1) || ($sThisCPU > 8)) {
		##stmt:No, telll user and exit
		$hOut["status"] = 1;
		$hOut["message"] = "Invalid CPU";
		print(json_encode($hOut));
		exit;
	} ##endif
}
else {
	##else:No cpu
	##stmt:Output JSON
	$hOut["status"] = 1;
	$hOut["message"] = "Invalid cpu";
	print(json_encode($hOut));
	exit;
} ##endif

##if:RAM set?
if (isset($_POST["ram"])) {
	##stmt:Yes, get value and clean it
	$sThisRAM = abs($_POST["ram"]);

	##if:Valid cpu?
	if (($sThisRAM < 1) || ($sThisRAM > 16)) {
		##stmt:No, telll user and exit
		$hOut["status"] = 1;
		$hOut["message"] = "Invalid CPU";
		print(json_encode($hOut));
		exit;
	} ##endif

	$sThisRAM = $sThisRAM * 1024;
}
else {
	##else:No RAM
	##stmt:Output JSON
	$hOut["status"] = 1;
	$hOut["message"] = "Invalid ram";
	print(json_encode($hOut));
	exit;
} ##endif

##if:Status set?
if (isset($_POST["status"])) {
	##stmt:Yes, get value and clean it
	$sThisStatus = $_POST["status"];

	##if:Valid status?
	if (($sThisStatus != "production") && ($sThisStatus != "test") && ($sThisStatus != "qa") && ($sThisStatus != "development")) {
		##stmt:No, tell user
		$hOut["status"] = 1;
		$hOut["message"] = "Invalid status";
		print(json_encode($hOut));
		exit;
	} ##endif

	##if:Proudction?
	if ($sThisStatus == "production") {
		##stmt:Yes, set datastore
		$sThisDataStore = "PRODDATASTORE";
	}
	else {
		##else:Test
		##stmt:Set datastore
		$sThisDataStore = "TESTDATASTORE";
	} ##endif
}
else {
	##else:No status
	##stmt:Output JSON
	$hOut["status"] = 1;
	$hOut["message"] = "Invalid status";
	print(json_encode($hOut));
	exit;
} ##endif

##if:Description set?
if (isset($_POST["description"])) {
	##stmt:Yes, get value and clean it
	$sThisDescription = $_POST["description"];
}
else {
	##else:No description
	##stmt:Output JSON
	$hOut["status"] = 1;
	$hOut["message"] = "Invalid description";
	print(json_encode($hOut));
	exit;
} ##endif

##if:Roles set?
if (isset($_POST["role"])) {
	##stmt:Yes, get value and clean it
	$sThisRole = $_POST["role"];
}
else {
	##else:No description
	##stmt:Output JSON
	$hOut["status"] = 1;
	$hOut["message"] = "Invalid role";
	print(json_encode($hOut));
	exit;
} ##endif

##stmt:Open DB
openDBSysMgt();

##stmt:Build SQL
$sSQL  = "select * from servers where serverid = :serverid";
$dbResData = $oDBConnSysMgt->prepare($sSQL);
$dbResData->bindValue(":serverid", $sThisHost, PDO::PARAM_STR);

##stmt:Get data
$dbResData->setFetchMode(PDO::FETCH_ASSOC);
$dbResData->execute();

##if:Have rows?
if ($dbResData->rowCount() > 0) {
    ##stmt:Yes, tell user and exit
	$hOut["status"] = 1;
	$hOut["message"] = $sThisHost . " already exists in the DB";
	print(json_encode($hOut));

	##subsec:Cleanup
	closeDBSysMgt();
    exit;
} ##endif

##stmt:Build SQL
$sSQL  = "insert into servers (serverid, vmname, status, sdescription, ipaddress, roles) ";
$sSQL .= " values (:serverid, :vmname, :status, :sdescription, :ipaddress, :roles)";
$dbResData = $oDBConnSysMgt->prepare($sSQL);
$dbResData->bindValue(":serverid", $sThisHost, PDO::PARAM_STR);
$dbResData->bindValue(":vmname", $sThisHost, PDO::PARAM_STR);
$dbResData->bindValue(":status", $sThisStatus, PDO::PARAM_STR);
$dbResData->bindValue(":sdescription", $sThisDescription, PDO::PARAM_STR);
$dbResData->bindValue(":ipaddress", $sThisIP, PDO::PARAM_STR);
$dbResData->bindValue(":roles", $sThisRole, PDO::PARAM_STR);

##if:Good insert?
if ($dbResData->execute()) {
	##stmt:Yes, create Ansible file
	$sFileName = "/var/www/html/builds/" . $sThisHost . ".yml";
	$fFile = fopen($sFileName, "w") or die("Unable to open Ansible file!");

	##stmt:Build file
	$sOut  = "- hosts: 127.0.0.1\n";
	$sOut .= "  connection: local\n";
	$sOut .= "  user: root\n";
	$sOut .= "  sudo: false\n";
	$sOut .= "  gather_facts: false\n";
	$sOut .= "  serial: 1\n";
	$sOut .= "\n";
	$sOut .= "  vars:\n";
	$sOut .= "    username: \"$sThisVMUser\"\n";
	$sOut .= "    password: \"$sThisVMPasswd\"\n";
	$sOut .= "\n";
	$sOut .= "  tasks:\n";
	$sOut .= "\n";
	$sOut .= "  ## Make sure VM does not already exist\n";
	$sOut .= "  - name: Find VM\n";
	$sOut .= "    shell: /opt/hs/bin/findVM.py " . $sThisHost . " {{ username }} {{ password }} " . $sThisVCenterHost . "\n";
	$sOut .= "    register: findVM\n";
	$sOut .= "    changed_when: \"findVM.rc == 1\"\n";
	$sOut .= "    failed_when: \"findVM.rc == 0\"\n";
	$sOut .= "\n";
	$sOut .= "  ## Create VM\n";
	$sOut .= "  - vsphere_guest:\n";
	$sOut .= "     vcenter_hostname: " . $sThisVCenterHost . "\n";
	$sOut .= "     username: \"{{ username }}\"\n";
	$sOut .= "     password: \"{{ password }}\"\n";
	$sOut .= "     guest: " . $sThisHost . "\n";
	$sOut .= "     state: powered_off\n";

	##stmt:Setup drives
	$sOut .= "     vm_disk:\n";
	$sOut .= "       disk1:\n";
	$sOut .= "         size_gb: " . $sThisHDSize . "\n";
	$sOut .= "         type: thin\n";
	$sOut .= "         datastore: " . $sThisDataStore . "\n";

	##stmt:Setup NIC
	$sOut .= "     vm_nic:\n";
	$sOut .= "       nic1:\n";
	$sOut .= "        type: vmxnet3\n";
	$sOut .= "        network: \"SERVER_BUILD_DHCP_NETWORK\"\n";
	$sOut .= "        network_type: dvs\n";

	##stmt:Setup other hardware
	$sOut .= "     vm_hardware:\n";
	$sOut .= "       memory_mb: " . $sThisRAM . "\n";
	$sOut .= "       num_cpus: " . $sThisCPU . "\n";
	$sOut .= "       scsi: lsi\n";
	$sOut .= "       osid: " . $sThisGuest . "\n";
	$sOut .= "       vm_cdrom:\n";
	$sOut .= "         type: \"iso\"\n";
	$sOut .= "         iso_path: \"" . $sThisISO . "\"\n";
	$sOut .= "     vm_extra_config:\n";
	$sOut .= "       folder: \"VMWARE_FOLDER\"\n";
	$sOut .= "       notes: \"" . $sThisNote . "\"\n";
	$sOut .= "       vcpu.hotadd: yes\n";
	$sOut .= "       mem.hotadd:  yes\n";
	$sOut .= "     esxi:\n";
	$sOut .= "      datacenter: VMWARE_DATA_CENTER_NAME\n";
	$sOut .= "      hostname: " . $sThisESXHost . "\n";
	$sOut .= "\n";
	$sOut .= "  - vsphere_guest:\n";
	$sOut .= "      vcenter_hostname: " . $sThisVCenterHost . "\n";
	$sOut .= "      username: \"{{ username }}\"\n";
	$sOut .= "      password: \"{{ password }}\"\n";
	$sOut .= "      guest: " . $sThisHost . "\n";
	$sOut .= "      vmware_guest_facts: yes\n";
	$sOut .= "\n";
	$sOut .= "  ## Update MAC address of the new VM\n";
	$sOut .= "  - name: Update MAC address\n";
	$sOut .= "    shell: /opt/hs/bin/updateMACAddress.py {{ hw_name }} {{ hw_eth0.macaddress }}\n";
	$sOut .= "\n";
	$sOut .= "  ## Power on the new VM\n";
	$sOut .= "  - name: Power On VM\n";
	$sOut .= "    shell: /opt/hs/bin/powerOnVM.py {{ hw_name }} {{ username }} {{ password }} " . $sThisVCenterHost . "\n";
	$sOut .= "\n";

	##stmt:Create Ansible file
	fwrite($fFile, $sOut);
	fclose($fFile);

	##stmt:Execute Ansible
	$sCommand = "/usr/bin/ansible-playbook " . $sFileName . " > /dev/null 2>&1 ";
	$sCommandOutput = system($sCommand, $iReturn);

	##if:Command executed successfully?
	if ($iReturn == 0) {
		##stmt:Yes, tell user
		$hOut["result"] = "0";
		$hOut["message"] = "Created VM";
	}
	else {
		##else:Command failed
		##stmt:Tell user
		$hOut["result"] = "1";
		$hOut["message"] = "Failed to create VM";
		##$hOut["debug"] = $sCommandOutput;
	} ##endif
}
else {
	##else:Failed to insert
	##stmt:Tell user
	$hOut["status"] = 1;
	$hOut["message"] = "Failed to update servers table";
} ##endif

##stmt:Output JSON
print(json_encode($hOut));

##subsec:Cleanup
closeDBSysMgt();

?>
