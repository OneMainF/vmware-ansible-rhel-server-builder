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

##extdep:pysphere
import pysphere 
##extdep:sys
import sys
##extdep:argparse
import argparse

##section:Main
##stmt:Parse args
parser = argparse.ArgumentParser()
parser.add_argument("host")
parser.add_argument("user")
parser.add_argument("passwd")
parser.add_argument("esxihost")
args = parser.parse_args()

##stmt:Get hostname
sThisHost = str(args.host).upper()
sThisUser = str(args.user)
sThisPasswd = str(args.passwd)
sThisEsxiHost = str(args.esxihost)

##if:User and passwd passed?
if sThisUser == "" or sThisPasswd == "":
	print "Invalid username or password"
	sys.exit(1)

##if:Do we have a host?
if sThisHost != "":
	##stmt:Yes, connect to vCenter
	server = pysphere.VIServer()
	server.connect(sThisEsxiHost, sThisUser, sThisPasswd)

	##stmt:Get server object
	vm = server.get_vm_by_name(sThisHost)

	##if:Found VM?
	if vm is None:
		##stmt:No, tell user and exit script
		print "Cannot find " + sThisHost
		sys.exit(1)

	##if:Is the server already powered on?
	if vm.get_status() != "POWERED ON":
		##stmt:No, power on server and exit script
		vm.power_on()
		sys.exit(0)
	else:
		print sThisHost + " is already powered on"
		sys.exit(1)
else:
	##else:Invalid host
	##stmt:Exit script
	print "Invalid hostname"
	sys.exit(1)

