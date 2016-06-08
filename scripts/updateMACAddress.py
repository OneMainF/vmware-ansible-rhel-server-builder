#!/usr/bin/python

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

##extdep:argparse
import argparse
##extdep:sys
import sys
##extdep:mysql.connector
import mysql.connector

##section:Main
##stmt:Parse args
parser = argparse.ArgumentParser()
parser.add_argument("host")
parser.add_argument("macaddress")
args = parser.parse_args()

##stmt:Get hostname and MACA address
sThisHost = str(args.host)
sThisMac = str(args.macaddress)

##stmt:Connect to MySQL
cnx = mysql.connector.connect(user='root', host="127.0.0.1", password="", database='sysmgmt', buffered=True)
cursor = cnx.cursor()
updateCursor = cnx.cursor()

##stmt:Search for VM in DB
query = "select serverid, vmname from servers where vmname = %s"

##stmt:Execute query
cursor.execute(query, (sThisHost,))

##if:Found the VM in DB?
if cursor.rowcount == 1:
	##stmt:Yes, update MAC address
	updateCursor.execute("""update servers set macaddress = %s where vmname = %s""", (sThisMac, sThisHost))
	cnx.commit()

	##stmt:Close connection and exit
	cnx.close()
	sys.exit(0)
else:
	##else:Not found 
	##stmt:Exit with error
	sys.exit(1)

