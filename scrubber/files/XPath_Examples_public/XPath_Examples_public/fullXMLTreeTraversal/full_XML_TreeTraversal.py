import sys
import xml.etree.ElementTree as etree

root = etree.parse('ships.xml')


#Create an iterator
iter = root.getiterator()

#Iterate
for element in iter:
    #First the element tag name
    print "Element:", element.tag
    
    #Next the attributes (available on the instance itself using
    #the Python dictionary protocol
    if element.keys():
        print "\tAttributes:"
        for name, value in element.items():
            print "\t\tName: '%s', Value: '%s'"%(name, value)
            
    #Next the child elements and text
    print "\tChildren:"
    #Text that precedes all child elements (may be None)
    if element.text:
        text = element.text
        
        # only print first 40 chars ...
        text = len(text) > 40 and text[:40] + "..." or text
        print "\t\tText:", repr(text)
        
    if element.getchildren():
        #Can also use: "for child in element.getchildren():"
        for child in element:
            #Child element tag name
            print "\t\tElement", child.tag
            
            #The "tail" on each child element consists of the text
            #that comes after it in the parent element content, but
            #before its next sibling.
            if child.tail:
                text = child.tail
                text = len(text) > 40 and text[:40] + "..." or text
                print "\t\tText:", repr(text)  
