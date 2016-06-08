
FROM centos
MAINTAINER Jason Hiatt <jason.hiatt@springleaf.com>

RUN yum -y update
RUN yum -y install unzip python-pip httpd php php-mysql mysql-server mysql mariadb-server bind-utils pwgen psmisc hostname ansible && yum clean all

RUN touch /var/log/httpd/error_log && touch /var/log/httpd/access_log
RUN ln -sf /dev/stdout /var/log/httpd/access_log && ln -sf /dev/stderr /var/log/httpd/error_log

##COPY build/conf/httpd.conf /etc/httpd/conf/
COPY build/src/startservice.sh /root/
RUN chmod +x /root/*.sh
COPY build/conf/servers.sql /root/
COPY docker.properties /root/
RUN mkdir -p /opt/hs/bin && mkdir -p /var/www/html/builds
COPY scripts/* /opt/hs/bin/
COPY build/src/* /var/www/html/
RUN chmod -R 755 /opt/hs/bin
RUN /usr/bin/pip install --upgrade pip
RUN /usr/bin/pip install -U setuptools
RUN /usr/bin/pip install -U pysphere 

COPY mysql-connector-python-master.zip /
RUN unzip mysql-connector-python-master.zip
RUN cd mysql-connector-python-master/ && python setup.py install
RUN rm /mysql-connector-python-master.zip

EXPOSE 80
ENTRYPOINT ["/bin/bash", "/root/startservice.sh"]
