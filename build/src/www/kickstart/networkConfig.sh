#!/bin/bash

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
IFCFG="/etc/sysconfig/network-scripts/ifcfg-eth0" ##vard:Config file for eth0
NETWORKFILE="/etc/sysconfig/network" ##vard:Config file for network
HOSTFILE="/etc/hosts" ##vard:Hosts file
THISNETMASK="255.255.255.0"
THISDOMAIN="CONFIGDOMAIN"
THISDNS1="CONFIGDNS1"
THISDNS2="CONFIGDNS2"

##if:Proper args?
if [ "$#" -lt "2" ]
then
	##stmt:No, exit
        exit 1
fi ##endif

##stmt:Get args
THISHOST=$1
THISIP=$2
THISMAC=$3

##stmt:Setup GW
THISGATEWAY="`echo ${THISIP} | cut -d "." -f1,2,3`.1"

##if:RHEL 6?
if [ `grep -ic "Red Hat Enterprise Linux Server release 6" /etc/redhat-release` == "1" ]
then
	##stmt:Yes, setup networking

	##if:eth0 config setup?
	if [ `grep -c "IPADDR" ${IFCFG}` == "0" ]
	then
	        ##stmt:No, modify eth0
	        echo "Configuring eth0"
	        sed -i s/dhcp/static/g ${IFCFG}
	        sed -i /NM_CONTROLLED/s/yes/no/ ${IFCFG}
	        echo "IPADDR=${THISIP}" >> ${IFCFG}
	        echo "NETMASK=${THISNETMASK}" >> ${IFCFG}
	        echo "DOMAIN=${THISDOMAIN}" >> ${IFCFG}
	        echo "DNS1=${THISDNS1}" >> ${IFCFG}
	        echo "DNS2=${THISDNS2}" >> ${IFCFG}
	fi ##endif

	##if:Routing setup?
	if [ `grep -c "GATEWAY" ${NETWORKFILE}` == "0" ]
	then
	        ##stmt:No, modify network
	        echo "Configuring gateway"
	        sed -i /HOSTNAME/d ${NETWORKFILE}
	        echo "GATEWAY=${THISGATEWAY}" >> ${NETWORKFILE}
	        echo "HOSTNAME=${THISHOST}.CONFIGDOMAIN" >> ${NETWORKFILE}
	        echo "NETWORKING_IPV6=no" >> ${NETWORKFILE}
	        echo "IPV6INIT=no" >> ${NETWORKFILE}
	fi ##endif
fi ##endif

##if:RHEL 7?
if [ `grep -ic "Red Hat Enterprise Linux Server release 7" /etc/redhat-release` == "1" ]
then
	##stmt:Yes, setup networking
	RHEL7DEV=`/usr/bin/nmcli device status | grep "ethernet" | awk '{print $1}'`
	IFCFG=`echo ${IFCFG} | sed s/"eth0"/"${RHEL7DEV}"/`
	echo "Configuring ${IFCFG}"
	sed -i /IPV6/s/yes/no/ ${IFCFG}
	sed -i s/dhcp/static/g ${IFCFG}
	echo "IPADDR=${THISIP}" >> ${IFCFG}
	echo "NETMASK=${THISNETMASK}" >> ${IFCFG}
	echo "DOMAIN=${THISDOMAIN}" >> ${IFCFG}
	echo "DNS1=${THISDNS1}" >> ${IFCFG}
	echo "DNS2=${THISDNS2}" >> ${IFCFG}
	echo "GATEWAY=${THISGATEWAY}" >> ${IFCFG}
fi ##endif

##if:Host file updates?
if [ `grep -c ${THISHOST} ${HOSTFILE}` == "0" ]
then
	##stmt:No, update hosts file
	echo "${THISIP} ${THISHOST}.${THISDOMAIN} ${THISHOST}" >> ${HOSTFILE}
fi ##endif

