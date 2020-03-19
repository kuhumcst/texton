# -*- coding: utf-8 -*-

import xml.etree.cElementTree as ET
import sys
import codecs

if len(sys.argv) >= 5:
    teifile = sys.argv[1]
    annotation = sys.argv[2]
    attribute = sys.argv[3]
    output = sys.argv[4]
    if len(sys.argv) == 6:
        emptyattr = sys.argv[5]
    else:
        emptyattr = None
else:
    print("python pymerge.py annotationFile dependentAnnotationfile attributeForImportedDependentAnnotation output [emptyAttributeForAnnotationToBeCreated]opt.")
    print("Create copy of 'annotationFile' with an additional imported attribute and with an extra attribute that will be set by the next program in the pipe line.")
    print("E.g., add POS tags as attribute to an annotation file that already contains tokens and create a 'lemma' attribute for a lemmatiser to fill in.")
    print("It is assumed that the dependent annotation elements do have a 'from' attribute, but no 'to' attribute that has a different value than the 'from' attribute.")
    print("Both input files must contain a 'spanGrp' element.")
    exit

treeTei = ET.ElementTree(file=teifile)
treeAnn = ET.ElementTree(file=annotation)
rootTei = treeTei.getroot()
rootAnn = treeAnn.getroot()
#spanGrpTei = rootTei.find('spanGrp')
#spanGrpAnn = rootAnn.find('spanGrp')

dict = {}

for i,elm in enumerate(rootAnn):
    fromAtt = elm.get('{http://www.w3.org/XML/1998/namespace}from')
    if fromAtt == None:
        fromAtt = elm.get('from')
    if fromAtt == None:
        fromAtt = elm.get('{http://www.tei-c.org/ns/1.0}from')
    if fromAtt != None:
        dict[fromAtt] = elm.text

for i,elm in enumerate(rootTei):
    ID = elm.get('{http://www.w3.org/XML/1998/namespace}id')
    if ID == None:
        ID = elm.get('id')
    if ID == None:
        ID = elm.get('{http://www.tei-c.org/ns/1.0}id')
    if ID != None:
        elm.set(attribute,dict['#'+ID])
        if emptyattr:
            elm.set(emptyattr,"")

treeTei.write(output,encoding="utf-8")

