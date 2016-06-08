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
#####################################################################################################
$oDBConnSysMgt;

##section:Functions
#####################################################################################################
##func:openDBSysMgt()
function openDBSysMgt($abReturnError = false) {
	##arg:1 - Return error or exit

	global $oDBConnSysMgt;

	$sDBHost = "127.0.0.1";
	$sDBUser = "root";
	$sDBPass = "";
	$sDBDatabase = "sysmgmt";
	
	##if:Is there already a MySQL connection?
	if (!isset($oDBConnSysMgt)) {
		##stmt:No, connect to MySQL server
		try {
			$oDBConnSysMgt = new PDO("mysql:host=" .$sDBHost .";dbname=" .$sDBDatabase ."", $sDBUser, $sDBPass);
			$oDBConnSysMgt->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			return "ok";
		}
		catch(PDOException $ePDOEx) {
			if ($abReturnError == true) {
				return $ePDOEx->getMessage();
			}
			else {
				print($ePDOEx->getMessage() ."<br />\n");
				exit;
			}
		}
	} ##endif
} ##endfunc


##func:closeDBSysMgt()
function closeDBSysMgt() {
	global $oDBConnSysMgt;

	##if:Do we have an open DB connection?
	if ($oDBConnSysMgt) {
		##stmt:Yes, close it
		$oDBConnSysMgt = null;
	} ##endif
} ##endfunc
?>
