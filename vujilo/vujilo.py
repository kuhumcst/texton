#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import os
os.environ["LANG"] = "C.UTF-8"
#os.environ['HOME'] = '/home/zgk261'
import sys
#import codecs
from cltk.stem.latin.j_v import JVReplacer

__author__ = ['Bart Jongejan']

logfile = open("/opt/texton/log/vujilo.py.log","w", encoding="utf-8")

def get_tags(inputfile, outputfile):
    try:
        f = open(inputfile, 'r', encoding="utf-8")
        #f = codecs.open(inputfile, 'r', encoding='utf-8')
        try:
            x = f.read()

        except IOError as e:
            logfile.write("I/O error({0}): {1}\n".format(e.errno, e.strerror))
        except: #handle other exceptions such as attribute errors
            logfile.write("Unexpected error:\n"+ sys.exc_info()[0]+"\n")

        f.close()
        #print("x:",x)
        j = JVReplacer()
        x = x.lower()
        x = j.replace(x)
        ofile = open(outputfile,"w", encoding="utf-8")
        ofile.write(x)
        ofile.close()
        logfile.write("processing done\n")

    except IOError as e:
        logfile.write("I/O error({0}): {1}\n".format(e.errno, e.strerror))
    except: #handle other exceptions such as attribute errors
        logfile.write("Unexpected error:"+ sys.exc_info()[0]+"\n")

def main():
    if len(sys.argv) == 3:
        print("3")
        inputfile = sys.argv[1]
        outputfile = sys.argv[2]
        print("inputfile:",inputfile)
        print("outputfile:",outputfile)
        get_tags(inputfile, outputfile)
    logfile.close()

if __name__ == "__main__":
    main()
