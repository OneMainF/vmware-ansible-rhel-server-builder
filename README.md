# vmware-rhel-server-builder

Here are list of things you need to get this working with Docker

1. Setup a Linux server and install Docker
2. Download this project
3. Edit the docker.properties file
4. Edit the networks.json file.  This file holds a mapping of your VMWare network and their Managed Object Reference (MoRef) 
5. Build Docker image - docker build -t hs .
6. Run the Docker image - docker run -p 80:80 -v /var/lib/mysql:/var/lib/mysql hs
7. When the container has started point your browser at http://your\_server/createVM.html
8. Create VMs.

The docker.properties needs to be setup correctly for this work.
There are a lot of details that go along with this file so please read carefully.

Things to note about the following varibles in the docker.properties file
RHEL6KICKSTARTISO
RHEL7KICKSTARTISO

You will need to crack open the bootable RHEL ISO and add the following to the boot line.

For RHEL6  
kssendmac ks.sendmac inst.ks.sendmac ks=http://CONTAINERHOSTNAME/kickstart/ks.php?osid=rhel6\_64Guest

For RHEL7  
kssendmac ks.sendmac inst.ks.sendmac ks=http://CONTAINERHOSTNAME/kickstart/ks.php?osid=rhel7\_64Guest

This will tell the installation media to connect to your container to pickup it's custom Kickstart configuration

If you need more help making your custom ISO this link my help - http://serverfault.com/questions/517908/how-to-create-a-custom-iso-image-in-centos

If you need help looking up the MoRef's of your netoworks check out this page - http://www.danilochiavari.com/2014/03/28/how-to-quickly-match-a-moref-id-to-a-name-in-vmware-vsphere/

To use the Ansible dynanmic inventory copy the inventory.py script to your machine that you run your Ansible plays from and replace CONTAINERHOSTNAME with the name of your Docker host.

Use the dynamic inventory like so

$ ansible-playbook -i /some/path/inventory.py coolPlaybook.yml


Here are list of things you need to get this working with on RHEL7

1. Setup a Linux server running RHEL 7 
2. Download this project
3. Edit the variables install.yml file
4. Edit the networks.json file.  This file holds a mapping of your VMWare network and their Managed Object Reference (MoRef)
5. Install Ansible - yum -y install ansible 
6. Run the Ansible playbook - ansible-playbook install.yml 
7. Once the play has completed point your browser at http://your\_server/createVM.html
8. Create VMs.

The variables in the install.yml needs to be setup correctly for this work.
Please reference the Docker section for more information about the variables.

