# -*- coding: utf-8 -*-

'''
pyvrt.py
-----------
Take output from lemmatiser that already resembles VRT format, but
1) remove ambiguity by discarding all lemmas after the first |
2) add sentence elements
3) add text element
4) add column with word class only (the part of the tag before the first _ or .)
5) finish off with newline before EOF

e.g.

<text title="Murværk opmulet med vådmørtel august" date= "20170830" datefrom="20170830" dateto="20170830" timefrom="000000" timeto="235959" >
<p idp ="1">
<sentence id ="1">
Murværk         N         N.IND.SING  Murværk
opmuret           V         V.PARTC.PAST        opmure
med     PREP  PREP  med
vådmørtler       N         N.IND.PLU    vådmørtel
</sentence>



<sentence id ="2">

…

</sentence>
</p>

</text>



Parameter   Description
input       name of input file
output      name of output file
'''


import sys
from datetime import date

if len(sys.argv) == 3:
    inp = sys.argv[1]
    output = sys.argv[2]
else:
    print("python pyvrt.py input output")
    exit

today = date.today().strftime("%Y%m%d")

foutput = open(output, "w", encoding="utf-8")
#foutput = open(output, "w", encoding="iso-8859-1")

sentenceCount = 0
startSentence = True
mustWriteSentenceClose = False

try:
    foutput.write("<text title='WRITE TITLE' date='"+today+"' datefrom='"+today+"' dateto='"+today+"' timefrom='000000' timeto='235959' >\n")
    with open(inp, 'r', encoding="utf-8") as fi:
#    with open(inp, 'r', encoding="iso-8859-1") as fi:
        for l in fi:
            line = l.strip().split("\t")
            if line[0] == "":
                if sentenceCount > 0:
                    if mustWriteSentenceClose:
                        foutput.write("</sentence>\n")
                        mustWriteSentenceClose = False
                startSentence = True
            else:
                line = line[:3]
                if len(line) == 3:
                    lemma = line[1].strip().split("|")[0]
                    klasse = line[2].strip().split("_")[0].split(".")[0]
                    if klasse == "":
                        klasse = line[2]
                    line[1] = klasse
                    line.append(lemma)
                else:
                    line[1] = line[1].strip().split("|")[0]
                    
                if startSentence:
                    sentenceCount = sentenceCount + 1
                    foutput.write("<sentence id='"+str(sentenceCount)+"'>\n")
                    mustWriteSentenceClose = True
                    startSentence = False
                    
            foutput.write('\t'.join(line))
            foutput.write('\n')

        foutput.write('</text>\n')

finally:
    foutput.close()

