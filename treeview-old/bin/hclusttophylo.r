#This code takes an hclust object and converts the dendrogram it contains into a XML file in the phyloXML format.
#To preform the conversion use the hClustToXML function.

#Changelog
#8-3-11  (dbass) Merged Donald and Amos's comments and code
#7-29-11 (dbass) Standardized formating for function header comments.
#7-28-11 (dbass) Added commets. Deleted unused code
#7-22-11 (dbass) Fixed bugs with height calculation
#7-20-11 (dbass) Added annotation mode
#7-19-11 (dbass) Cleaned up code
#7-18-11 (dbass) Added code to convert hClust into a phyloxml file

CANVAS_WIDTH <- 600 #width of canvas the xml will be drawn on

# -----------------
#  openXMLElement  \
#------------------------------------------------
# Summary: outputs a line that contains a tag that opens an xml element
#
# ARGS:    elementName:     string containing the name of the tag that opens the element
#
#          indent:      numeric vector containing the number of tabs the tag needs to be indented by. Must be an integer
#
# RETURNS: A string containing a line of xml, that opens the element
#------------------------------------------------
openXMLElement <- function(tagName, indent)
{
	return(paste(paste(rep("\t", each=indent), collapse=""), "<", tagName, ">\n", sep = ""))
}

# -----------------
# closeXMLElement  \
#------------------------------------------------
# Summary: outputs a line that contains a tag that closes an xml element
#
# ARGS:    elementName:     string containing the name of the tag that closes the element
#
#          indent:      numeric vector containing the number of tabs the tag needs to be indented by. Must be an integer
#
# RETURNS: A string containing a line of xml, that closes the element
#------------------------------------------------
closeXMLElement <- function(tagName, indent)
{
	return(paste(paste(rep("\t", each=indent), collapse=""), "</", tagName, ">\n", sep = ""))
}

# -------------------
# oneLineXMLElement  \
#------------------------------------------------
# Summary: outputs a complete xml element on one line
# ARGS:    elementName:     string containing the name of the element
#
#          contents:        string containing contents that go between the opening and closing tags
#
#          indent:          numeric vector containing the number of tabs the tags needs to be indented by. Must be an integer
# RETURNS: A string containing a line of xml, that closes the element
#------------------------------------------------
oneLineXMLElement <- function(tagName, contents, indent)
{
	return(paste(paste(rep("\t", each=indent), collapse=""), "<", tagName, ">", contents, "</", tagName, ">\n", sep=""))
}

# ---------------------
# addAnnotationBranch  \
#------------------------------------------------
# Summary: outputs the necessary xml framework to add annotations to a clade
#
# ARGS:    indent:          numeric vector containing the number of tabs the tags the first line needs to be indented by
#
#          id:              number uniquely identifying the particular clade(This cannot be the same number passed to any other instance in which this function was called)
#
# RETURNS: A string containing several lines of xml, that give a clade a label of id and an annotation of "___" ___(this is necessary to avoid the node inheriting a annotation from a parent clade)
#          where id is the parameter passed in the function call. It also generates an empty uri tag(used to attach a url to a node)
#------------------------------------------------
addAnnotationBranch <- function(indent, id)
{
	IDstring <- oneLineXMLElement("name", id, indent)
	IDstring <- paste(IDstring, openXMLElement("annotation", indent), sep = "")
	indent <- indent + 1
	IDstring <- paste(IDstring, oneLineXMLElement("desc", "___", indent), sep = "")
	IDstring <- paste(IDstring, oneLineXMLElement("uri", "", indent), sep = "")
	indent <- indent - 1
	IDstring <- paste(IDstring, closeXMLElement("annotation", indent), sep = "")
}


# ---------------------
# addAnnotationLeaf    \
#------------------------------------------------
# Summary: outputs the necessary xml framework to add annotations to a leaf node. (The leaf nodes are the chunks contained in file read by mycluster. This function does not handle setting up the name
#          element. It is meant to be run immediatly before or after that element has been added
#
# ARGS:    indent:          numeric vector containing the number of tabs the tags the first line needs to be indented by
#
# RETURNS: A string containing several lines of xml, that gives the leaf node an annotation of ___(this is necessary to avoid the node inheriting a annotation from a parent clade)
#------------------------------------------------
addAnnotationLeaf <- function(indent)
{
	IDstring <- openXMLElement("annotation", indent)
	indent <- indent + 1
	IDstring <- paste(IDstring, oneLineXMLElement("desc", "___", indent), sep = "")
	IDstring <- paste(IDstring, oneLineXMLElement("uri", "", indent), sep = "")
	indent <- indent - 1
	IDstring <- paste(IDstring, closeXMLElement("annotation", indent), sep = "")
}


# ---------------------
# hClustToXML          \
#------------------------------------------------
# Summary: Takes an hClust object converts the object into XML format, and outputs the XML to a file
#
# ARGS:    hCluster:        hClust object containing dendrogram to convert
#
#          outputFile:      name of the file to output XML to
#
#          annotationMode   boolean specifying whether to add the framework necessary to add annotations to the xml file. If TRUE the XML file with add annotation tags and
#                           identifiers to every node to pinpoint what needs to be changed to add annotations. This will also give every clade a label of ~. If FALSE
#                           the function will generate a clean xml file with no annotations
#
#          distMetric       character string containing name of distance metric
#
#
#          clusterMethod    character string containing name of clustering method
#
# RETURNS: The function doesn't directly return anything. However it will output a file called outputFile (where outputFile is one of the parameters passed) which is a conversion of the data
#          in the hCluster object  to XML format.
#------------------------------------------------
hClustToXML <- function(hCluster, outputFile, annotationMode=FALSE, distMetric = "", clusterMethod = "")
{
        #adds the appropiate header before the tree
	XML <- "<phyloxml xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:schemaLocation=\"http://www.phyloxml.org http://www.phyloxml.org/1.10/phyloxml.xsd\" xmlns=\"http://www.phyloxml.org\">\n<phylogeny rooted=\"false\">\n"

        #store information on how the tree was generated
        XML <- paste(XML, openXMLElement("Clustering", 0), sep = "")
        XML <- paste(XML, oneLineXMLElement("DistanceMetric", distMetric, 1), sep = "")
        XML <- paste(XML, oneLineXMLElement("ClusteringMetric", clusterMethod, 1), sep = "")
        XML <- paste(XML, closeXMLElement("Clustering", 0), sep = "")

	#calculate the scaling factor
	#The scaling factor is a number every branch length is divided by. This is because if the lengths are to long the tree will fail to display properly.
	lastRow <- dim(hCluster$merge)[1]     #get the index of the last row
	maxHeight <- hCluster$height[lastRow] #the last row is the uppermost merge on the dendogram so it has the maximum height.

	if(maxHeight > CANVAS_WIDTH) #if the dendogram takes up more room then there is on the canvas
	{
		scalingFactor <- maxHeight / CANVAS_WIDTH  #find the number necessary to scale the dendrogram down to the size of the canvas.
	}
	else
	{
		scalingFactor <- 1
	}

        #build the tree
	XML <- paste(XML, traverseTree(hCluster$merge, hCluster$height, hCluster$labels, 0, lastRow, maxHeight, scalingFactor, 1, annotationMode)[[1]], sep = "")

	#close the tags opened in the header
	XML <- paste(XML, "</phylogeny>\n\n</phyloxml>", sep="")

	if(!is.null(outputFile)){              #if filename was given write to that file
		write(XML, outputFile)
	}
	else                                   #else write to stdout
	{
		print(XML)
	}
} 

# --------------
# traverseTree  \
#------------------------------------------------
# Summary: Recursively travels through the tree contain in a hClust object in order to convert it into XML format. The inital functioncall will start at the root of the
#          tree, and each recursive call works on a subtree.
# 
# ARGS:    Merge:               Details the actual structure of the tree. It is a n-1 x 2 matrix. (n is the number of chunks)
#                               Each row of the matrix documents a merge of two observations or groups
#                               of already merged observations. The numbers in the
#                               two columns detail what was merged. A negative number refers to a single
#                               observation with the number of that observation being the negative's inverse. A
#                               positive number refers to the outcome of the row which index is that number.
#                               This should be taken directly from component with same name contained in hClust object.
#
#          Height:              Gives the length of the branches. It is an array and an element
#                               at index i corrosponds to the length of the branch formed on row i of
#                               the merge table This should be taken directly from component with same name contained in hClust object.
#
#          Labels:              Gives the names of the observations.
#                               This should be taken directly from component with same name contained in hClust object.
#
#          Indent:              Keeps track of the current level of indentation. Any output will be
#                               preceded by indent tabs. When the function is first called indent
#                               should equal 0.
#
#          i:                   The row of merge currently being worked on.
#                               When traverseTree is first called i should equal the index of the last
#                               row in the Merge matrix.
#
#          previousHeight:      The height of the clade that is the parent to the current clade.
#                               It is used to calculate the length of the branch connecting the two. When this function is first called
#                               It should equal the maximum height of the entire dendrogram.
#
#          scalingFactor:       Is a number that all branch lengths are divided by to
#                               prevent bugs that occur when the branch lengths get too high in comparision to the size of the canvas being drawn on.
#                               See the hClustToXML function above for calculation details.
#
#	   id:                  A integer used to uniquely identify the intersections of the
#	                        branches when in annotationMode. Should no matter what the inital value entered is but for best results let the inital value equal 1
#
#          AnnotationMode:      A boolean keeping track of whether the output should contain the necessary skeleton to add annotations. If true
#	                        the output will contain it, if false it won't
#
# RETURNS: The function returns a list containing two elements. The first is a string containing the current subtree in XML format. The second is the current id number, which is used
#          to ensure no id number is repeated.
traverseTree <- function(merge, height, labels, indent, i, previousHeight, scalingFactor, id, annotationMode)
{
      #start building a new subtree in phyloxml format
	XMLClades <- openXMLElement("clade", indent)
	indent <- indent + 1 #increase the indentation so subsequent elements are shown to be inside the clade

      #calculate the height of the current branch
      #Note R's height has a different definition then phyloxml's branch length. R's height is the distance between the clade and the bottom of the dendrogram(which equals 0)
      #phyloxml's branch length is merely the distance betweem two clades. So to convert R's height into branch length take the difference of the height of the current clade
      #and the previous clade.
      XMLClades <- paste(XMLClades, oneLineXMLElement("branch_length", ((previousHeight - height[i]) / scalingFactor), indent), sep="")


  	if(annotationMode) #if annotations are being added
  	{
  		XMLClades <- paste(XMLClades, addAnnotationBranch(indent, id)) #add am annotation to mark the location of the current clade in the xml file
  		id <- id + 1 #go to next id number since each id number must be unique
  	}

      #build right branch of tree
	if(merge[i,2] < 0) #if the number in the merge matrix is negative it's a leaf
	{
		XMLClades <- paste(XMLClades, openXMLElement("clade", indent), sep = "") #create a new clade for the leaf
		indent <- indent + 1  #indent to go inside clade
		XMLClades <- paste(XMLClades, oneLineXMLElement("branch_length", (height[i] / scalingFactor), indent), sep="")  #determine necessary length of branch to line up all the leaves
			                                                                                                        #this is the distance to go from the height of the previous clade to 0
		XMLClades <- paste(XMLClades, oneLineXMLElement("name", labels[-merge[i,2]], indent), sep="") #get name of leaf
		
		if(annotationMode)  #if annotations are being added
		{
			XMLClades <- paste(XMLClades, addAnnotationLeaf(indent))  #add an annotation to the leaf
			id <- id + 1 #go to next id number since each id number must be unique
		}

		indent <- indent - 1  #unindent to go outside clade
		XMLClades <- paste(XMLClades, closeXMLElement("clade", indent), sep = "") #close clade for the leaf
	}

	else    #otherwise the right branch doesn't go to a leaf
	{
		subtreeData <- traverseTree(merge, height, labels, indent, merge[i,2], height[i], scalingFactor, id, annotationMode) #build the portion of the tree down the right branch
		id <- subtreeData[[2]] #update identifier
		XMLClades <- paste(XMLClades, subtreeData[[1]], sep = "") #add subtree to the current tree. The [[1]] selects just the subtree from the data returned
	}

	#build left branch of tree
	if(merge[i,1] < 0) #if the number in the merge matrix is negative it's a leaf
	{
		XMLClades <- paste(XMLClades, openXMLElement("clade", indent), sep = "") #create a new clade for the leaf
		indent <- indent + 1  #indent to go inside clade
		XMLClades <- paste(XMLClades, oneLineXMLElement("branch_length", (height[i] / scalingFactor), indent), sep="")  #determine necessary length of branch to line up all the leaves
			                                                                                                        #this is the distance to go from the height of the previous clade to 0
		XMLClades <- paste(XMLClades, oneLineXMLElement("name", labels[-merge[i,1]], indent), sep="") #get name of leaf
		
		if(annotationMode)
		{
			XMLClades <- paste(XMLClades, addAnnotationLeaf(indent)) 
			id <- id + 1
		}

		indent <- indent - 1  #unindent to go outside clade
		XMLClades <- paste(XMLClades, closeXMLElement("clade", indent), sep = "") #close clade for the leaf
	}

	else    #otherwise the left branch doesn't go to a leaf
	{
		subtreeData <- traverseTree(merge, height, labels, indent, merge[i,1], height[i], scalingFactor, id, annotationMode) #build the portion of the tree down the left branch
		id <- subtreeData[[2]] #update identifier
		XMLClades <- paste(XMLClades, subtreeData[[1]], sep = "") #add subtree to the current tree. The [[1]] selects just the subtree from the data returned
	}

	indent <- indent - 1  #unindent to go outside clade
	XMLClades <- paste(XMLClades, closeXMLElement("clade", indent), sep = "") #close clade
	return(list(XMLClades, id)) #return the subtree that was just built and the current unique identifier for annotation mode
}
