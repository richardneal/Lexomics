from xml.dom import minidom       
import os
import re
import sys

import codecs


"""
---------------
ScrubXML.py
===============================================================
SUMMARY:
Uses Python (XML) miniDOM (Document Object Model) to find and
remove XML tagging. No care taken to be general, as here
we're prep'n to run experiments on Allen J. Frantzen's
Anglo-Saxon Penitentials.

outputDump (string) holds concatenated (raw) text to keep

Note: some (tricky) nested tags, e.g., triple
<hi> (highlighting) tags; handled with recursive call.


INPUT - filename at top of main
OUTPUT- (use inputfilename)_OUTPUTending.txt

DATE Last Modified:

07/16/2011 (mdl)
 - as of 07/14/2011, we learned that diviText is ok with unicode,
	so there is no need to do:  hexLine = repr(line)
	
07/13/2011 (mdl)
 - now catching lines with u' or u"
 - egad ... <hi> tags have nested <space/> tags; add padded blank space
 
07/12/2011 (mdl)
 - NOT removing Roman Numerals (but code is in place; optional?)
 - LAUD484.xml causing some new problems
   - remove periods, commas, dash, colons, forward-slash, brackets
   - remove (decimal) digits in margins

07/11/2011 (mdl)
 - no longer ignoring the <add> tags, found within <corr>; 
   these seem relevant to correct spellings
   
 - replacing all "punctus" symbols (\xb7, decimal=183) with newlines

07/01/2011 (mdl)
 - think i fixed the unicode issue; divitext does
   not like unicode (PHP 5 doesn't play well), so
   this script is dumping unicode characters as
   u' UNICODE-TEXT-HERE ' via python's repr() fx;
   (see details below)
 
 6/18/2011 (mdl)
 - dumping good text into one string
 - output looks good on first glance
 - still gotta deal with this unicode business
 - could use better newlines so output looks more
   like the (formatted) website listing
   
6/17/2011 (mdl)
 - still 2do
 - check note/milestone/add/xref
 - unicode (hmmm, gaks on cmd-line, e.g., when reading ae (ash)
 - convert print to write (no newline)
 - how to catch where Alan insert <lb> in HTML and print "\n"  
   
6/5/2011 (mdl) -- getting started ... no time, gotta fly fish
===============================================================
"""



def main():
    
    inDirectory  = "../Penitentials_XML_Frantzen/"
    outDirectory = "../Penitentials_Scrubbed/"
    
    """
    if (len(sys.argv) < 1):
	print "ERROR: missing input filename inside the folder:", inDirectory
	exit()
    
    inputFile = sys.argv[1] 
    """
    inputFile  = "BX8558.xml"
 
    inFile = inDirectory + inputFile

    # use inputfilename + ending
    outFile =  outDirectory + inputFile + "_scrubbed.txt"
    
    print "\nScrubbing file: %s ...\n" % inFile
    
    # outputDump will hold the (concatenated) text to keep
    # hopefully, sans XML-tags and unicode characters as hex
    outputDump = ""
     
    xmldoc = minidom.parse(inFile)  

    # iterate over all the <div2> tags
    # div2 tag seems to contain *the* text (so ignoring div1's)
    for nextDIV2 in xmldoc.getElementsByTagName('div2'):
	
	# each element(e) <tag> within a <div2>
	for e in nextDIV2.childNodes:
	    #print e
	    if e.nodeType == e.ELEMENT_NODE and e.localName == "p":
		
		# force spacing on new paragraphs since some tagging
		# mashes words together
		outputDump = outputDump + " "
		
		# each <p> tag (seems to contain all text)
		#print "=============== new paragraph =============="
		
		# iterate over all the childnodes inside a <p> tag
		for nextPchild in e.childNodes:
		    
		    # handle all elementnodes recursively
		    if ( nextPchild.nodeType == nextPchild.ELEMENT_NODE ):
			#print "HANDLE nested TAG here", nextPchild
			outputDump = nestedTag(nextPchild, outputDump)
			#print "Main: back from recursive HI ..."
			
		    # but textnodes are ready to give up the words we want to keep
		    elif (nextPchild.nodeType == nextPchild.TEXT_NODE):
			outputDump = outputDump + nextPchild.data
			#printf "%s", nextPchild.data
		
    
    # ok, finished all <div2> tags
    # time to gak
    prep4output(outFile, outputDump)
    
# ----- end main --------------------

# ===============================================================
def nestedTag(tag, outputDump):
    #print "----------- IN handleNestedHI ... -----------------", tag
    if (tag.nodeType == tag.TEXT_NODE):
	#print "\tTEXT NODE tag"
	#print "\t", tag.nodeName
	"""
	if (tag.data == '\n'):  
	    print "==========================================\n"
	else:
	"""
	if (tag.data != '\n'):
	    #print tag
	    #print tag.data
	    outputDump = outputDump + tag.data
	
    # ignoring these annotation <tags> ...
    elif (tag.nodeType == tag.ELEMENT_NODE):
	"""
	# just wondering if i really run into these; i think
	# the last milestone and xref are nested within <div1> tags, so
	# we don't run across them in this scrub
	
	if (tag.nodeName == "note"):
	    #print "note%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%"
	elif (tag.nodeName == "xref"):
	    #print "xref%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%"
	elif (tag.nodeName == "milestone"):
	    #print "milestone%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%"
	
	else:
	"""
	# need to separate words at end of one line <lb> start of next line
	if ( tag.nodeName == "lb" ):
	    outputDump = " " + outputDump + " "
	    
	# some <hi> tags include a nested <space/> tag; ugh
	elif ( tag.nodeName == "space" ):
	    outputDump = " " + outputDump + " "
		    
	# skip/ignore text inside <note>, <xref>, <add>, and <milestone> tags
	elif ( tag.nodeName != "note" and tag.nodeName != "xref" and 
	       # tag.nodeName != "add"  and 
	       tag.nodeName != "milestone" ):
	    
	    for nested in tag.childNodes:
		
		# RECUSIVE CALL: head inside this tag ... looking for other tags inside
		outputDump = nestedTag( nested, outputDump )
		#print "\t\tback from recursive HI ..."
	    
    else:
	print "wtf: not sure why here: ", tag.nodeName
	
    # return concatenated string
    return outputDump

	
# ===============================================================
def prep4output(outFile, outputDump):
    
    # outFile: name of output file
    # outputDump: entire string to write to file
    
    # prepare to dump this to a new output file
    # XML tags are now gone; need to handle the
    # unicode characters by (i) converting lines
    # into repr() (unicode-char-as-hex) strings (see below) and then
    # (ii) split line into words, (iii) ignore the
    # initial unicode (u') marker and (iv) nuke the
    # ending quote(') from the repr()
    
    fout = codecs.open(outFile, 'w', encoding='utf-8')
    
    # in order to make the output file look (sort of) like what
    # Alan's website shows on the web, we'll split on newlines
    # here and work line by line
    lines = outputDump.split("\n")

    # one line at a time
    for nextLine in lines:
	
	# replace "punctus" (·, \xb7, early punctuation symbol) with newline
	#line = re.sub( "·", "\n", nextLine )
	temp_line1 = re.sub( u"\u00B7", r" \n ", nextLine )
	
	# LAUD482.xml has decimal numbers in the margins; remove 'em
	temp_line2 = re.sub( "\d+", " ", temp_line1 )
	
	line = re.sub( "[+;:\[\].,-/\\\]", " ", temp_line2 )
	
	
	# encode unicode characters (>ascii 255) with hex equivalents
	# NOTE: as of 07/14/2011, diviText is ok with unicode
	#hexLine = repr(line)
	hexLine = line
	#print hexLine
	
	# for example
	# u'     XLI Be \xfe\xe6s m\xe6ssepreostesgesceadwisnesse \xb7'
	
	# replace leading (u' or u") with a space
	noLeadingUline = re.sub( "^u['\"]", " ", hexLine )
	
	# replace the ending quote (' or ") with nothing ""
	# ... esse \xb7'  becomes
	# ... esse \xb7
	noQuoteHexLine = re.sub( "['\"]$", "", noLeadingUline )
	#print noQuoteHexLine
	
	# now iterate through all the words from this line
	
	words = noQuoteHexLine.split()

	for word in words:
	    # newlines have replaced old school punctus
	    # dump a newline here so output looks (more) like website
	    if ( word == r'\n' ):
		fout.write(" \n")
		
	    # drop all Roman Numberals
	    #elif ( not re.match( "^[XVIxvi]+$", word ) ):
	    else:
		paddedWord = word + " "
		#print "[%d]: %s\n" % (i, paddedWord)
		#i = i + 1
		fout.write( paddedWord )
		
	fout.write("\n")
	#junk = raw_input("hit to continue")
	
    fout.close()
    

# ===============================================================
if __name__ == '__main__':
	main()
# ===============================================================