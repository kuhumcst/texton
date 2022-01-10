#!/bin/sh 
SERVICE_NAME=CoreNLP 
#PATH_TO_JAR=/opt/corenlp/stanford-corenlp-VV.jar:/opt/corenlp/stanford-corenlp-VV-models.jar:/opt/corenlp/xom.jar:/opt/corenlp/joda-time.jar:/opt/corenlp/jollyday.jar:/opt/corenlp/ejml-VV.jar 
PID_PATH_NAME=/tmp/CoreNLP-pid 
case $1 in 
start)
       echo "Starting $SERVICE_NAME ..."
  if [ ! -f $PID_PATH_NAME ]; then 
#      nohup java -jar $PATH_TO_JAR /tmp 2>> /dev/null >>/dev/null &
       nohup java -mx6g -cp "/opt/corenlp/*" edu.stanford.nlp.pipeline.StanfordCoreNLPServer -port 9000 -timeout 5000 --add-modules java.se.ee /tmp 2>> /dev/null >>/dev/null &
#      nohup java -mx6g -cp "/opt/corenlp/*" edu.stanford.nlp.pipeline.StanfordCoreNLPServer -port 9000 -timeout 5000 --add-modules java.se.ee /tmp &
       echo $! > $PID_PATH_NAME  
       echo "$SERVICE_NAME started ..."         
  else 
       echo "$SERVICE_NAME is already running ..."
  fi
;;
stop)
  if [ -f $PID_PATH_NAME ]; then
         PID=$(cat $PID_PATH_NAME);
         echo "$SERVICE_NAME stopping ..." 
         kill $PID;         
         echo "$SERVICE_NAME stopped ..." 
         rm $PID_PATH_NAME       
  else          
         echo "$SERVICE_NAME is not running ..."   
  fi    
;;    
restart)  
  if [ -f $PID_PATH_NAME ]; then 
      PID=$(cat $PID_PATH_NAME);    
      echo "$SERVICE_NAME stopping ..."; 
      kill $PID;           
      echo "$SERVICE_NAME stopped ...";  
      rm $PID_PATH_NAME     
      echo "$SERVICE_NAME starting ..."  
#     nohup java -jar $PATH_TO_JAR /tmp 2>> /dev/null >> /dev/null &            
      nohup java -mx6g -cp "/opt/corenlp/*" edu.stanford.nlp.pipeline.StanfordCoreNLPServer -port 9000 -timeout 5000 --add-modules java.se.ee /tmp 2>> /dev/null >>/dev/null &
      echo $! > $PID_PATH_NAME  
      echo "$SERVICE_NAME started ..."    
  else           
      echo "$SERVICE_NAME is not running ..."    
     fi     ;;
 esac
