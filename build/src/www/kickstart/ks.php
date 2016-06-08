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

$sThisHost = "";
$sThisStatus = "";
$sThisIP = "";
$sThisSatelliteEnv = "";
$sThisOSID = "";
$iUseXFS = 0;

##if:OSID set?
if (isset($_GET["osid"])) {
	##stmt:Yes, get value
	$sThisOSID = $_GET["osid"];

	##if:Valid OSID?
	if (($sThisOSID != "rhel6_64Guest") && ($sThisOSID != "rhel7_64Guest")) {
		##stmt:Send 404
		header("HTTP/1.0 404 Not Found");
		exit(1);
	} ##endif
}
else {
	##else:No OSID
	##stmt:Send 404
	header("HTTP/1.0 404 Not Found");
	exit(1);
} ##endif

##stmt:Open DB
openDBSysMgt();

##loop:Get headers
foreach (getallheaders() as $sName => $sValue) {
	##stmt:Get next header
	$sOut = "$sName\t$sValue\n";

	##if:MAC header?
	if ($sName == "X-RHN-Provisioning-MAC-0") {
		##stmt:Yes, parse
		$aValue = explode(" ", $sValue);
		$sThisMAC = strtolower($aValue[1]);

		##if:Did we get a MAC?
		if ($sThisMAC != "") {
			##stmt:Yes, query for server info
			$sSQL = "select * from servers where LOWER(macaddress) = '" . $sThisMAC . "'\n";
			$dbResData = $oDBConnSysMgt->prepare($sSQL);
			$dbResData->setFetchMode(PDO::FETCH_ASSOC);
			$dbResData->execute();

			##if:Have rows?
			if ($dbResData->rowCount() == 1) {
				##stmt:Yes, get info
				$aRow = $dbResData->fetch();
				$sThisHost = strtolower($aRow["serverid"]);
				$sThisStatus = strtolower($aRow["status"]);
				$sThisIP = $aRow["ipaddress"];

				##if:RHEL6?
				if ($sThisOSID == "rhel6_64Guest") {
					##stmt:Yes, setup vars

					##if:Prouduction status?
					if ($sThisStatus == "production") {
						##stmt:Yes, setup Satellite env
						$sThisSatelliteEnv = "Production/RHEL6Server";
					}
					else if ($sThisStatus == "qa") {
						##elseif:QA
						##stmt:Setup env
						$sThisSatelliteEnv = "QA/RHEL6Server";
					}
					else if ($sThisStatus == "test") {
						##elseif:Test
						##stmt:Setup env
						$sThisSatelliteEnv = "Test/RHEL6Server";
					}
					else if ($sThisStatus == "development") {
						##elseif:Dev
						##stmt:Setup env
						$sThisSatelliteEnv = "Development/RHEL6Server";
					}
					else {
						##else:Unknown
						##stmt:Send 404
						header("HTTP/1.0 404 Not Found");
						exit(1);
					} ##endif
				}
				else if ($sThisOSID == "rhel7_64Guest") {
					##else:RHEL 7
					##stmt:Setup vars

					##if:Prouduction status?
					if ($sThisStatus == "production") {
						##stmt:Yes, setup Satellite env
						$sThisSatelliteEnv = "Production/RHEL7Server";
					}
					else if ($sThisStatus == "qa") {
						##elseif:QA
						##stmt:Setup env
						$sThisSatelliteEnv = "QA/RHEL7Server";
					}
					else if ($sThisStatus == "test") {
						##elseif:Test
						##stmt:Setup env
						$sThisSatelliteEnv = "Test/RHEL7Server";
					}
					else if ($sThisStatus == "development") {
						##elseif:Dev
						##stmt:Setup env
						$sThisSatelliteEnv = "Development/RHEL7Server";
					}
					else {
						##else:Unknown
						##stmt:Send 404
						header("HTTP/1.0 404 Not Found");
						exit(1);
					} ##endif
				}
				else {
					##else:Unknown
					##stmt:Send 404
					header("HTTP/1.0 404 Not Found");
					exit(1);
				} ##endif
			}
			else {
				##else:Not found, send 404
				header("HTTP/1.0 404 Not Found");
				exit(1);
			} ##endif
		}
		else {
			##else:No MAC
			##stmt:Send 404
			header("HTTP/1.0 404 Not Found");
			exit(1);
		} ##endif
	} ##endif
}  ##endloop

##stmt:Close DB
##fclose($myfile);
closeDBSysMgt();

##if:Did we find a host?
if ($sThisHost == "") {
	##stmt:No, exit with 404
	header("HTTP/1.0 404 Not Found");
	exit(1);
} ##endif

##if:RHEL6?
if ($sThisOSID == "rhel6_64Guest") {
	##stm:Build RHEL 6 kickstart file
?>

lang en_US
keyboard us
timezone America/Chicago --isUtc
#platform x86, AMD64, or Intel EM64T
reboot
text
cdrom
rootpw D3m0P$ssw0Rd 
bootloader --location=mbr --append="rhgb quiet crashkernel=auto"
zerombr
network --device eth0 --bootproto dhcp --hostname <?php echo $sThisHost . ".CONFIGDOMAIN\n"; ?>
clearpart --all

part /boot --fstype=ext2 --size=1024 --ondisk=sda
part swap --size=1024 --ondisk=sda

part pv.008017 --grow --size=200 --ondisk=sda

volgroup sysvg --pesize=8192 pv.008017

logvol / --fstype=ext3 --name=rootlv --vgname=sysvg --size=20480
logvol /usr --fstype=ext3 --name=usrlv --vgname=sysvg --size=10240
logvol /home --fstype=ext3 --name=homelv --vgname=sysvg --size=2048
logvol /opt --fstype=ext3 --name=optlv --vgname=sysvg --size=5120
logvol /var --fstype=ext3 --name=varlv --vgname=sysvg --size=5120
logvol /tmp --fstype=ext3 --name=tmplv --vgname=sysvg --size=1024 --fsoption="nodev,nosuid,noexec"

auth --passalgo=sha512 --useshadow
selinux --enforcing
firewall --enabled --ssh
skipx
firstboot --disable
%packages
@base
oddjob-mkhomedir
samba-common
samba-winbind
samba-winbind-clients
sssd
sssd-ad
krb5-workstation
authconfig
libselinux-python
%end

%post --log=/root/ks-post.log
# This is done here in the post section, after the system
# has completed install and before a reboot

/usr/bin/curl -s http://CONTAINERHOSTNAME/kickstart/networkConfig.sh -o /root/networkConfig.sh
chmod +x /root/networkConfig.sh
/root/networkConfig.sh <?php echo $sThisHost; ?> <?php echo $sThisIP . "\n"; ?>
rm -f /root/networkConfig.sh

curl -s -m 10 http://CONTAINERHOSTNAME/ws/changeVMNetwork.php?host=<?php echo $sThisHost . "\n"; ?>

<?php
}
else if ($sThisOSID == "rhel7_64Guest") {
	##elseif:RHEL 7
	##stmt:Build kickstart file
?>
lang en_US
keyboard us
timezone America/Chicago --isUtc
#platform x86, AMD64, or Intel EM64T
reboot
text
cdrom
rootpw D3m0P$ssw0Rd 
bootloader --location=mbr --append="rhgb quiet crashkernel=auto"
zerombr
network --device eth0 --bootproto dhcp --hostname <?php echo $sThisHost . ".CONFIGDOMAIN\n"; ?>
clearpart --all

part /boot --fstype=ext2 --size=1024 --ondisk=sda
part swap --size=1024 --ondisk=sda

part pv.008017 --grow --size=200 --ondisk=sda

volgroup sysvg --pesize=8192 pv.008017
logvol / --fstype=xfs --name=rootlv --vgname=sysvg --size=20480
logvol /home --fstype=xfs --name=homelv --vgname=sysvg --size=2048
logvol /opt --fstype=xfs --name=optlv --vgname=sysvg --size=5120
logvol /var --fstype=xfs --name=varlv --vgname=sysvg --size=5120
logvol /tmp --fstype=xfs --name=tmplv --vgname=sysvg --size=1024 --fsoption="nodev,nosuid,noexec"

auth --passalgo=sha512 --useshadow
selinux --enforcing
firewall --enabled --ssh
skipx
firstboot --disable
%packages
@base
oddjob-mkhomedir
samba-common
samba-winbind
sssd
sssd-ad
krb5-workstation
authconfig
libselinux-python
%end

%post --log=/root/ks-post.log
# This is done here in the post section, after the system
# has completed install and before a reboot

/usr/bin/curl -s http://CONTAINERHOSTNAME/kickstart/networkConfig.sh -o /root/networkConfig.sh
chmod +x /root/networkConfig.sh
/root/networkConfig.sh <?php echo $sThisHost; ?> <?php echo $sThisIP; ?> <?php echo $sThisMAC . "\n"; ?>
rm -f /root/networkConfig.sh

curl -s -m 10 http://CONTAINERHOSTNAME/ws/changeVMNetwork.php?host=<?php echo $sThisHost . "\n"; ?> > /dev/null 2>&1
%end
<?php
} ##endif
?>
