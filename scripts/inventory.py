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

##extdep:json
import json
##extdep:urllib
import urllib
##extdep:sys
import sys

##section:Main

##if:Getting a list of servers?
if len(sys.argv) == 2 and (sys.argv[1] == '--list'):
	##stmt:Yes, setup URL
	sURL = "http://CONTAINERHOSTNAME/ws/inventory.php"
elif len(sys.argv) == 3 and (sys.argv[1] == '--host'):
	##elseif:Just a host
	##stmt:Setup URL
	sURL = "http://CONTAINERHOSTNAME/ws/inventory.php?host=" + sys.argv[2]
else:
	##else:Invalid arg
	##stmt:Tell user and exit
	print "Usage: %s --list or --host <hostname>" % sys.argv[0]
	sys.exit(1)

##stmt:Connect to web service
oResponse = urllib.urlopen(sURL);

##stmt:Load JSON
jData = json.loads(oResponse.read())

##stmt:Output JSON
print json.dumps(jData)

##stmt:Exit script
sys.exit(0)
