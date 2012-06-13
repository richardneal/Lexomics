import sys

# access to Fredrik Lundh's ElementTree module, an increasingly popular API
# for light-weight and fast manipulation of XML documents within Python.
# http://effbot.org/zone/element-index.htm
import xml.etree.ElementTree as etree

# ===============================================================
def main():
	tree = etree.parse('daniel.xml')
	root = tree.getroot()

	# --------------------------------------------------
	# (0) print all the attributes of each <w> element
	printAllAttributes( root )

	# --------------------------------------------------
	# (1) print all the words that have 'he' as their lemma
	#he_as_Lemma( root )
	
	# --------------------------------------------------
	# (2) print all the words that have a function attribute
	#wordsWithFunctionAttribute( root )
	

# ===============================================================
def printAllAttributes( root ):
	'''print all the attributes of each <w> element'''
	words = root.findall('text/body/w')

	for word in words:
		print word.text
		if word.keys():
			print "\tAttributes:"
			for name, value in word.items():
				print "\t\t%s:'%s'"%(name, value)
	
		"""
		# sample output
			
		  swealg
		    Attributes:
			lemma:'swelgan'
			resp:'Scott Kleinman'
			dipl:'ealg'
		"""

		
# ===============================================================		
def he_as_Lemma( root ):
	'''print all the words that have 'he' as their lemma'''
	words = root.findall('text/body/w')

	for word in words:
		if word.attrib['lemma'] == 'he':
			print "\t '%s' lemma is '%s' "% (word.text, word.attrib['lemma'] )
	"""
		# sample output
	        # 'him' lemma is 'he' 
	        # 'his' lemma is 'he' 
	        # 'his' lemma is 'he' 
	        # 'he' lemma is 'he' 
	        # 'he' lemma is 'he' 
	"""
	        
# ===============================================================	        
def wordsWithFunctionAttribute( root ):
	'''print all the words that have a function attribute'''
	words = root.findall('text/body/w')

	for word in words:
		if 'function' in word.keys():
			print "\t '%s' lemma is '%s' and function is: '%s'"% (word.text, word.attrib['lemma'], word.attrib['function'] )
	
	"""
	# sample output
	#   'ac' lemma is 'ac' and function is: 'conjunctive'
	"""
	

# ===============================================================
if __name__ == '__main__':
	main()
# ===============================================================