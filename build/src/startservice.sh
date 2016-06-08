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

## Source properties
source /root/docker.properties

## Update scripts
## Set vCenter Host
sed -i s/"VCENTERHOST"/"${VCENTERHOST}"/g /var/www/html/ws/changeVMNetwork.php
sed -i s/"VCENTERHOST"/"${VCENTERHOST}"/g /var/www/html/ws/createvm.php

## Set clusters
sed -i s/"CLUSTER1"/"${CLUSTER1}"/g /var/www/html/ws/createvm.php
sed -i s/"CLUSTER2"/"${CLUSTER2}"/g /var/www/html/ws/createvm.php
sed -i s/"CLUSTER1"/"${CLUSTER1}"/g /var/www/html/createVM.html
sed -i s/"CLUSTER2"/"${CLUSTER2}"/g /var/www/html/createVM.html

## Set VM user/password
sed -i s/"VMORCHESTRATORUSER"/"${VMORCHESTRATORUSER}"/g /var/www/html/ws/changeVMNetwork.php
sed -i s/"VMORCHESTRATORUSER"/"${VMORCHESTRATORUSER}"/g /var/www/html/ws/createvm.php
sed -i s/"VMORPASSWD"/"${VMORPASSWD}"/g /var/www/html/ws/changeVMNetwork.php
sed -i s/"VMORPASSWD"/"${VMORPASSWD}"/g /var/www/html/ws/createvm.php

## Set ISO Datastore
sed -i s/"ISODATASTORENAME"/"${ISODATASTORENAME}"/g /var/www/html/ws/createvm.php
sed -i s/"RHEL6KICKSTARTISO"/"${RHEL6KICKSTARTISO}"/g /var/www/html/ws/createvm.php
sed -i s/"RHEL7KICKSTARTISO"/"${RHEL7KICKSTARTISO}"/g /var/www/html/ws/createvm.php

## Set DHCP Network
sed -i s/"SERVER_BUILD_DHCP_NETWORK"/"${SERVER_BUILD_DHCP_NETWORK}"/g /var/www/html/ws/createvm.php

## Set Default VMWare folder
sed -i s/"VMWARE_FOLDER"/"${VMWARE_FOLDER}"/g /var/www/html/ws/createvm.php

## Set VMware Data Center Name
sed -i s/"VMWARE_DATA_CENTER_NAME"/"${VMWARE_DATA_CENTER_NAME}"/g /var/www/html/ws/createvm.php

## Set Datastore
sed -i s/"PRODDATASTORE"/"${PRODDATASTORE}"/g /var/www/html/ws/createvm.php
sed -i s/"TESTDATASTORE"/"${TESTDATASTORE}"/g /var/www/html/ws/createvm.php

## Set Domain and DNS
sed -i s/"CONFIGDOMAIN"/"${CONFIGDOMAIN}"/g /var/www/html/kickstart/networkConfig.sh
sed -i s/"CONFIGDOMAIN"/"${CONFIGDOMAIN}"/g /var/www/html/kickstart/ks.php
sed -i s/"CONFIGDOMAIN"/"${CONFIGDOMAIN}"/g /var/www/html/ws/inventory.php
sed -i s/"CONTAINERHOSTNAME"/"${CONTAINERHOSTNAME}"/g /var/www/html/kickstart/ks.php
sed -i s/"CONFIGDNS1"/"${CONFIGDNS1}"/g /var/www/html/kickstart/networkConfig.sh
sed -i s/"CONFIGDNS2"/"${CONFIGDNS2}"/g /var/www/html/kickstart/networkConfig.sh

chown -R apache:apache /var/www/html/*

DATADIR="/var/lib/mysql"
echo "user=root" >> /etc/my.cnf
mysql_install_db --user=root --ldata=/var/lib/mysql/ &> /dev/null

cd /usr; /usr/bin/mysqld_safe --datadir=${DATADIR} &
sleep 10
mysql -u root  < /root/servers.sql
/usr/sbin/httpd -DFOREGROUND
