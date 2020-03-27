import xml.etree.cElementTree as ET
#from lxml import etree
import os
import sys
import codecs
from cltk.stem.latin.j_v import JVReplacer

__author__ = ['Bart Jongejan']

def get_tags(inputfile, outputfile):
    tree = ET.ElementTree(file=inputfile)
    root = tree.getroot()
    j = JVReplacer()
    for w in root.iter('w'):
        w.text = w.text.lower()
        w.text = j.replace(w.text)

    tree.write(outputfile, xml_declaration=True, encoding="utf-8")

def main():
    if len(sys.argv) == 3:
        inputfile = sys.argv[1]
        outputfile = sys.argv[2]
        get_tags(inputfile, outputfile)

if __name__ == "__main__":
    main()
