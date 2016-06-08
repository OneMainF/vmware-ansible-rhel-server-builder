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
from pysphere import VIServer, VIProperty, MORTypes
from pysphere.resources import VimService_services as VI
from pysphere.vi_task import VITask
##extdep:sys
import sys
##extdep:argparse
import argparse

##section:Main
##stmt:Parse args
parser = argparse.ArgumentParser()
parser.add_argument("cluster")
parser.add_argument("user")
parser.add_argument("passwd")
parser.add_argument("esxihost")
args = parser.parse_args()

##stmt:Get clustername, user and passwd
sThisCluster = str(args.cluster)
sThisUser = str(args.user)
sThisPasswd = str(args.passwd)
sThisEsxiHost = str(args.esxihost)
bFound = False

##if:User and passwd passed?
if sThisUser == "" or sThisPasswd == "":
	print "Invalid username or password"
	sys.exit(1)

##if:Do we have a host?
if sThisCluster != "":
	##stmt:Yes, connect to vCenter
	server = VIServer()
	server.connect(sThisEsxiHost, sThisUser, sThisPasswd)

	##stmt:Get datacenter object
	oDataCenter = server.get_datacenters()

	##loop:Get each datacenter
	for dc_mor, dc_name in oDataCenter.items():
		##stmt:Get clusters in the datacenter
		oClusters = server.get_clusters(from_mor=dc_mor)

		##loop:Get each cluster
		for c_mor, c_name in oClusters.items():
			##stmt:Get next cluster

			##if:Is this the cluster we are looking for?
			if sThisCluster == c_name:
				##stmt:Yes, get hosts
				oHosts = server.get_hosts(from_mor=c_mor)

				##loop:Get each host from cluster
				for h_mor, h_name in oHosts.items():
					##stmt:Get next host
					print h_name
					bFound = True

	##if:Did we find any hosts?
	if bFound == True:
		##stmt:Yes, exit script
		server.disconnect()
	        sys.exit(0)
	else:
		##else:Didn't find any hosts 
		##stmt:Exit script
		server.disconnect()
		sys.exit(1)
					
else:
	##else:Cluster not pasesd
	##stmt:Exit script
	sys.exit(1)

