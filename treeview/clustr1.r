# Idea for text labeling input
# textlabs is a list of character strings for the text part of the the label
# chunksize is a list of numeric for the number of chunks in each text
# textlabs and chunksize must have same length and are assumed to correspond elementwise
# so that first textlabs has first chunksize number of chunks

myCluster <- function(input.file , textlabs = NULL , chunksize = NULL ,
                      metric = "euclidean" , method = "average" ,
                      output.type = "pdf", outputfile = "Dendogram" , 
		      main = "Dendogram",header=T, comment.char="", 
		      row.names=1, p=2, type='tsv',
		      labelFile=NULL,scrubtags="",divitags=""){


	

        ## List of possible distance metrics
        ## METHODS <- c("euclidean", "maximum", "manhattan", "canberra",
        ## "binary", "minkowski")

        ## List of possible cluster-distance methods
        ## METHODS <- c("ward", "single", "complete", "average", "mcquitty",
        ## "median", "centroid")


    library(stats)
     #change this for the text you'd like to input
    if (type == 'csv') {
    	input.data <- read.table(as.character(input.file), header=header, comment.char=comment.char, row.names=row.names, sep=",")
    }
    else if (type == 'txt') {
		input.data <- read.table(as.character(input.file), header=header, comment.char=comment.char, row.names=row.names, sep="")
    }
    else {
    	input.data <- read.table(as.character(input.file), header=header, comment.char=comment.char, row.names=row.names, sep="\t")
    }

    tTable <- input.data #Transpose is necessary if data file originally has words for rows and chunks for columns
					# hclust assumes objects/chunks are the rows
					# if someone passes us data, we should check which direction

    rowSums <- apply(tTable, 1, sum) # getting the total for each chunk/row, we know we are adding acrossing the rows because of the second argument
	denoms <- matrix(rep(rowSums, dim(tTable)[2]), byrow=F, ncol=dim(tTable)[2]) # repeat the row sums by n times; n= the number of columns. 
# the matrix is filled by column. 

    relFreq <- tTable/denoms #  the original data divides denoms piece-wise. This gives the proportion of each word in a chunk 

    if( !is.null(textlabs) && !is.null(chunksize)) { # if use textlabs and chunksize, the data must be order with one text followed by the next
		if(length(textlabs) != length(chunksize)) stop("number of texts and corresponding chunk numbers must match")
		else {# check that sum(chunksize) == dim(relFreq)[1] , total number of chunks equals number of rows in relFreq
			L <- length(chunksize)
			temp <- NULL
			for(i in 1:L) {
				for(k in 1:chunksize[i]){
				temp <- c(temp,paste(textlabs[i],as.character(k),sep=""))
				}
			}
			row.names(relFreq) <- temp
		}
	}
	# else 0

    # change the names of the labels
    #row.names(relFreq) <- c("a","b","c","d","e","f","g","h","i","j"...)
	if (file.info(labelFile)$size!=0)
	{
	    tempLABELS <- read.csv(as.character(labelFile), sep=",", as.is=TRUE, header=FALSE);
	    if (length(tempLABELS) == length(row.names(relFreq)))
	    {
	        row.names(relFreq) <- tempLABELS;
	    }	
    }


   

    dist.tTable <- dist(relFreq , method = metric, p=p)

    hCluster <- hclust(dist.tTable, method = method)

    if(!is.character(main)) stop("main must be a character string")

    if(output.type=="pdf"){
    	# dev.control()
		#outfilename <- paste(outputfile,".pdf",sep="")
		outfilename<-paste(outputfile,sep="")
    	pdf(outfilename , onefile = TRUE, width=7.25, height=10)
		max <- max( nchar( hCluster$labels ) ) # be sure there's room for the labels
		par( mar=c( 6.1, 2.1, 4.1, ( max / 2.0 ) ) ) # margins
    	#plot(hCluster, hang = -1, main = main)

		# create bottom lines of tags from scrubber an divitext
		t.subtitle <- paste("TreeView Options: Metric: ",metric,", Linkage: ",method)
    		s.subtitle <- gsub("_"," ",scrubtags,fixed=T)
		s.subtitle <- paste(s.subtitle,sep="")
    		d.subtitle <- gsub("_"," ",divitags,fixed=T)
		d.subtitle <- paste(d.subtitle,sep="")
		subtitle <- paste(s.subtitle,"\n",d.subtitle,"\n",t.subtitle)

		plot( as.dendrogram(hCluster), main=main, horiz=TRUE, cex=2, axes=FALSE, xlab="", sub=subtitle, cex.sub=.5);
		# to put the title on top:
			# change ylab=main to main=main
			# change font.lab=2 to font.main=2
			# and adjust margins
    	junk <- dev.off() # junk catches stdout of dev. (otherwise everything breaks)
    }
    else if (output.type=="phyloxml"){
	outfilename <- paste(outputfile,sep="");
	hClustToXML(hCluster, outfilename, TRUE, metric, method)
   
	}
    else{}

	# return the row labels as a string 
	str<-"<r>";
	for (i in row.names(relFreq)) {
		str<-paste(str,i,sep=",");
	}	

	return (str);


}
