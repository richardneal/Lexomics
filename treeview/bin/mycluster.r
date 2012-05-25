#This section of the code runs the actual cluster analysis.

#8-3-11  (dbass) Merged Donald and Amos's comments and code
#7-29-11 (dbass) Standardized formating for function header comments.
#7-28-11 (dbass) Added comments. Deleted unused code
#7-22-11 (dbass) Changed pdf output to match dendrograms generated in phyloxml format.
#7-18-11 (amos)  Added SVG output

# ------------
#  myCluster  \
#------------------------------------------------
# Summary: Takes an input file containing word counts for a set of chunks, uses them to create a dendrogram, and outputs the dendogram into one of several formats
#
# ARGS:    input.file:          Name of the file containing the input
#
#          textlabs:            Vector containing the names of all the texts in the input set(A text is an entire work, while a chunk is usually just one portion of a text)
#                               Used to label the chunks when the dendrogram is generated. The texts should be in the same order in the vector as they are in
#                               the input file. If NULL the function uses the names of the chunks as a label instead.
#
#          chunksize:           Vector containing the number of chunks in each text. Each element corrosponds to the text in the same position in textlabs. Must have the same size
#                               as textlabs
#
#          metric:              String specifying the metric used to evaluate the distance between two chunks. Valid options are "euclidean", "maximum", "manhattan", "canberra",
#                               "binary", and "minkowski"
#
#          method:              String specifying the method used to form clades. Valid options are "ward", "single", "complete", "average", "mcquitty",
#                               and "median", "centroid"
#          output.type          String specifying the format of the output. Valid options are "pdf", "svg", and "phyloxml" which create respectively a pdf
#                               an svg file, and an xml file using the phyloxml schema
#          output.file          The name of the file to output to without the extension.
#
#          main:                String containing the title for the Graph. Currently only working when using svg output.
#
#          input.transposed     Boolean specifying whether the input is transposed. If TRUE the input is transposed meaining the name of the chunks are the names of the rows
#                               If false input is not transposed meaning the chunk names are the names of the columns
#
# RETURNS: The function doesn't directly return any output, but it does create an file named output.file that contains the dendrogram in pdf, xml, or svg format based upon the value of output.type
#------------------------------------------------

myCluster <- function(input.file , textlabs = NULL , chunksize = NULL ,
                      metric = "euclidean" , method = "average" ,
                      output.type = "pdf", output.file = "" , main = "",
                      input.transposed = TRUE )
{

        ## List of possible distance metrics
        ## METHODS <- c("euclidean", "maximum", "manhattan", "canberra",
        ## "binary", "minkowski")

        ## List of possible cluster-distance methods
        ## METHODS <- c("ward", "single", "complete", "average", "mcquitty",
        ## "median", "centroid")

        ## List if possible output.type for writing to file
        ## OUTPUT.TYPE <- c( "pdf", "svg", "phyloxml" )

        ## If want to dump to stdout, do not provide output.file

        ## If the input has text names on top, set input.transposed <- FALSE

        ##

    library(stats)
     #change this for the text you'd like to input
    input.data <- read.table(as.character(input.file), header=T,
        comment.char="", row.names=1, sep="\t",quote="")

    #tTable <- ifelse( input.transposed, input.data, t( input.data ) )
    if ( input.transposed )
        tTable <- input.data
    else 
        tTable <- t( input.data )

    #convert from raw counts to relative frequencies (precentages) #get the total number of words in each chunk
    rowSums <- apply(tTable, 1, sum)
    denoms <- matrix(rep(rowSums, dim(tTable)[2]), byrow=F, ncol=dim(tTable)[2]) #create a matrix the same size as the data table and replace each wordcount with the number of words in that chunk
    relFreq <- tTable/denoms  #divide each word count by the total number of words to get relative frequencies

    #if there is input for the names of the texts and the number of chunks in each text use them to generate labels for the chunks inputted.
    if( !is.null(textlabs) && !is.null(chunksize)) {
        if(length(textlabs) != length(chunksize)) stop("number of texts and corresponding chunk numbers must match") #check that there are the same number of texts, 
                                                                                                                     #as the number of times the number of chunks in a text is specified
        else {
                 L <- length(chunksize)  #get the number of texts
                 temp <- NULL            #create an array to store the labels in
                 for(i in 1:L) {         #for each text
                     for(k in 1:chunksize[i]){ #do the following once per chunk
                         temp <- c(temp,paste(textlabs[i],as.character(k),sep="")) #add a label with the textname and the chunk number
			   }
                 }
        row.names(relFreq) <- temp  #store the labels
	   }
    }
    # else 0

    dist.tTable <- dist(relFreq , method = metric)

    hCluster <- hclust(dist.tTable, method = method)

    # find legnth of longest chunk name
    max <- max( nchar( hCluster$labels ) )

    if(!is.character(main)) stop("main must be a character string")

    subtitle <- paste( "Linkage Method:",method,", Distance Metric:",metric) #add the distance metric and clustering method to the subtitle

    if(output.type=="pdf"){
    	# dev.control()
    	outfilename <- paste(output.file,sep="")
    	junk <- pdf(outfilename , onefile=TRUE, width=7.25, height=10)

        # set margins so there is just enough room for the labels
        # The numbers measure margin size in line units
        # The paramets are the size of the bottom,left,top,right margins
        # On average a margin one line wide seems to have room for about 2.5 characters)
	# so the margin on the right is set to the number of lines necessary to display
        # the longest label if there was only 2 characters per line which leave's a decent buffer
        par( mar=c( 6.1, 2.1, 4.1, ( max / 2.0 ) ) )

    	junk <- plot( as.dendrogram(hCluster), horiz=TRUE, 
                      xlab="", main=main, sub=subtitle, cex=2,
                      axes=FALSE ) #draws the dendrogram horizontally (root node is on the left, leaves on the right). The conversion is necessary
	                           #since the hClust object doesn't allow for horizontal plotting
      	junk <- dev.off() #reset the reset the current device to no device
    }
    else if ( output.type == "svg" )
    {
        library("RSvgDevice") # amos: added RSvgDevice lib 7/18/11
    	outfilename <- paste(output.file,sep="")
        junk <- devSVG( file=outfilename, width = 10, height = 8,
            bg = "white", fg = "black", onefile=TRUE, xmlHeader=TRUE
        )

        # set margins so there is just enough room for the labels
        par( mar=c( 2.1, 4.1, 4.1, ( max / 2.0 ) ) )

        junk <- plot( as.dendrogram(hCluster), horiz=TRUE,
            hang=-1, main = main, xlab="", sub="" )
        junk <- dev.off()
    }
    else if ( output.type == "phyloxml" )
    {
        if ( output.file == "" )
        {
            #( hClustToXML( hCluster ) );
        }
        else
	    {
            outfilename <- paste(output.file,sep="");
	        hClustToXML(hCluster, outfilename, TRUE, metric, method)
        } 

    }
    else{}

}
