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
##extdep:pysphere
import pysphere
##extdep:sys
import sys
##extdep:argparse
import argparse
##extdep:pysphere
from pysphere import *
from pysphere.resources import VimService_services as VI

##section:Variables
###########################################################
hDVS = {}
sNetworkFile = "/opt/hs/bin/networks.json"

##section:Functions
###########################################################

##function:change_dvs_net
def change_dvs_net(s, vm_obj, pg_mor):
	##desc:Changes VM NIC

	##if:VM object exist?
	if vm_obj:
		##stmt:Yes, get network devices
		net_device = []

		##loop:Get each device
		for dev in vm_obj.properties.config.hardware.device:
			##stmt:Get ext device

			##if:Do we have a virtual NIC?
			if dev._type in ["VirtualE1000", "VirtualE1000e", "VirtualPCNet32", "VirtualVmxnet", "VirtualNmxnet2", "VirtualVmxnet3"]:
				##stmt:Yes, add it to the list
				net_device.append(dev)

		##if:Did we find a NIC?
		if len(net_device) == 0:
			##stmt:No, telll user and exit
			raise Exception("The vm seems to lack a Virtual Nic")
			sys.exit(1)

		##if:Did we find 1 NIC?
		if len(net_device) == 1:
			##stmt:Yes, change network on that NIC

			##loop:Get each NIC
			for dev in net_device:
				##stmt:Get NIC

				##if:New network?
				if dev.backing.port.portgroupKey != pg_mor:
					##stmt:Set new port group
					dev.backing.port._obj.set_element_portgroupKey(pg_mor)
					dev.backing.port._obj.set_element_portKey('')
				else:
					##else:Same network
					##stmt:Exit
					print "Network already configured"
					sys.exit(0)
		else:
			##else:More than 1 NIC
			##stmt:Tell user and exit
			raise Exception( "More than 1 NIC found, exiting")
			sys.exit(1)

		##stmt:Invoke ReconfigVM_Task
		request = VI.ReconfigVM_TaskRequestMsg()
		_this = request.new__this(vm_obj._mor)
		_this.set_attribute_type(vm_obj._mor.get_attribute_type())
		request.set_element__this(_this)

		#stmt:Build a list of device change spec objects
		devs_changed = []

		##loop:Get the NIC
		for dev in net_device:
			##stmt:Make changes
			spec = request.new_spec()
			dev_change = spec.new_deviceChange()
			dev_change.set_element_device(dev._obj)
			dev_change.set_element_operation("edit")
			devs_changed.append(dev_change)

		#stmt:Submit the device change list
		spec.set_element_deviceChange(devs_changed)
		request.set_element_spec(spec)
		ret = s._proxy.ReconfigVM_Task(request)._returnval

		#stmt:Wait for the task to finish
		task = VITask(ret, s)
		status = task.wait_for_state([task.STATE_SUCCESS, task.STATE_ERROR])

		##if:Success?
		if status == task.STATE_SUCCESS:
			##stmt:Yes, tell user and exit
			print "VM %s successfully reconfigured" % vm_obj
			sys.exit(0)
		elif status == task.STATE_ERROR:
			##elseif:Error
			##stmt:Tell user and exit
			print "Error reconfiguring vm: %s" % vm_obj, task.get_error_message()
			sys.exit(1)
		else:
			##else:Can't find VM
			##stmt:Tell user and exit
			print "VM %s not found" % vm_obj
			sys.exit(1)

##endfunc

##section:Main
###################################

##stmt:Parse args
parser = argparse.ArgumentParser()
parser.add_argument("host")
parser.add_argument("newnetwork")
parser.add_argument("user")
parser.add_argument("passwd")
parser.add_argument("esxihost")
args = parser.parse_args()

##stmt:Get hostname
sThisHost = str(args.host).upper()
sThisNewNetwork = str(args.newnetwork)
sThisUser = str(args.user)
sThisPasswd = str(args.passwd)
sThisEsxiHost = str(args.esxihost)

##loop:Get network MORS file
with open(sNetworkFile, "r") as oFile:
	##stmt:Load into hash
        hDVS = json.load(oFile)

##stmt:Connect to vCenter
server = VIServer()
server.connect(sThisEsxiHost, sThisUser, sThisPasswd)

##stmt:Get VM objcet
vm_obj = server.get_vm_by_name(sThisHost)

##if:VM object exist?
if vm_obj:
	##stmt:Yes, check network

	##if:Does network exist?
	if sThisNewNetwork in hDVS:
		##stmt:Yes, get port group MOR
		new_pg = hDVS[sThisNewNetwork]
	else:
		##else:Not in hash
		##stmt:Tell user and exit
		print "Cannot find MOR for " + sThisNewNetwork
		sys.exit(1)
else:
	##else:VM not found
	##stmt:Tell user and exit
	print "Cannot find " + sThisHost
	sys.exit(1)

##stmt:Change VM network
change_dvs_net(server, vm_obj, new_pg)


