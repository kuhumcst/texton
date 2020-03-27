# -*- coding: utf-8 -*-
'''
Comments:

1)
ElementTree needs XML. HTML 5 cannot be parsed. E.g., <x selected> is not parsed. This is strange, as HTML is purely tree-structured.
For HTML and XHTML some propose HTMLParser, lxml.html, BeautifulSoup, and html5lib

2)
This works:
    print "elem:"
    print elem.attrib
This doesn't
    print "elem:"+elem.attrib
but it does in no way tell me what to do.

3)
Namespaces are terrible. The attributes in this element
<c xml:id="B2009004306-1-2" type="s"/>
are internally represented as
{'{http://www.w3.org/XML/1998/namespace}id': 'B2009004306-1-2', 'type': 's'}

4)
This:
    for elm in elem.iter():
        print "elem:"
        print elem.tag
        print elem.attrib
        print "elm:"
        print elm.tag
        print elm.attrib
seems to iterate over elem and all its descendants. How do I iterate over elem's children only?
Answer: don't iter()
    for elm in elem:
Why is it easy to get elem's tag, attributes and (its first and perhaps only) text, but difficult to get its PCDATA?

5) "[ElementTree] skips over any XML comments, processing instructions, and document type declarations in the input"
   (https://docs.python.org/3/library/xml.etree.elementtree.html)

6) Unicode issue upon enountering Danish character in the word århundrede in the text
PARAGRAPH
Kr
tok.py:65: UnicodeWarning: Unicode equal comparison failed to convert both arguments to Unicode - interpreting them as being unequal
  for i in (i for i,elm in enumerate(elem) if elm.text in fork_short):
PARAGRAPH
Chr
f
eks

Writing
    unicode(elm.text) in fork_short
instead of
    elm.text in fork_short
doesn't help

http://stackoverflow.com/questions/3418262/python-unicode-and-elementtree-parse
'Your problem is that you are feeding ElementTree unicode, but it prefers to consume bytes. It will provide you with unicode in any case.'

Then how comes that
    print type(elem[i].text)
writes
    <type 'str'>
?????

Interestingly, this is not quite the case. type can be 'str', 'NoneType' or
'unicode', depending on the actual text. But at least no exception is raised!
    '
    <type 'str'>
    None
    <type 'NoneType'>
    Så
    <type 'unicode'>

It seems the accepted solution is to use unicode everywhere. For reading,
    import codecs
    f = codecs.open('/tmp/ivan_utf8.txt', 'r',
                    encoding='utf-8')
    f.read()

Good explanation http://farmdev.com/talks/unicode/

7) debugging using the help of print (write to terminal) can cause your program to exit
This happens if your terminal cannot show the text that is printed. E.g, when trying to print a fancy bullet to a DOS winodw.
Also, print converts characters to your terminal's character set, if such conversion exists. Thus, printing å works both
in a terminal supporting  UTF-8 and one supporting an 8-bit encoding that has this character.
This can be particularly annoying when you want to reassure yourself that your program is working with Unicode, not some ISO 8859.


8) tree.write('doc2.xml', pretty_print=True) is not an option. You have to do something complex.
See http://pymotw.com/2/xml/etree/ElementTree/create.html


9) 'print' will cause premature exit if it encounters a character that can't be displayed on the terminal, such as u2022 (•)

'''

import xml.etree.cElementTree as ET
import sys
import codecs

def screen(text):
    pass
    #if text:
    #    print text.encode('cp437', 'xmlcharrefreplace')

def writeSent(spanGrpSent,begin,end,tokenSent,sentenceText):
    #print('writeSent(spanGrpSent,'+begin+','+end+','+str(tokenSent)+','+''.join(sentenceText)+')')
    c = ET.SubElement(spanGrpSent, 'span')
    id = "Z"+str(tokenSent)
    tokenSent += 1
    c.set("xml:id",id)
    c.set("from","#"+begin)
    if begin != end:
        c.set("to","#"+end)
    c.set("type","Ssent")
    if sentenceText:
        if sentenceText[0] == ' ':
            sentenceText.pop(0)
    tekst = ''.join(sentenceText)
    #print('writeSent['+tekst+']')
    c.text = tekst
    return tokenSent

def writeTS(token,spanGrp,elem,text,From,To):
#    print('writeTS:['+text+']')
#    print('From:['+str(From)+']')
#    print('To:['+str(To)+']')

    if From <= To:
        spanGrpTok,spanGrpSent = spanGrp
        if From == -1:
            From = To
        begin = elem[From].attrib['{http://www.w3.org/XML/1998/namespace}id']
        end = elem[To].attrib['{http://www.w3.org/XML/1998/namespace}id']
        c = ET.SubElement(spanGrpTok, 'span')
        c.set("xml:id","t"+str(token))
        c.set("from","#"+begin)
        if To != From:
            c.set("to","#"+end)
        c.text = text
    return token

def writePT(token,spanGrp,elem,text,From,To):
    #print('writeT:['+text+']')
    #print('From:['+str(From)+']')
    #print('To:['+str(To)+']')
    tokenDone = False
    if From <= To:
        spanGrpTok,spanGrpSent = spanGrp
        if From == -1:
            From = To
        begin = elem[From].attrib['{http://www.w3.org/XML/1998/namespace}id']
        end = elem[To].attrib['{http://www.w3.org/XML/1998/namespace}id']
        length = len(text)
        if length > 3:
            last3 = text[length-3:].lower()
            #print("last3:["+last3+"]")
            if last3[0] == '\'':
                last2 = last3[1:]
                if last2 in ["ll", "ve", "re"]:
                    c = ET.SubElement(spanGrpTok, 'span')
                    c.set("xml:id","t"+str(token))
                    c.set("from","#"+begin)
                    c.text = text[:length-3]
                    token = token + 1
                    c = ET.SubElement(spanGrpTok, 'span')
                    c.set("xml:id","t"+str(token))
                    c.set("from","#"+begin)
                    if To != From:
                        c.set("to","#"+end)
                    c.text = text[length-3:]
                    tokenDone = True
                elif text.lower() == "d'ye":
                    c = ET.SubElement(spanGrpTok, 'span')
                    c.set("xml:id","t"+str(token))
                    c.set("from","#"+begin)
                    c.text = text[:2]
                    token = token + 1
                    c = ET.SubElement(spanGrpTok, 'span')
                    c.set("xml:id","t"+str(token))
                    c.set("from","#"+begin)
                    if To != From:
                        c.set("to","#"+end)
                    c.text = text[2:]
                    tokenDone = True
                else:
                    writeTS(token,spanGrp,elem,text,From,To)
                    tokenDone = True
            elif last3 == "n\'t":
                c = ET.SubElement(spanGrpTok, 'span')
                c.set("xml:id","t"+str(token))
                c.set("from","#"+begin)
                c.text = text[:3]
                token = token + 1
                c = ET.SubElement(spanGrpTok, 'span')
                c.set("xml:id","t"+str(token))
                c.set("from","#"+begin)
                if To != From:
                    c.set("to","#"+end)
                c.text = text[3:]
                tokenDone = True

        if not tokenDone and length > 2:
            last2 = text[length-2:].lower()
            #print("last2:["+last2+"]")
            if last2[0] == '\'':
                last1 = last2[1:]
                if last1 in ["s", "m", "d"]:
                    c = ET.SubElement(spanGrpTok, 'span')
                    c.set("xml:id","t"+str(token))
                    c.set("from","#"+begin)
                    c.text = text[:length-2]
                    token = token + 1
                    c = ET.SubElement(spanGrpTok, 'span')
                    c.set("xml:id","t"+str(token))
                    c.set("from","#"+begin)
                    if To != From:
                        c.set("to","#"+end)
                    c.text = text[length-2:]
                    tokenDone = True

        if not tokenDone:
            textl = text.lower()
            if textl == "cannot" or textl == "gimme" or  textl == "gonna" or textl == "gotta" or textl == "lemme" or textl == "wanna":
                c = ET.SubElement(spanGrpTok, 'span')
                c.set("xml:id","t"+str(token))
                c.set("from","#"+begin)
                c.text = text[:3]
                token = token + 1
                c = ET.SubElement(spanGrpTok, 'span')
                c.set("xml:id","t"+str(token))
                c.set("from","#"+begin)
                if To != From:
                    c.set("to","#"+end)
                c.text = text[3:]
                tokenDone = True
            elif textl == "more'n":
                c = ET.SubElement(spanGrpTok, 'span')
                c.set("xml:id","t"+str(token))
                c.set("from","#"+begin)
                c.text = text[:4]
                token = token + 1
                c = ET.SubElement(spanGrpTok, 'span')
                c.set("xml:id","t"+str(token))
                c.set("from","#"+begin)
                if To != From:
                    c.set("to","#"+end)
                c.text = text[4:]
                tokenDone = True
            elif textl == "whaddya":
                c = ET.SubElement(spanGrpTok, 'span')
                c.set("xml:id","t"+str(token))
                c.set("from","#"+begin)
                c.text = text[:3]
                token = token + 1
                c = ET.SubElement(spanGrpTok, 'span')
                c.set("xml:id","t"+str(token))
                c.set("from","#"+begin)
                if To != From:
                    c.set("to","#"+end)
                c.text = text[3:5]
                token = token + 1
                c = ET.SubElement(spanGrpTok, 'span')
                c.set("xml:id","t"+str(token))
                c.set("from","#"+begin)
                if To != From:
                    c.set("to","#"+end)
                c.text = text[5:]
                tokenDone = True
            elif textl == "whatcha":
                c = ET.SubElement(spanGrpTok, 'span')
                c.set("xml:id","t"+str(token))
                c.set("from","#"+begin)
                c.text = text[:3]
                token = token + 1
                c = ET.SubElement(spanGrpTok, 'span')
                c.set("xml:id","t"+str(token))
                c.set("from","#"+begin)
                if To != From:
                    c.set("to","#"+end)
                c.text = text[3]
                token = token + 1
                c = ET.SubElement(spanGrpTok, 'span')
                c.set("xml:id","t"+str(token))
                c.set("from","#"+begin)
                if To != From:
                    c.set("to","#"+end)
                c.text = text[4:]
                tokenDone = True
        if not tokenDone:
            writeTS(token,spanGrp,elem,text,From,To)
            tokenDone = True

    return token

#def writeT(token,spanGrp,elem,text,From,To):
#    return writePT(token,spanGrp,elem,text,From,To) 

def writeS(spanGrp,elem,text,From,To,lastElement,sentenceStuff):
    needFirstID,firstID,lastID,tokenSent,sentenceText = sentenceStuff
    #print('write:['+text+']')
    #print('From:['+str(From)+']')
    #print('To:['+str(To)+']')

    if From <= To:
        spanGrpTok,spanGrpSent = spanGrp
        if From == -1:
            From = To
        begin = elem[From].attrib['{http://www.w3.org/XML/1998/namespace}id']
        end = elem[To].attrib['{http://www.w3.org/XML/1998/namespace}id']
        lastID = end
        if needFirstID:
            firstID = begin
            needFirstID = False
        #print('to:'+str(To)+' lastElement:'+str(lastElement)+' begin:'+begin+' end:'+end+' lastID:'+lastID)
        if text == "." or text == "!" or text == "?" or lastElement:
            #print('a writeSent text:'+text+ ' sentenceText:'+str(sentenceText))
            tokenSent = writeSent(spanGrpSent,firstID,lastID,tokenSent,sentenceText)
            needFirstID = True
            sentenceText = []
        #print('needFirstID:'+str(needFirstID))
    return (needFirstID,firstID,lastID,tokenSent,sentenceText)

def catchUp(token,spanGrp,elem,From,To,lastElement,sentenceStuff):
    #print('catchUp:'+str(From)+','+str(To))
    if From == -1:
        if To >= 0:
            lngth = len(elem)
            token = writeT(token,spanGrp,elem,elem[To].text,From,To)
            sentenceStuff = writeS(spanGrp,elem,elem[To].text,From,To,lastElement,sentenceStuff)
    else:
        for i,Elm in enumerate(elem[From:To+1]):
            token = writeT(token,spanGrp,elem,Elm.text,-1,i)
            sentenceStuff = writeS(spanGrp,elem,Elm.text,-1,i,lastElement,sentenceStuff)
    return 1,sentenceStuff

def outputThis(perhapsInitials,perhapsAbbreviation,perhapsCode,hyphenseen,From,To,token,spanGrp,elem,separatorSeen,lastElement,sentenceStuff):
    #print('outputThis')
    if perhapsInitials and (not perhapsAbbreviation) and (not perhapsCode):
        #print('perhapsInitials and (not perhapsAbbreviation)')
        # Each initial is a separate token
        for j in range(From,To+1,2):
            token = writeT(token,spanGrp,elem,''.join(Elm.text for Elm in elem[j:j+2]),j,j+1)
            sentenceStuff = writeS(spanGrp,elem,''.join(Elm.text for Elm in elem[j:j+2]),j,j+1,lastElement,sentenceStuff)
            token += 1
    elif perhapsAbbreviation:
        #print('perhapsAbbreviation')
        if separatorSeen and elem[To].text != ".":
            # All elements but last together in one token
            # Last element is separate token
            Text1 = ''.join(Elm.text for Elm in elem[From:To])
            Text2 = elem[To].text
            token = writeT(token,spanGrp,elem,Text1,From,To-1)
            sentenceStuff = writeS(spanGrp,elem,Text1,From,To-1,False,sentenceStuff)
            token += 1
            token = writeT(token,spanGrp,elem,Text2,-1,To)
            sentenceStuff = writeS(spanGrp,elem,Text2,-1,To,lastElement,sentenceStuff)
            token += 1
        else:
            # All elements together in one token
            Text = ''.join(Elm.text for Elm in elem[From:To+1])
            token = writeT(token,spanGrp,elem,Text,From,To)
            sentenceStuff = writeS(spanGrp,elem,Text,From,To,lastElement,sentenceStuff)
            token += 1
    elif perhapsCode:
        #print('perhapsCode')
        if separatorSeen:
            #print('separatorSeen')
            ft = elem[From].text
            allsame = True
            for j in range(From,To+1,1):
                if elem[j].text != ft:
                    allsame = False
                    break
            if allsame:
                Text1 = ''.join(Elm.text for Elm in elem[From:To+1])
                token = writeT(token,spanGrp,elem,Text1,From,To)
                sentenceStuff = writeS(spanGrp,elem,Text1,From,To,lastElement,sentenceStuff)
                token += 1

            elif ft.isalnum() or ft == '-':
                Text1 = ''.join(Elm.text for Elm in elem[From:To])
                Text2 = elem[To].text
                token = writeT(token,spanGrp,elem,Text1,From,To-1)
                #sentenceStuff = writeS(spanGrp,elem,Text1,From,To-1,False,sentenceStuff)
                token += 1
                token = writeT(token,spanGrp,elem,Text2,-1,To)
                sentenceStuff = writeS(spanGrp,elem,Text2,From,To,lastElement,sentenceStuff)
                token += 1
            else:
                # first and last elements constitute two tokens.
                # The elements in between constitute one token.
                Text1 = ft
                Text2 = ''.join(Elm.text for Elm in elem[From+1:To])
                Text3 = elem[To].text #''.join(Elm.text for Elm in elem[To:To+1])
                token = writeT(token,spanGrp,elem,Text1,-1,From)
                #sentenceStuff = writeS(spanGrp,elem,Text1,-1,From,False,sentenceStuff)
                token += 1
                token = writeT(token,spanGrp,elem,Text2,From+1,To-1)
                #sentenceStuff = writeS(spanGrp,elem,Text2,From+1,To-1,False,sentenceStuff)
                token += 1
                token = writeT(token,spanGrp,elem,Text3,-1,To)
                sentenceStuff = writeS(spanGrp,elem,Text3,From,To,lastElement,sentenceStuff)
                token += 1
        elif elem[From].text.isalnum() or elem[From].text == '-':
            #print('elem[From].text.isalnum() or elem[From].text == -')
            Text = ''.join(Elm.text for Elm in elem[From:To+1])
            #print('I:'+str(From)+' to:'+str(To+1))
            #print(elem[From].text)
            #print(elem[To].text)
            #print(elem[To+1].text)
            token = writeT(token,spanGrp,elem,Text,From,To)
            sentenceStuff = writeS(spanGrp,elem,Text,From,To,lastElement,sentenceStuff)
            token += 1
        else:
            Text2 = ''.join(Elm.text for Elm in elem[From+1:To+1])
            Text1 = elem[From].text
            token = writeT(token,spanGrp,elem,Text1,-1,From)
            sentenceStuff = writeS(spanGrp,elem,Text1,-1,From,False,sentenceStuff)
            token += 1
            token = writeT(token,spanGrp,elem,Text2,From+1,To)
            sentenceStuff = writeS(spanGrp,elem,Text2,From+1,To,lastElement,sentenceStuff)
            token += 1

    elif hyphenseen:
        #print('hyphenseen')
        Text1 = ''.join(Elm.text for Elm in elem[From:To+1])
        token = writeT(token,spanGrp,elem,Text1,From,To)
        sentenceStuff = writeS(spanGrp,elem,Text1,From,To,lastElement,sentenceStuff)
        token += 1
    elif From + 1 == To:
        #print('From + 1 == To')
        Text1 = ''.join(Elm.text for Elm in elem[From:From+1])
        Text2 = ''.join(Elm.text for Elm in elem[From+1:From+2])
        token = writeT(token,spanGrp,elem,Text1,From,From)
        sentenceStuff = writeS(spanGrp,elem,Text1,From,From,False,sentenceStuff)
        token += 1
        token = writeT(token,spanGrp,elem,Text2,From+1,From+1)
        sentenceStuff = writeS(spanGrp,elem,Text2,From+1,From+1,lastElement,sentenceStuff)
        token += 1
    else:
        for j in range(From,To+1,1):
            #print(elem[j].text)
            token = writeT(token,spanGrp,elem,elem[j].text,j,j)
            sentenceStuff = writeS(spanGrp,elem,elem[j].text,j,j,lastElement,sentenceStuff)
            token += 1
    return token,sentenceStuff

def initialUppercase(text):
    if text:
        if text[0].isupper():
            return True
    return False

def possibleFunnyStartOfNewSentence(text):
    if text:
        if text.encode('utf-8') == '”' or text == '"' or text == '\'' or text == '(' or text == '[' or text == '\u00BB' or text == '\u00AB' :
            return True
    return False

def is_number(s):
    return s[0].isdigit()

def textify(t):
    s = []
    if t.text:
        if not t.text.isspace():
            s.append(t.text.strip(' \t\n\r'))
    for child in t.getchildren():
        s.extend(textify(child))
    if t.tail:
        if not t.tail.isspace():
            s.append(t.tail.strip(' \t\n\r'))
    return ''.join(s)

def recurseP(root,token,spanGrp,flags,wcindex,wcs,From,sentenceStuff):
    for j,elem in enumerate(root):
        definitelyNotInitials,definitelyNotAbbreviation,perhapsInitials,perhapsAbbreviation,perhapsCode,hyphenseen,separatorSeen = flags

        globneedFirstID,globfirstID,globlastID,tokenSent,sentenceText = sentenceStuff
        #print('j:'+str(j)+' L:'+str(len(root)))
        lastElementId = len(root) - 1
        lastElement = False
        if lastElementId == j:
            lastElement = True

        #print('elem.tag:'+ elem.tag)
        if (elem.tag == '{http://www.tei-c.org/ns/1.0}w' or elem.tag == 'w') and '{http://www.w3.org/XML/1998/namespace}id' in elem.attrib:
            elemtext = textify(elem)
            sentenceText.append(elemtext)
            elem.text = elemtext
            wcs.append(elem)
            wcindex += 1
            if (   (not definitelyNotAbbreviation)
               and (not elem.text in fork_short)
               ):
                definitelyNotAbbreviation = True
                perhapsAbbreviation = False

            if  not (  (definitelyNotInitials)
                    or (   (elem.text[0].isupper())
                       and (  len(elem.text) == 1
                           or elem.text in initials
                           )
                       )
                    ):
                #print('elem.text['+elem.text+']')
                # Johs.V. Jensen
                definitelyNotInitials = True
                perhapsInitials = False

            if From == -1:
                From = wcindex

            separatorSeen = False
            flags = definitelyNotInitials,definitelyNotAbbreviation,perhapsInitials,perhapsAbbreviation,perhapsCode,hyphenseen,separatorSeen

        elif (elem.tag == '{http://www.tei-c.org/ns/1.0}c' or elem.tag == 'c') and '{http://www.w3.org/XML/1998/namespace}id' in elem.attrib and 'type' in elem.attrib:
            elem.text = textify(elem)
            wcs.append(elem)
            wcindex += 1
            if elem.attrib['type'] == 's':
                #print('s')
                #
                #   White space
                #
                sentenceText.append(' ')
                if From == -1:
                    #print('Write one token')
                    # Write one token
                    n,sentenceStuff = catchUp(token,spanGrp,wcs,From,wcindex-1,lastElement,sentenceStuff)
                    token += n
                else:
                    #print('Write some tokens From:'+str(From))
                    # Write some tokens
                    token,sentenceStuff = outputThis(perhapsInitials,perhapsAbbreviation,perhapsCode,hyphenseen,From,wcindex-1,token,spanGrp,wcs,separatorSeen,lastElement,sentenceStuff)

                From = -1
                definitelyNotInitials = False
                definitelyNotAbbreviation = False
                perhapsInitials = False
                perhapsAbbreviation = False
                perhapsCode = False
                hyphenseen = False
                separatorSeen = False
            elif elem.attrib['type'] == 'p':
                #
                #   Punctuation
                #
                sentenceText.append(elem.text)
                dashes = [ '\u2012' , '\u2013' , '\u2014' , '\u2015' , '\u2053' ]
                invertedMarks = [ '\u00BF' , '\u00A1' ]
                ellipsis = [ '\u2026' ]
                doubleAngles = [ '\u00AB' , '\u00BB' ]
                singleQuotationMarks = [ '\'' , '`' , '\u2018' , '\u2019' ]
                doubleQuotationMarks = [ '"' , '\u201C' , '\u201D' ]
                parentheses = [ '(' , ')' ]
                charsbefore = False
                charsafter = False
                length = len(root)
                if j > 0:
                    if root[j-1].tag == 'w' or root[j-1].tag == '{http://www.tei-c.org/ns/1.0}w':
                        charsbefore = True
                if j < length - 1:
                    if root[j+1].tag == 'w' or root[j+1].tag == '{http://www.tei-c.org/ns/1.0}w':
                        charsafter = True
                
                if elem.text == '.':
                    #print('dot')
                    # Look forward. If there is a next token after a space, does it look like a sentence initial token?
                    # If not, period is likely part of the previous token.
                    if j < length - 1:
                        if 'type' in root[j+1].attrib and root[j+1].attrib['type'] == 's':
                            #print('space')
                            if not definitelyNotInitials:
                                if j < length - 2:
                                    if possibleFunnyStartOfNewSentence(root[j+2].text):
                                        definitelyNotInitials = True
                                        perhapsInitials = False
                                    elif not initialUppercase(root[j+2].text):
                                        # L. van Beethoven
                                        perhapsInitials = True
                        else:
                            #print('nospace')
                            next = root[j+1].text
                            if not definitelyNotInitials and (next == ',' or next == ')' or next == ']' or next == ';' or next == '.'):
                                #print('A perhapsInitials = True')
                                perhapsInitials = True
                    #else:
                        #print('j==length-1')
                    perhapsCode = True

                    if From == -1:
                        From = wcindex
                        definitelyNotInitials = True
                        definitelyNotAbbreviation = True

                    if not definitelyNotInitials:
                        perhapsInitials = True

                    if not definitelyNotAbbreviation:
                        perhapsAbbreviation = True

                    hyphenseen = False
                    separatorSeen = True
                    if j < length - 2:
                        if 'type' in root[j+1].attrib and root[j+1].attrib['type'] == 's':
                            if root[j+2].text and not initialUppercase(root[j+2].text) and not is_number(root[j+2].text) and not possibleFunnyStartOfNewSentence(root[j+2].text):
                                separatorSeen = False

                elif elem.text == '!' or elem.text == '?':
                    perhapsInitials = False
                    perhapsCode = True

                    if From == -1:
                        From = wcindex
                        definitelyNotInitials = True
                        definitelyNotAbbreviation = True

                    hyphenseen = False
                    separatorSeen = True

                elif elem.text in [ ',' , ';' , ':' ]:
                    perhapsInitials = False
                    perhapsCode = True

                    if From == -1:
                        From = wcindex
                        definitelyNotInitials = True
                        definitelyNotAbbreviation = True

                    hyphenseen = False
                    separatorSeen = True

                elif elem.text in dashes or elem.text in invertedMarks or elem.text in ellipsis:
                    perhapsCode = False

                    if From == -1:
                        From = wcindex
                        definitelyNotInitials = True
                        definitelyNotAbbreviation = True

                    hyphenseen = False
                    separatorSeen = True


                elif elem.text in ['\''] and charsbefore and charsafter:
                    hyphenseen = True
                    separatorSeen = False


                    if From == -1:
                        From = wcindex
                        definitelyNotAbbreviation = True

                    if not definitelyNotAbbreviation:
                        perhapsAbbreviation = True
                    definitelyNotInitials = True
                    perhapsInitials = False

                    if j < length - 1:
                        if 'type' in root[j+1].attrib:
                            if root[j+1].attrib['type'] != 's':
                                perhapsCode = True
                        else:
                            perhapsCode = True


                elif (elem.text in parentheses) or (elem.text in singleQuotationMarks) or (elem.text in doubleQuotationMarks) or (elem.text in doubleAngles):
#                elif (elem.text in parentheses) or (elem.text in doubleQuotationMarks) or (elem.text in doubleAngles):
                    #print('AAA elem.text is '+elem.text)
                    if From != -1:
                        token,sentenceStuff = outputThis(perhapsInitials,perhapsAbbreviation,perhapsCode,hyphenseen,From,wcindex-1,token,spanGrp,wcs,separatorSeen,lastElement,sentenceStuff)

                    From = -1
                    n,sentenceStuff = catchUp(token,spanGrp,wcs,From,wcindex,lastElement,sentenceStuff)
                    token += n
                    From = wcindex + 1
                    definitelyNotInitials = False
                    definitelyNotAbbreviation = False
                    perhapsInitials = False
                    perhapsAbbreviation = False
                    perhapsCode = False
                    hyphenseen = False
                    separatorSeen = False

                elif elem.text in ['-']:
                    hyphenseen = True
                    separatorSeen = False


                    if From == -1:
                        From = wcindex
                        definitelyNotAbbreviation = True

                    if not definitelyNotAbbreviation:
                        perhapsAbbreviation = True
                    definitelyNotInitials = True
                    perhapsInitials = False

                    if j < length - 1:
                        if 'type' in root[j+1].attrib:
                            if root[j+1].attrib['type'] != 's':
                                perhapsCode = True
                        else:
                            perhapsCode = True


                elif elem.text in ['\u2010']:
                    hyphenseen = True
                    separatorSeen = False

                    if From == -1:
                        From = wcindex
                        definitelyNotAbbreviation = True

                    if not definitelyNotAbbreviation:
                        perhapsAbbreviation = True
                    definitelyNotInitials = True
                    perhapsInitials = False

                elif elem.text == '/' or elem.text == '\\' or elem.text == u"\u00A0":

                    perhapsCode = True

                    definitelyNotInitials = True
                    perhapsInitials = False
                    separatorSeen = False

                    if From == -1:
                        From = wcindex
                        definitelyNotInitials = True
                        definitelyNotAbbreviation = True

                elif (perhapsAbbreviation or perhapsInitials):
                    #print('(perhapsAbbreviation or perhapsInitials)')
                    if From == -1:
                        #THAT'S FUNNY. HOW CAN THAT BE?
                        n,sentenceStuff = catchUp(token,spanGrp,wcs,-1,wcindex,lastElement,sentenceStuff)
                        token += n
                    else:
                        token,sentenceStuff = outputThis(perhapsInitials,perhapsAbbreviation,perhapsCode,hyphenseen,From,wcindex-1,token,spanGrp,wcs,separatorSeen,lastElement,sentenceStuff)
                        n,sentenceStuff = catchUp(token,spanGrp,wcs,-1,wcindex,lastElement,sentenceStuff)
                        token += n

                    From = -1
                    definitelyNotInitials = False
                    definitelyNotAbbreviation = False
                    perhapsInitials = False
                    perhapsAbbreviation = False
                    hyphenseen = False
                    perhapsCode = False
                    separatorSeen = False
                else:
                    separatorSeen = False
                    if From == -1:
                        From = wcindex
#            else:
#                print("c tag with unknown type" + (elem.attrib['type'] if 'type' in elem.attrib else ""))

            flags = definitelyNotInitials,definitelyNotAbbreviation,perhapsInitials,perhapsAbbreviation,perhapsCode,hyphenseen,separatorSeen
        else:
            token,flags,(wcindex,wcs),From,sentenceStuff = recurseP(elem,token,spanGrp,flags,wcindex,wcs,From,sentenceStuff)

    return token,flags,(wcindex,wcs),From,sentenceStuff

def recurse(root,token,spanGrp,flags,wcindex,wcs,From,sentenceStuff):
    for j,elem in enumerate(root):
        definitelyNotInitials,definitelyNotAbbreviation,perhapsInitials,perhapsAbbreviation,perhapsCode,hyphenseen,separatorSeen = flags

        globneedFirstID,globfirstID,globlastID,tokenSent,sentenceText = sentenceStuff

        lastElementId = len(root) - 1
        lastElement = False
        if lastElementId == j:
            lastElement = True

        isTextBlock = False
        #print(elem.tag)
        if ((elem.tag == '{http://www.tei-c.org/ns/1.0}p') or (elem.tag == 'p')):
            isTextBlock = True
        else:
            for j,elm in enumerate(elem):
                if elm.tag == '{http://www.tei-c.org/ns/1.0}c' or elm.tag == 'c' or elm.tag == '{http://www.tei-c.org/ns/1.0}w' or elm.tag == 'w':
                    isTextBlock = True
                    #print('j:'+str(j)+':'+elm.tag)
                    break

        #print('recurse')
        if isTextBlock:
            #print('isTextBlock')
            flags = False,False,False,False,False,False,False
            lasti = -1
            length = len(elem)
            localsentenceStuff = True, "", "", tokenSent, []
            token,flags,(wcindex,wcs),From,localsentenceStuff = recurseP(elem,token,spanGrp,flags,wcindex,wcs,From,localsentenceStuff)
            needFirstID,firstID,lastID,tokenSent,sentenceText = localsentenceStuff
                #if i < 10:
                    #print('i:'+str(i))
            definitelyNotInitials,definitelyNotAbbreviation,perhapsInitials,perhapsAbbreviation,perhapsCode,hyphenseen,separatorSeen = flags
            if From != -1:
                # Last element was not space
                token,localsentenceStuff = outputThis(perhapsInitials,perhapsAbbreviation,perhapsCode,hyphenseen,From,wcindex,token,spanGrp,wcs,separatorSeen,lastElement,localsentenceStuff)
                needFirstID,firstID,lastID,tokenSent,sentenceText = localsentenceStuff
                if not needFirstID:
                    spanGrpTok,spanGrpSent = spanGrp
                    #print('b writeSent')
                    tokenSent = writeSent(spanGrpSent,firstID,lastID,tokenSent,sentenceText)
                    sentenceText = []
                else:
                    needFirstID, firstID, lastID, tokenSent, sentenceText = localsentenceStuff
            From = -1
            wcs = []
            wcindex = -1
            separatorSeen = False
            sentenceStuff = globneedFirstID,globfirstID,globlastID,tokenSent,sentenceText

        else:
            token,flags,(wcindex,wcs),From,sentenceStuff = recurse(elem,token,spanGrp,flags,wcindex,wcs,From,sentenceStuff)
            if From != -1:
                token,sentenceStuff = outputThis(perhapsInitials,perhapsAbbreviation,perhapsCode,hyphenseen,From,wcindex,token,spanGrp,wcs,separatorSeen,lastElement,sentenceStuff)
            From = -1
            wcs = []
            wcindex = -1

    return token,flags,(wcindex,wcs),From,sentenceStuff

outputfileTok = None
outputfileSent = None
tokenstyle = "simple"

if len(sys.argv) == 2:
    inputfile = sys.argv[1]
    outputfileSent = inputfile + ".sent.xml"
elif len(sys.argv) == 3:
    inputfile = sys.argv[1]
    outputfileTok = sys.argv[2]
elif len(sys.argv) == 4:
    inputfile = sys.argv[1]
    outputfileTok = sys.argv[2]
    outputfileSent = sys.argv[3]
elif len(sys.argv) == 5:
    inputfile = sys.argv[1]
    outputfileTok = sys.argv[2]
    outputfileSent = sys.argv[3]
    tokenstyle = sys.argv[4]

if tokenstyle in ['PT', 'pt', 'PennTreebank']:
    tokenstyle = "PT"
    writeT = writePT
else:
    writeT = writeTS

with codecs.open('fork_short-utf8', 'r', encoding='utf-8') as f:
    fork_short = f.read().splitlines()

with codecs.open('longinitials', 'r', encoding='utf-8') as f:
    initials = f.read().splitlines()

tree = ET.ElementTree(file=inputfile)
root = tree.getroot()

spanGrpTok = ET.Element('spanGrp')
spanGrpTok.set('ana',"ClarinTokeniser")

spanGrpSent = ET.Element('spanGrp')
spanGrpSent.set('ana',"ClarinSentences")

spanGrp = (spanGrpTok,spanGrpSent)

token = 1
wcindex = -1
From = -1

flags = False,False,False,False,False,False,False
#              (needFirstID,firstID,lastID,tokenSent,sentenceText)
sentenceStuff =    True    ,   ""  ,  ""  ,   1     ,     []
token,flags,(wcindex,wcs),From,sentenceStuff = recurse(root,token,spanGrp,flags,wcindex,[],From,sentenceStuff)
# tokenisation DONE, now building token tree ...

if not outputfileTok is None:
    tree = ET.ElementTree(spanGrpTok)
#   Write the token tree ...
    tree.write(outputfileTok, xml_declaration=True, encoding="utf-8")
#   tree written.

if not outputfileSent is None:
#   Delineating sentences also DONE, now building sentence tree ...
    tree = ET.ElementTree(spanGrpSent)
#   Write the sentence tree ...
    tree.write(outputfileSent, xml_declaration=True, encoding="utf-8")
#   tree written.
