# Text Tonsorium

This document explains how you can install the Text Tonsorium under Linux Ubuntu.

The instructions are valid under the following assumptions:

  * The software is installed in the Windows Subsystem for Linux
  * The OS is Ubuntu 18.04 or higher
  * The URI to the resulting web application is http://localhost/texton 
  * Most of the Text Tonsorium (software and linguistic resources) is located under `/opt/texton/`.  
   Only programs that are installed using apt-get reside elsewhere.

Installation requires 
  * git-lfs  
   Some files in the Text Tonsorium are too big for GitHub. There is another place where large files are kept. `git-lfs` is needed to seamlessly access these.
  * texton - Bracmat part (this repo)
  * linguistic resources
  * apache2
  * PHP
  * Java
  * ant
  * Tomcat  
   *Not* installed using apt-get install, sits in /opt/tomcat/latest/
  * proxy settings
  * cron jobs
  * python3
  * xmllint
  * bracmat  
   Interpreters are installed in two locations:  
   as a JNI (Java Native Interface) inside Tomcat  
   and as a command line tool in `/opt/texton/bin/`
  * texton - Java part
   This is the central hub in the Text Tonsorium. It communicates with the user via a
   browser and communicates with the tools using HTTP `GET` or `POST` requests.
  * many tools wrapped in web services in `/opt/texton/`
  * tools that can be compiled from source

## git-lfs

```bash
$> sudo apt-get install -y git-lfs
```

## texton - Bracmat part (this repo)

```bash
$> cd /opt
$> sudo git clone https://github.com/kuhumcst/texton.git
$> cd texton
$> sudo chgrp -R www-data: *
$> sudo chmod -R g+w * 
$> cd BASE
$> sudo chown -R tomcat: *
```

In the BASE folder (/opt/texton/BASE), which contains things that Tomcat wants to interact with, owner must be set to "tomcat".
Notice that the BASE/tmp subfolder, which seems to contain nothing but a readme file, also should be owned by tomcat. It is not good enough to let it be owned by www-data. Failing to do this can result in failed upload of input.    

## linguistic resources

```bash
$> cd /opt
$> sudo git clone https://github.com/kuhumcst/texton-linguistic-resources.git
$> cd texton
$> sudo ln -s /opt/texton-linguistic-resources texton-linguistic-resources
```

Make all directories accessible and readable and give owner and group write rights

```bash
$> sudo find /opt/texton/texton-linguistic-resources -type d -exec chmod 775 {} \; 
```

Set group to www-data, recursively

```bash
$> sudo chown -R <user>:www-data /opt/texton/texton-linguistic-resources
```

## apache

```bash
$> sudo apt install apache2
```

### enabling webservices

```bash
$> cd /opt/texton/apache2-sites/
$> sudo cp texton.conf /etc/apache2/sites-available/
$> sudo a2ensite texton.conf
$> sudo service apache2 reload
```

## PHP

```bash
$> sudo apt-get install php libapache2-mod-php
$> sudo a2enmod php7.4
$> sudo service apache2 restart
```

Note "php7.4" is an example. Use the php version that you saw being installed in the previous step. 
Copy /opt/texton/apache2-sites/texton.conf (i.e. a file comtained in this repo) to /etc/apache2/sites-available. 

Some php scripts use the CURLFile class. To make that work

```bash
$> sudo apt-get install php-curl
```

The html2text converter (https://github.com/soundasleep/html2text.git) requires two PHP packages

```bash
$> sudo apt-get install php-mbstring
$> sudo apt-get install php-dom
```

Restart apache

```bash
$> sudo service apache2 restart
```

## Proxy settings

```bash
$> sudo vi /etc/apache2/mods-available/proxy.conf
```

Add:

        ProxyPass /texton/ http://127.0.0.1:8080/texton/
        ProxyPass /texton/ http://127.0.0.1:8080/texton/
        ProxyPass /texton/mypoll  http://127.0.0.1:8080/texton/mypoll
        ProxyPass /texton/poll  http://127.0.0.1:8080/texton/poll
        ProxyPass /texton/upload  http://127.0.0.1:8080/texton/upload
        ProxyPass /texton/zipresults  http://127.0.0.1:8080/texton/zipresults
        ProxyPass /texton/data  http://127.0.0.1:8080/texton/data
        ProxyPass /tomcat-manager http://127.0.0.1:8080/manager/html
        
All of the above can also be expressed as

        ProxyPassMatch "/texton/(.*)$" "http://127.0.0.1:8080/texton/$1"

```bash
$> sudo a2enmod proxy
$> sudo a2enmod proxy_ajp
$> sudo a2enmod proxy_http
$> sudo service apache2 restart
```

## cron jobs
The input, intermediate and final data in workflow processes, and tomcat log files, can be cleaned out automatically by using cron jobs as follows: 

    0  *  * * * /usr/bin/find /opt/texton/BASE/data/ -mtime +2 -exec rm {} \;  > /dev/null 2> /dev/null
    0  *  * * * /usr/bin/find /var/log/tomcat9/ -mtime +2 -exec rm {} \;  > /dev/null 2> /dev/null
    0  *  * * * /usr/bin/curl http://127.0.0.1:8080/texton/cleanup > /dev/null 2> /dev/null

## Python3

We need pip3

```bash
$> sudo apt-get install python3-pip
```

Libraries must be installed for all users, so we install them as root:

```bash
$> sudo su
# cd ~
# umask 022
# pip3 install cltk
# exit
```

## xmllint

The teianno tool uses xmllint.
Installing:

```bash
$> sudo apt install libxml2-utils
```

## bracmat

```bash
$> cd ~
$> git clone https://github.com/BartJongejan/Bracmat.git
$> cd Bracmat/src/
$> make
$> sudo cp bracmat /opt/texton/bin/
```

## texton-Java

See INSTALL.md in the texton-Java repo.

### running Text Tonsorium the first time

Create file /opt/texton/BASE/meta/properties containing

    ( baseUrlTools
    . "http://localhost:8080"
    . "Protocol and domain of infrastructure as made known to integrated tools"
    )
    ( wwwServer
    . "http://localhost"
    . "Protocol and domain of infrastructure as made known to users' browsers"
    )
    ( password
    . "Fq3vdqxIPqrKGMYh0pD+MY64Acgv8zA9Qhye+S7+mVWujVWuEPUZcEvoKGDLs6tsxJyqVnRzOZFkUBwz2QmiWA=="
    . "Empty string as password."
    )
    (salt."CvPAQd7naaqtVD1xJD37eg==".)

Open a browser and navigate to http://localhost:8080/texton/

Before proceeding, we need to install the metadata tables that the Text Tonsorium needs to compute workflows. Assuming that the Text Tonsorium is installed in /opt, do

```bash
$> cd /opt/texton/BASE/
$> ls -lrt alltables*
```

Copy the file name of the most recent "alltables..." file to the clipboard. Now navigate to http://localhost:8080/texton/admin.html. In the text field under "Import metadata tables", paste the name of the "alltables..." file and press the "import" button.

You are now ready to upload input to http://localhost:8080/texton/ and to compute workflows, but you cannot yet run those workflows, since many tools are still lacking.

If you want to run Text Tonsorium on anything else but a personal computer, you must set an administrator 'password' and a 'salt' value in the file /opt/texton/BASE/metaproperties.
Such a password/salt pair can be created in the following way:

1. On your development machine, go to http://localhost/texton/admin
2. Enter the password that you want to use on your production system in the password field below the `Show Bracmat version' heading.
3. Press the `Bracmat' button.
4. Open a linux terminal, and find the location of the file 'textonJava.log'. This location defaults to '/opt/texton/BASE/textonJava.log'. See setting in conf/log4j2.xml in the texton-Java repo.
5. Open the log file for the java part of Text Tonsorium
  $> sudo less textonJava.log
6. Go to the end of this file and find the log statement that contains the string `XMLprop`. Copy everything between `[` and `]` to the file 'properties', replacing the two same named elements.
7. Save 'properties'

Notice that you also need to replace the values 'www-server' (default http://localhost) and 'baseUrlTools' (default http://localhost:8080) into something that is meaningful for your server.
E.g. if Text Tonsorium runs as https://me.nu/texton/, then you should change these fields to (www-server."https://me.nu".) and (baseUrlTools."https://me.nu".). 
If the registered tools are configured to be on the same localhost as the Text Tonsorium itself, then (baseUrlTools."http://localhost:8080") may work as well, assuming that Tomcat runs on port 8080.

The dot following the property value of each of the entries in the 'properties' file is important. Between this dot and the closing parenthesis you can write a comment, e.g., "This is the password used on my development machine.".

## Wrapped NLP tools

Many of the tools require binary executable (i.e. compiled and linked) files.
Some of the necessary binaries can be obtained by cloning https://github.com/kuhumcst/texton-bin. Some binaries must be obtained from 3rd party repos. Some binaries can be built from source.

### CST-lemma

Binary is in https://github.com/kuhumcst/texton-bin. Copy or link to /opt/texton/bin.

### Cuneiform

A somewhat old OCR program. In most cases not as good as Tesseract, but sometimes it is. Nice feature: RTF output that more or less retains page lay-out. 

```bash
$> sudo apt install cuneiform
```

### daner

Daner is at https://github.com/ITUnlp/daner

```bash
$> cd /opt/texton/daner
$> sudo git clone https://github.com/ITUnlp/daner.git
```

Afterwards there will be a subdirectory `daner/daner`.

### dapipe

Dapipe is at https://github.com/ITUnlp/dapipe 

```bash
$> cd /opt/texton/dapipe
$> sudo git clone https://github.com/ITUnlp/dapipe.git
```

Afterwards there will be a subdirectory `dapipe/dapipe`.
It is probably better to use the newer udpipe software. 

### dependency2tree

```bash
$> git clone https://github.com/boberle/dependency2tree.git
```


```bash
$> sudo cp dependency2tree/dependency2tree.py /opt/texton/dep2tree
$> sudo apt install graphviz
```


### espeak

This is simply installed by the following command:

```bash
$> sudo apt-get install espeak
```

### html2text

```bash
$> cd /opt/texton/html2text
$> sudo git clone https://github.com/soundasleep/html2text
```

Afterwards there will be a subdirectory `html2text/html2text`.

### jsoncat

See https://github.com/kuhumcst/texton-bin#jsoncat

```bash
$> cd ~
$> git clone https://github.com/pantuza/jsoncat.git
$> cd jsoncat
$> make
$> sudo cp bin/jsoncat /opt/texton/bin
```

### Lapos

This tool must be built from source. See below.

### LibreOffice (soffice)

LibreOffice is used to convert sundry Office formats to RTF. RTF can be handled by the tokenizer, RTFreader.

```bash
$> sudo apt install libreoffice
```

It is difficult to get soffice to do what we want from PHP. What works on one machine does not always work on another one. Be warned.

### mate-parser

This webservice calls another webservice. The .war file for that webservice is in https://github.com/kuhumcst/texton-bin. Copy the .war file to the tomcat webapps folder.

### mate-POStagger

This webservice calls another webservice. The .war file for that webservice is in https://github.com/kuhumcst/texton-bin. Copy the .war file to the tomcat webapps folder.

### np-genkender

This tool uses a very old, but still functioning, 3rd party program, CASS. To install,
go to the np-genkender/CASS/ directory and unpack scol-1-12.tgz.

### opennlpPOSTagger

This webservice calls another webservice. The .war file for that webservice is in https://github.com/kuhumcst/texton-bin.  Copy the .war file to the tomcat webapps folder.

### pdf2htmlEX

This tool can be downloaded in binary format, but we have not tried that. For building, see further down.

### PDFminer

Install prerequisite:

```bash
$> sudo apt install poppler-utils
```

This installs /usr/bin/pdffonts.

Visit https://github.com/euske/pdfminer and follow the installation instructions.

```bash
$> sudo su
# cd ~
# umask 022
# pip3 install pdfminer
```

If you like, you can instead install the newer pdfminer.six (https://github.com/pdfminer/pdfminer.six) software. We do currently (2020.08.20) see no reason to do that.

```bash
$> pip3 install pdfminer.six
```

### repetitiveness checker

Binary is in https://github.com/kuhumcst/texton-bin. Copy or link to /opt/texton/bin

### taggerXML

Binary is in https://github.com/kuhumcst/texton-bin. Copy or link to /opt/texton/bin

### Stanford CoreNLP

See the readme file in the CoreNLP folder for install instructions. (The instructions assume installation in a system with systemd.)

### Tesseract OCR

```bash
$> sudo apt install tesseract-ocr
```

In addition

```bash
$> cd /opt/texton/tesseract
$> sudo git clone https://github.com/tesseract-ocr/tessdata_best.git
```

For better results, it may be better to install Tesseract from  source (https://github.com/tesseract-ocr/tesseract).
Make sure that tesseract can be seen by the webserver.

```bash
$> sudo ln /usr/local/bin/tesseract /usr/bin/tesseract
```

Text Tonsorium needs ImageMagick to extract a PDF file. Sometimes the program 'convert', part of ImageMagic, says it is not authorized to do that:
       
    convert-im6.q16: not authorized `*******' @ error/constitute.c/ReadImage/412.

In that case, edit /etc/ImageMagick-6/policy.xml and add the line

    <policy domain="coder" rights="read|write" pattern="{EPS,PS2,PS3,PS,PDF,XPS}" />

and comment out the lines telling that rights is "none" for these file types.

### udpipe

You need to build this program. See below.

The models udpipe-ud-2.5-191206.zip can be downloaded from https://lindat.mff.cuni.cz/repository/xmlui/handle/11234/1-3131
Unzip this resource:

```bash
$> cd <texton folder>/udpipe
$> cp ~/udpipe-ud-2.5-191206.zip .
$> unzip udpipe-ud-2.5-191206.zip
```

## Tools that can or must be compiled from source

In this readme, we assume that the `bin` directory is `/opt/texton/bin`.

### cstlemma

For building from source, also see https://github.com/kuhumcst/texton-bin#cstlemma

```bash
$> wget https://raw.githubusercontent.com/kuhumcst/cstlemma/master/doc/makecstlemma.bash
$> chmod ugo+x makecstlemma.bash
$> ./makecstlemma.bash
$> sudo cp cstlemma/cstlemma /opt/texton/bin/
```

### jsoncat

```bash
$> git clone https://github.com/pantuza/jsoncat.git
```
Follow the instructions in `README.md`. Copy jsoncat to `/opt/texton/bin/`.  

### Lapos

```bash
$> cd ~
$> git clone https://github.com/cltk/lapos.git
```

Follow the build instructions. Copy the executable file "lapos" to /opt/texton/bin.

### mate-parser

The .war file can be built from source, see https://github.com/kuhumcst/mate-parser. Copy the .war file to the tomcat webapps folder.

### mate-POStagger

The .war file can be built from source, see  https://github.com/kuhumcst/mate-POStagger. Copy the .war file to the tomcat webapps folder.

### opennlpPOSTagger

The .war file can be built from source, see https://github.com/kuhumcst/opennlpPOSTagger.  Copy the .war file to the tomcat webapps folder.

### pdf2htmlEX
```bash
$> sudo git clone https://github.com/pdf2htmlEX/pdf2htmlEX.git
```

See https://github.com/pdf2htmlEX/pdf2htmlEX/wiki/Building

### repetitiveness checker

For building from source, also see https://github.com/kuhumcst/texton-bin#repver

```bash
$> wget https://raw.githubusercontent.com/kuhumcst/repetitiveness-checker/master/doc/makerepver.bash
$> chmod ugo+x makerepver.bash
$> ./makerepver.bash
$> sudo cp repetitiveness-checker/repver /opt/texton/bin/
```

### rtfreader
```bash
$> wget https://raw.githubusercontent.com/kuhumcst/rtfreader/master/doc/makertfreader.bash
$> sudo chmod ugo+x makertfreader.bash
$> ./makerepver.bash
$> sudo cp rtfreader/rtfreader /opt/texton/bin/
```

### taggerXML

For building from source, also see https://github.com/kuhumcst/texton-bin#taggerXML

Copy https://github.com/kuhumcst/taggerXML/blob/master/doc/maketaggerXML.bash to your disk and run it.
Copy `taggerXML/taggerXML` to `/opt/texton/bin`.

```bash
$> wget https://raw.githubusercontent.com/kuhumcst/taggerXML/master/doc/maketaggerXML.bash
$> sudo chmod ugo+x maketaggerXML.bash
$> ./maketaggerXML.bash
$> sudo cp taggerXML/taggerXML /opt/texton/bin/
```

### udpipe

UDPipe is at https://github.com/ufal/udpipe
The generated binary needs shared objects. Therefore you need to clone this repo and build udpipe from source.

```bash
$> cd ~
$> git clone https://github.com/ufal/udpipe.git
$> cd udpipe/src
$> make
$> cp udpipe <texton folder>/bin
```
