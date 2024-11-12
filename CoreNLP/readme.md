How to install CoreNLP

Fetch CoreNLP. Visit https://stanfordnlp.github.io/CoreNLP/download.html and copy the link to the latest version. In this case `https://nlp.stanford.edu/software/stanford-corenlp-4.5.7.zip`.

    cd ~
    wget https://nlp.stanford.edu/software/stanford-corenlp-4.5.7.zip

Unzip and move to destination folder

    unzip stanford-corenlp-4.5.7.zip
    sudo mv stanford-corenlp-4.5.7 /opt/

Make link to latest version

    sudo ln -s /opt/stanford-corenlp-4.5.7 /opt/corenlp

Copy CoreNLP.sh to its destination folder

    cd /opt/texton/CoreNLP/
    sudo cp CoreNLP.sh /usr/local/bin/

Make executable

    sudo chmod +x /usr/local/bin/CoreNLP.sh

Check

    /usr/local/bin/./CoreNLP.sh start
    sudo ps -ef | grep NLP
    /usr/local/bin/./CoreNLP.sh stop
    sudo ps -ef | grep NLP

Copy CoreNLP.service to its destination folder

    sudo cp CoreNLP.service /etc/systemd/system/

Enable the service

    sudo systemctl daemon-reload
    sudo systemctl enable CoreNLP.service

Start/Stop service

    sudo systemctl start CoreNLP.service
    sudo systemctl stop CoreNLP.service

Acknowledgement:
Ameya Dhamnaskar (https://medium.com/@ameyadhamnaskar/running-java-application-as-a-service-on-centos-599609d0c641)
