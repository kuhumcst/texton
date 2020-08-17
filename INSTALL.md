# Text Tonsorium

**WORK IN PROGRESS**

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
  * bracmat  
   Interpreters are installed in two locations:  
   as a JNI (Java Native Interface) inside Tomcat  
   and as a command line tool in `/opt/texton/bin/`
  * python3
  * this repo
  * DK-ClarinTools
   This is the central hub in the Text Tonsorium. It communicates with the user via a
   browser and communicates with the tools using HTTP `GET` or `POST` requests.
  * Many tools wrapped in web services in `/opt/texton/`

## git-lfs

    $> sudo apt-get install -y git-lfs

## apache

    $> sudo apt install apache2
    $> sudo apt-get install php libapache2-mod-php
    $> sudo a2enmod php7.2
    $> sudo service apache2 restart

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
    $> sudo tar xf apache-tomcat-9*.tar.gz -C /opt/tomcat
    $> sudo ln -s /opt/tomcat/apache-tomcat-9.0.14 /opt/tomcat/latest
    $> sudo chown -RH tomcat: /opt/tomcat/latest
    $> sudo chmod o+x /opt/tomcat/latest/bin/
   
    $> sudo vi /opt/tomcat/latest/conf/tomcat-users.xml  

Add

    <role rolename="manager-gui"/>
    <user username="tomcat" password="hemmligt-password" roles="manager-gui"/>

    $> sudo vi /opt/tomcat/latest/conf/server.xml

   
change

    <Connector port="8080" protocol="HTTP/1.1"

to

    <Connector address="127.0.0.1" port="8080" protocol="HTTP/1.1"

Start Tomcat

    $> sudo /opt/tomcat/latest/bin/startup.sh

Stop Tomcat

    $> sudo /opt/tomcat/latest/bin/shutdown.sh

Add to classpath, create (or edit) the file /opt/tomcat/latest/bin/setenv.bin. For example

    CLASSPATH=$CLASSPATH:$CATALINA_HOME/lib/bracmat.jar

If there are several java versions, create 

    $> sudo vi bin/setenv.sh

Enter the path to the version of java that tomcat must use, e.g.

    export JAVA_HOME=/usr/lib/jvm/java-11-openjdk-amd64

Make the file executable

    $> sudo chmod ugo+x bin/setenv.sh


## Bracmat

See https://github.com/kuhumcst/texton-bin.

## Python3

We need pip3

    $> sudo apt-get install python3-pip
    
Libraries must be installed for all users, so we install them as root:

    $> sudo su
    $> cd ~
    $> umask 022
    $> pip3 install cltk

## This repo

    $> cd /opt
    $> sudo git clone https://github.com/kuhumcst/texton.git
    $> cd texton
    $> sudo chgrp -R www-data *
    $> sudo sudo chmod -R g+w * 

## DK-ClarinTools

   The repo https://github.com/kuhumcst/DK-ClarinTools contains the Java code 
   
## Wrapped 3rd party tools

## PDFminer

Install prerequisite:

    $> sudo apt install poppler-utils

This installs /usr/bin/pdffonts.

Visit https://github.com/pdfminer/pdfminer.six and follow installation instructions.

    $> sudo su
    $> cd ~
    $> umask 022
    $> pip3 install pdfminer.six

### Cuneiform

A OCR program. In most cases not as good as Tesseract, but sometimes it is. Nice feature: RTF output that more or less retains page lay-out. 

    $> sudo apt install cuneiform

### daner

Daner is at https://github.com/ITUnlp/daner

    $> cd daner
    $> git clone https://github.com/ITUnlp/daner.git

Afterwards there will be a subdirectory `daner/daner`.

### dapipe

Dapipe is at https://github.com/ITUnlp/dapipe 

    $> cd dapipe
    $> git clone https://github.com/ITUnlp/dapipe.git

Afterwards there will be a subdirectory `dapipe/dapipe`.

### espeak

This is simply installed by the following command:

    $> apt-get install espeak

### html2text

    $> cd html2text
    $> git clone https://github.com/soundasleep/html2text

Afterwards there will be a subdirectory `html2text/html2text`.

### jsoncat

See https://github.com/kuhumcst/texton-bin#jsoncat

    $> git clone https://github.com/pantuza/jsoncat.git
    $> cd jsoncat
    $> make
    $> sudo cp bin/jsoncat /opt/texton/bin


### Lapos

    $> git clone https://github.com/cltk/lapos.git

Follow the build instructions. Copy the executable fil "lapos" to /opt/texton/bin.

### mate-POStagger

This webservice calls another webservice, https://github.com/kuhumcst/mate-POStagger

### mate-parser

This webservice calls another webservice, https://github.com/kuhumcst/mate-parser

### np-genkender

Go to the np-genkender/CASS/ directory and unpack scol-1-12.tgz.

### opennlpPOSTagger

This webservice calls another webservice, https://github.com/kuhumcst/opennlpPOSTagger

### rep-check

See https://github.com/kuhumcst/texton-bin#repver

### LibreOffice (soffice)

LibreOffice is used to convert sundry Office formats to RTF. RTF can be handled by the tokenizer, RTFreader.

    $> sudo apt install libreoffice

It is difficult to get soffice to do what we want from PHP. What works on one machine does not always work on another one. Be warned.

### Tesseract OCR

    $> sudo apt install tesseract-ocr

In addition

    $> cd tesseract
    $> git clone https://github.com/tesseract-ocr/tessdata_best.git

## set access rights

Make all directories accessible and readable and give owner and group write rights

    $> sudo find /opt/texton/res -type d -exec chmod 775 {} \; 

Set group to www-data, recursively

    $> sudo chown -R <user>:www-data /opt/texton/res

## create cron jobs
The input, intermediate and final data in workflow processes, and tomcat log files, can be cleaned out automatically by using cron jobs as follows: 

    0  *  * * * /usr/bin/find /opt/texton/DK-ClarinTools/work/data/ -mtime +2 -exec rm {} \;  > /dev/null 2> /dev/null
    0  *  * * * /usr/bin/find /var/log/tomcat9/ -mtime +2 -exec rm {} \;  > /dev/null 2> /dev/null
    0  *  * * * /usr/bin/curl http://127.0.0.1:8080/texton/cleanup > /dev/null 2> /dev/null


