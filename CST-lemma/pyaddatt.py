# -*- coding: utf-8 -*-

import xml.etree.cElementTree as ET
import sys
import codecs

if len(sys.argv) == 7:

    input = sys.argv[1]
    output = sys.argv[2]
    attribute = sys.argv[3]
    ancestor = sys.argv[4]
    elem = sys.argv[5]
    attr = sys.argv[6]
else:
    print("python pyaddatt.py input output attributeToAdd ancestor element attr")
    print("The value - can be used as 'don't care' value for ancestor, element and attr")
    exit

tree = ET.ElementTree(file=input)
root = tree.getroot()
if ancestor == '-':
    ancestorNode = root
else:
    ancestorNode = root.find(ancestor)
    if ancestorNode == None:
        ancestorNode = root

for i,elm in enumerate(ancestorNode):
    if elem == '-' or elm.tag == elem:
        if attr == '-':
            elm.set(attribute,"")
        else:
            att = elm.get('{http://www.w3.org/XML/1998/namespace}'+attr)
            if att == None:
                att = elm.get(attr)
            if att == None:
                att = elm.get('{http://www.tei-c.org/ns/1.0}'+attr)
            if att != None:
                elm.set(attribute,"")

tree.write(output, encoding="utf-8")

