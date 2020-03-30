# -*- coding: utf-8 -*-

import xml.etree.cElementTree as ET
import sys
import codecs

if len(sys.argv) >= 8:
    inputAnnotationAsAttribute = sys.argv[1]
    outputAnnotationAsSpanElement = sys.argv[2]
    ancestor = sys.argv[3]
    element = sys.argv[4]
    attributeContaningValue = sys.argv[5]
    nameOfAnnotation = sys.argv[6]
    idPrefix = sys.argv[7]
    if len(sys.argv) == 9:
        type = sys.argv[8]
    else:
        type = None
else:
    print("python pysplit.py inputAnnotationAsAttribute outputAnnotationAsSpanElement ancestor element attributeContaningValue nameOfAnnotation idPrefix [type (opt.)]")
    exit

treeAtt = ET.ElementTree(file=inputAnnotationAsAttribute)
rootAtt = treeAtt.getroot()

spanGrpSpan = ET.Element(ancestor)
spanGrpSpan.set('ana',nameOfAnnotation)

spangroup = rootAtt.find(ancestor)
if spangroup == None:
    spangroup = rootAtt.find('{http://www.tei-c.org/ns/1.0}'+ancestor)
if spangroup == None:
    spangroup = rootAtt.find('{http://www.w3.org/XML/1998/namespace}'+ancestor)
if spangroup == None:
    spangroup = rootAtt

for i,elm in enumerate(spangroup):
    ID = elm.get('{http://www.w3.org/XML/1998/namespace}id')
    if ID == None:
        ID = elm.get('id')
    if ID == None:
        ID = elm.get('{http://www.tei-c.org/ns/1.0}id')
    if ID != None:
            
        value = elm.get(attributeContaningValue)
                
        c = ET.SubElement(spanGrpSpan, element)
        id = idPrefix+str(i+1)
        c.set("xml:id",id)
        c.set("from","#"+ID)
        if type != None:
            c.set("type",type)
        c.text = value
            
        

if not outputAnnotationAsSpanElement is None:
    tree = ET.ElementTree(spanGrpSpan)
    tree.write(outputAnnotationAsSpanElement, xml_declaration=True, encoding="utf-8")
