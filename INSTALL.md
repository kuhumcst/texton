# Text Tonsorium

This document explains how you can install the Text Tonsorium under Linux.

The instructions are valid under the following assumptions:

  * The software is installed in the Windows Subsystem for Linux
  * The OS is Ubuntu 18.04
  * The URI to the resulting web application is http://localhost/texton 
  * Most of the Text Tonsorium (software and linguistic resources) is located under `/opt/texton/`.  
   Only programs that are installed using apt-get reside elsewhere.

Installation requires 
  * git-lfs  
   Some files in the Text Tonsorium are too big for GitHub. There is another place where large files are kept. `git-lfs` is needed to seamlessly access these.
  * apache2
  * PHP
  * java
  * ant
  * Tomcat  
   *Not* installed using apt-get install, sits in /opt/tomcat/latest/
  * python3
  * xmllint
  * this repo
  * bracmat  
   Interpreters are installed in two locations:  
   as a JNI (Java Native Interface) inside Tomcat  
   and as a command line tool in `/opt/texton/bin/`
  * DK-ClarinTools
   This is the central hub in the Text Tonsorium. It communicates with the user via a
   browser and communicates with the tools using HTTP `GET` or `POST` requests.
  * Many tools wrapped in web services in `/opt/texton/`
  * Linguistic resources

## git-lfs

    $> sudo apt-get install -y git-lfs

## apache

    $> sudo apt install apache2
    $> sudo apt-get install php libapache2-mod-php
    $> sudo a2enmod php7.4
    $> sudo service apache2 restart

Note "php7.4" is an example. Use the php version that you saw being installed in the presvious step. 
Copy apache2-sites/texton.conf to /etc/apache2/sites-available. 

## PHP

Some php scripts use the CURLFile class. To make that work

    $> sudo apt-get install php-curl

The html2text converter (https://github.com/soundasleep/html2text.git) requires two PHP packages

    $> sudo apt-get install php-mbstring
    $> sudo apt-get install php-dom

Restart apache

    $> sudo service apache2 restart

## java

    $ sudo apt install default-jdk
    
## ant
Ant is needed if you want to build tha DK-Clarin tools .war file from source.

    $> sudo apt install ant

## Tomcat

Tomcat can be installed using `apt install`, but it can not be started using `sudo service tomcat start` if running under WSL.  
Therefore, if you install the Text Tonsorium under WSL, you must install Tomcat from a downloaded archive.

Visit https://tomcat.apache.org/ to obtain a link to a recent archive.

    $> sudo useradd -r -m -U -d /opt/tomcat -s /bin/false tomcat
    $> cd /tmp
    $> wget http://www-eu.apache.org/dist/tomcat/tomcat-9/v9.0.14/bin/apache-tomcat-9.0.14.tar.gz -P .
    $> sudo mkdir /opt/tomcat
    $> sudo tar xf apache-tomcat-9*.tar.gz -C /opt/tomcat
    $> sudo ln -s /opt/tomcat/apache-tomcat-9.0.14 /opt/tomcat/latest
    $> sudo chown -RH tomcat: /opt/tomcat/latest
    $> sudo chmod o+x /opt/tomcat/latest/bin/
   
    $> sudo vi /opt/tomcat/latest/conf/tomcat-users.xml  

Add

    <role rolename="manager-gui"/>
    <user username="tomcat" password="secret-password" roles="manager-gui"/>

Make sure that you use a good password instead of "secret-password".

    $> sudo vi /opt/tomcat/latest/conf/server.xml

change

    <Connector port="8080" protocol="HTTP/1.1"

to

    <Connector address="127.0.0.1" port="8080" protocol="HTTP/1.1"

Start Tomcat

    $> sudo /opt/tomcat/latest/bin/startup.sh

Stop Tomcat

    $> sudo /opt/tomcat/latest/bin/shutdown.sh

Add "bracmat.jar" to classpath: create (or edit) the file /opt/tomcat/latest/bin/setenv.sh. For example

    CLASSPATH=$CLASSPATH:$CATALINA_HOME/lib/bracmat.jar

(See below how to make bracmat.jar.)

If there are several java versions, enter the path to the version of java that tomcat must use in setenv.sh, e.g.

    export JAVA_HOME=/usr/lib/jvm/java-11-openjdk-amd64

If your computer has more than 8 GB RAM, you can add

    JAVA_OPTS="-Djava.awt.headless=true -XX:+UseG1GC -Xms7168m -Xmx7168m"

Make the file executable

    $> sudo chmod ugo+x /opt/tomcat/latest/bin/setenv.sh

## Python3

We need pip3

    $> sudo apt-get install python3-pip
    
Libraries must be installed for all users, so we install them as root:

    $> sudo su
    $> cd ~
    $> umask 022
    $> pip3 install cltk
    $> exit

## xmllint

The teianno tool uses xmllint.
Installing:

    $> sudo apt install libxml2-utils

## This repo

    $> cd /opt
    $> sudo git clone https://github.com/kuhumcst/texton.git
    $> cd texton
    $> sudo chgrp -R www-data *
    $> sudo chmod -R g+w * 

## Bracmat

See https://github.com/kuhumcst/texton-bin.

## DK-ClarinTools

The repo https://github.com/kuhumcst/DK-ClarinTools contains the Java code of the central hub.
The Bracmat code of the central hub is in this (https://github.com/kuhumcst/texton) repo.
   
You can clone whereever you want. The Text Tonsorium only needs the .war file that is the result
of compiling the java source.
   
The installation instructions in https://github.com/kuhumcst/DK-ClarinTools are not up-to-data as of 2020.08.17
Just do:

    $> git clone https://github.com/kuhumcst/DK-ClarinTools.git
    $> cd DK-ClarinTools/
    $> sudo chmod ugo+x compileTomcat.sh
    $> sudo ./compileTomcat.sh
    $> sudo /opt/tomcat/latest/bin/startup.sh
   
(Assuming you installed Tomcat from a downloaded archive, see above.)

Then, open a browser and navigate to http://localhost:8080/ That should open the Tomcat welcome page. Click the "Manager App" button, using the user and password that
you defined in tomcat-users.xml. Then, in the "Path" column, click "/texton". That must open the Text Tonsorium front page.

Before proceeding, we need to install the metadata table that the Text Tonsorium needs to compute workflows. Assuming that the Text Tonsorium is installed in /opt, do

    $> cd /opt/texton/DK-ClarinTools/work/
    $> ls -lrt alltables*

Copy the file name of the most recent "alltables..." file to the clipboard. Now bavigate to http://localhost:8080/texton/admin.html. In the text field under "Import metadata tables", paste the name of the "alltables..." file and press the "import" button.

You are now ready to upload input to http://localhost:8080/texton/ and to compute workflows, but you cannot yet run those workflows, since many tools are still lacking.


## Wrapped binaries

Many of the tools require binary executable (i.e. compiled and linked) files.
Some of the necessary binaries can be obtained by cloning https://github.com/kuhumcst/texton-bin. Some binaries must be obtained from 3rd party repos. Some binaries can be built from source.

### PDFminer

Install prerequisite:

    $> sudo apt install poppler-utils

This installs /usr/bin/pdffonts.

Visit https://github.com/pdfminer/pdfminer.six and follow installation instructions.

    $> sudo su
    $> cd ~
    $> umask 022
    $> pip3 install pdfminer

If you like, you can instead install the newer pdfminer.six software. We do currently (2020.08.20) see no reason to do that.

    $> pip3 install pdfminer.six


### Cuneiform

A somewhat old OCR program. In most cases not as good as Tesseract, but sometimes it is. Nice feature: RTF output that more or less retains page lay-out. 

    $> sudo apt install cuneiform

### CST-lemma

Binary is in https://github.com/kuhumcst/texton-bin.
For building from source, see https://github.com/kuhumcst/texton-bin#cstlemma

### taggerXML

For building from source, see https://github.com/kuhumcst/texton-bin#taggerXML

### daner

Daner is at https://github.com/ITUnlp/daner

    $> cd /opt/texton/daner
    $> sudo git clone https://github.com/ITUnlp/daner.git

Afterwards there will be a subdirectory `daner/daner`.

### dapipe

Dapipe is at https://github.com/ITUnlp/dapipe 

    $> cd /opt/texton/dapipe
    $> sudo git clone https://github.com/ITUnlp/dapipe.git

Afterwards there will be a subdirectory `dapipe/dapipe`.

### espeak

This is simply installed by the following command:

    $> sudo apt-get install espeak

### html2text

    $> cd /opt/textom/html2text
    $> sudo git clone https://github.com/soundasleep/html2text

Afterwards there will be a subdirectory `html2text/html2text`.

### jsoncat

See https://github.com/kuhumcst/texton-bin#jsoncat

    $> cd ~
    $> git clone https://github.com/pantuza/jsoncat.git
    $> cd jsoncat
    $> make
    $> sudo cp bin/jsoncat /opt/texton/bin


### Lapos

    $> cd ~
    $> git clone https://github.com/cltk/lapos.git

Follow the build instructions. Copy the executable file "lapos" to /opt/texton/bin.

### mate-POStagger

This webservice calls another webservice. The .war file for that webservice is in https://github.com/kuhumcst/texton-bin.
The .war file can also be built from source, see  https://github.com/kuhumcst/mate-POStagger.

### mate-parser

This webservice calls another webservice. The .war file for that webservice is in https://github.com/kuhumcst/texton-bin.
The .war file can also be built from source, see https://github.com/kuhumcst/mate-parser

### np-genkender

This tool uses a very old, but still functioning, 3rd party program, CASS. To install,
go to the np-genkender/CASS/ directory and unpack scol-1-12.tgz.

### opennlpPOSTagger

This webservice calls another webservice. The .war file for that webservice is in https://github.com/kuhumcst/texton-bin.
The .war file can also be built from source, see https://github.com/kuhumcst/opennlpPOSTagger

### rep-check

Binary is in https://github.com/kuhumcst/texton-bin.
For building from source, see https://github.com/kuhumcst/texton-bin#repver

### LibreOffice (soffice)

LibreOffice is used to convert sundry Office formats to RTF. RTF can be handled by the tokenizer, RTFreader.

    $> sudo apt install libreoffice

It is difficult to get soffice to do what we want from PHP. What works on one machine does not always work on another one. Be warned.

### Tesseract OCR

    $> sudo apt install tesseract-ocr

In addition

    $> cd /opt/texton/tesseract
    $> sudo git clone https://github.com/tesseract-ocr/tessdata_best.git

## Install linguistic resources

    $> cd /opt
    $> sudo git clone https://github.com/kuhumcst/texton-linguistic-resources.git
    $> cd texton
    $> sudo ln -s /opt/texton-linguistic-resources texton-linguistic-resources
    
## set access rights

Make all directories accessible and readable and give owner and group write rights

    $> sudo find /opt/texton/res -type d -exec chmod 775 {} \; 

Set group to www-data, recursively

    $> sudo chown -R <user>:www-data /opt/texton/texton-linguistic-resources
    
## Enabling webservices

    $> cd apache2-sites/
    $> sudo cp texton.conf /etc/apache2/sites-available/
    $> sudo a2ensite texton.conf
    $> sudo service apache2 reload

## Proxy

    $> sudo vi /etc/apache2/mods-available/proxy.conf

Add:

        ProxyPass /texton/ http://127.0.0.1:8080/texton/
        ProxyPass /texton/ http://127.0.0.1:8080/texton/
        ProxyPass /texton/mypoll  http://127.0.0.1:8080/texton/mypoll
        ProxyPass /texton/poll  http://127.0.0.1:8080/texton/poll
        ProxyPass /texton/upload  http://127.0.0.1:8080/texton/upload
        ProxyPass /texton/zipresults  http://127.0.0.1:8080/texton/zipresults
        ProxyPass /texton/data  http://127.0.0.1:8080/texton/data
        ProxyPass /tomcat-manager http://127.0.0.1:8080/manager/html

    $> sudo a2enmod proxy
    $> sudo a2enmod proxy_ajp
    $> sudo a2enmod proxy_http
    $> sudo service apache2 restart

## create cron jobs
The input, intermediate and final data in workflow processes, and tomcat log files, can be cleaned out automatically by using cron jobs as follows: 

    0  *  * * * /usr/bin/find /opt/texton/DK-ClarinTools/work/data/ -mtime +2 -exec rm {} \;  > /dev/null 2> /dev/null
    0  *  * * * /usr/bin/find /var/log/tomcat9/ -mtime +2 -exec rm {} \;  > /dev/null 2> /dev/null
    0  *  * * * /usr/bin/curl http://127.0.0.1:8080/texton/cleanup > /dev/null 2> /dev/null


