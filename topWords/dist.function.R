#dist.function.R

####Behavior
## This runDist will create a randomly named folder in the /tmp directory.
## It will process & create text files for the clade listings and word listings
## for each level of the specified distance metric and write them to that folder.
## Plots, in the form of .pdf files, will be saved in a /graphs folder within
## the temporary folder. All the output files will be stored in a .zip file, and
## the original uncompressed files removed.  The function will return a file name
## and path for the .zip file for the user to download.

####Input parameters:
##  input.file : is the data file with word counts from diviText
##  n.clades : number of cuts to make with cutree()
##  n.dist.values : how many levels of distance stats to process. default is 1
##  metric : for distance. default is Euclidean
##  p : for hclust.  default is 2
##  linkage : method for hclust.  default is "ave"
##  is.tab.sep : boolean for if file should use tab sep or default
##  dataset.name : name of data set (used for labels & output files, etc
##  d.metric : is the metric to use for analysis.  All metrics will have a numeric code,
##           : default is 1

####Return Value:
## the path & filename of the zipped output file

#runDist("/home/raffle/work/forPeople/Rose/merge_transpose_olafs_saga_3000.tsv", 2, 1, dataset.name="olaf-kw-manual")

## runDist(input.file  ="/home/raffle/work/forPeople/Rose/merge_transpose_olafs_saga_3000.tsv", n.clades = "2", n.dist.values ="1", dataset.name="olaf-kw-manual", metric = "euclidean", p = "2", linkage = "ave", is.tab.sep=TRUE,  d.metric = "1")

currentNode = 1 #global variable to keep track of position while
                #recursively travelling dendrogram during the plotting
                #code


runDist <- function(input.file, n.clades, n.dist.values = 1, metric = "euclidean",
                  p = 2, linkage = "ave", is.tab.sep=TRUE, dataset.name = "dist-output", d.metric = 1)
  {
    #convert input parameters to numeric as needed
    d.metric <- as.numeric(d.metric)
    p <- as.numeric(p)
    n.clades <- as.numeric(n.clades)

    #load libraries needed
    library(stats)
    #set working directory to tmp/randomwhatever
    tmp.dir <- paste("/tmp/", (10000 * round(runif(1, 0.001, 1), 4)), sep="")

    #Need to make sure the directory doesn't exist, otherwise the
    #final .zip file could have garbage in it.  So check it, if it
    #does, try again (keep looping until the directory doesn't exist).
    while(file.exists(tmp.dir)){
        tmp.dir <- paste("/tmp/", (10000 * round(runif(1, 0.0001, 1), 4)), sep="")
    }
    #Now we know the directory doesn't exist, so we can create a new one
    dir.create(tmp.dir)
    #set working directory to temp directory
    setwd(tmp.dir)

    # read in the data, changing comment character to nothing so # isn't confused
    # checking if read.table should use default separators or tabs

    if(is.tab.sep == TRUE){
      test.data <- read.table(input.file,
                              header=T,comment.char="",sep="\t",
                              row.names=1, check.names=FALSE, fill = TRUE)
    }
    else{
      test.data <- read.table(input.file, header=T,comment.char="",row.names=1, check.names=FALSE)
    }


    #move test.data to gen.data. Check to see based on dimensions if
    #transposition is necessary
    if(dim(test.data)[1] < dim(test.data)[2]){
        gen.data <- test.data
    }
    else{
        gen.data <- t(test.data)
    }



    # get the row sums for the gen.data
    rsums <- apply(gen.data,1,sum)


    ######test to see if scrubber & divitext options are included in
    ##first column of bottom two rows of .tsv (added at divitext
    ##through php) to facilitate subtitle on dendro listing all
    ##options used when generating data
    ##find number of rows
    n.rows <- dim(gen.data)[1]
    ##if the last two rows have NA values for word counts, recreate gen.data without
    ##those rows
    if(sum(is.na(rsums)[(n.rows-1):n.rows]) == 2){
        gen.data <- gen.data[-c(n.rows-1,n.rows),]
        rsums <- rsums[-c(n.rows-1,n.rows)]
    }else if(sum(is.na(rsums)[(n.rows-1):n.rows]) == 1){
        gen.data <- gen.data[-c(n.rows),]
        rsums <- rsums[-c(n.rows)]
    }


    ####Normalize gen.data by converting word counts to relative frequencies
    # create a matrix that gives the row sums as denominators at each element of the data matrix
    # to allow conversion to relative frequencies
    denoms <- matrix(rep(rsums,dim(gen.data)[2]),
                     byrow=F,ncol=dim(gen.data)[2])
    # divide each member of gen.data by it's computed denominator to get relative frequency
    gen.names <- names(gen.data)
    gen.data <- gen.data/denoms
    names(gen.data) <- gen.names

    # compute distances between all vectors, using input parameters
    dist.gen <- dist(gen.data, method=metric, p=p)
    #generate cluster object of dist.gen using input parameter
    hc <- hclust(dist.gen, method=linkage)


    ###################################################################################################
    #Distance Test
    ###################################################################################################

    #cut dendro into desired number of groups based on input parameter
    #check if n.clades is within bounds (1 <= n.clades <= number of chunks)
    if(n.clades < 1 || n.clades > dim(gen.data)[1]){
        stop("Number of clades must be between 1 and the number of rows.")
    }
    else{
        cut <- cutree(hc, k=n.clades)
    }

    #get order of indexes for reordering gen.data based on which chunks
    #are in which clade of the cut
    reorderrows.index <- order(cut)

    #reorder gen.data based on indexes from above.  After executing this command,
    #chunks are regrouped so that the first N1 rows are in clade1, the next N2
    #rows are in clade2, etc.
    reorder.gen.data <- gen.data[reorderrows.index,]

    #get the table of how many chunks are in each cut
    n.clade <- table(cut)

    ##print list of elements to elements.txt
    #generate file heading and write to file
    heading <- paste("Chunks in each clade after cutting into ", n.clades, " clades:",sep="")
    write(heading, "./clade-listing.txt")
    #for each of the unique clades from cut, write which chunks are in that clade
    for(i in unique(cut))
      {
        write(paste("\nElements of Clade ", i, ":", sep=""), "./clade-listing.txt", append=TRUE)
        write(names(cut[cut==i]), "./clade-listing.txt",ncolumns=3,append=TRUE, sep = ", ")
      }

    #creates strings of the clade names based on the the clade number for distance test
    fdoc.collection <- factor(cut, levels=1:n.clades)
    levels(fdoc.collection) <- paste("clade",1:n.clades,sep="")

    ##Plot the dendros with color-coded clades
    #create filename for dendro
    dendro.name <- paste(dataset.name, "_dendro.pdf", sep="")
    plot.trueTree(hc, outputFilename = dendro.name,
                  dataset.name=dataset.name, fdoc.collection=fdoc.collection)


    #create an array to store distance stat for all of the words
    compare.dist <- vector(length = number.words <- dim(gen.data)[2])

    #switch on d.metric to decide which distance metric to use to fill
    #compare.dist
    switch(d.metric,
           compare.dist <- getKW(number.words, fdoc.collection,
                                 gen.data, compare.dist),
           compare.dist <- getAbsoluteDistance(gen.data,fdoc.collection, n.clades)
          )
    switch(d.metric,
           d.metric.name <- "KRUSKAL-WALLIS",
           d.metric.name <- "ABSOLUTE-DISTANCE"
      )

    #let's order the results (distance values) from the biggest to the smallest
    ordered.diff <- order(compare.dist, decreasing = TRUE)

    #sort & store the values of compare.dist from largest to smallest
    sorted.uniquevals.dist <- rev(sort(unique(compare.dist)))

    #stores each of the top n.dist.values stats from distance test
    topscores.dist <- unique(sorted.uniquevals.dist[1:n.dist.values])

    #create list to store words at each level of KW score
    topwords.dist <- NULL

    #loop through each level of distance scores.  Words at each level are stored as list within the
    # list of lists topwords.dist, where each of the sublists are the list of all the words
    # at each of the distance values by index, where
    # index == 1 corresponds to the words with largest distance stat, index == 2
    # corresponds to the words with next largest distance stat,  and so
    # on... Can retrieve these using
    # topwords.dist[[1]], topwords.dist[[2]], etc.
    for(i in 1:n.dist.values)
      {
        topwords.dist <- c(topwords.dist,list(colnames(gen.data)[compare.dist == sorted.uniquevals.dist[i]]))
      }

    ##write words at each level to a file
    #create and write heading
    heading <- paste("LIST OF WORDS FOR EACH ", d.metric.name, "  DISTANCE SCORE.\n")
    write(heading, "./words-list.txt")
    #loop for however many top distance scores the user specifies
    #printing all the words that occur at that level
    for(i in 1:n.dist.values)
      {
        section.heading <- paste("\n - Words at ", getOrder(i), " ", d.metric.name, " score (",
                                 round(sorted.uniquevals.dist[i],2), ") - \n", sep="")
        write(section.heading, "./words-list.txt", append=TRUE)
        cat(topwords.dist[[i]], file="./words-list.txt",append=TRUE,
            fill=TRUE, sep=", ")
      }


    #####create plots for each word at each level of distance score
    #define x as the list of which chunks are in which clade
    x <- rep(1:n.clades, n.clade)
    #define n as the number of chunks being plotted
    n <- length(x)

    #create graphs directory at tempDirectory/graphs/
    if(!file.exists("./graphs")){
      dir.create("./graphs")
    }
    #set working directory to graphs directory
    setwd("./graphs")

    #The pdf doesn't like R's encoding of the names & vice versa, so create a copy
    #of topwords to convert to UTF-8, but with the same structure so
    #it can be referenced by the nested pdf loops.  Also need to loop
    #through at every level and convert to UTF-8, word by word.
    #Because R says "fuck you."
    topwords.UTF <- topwords.dist
    #Encoding(topwords.UTF[[1:n.dist.values]]) <- "UTF-8"
    for(i in 1:n.dist.values){
        for(j in 1:length(topwords.UTF[[i]])){
            Encoding(topwords.UTF[[i]][j]) <- "UTF-8"
        }
    }

    #### Print graphs
    #outer loop loops for each level i where i corresponds to the top levels
    #of distance scores for however many levels user wants to look at
    for(i in 1:n.dist.values)
      {
        #inner loop loops through every word [j] at each level of scores [i]
        for(j in 1:length(topwords.dist[[i]]))
          {
            #open pdf device and set file name for file
            pdf(file=paste(d.metric.name,"_", round(topscores.dist[i],2), "_",
                  topwords.dist[[i]][j], ".pdf",sep=""))
            #create plot for word
            plot(x + (.1)*runif(n, -1, 1), y <-
                 reorder.gen.data[,topwords.dist[[i]][j]],
                 xlim=c(min(x)*.8, max(x)*1.2), ylim=c(0, 1.1*max(y)),
                 #set plot character and x,y labels
                 pch="*", xlab="Cluster", ylab="Relative Frequency",
                 #give the graph title some details about the word so the graph is useful
                 main=paste(dataset.name, " | ",  getOrder(i), " Distance \n", d.metric.name, "  Score: ",
                   round(topscores.dist[i], 2), " | Word[", j, "/",
                   length(topwords.dist[[i]]), "]: ", topwords.UTF[[i]][j], sep = ""),
                 axes=FALSE)
            #specify parameters for the axes so they're not full of useless info
            axis(side=1, at=(ux <- unique(x)), labels=as.character(ux))
            axis(side=2, at=(uy <- seq(min(y),max(y), length=5)), labels=signif(uy, digits=2))
            box(which = "plot")

            #close pdf device(s) so R doesn't shit itself from too many open devices
            graphics.off()
          }

      }
    #set working directory back to tmp.dir
    setwd(tmp.dir)
    #zip contents of working directory including clade-listing.txt, words-list.txt, and the graphs dir
    zip(dataset.name, "./")

    #get rid of uncompressed data including output txt files & graphs directory
    file.remove("clade-listing.txt")
    file.remove("words-list.txt")
    file.remove(dendro.name)
    unlink("./graphs/", recursive=TRUE)

    #return output file name & path
    return(paste(tmp.dir, "/", dataset.name, ".zip", sep=""))
  }

##this function takes an integer and returns a string w/ the ordinal
##for more useful printing.  Only works on 1-20, if they want more than
##that they can deal with the 21TH, etc for the time being.
getOrder <- function(n){
  order <- NULL
  #switch on n for 1-3, which are unique
  switch(n,
         order <- "LARGEST",
         order <- "2ND LARGEST",
         order <- "3RD LARGEST"
         )
  #if n is > 3, order is set to null. From these just appending TH to n
  #takes care of most cases.
  if(is.null(order)){
    order <- paste(n, "TH LARGEST", sep="")
  }
  return(order)
}

## This function takes gen.data and fdoc.collection to find the
## Kruskal-Wallis scores for gen.data.  The function returns compare.dist
getKW <- function(number.words, fdoc.collection, gen.data, compare.dist){
    #fill compare.dist with the results of KW analysis
    for(j in 1:number.words)
      {
        compare.dist[j] <-
          as.numeric(kruskal.test(gen.data[,j]~fdoc.collection)$statistic)
      }

    return(compare.dist)
}


##Function to calculate absolute distance values
getAbsoluteDistance <- function(gen.data, fdoc.collection, n.clades){

  #find number of words based on number of cols in gen.data
  n.words <- dim(gen.data)[2]
  #create vector for means of each clade
  med.clades <- NULL
  #create matrix w/ relative frequencies & a col showing which
  #clade which chunk is in
  syd.data <- data.frame(gen.data, fdoc.collection)
  #set the names of syd.data
  names(syd.data)[length(colnames(syd.data))] <- "clade"

  #create an array of length number.words to store distances for each word
  syd.dist <- array(dim=n.words)



  #loop for each word in gen.data
  for(i in 1:n.words){
    med.clades <- NULL
    #loop for each clade in each word
    for(j in levels(fdoc.collection)){
      #get the median when the i = clade
      med.clades <- c(med.clades, median(syd.data[syd.data$clade == j, i]))
    }

    #get the medians of all of the clade medians
    grand.median <- median(med.clades)
    #calculate clade effects
    clade.effects <- med.clades - grand.median
    gen.data.resids <- gen.data[,i] - med.clades[syd.data$clade]
    #find sum of treatment groups
    sum.trt <-sum(abs(clade.effects[syd.data$clade]))
    #get sum of error between treatment groups
    sum.error <- sum(abs(gen.data.resids))
    #check to see if error is close to 0 (since it's a denom)
    if(sum.error < .0000000001){
      syd.dist[i] <- -10.1
      warning(paste("Not enough variation in data set within clades for word", names(gen.data)[i],
                 "to calculate ABSOLUTE DISTANCE"))
    }
    else{
      #store the distance score for the word
      syd.dist[i] <- (sum.trt/sum.error)*((length(fdoc.collection)-n.clades)/(n.clades-1))
    }
  }

  syd.dist[syd.dist==-10.1] <- max(syd.dist)+1

  #return matrix of all distance scores to calling function
  return(syd.dist)
}


lineColor <- function(x, colorOfNodes)
{
	attr(x, "nodePar") <- list("pch"  = NA, "lab.col"  = colorOfNodes[[currentNode]])
	attr(x, "edgePar") <- list("col"  = colorOfNodes[[currentNode]])
	assign("currentNode",  currentNode + 1, envir = .GlobalEnv)

	x #this line is necessary for some reason for the dendrapply function that calls this to work
}

#get the color for a given leaf based on it's label
getColor <- function(label, specialLabels, metaTable = NULL, colors)
{

    return(colors[label])
}

#generates an list containing the color for every node in the tree
#rules are a node is red if it only contains Archeia, green if it only contains Bacteria, Gold if it was specially selected for highlighting, and blue if it contains mulitple of the previous categories'
#The list is ordered in the order that nodes are visited by dendrapply.
generateLineColorList <- function(x, mergeTableRow, specialLabels,
                                  metaTable = NULL, colors)
{
	colorlist <- list()

	#color the left half of the clade
	if(x$merge[mergeTableRow,1] < 0) #if the left node is a chunk determine the chunk's color
	{
		leftColor <-
	getColor(x$labels[-x$merge[mergeTableRow,1]],
	specialLabels=specialLabels, metaTable = metaTable, colors) #the color of the chunk
		leftList <- list(leftColor) #list of the colors of all the nodes to the left
	}

	else #if the left node is a clade recursively run the function on that clade
	{
		result <- generateLineColorList(x,
	x$merge[mergeTableRow,1], specialLabels=specialLabels,
	metaTable = metaTable, colors)
		leftColor <- result$color #the overall color of the subclade
		leftList <- result$colorList #list of the colors of all the nodes to the left
	}

	#color the right half of the clade
	if(x$merge[mergeTableRow,2] < 0) #if the right node is a chunk determine the chunk's color
	{
		rightColor <-
	getColor(x$labels[-x$merge[mergeTableRow,2]],
	specialLabels=specialLabels, metaTable = metaTable, colors) #the color of the chunk
		rightList <- list(rightColor) #list of the colors of all the nodes to the right
	}

	else #if the right node is a clade recursively run the function on that clade
	{
		result <- generateLineColorList(x,
	x$merge[mergeTableRow,2], specialLabels=specialLabels,
	metaTable = metaTable, colors)
		rightColor <- result$color #the overall color of the subclade
		rightList <- result$colorList #list of the colors of all the nodes to the right
	}

	if(leftColor == rightColor) #check if the colors of the two subclades of the current clade are the same
	{
		color <- leftColor #if so use the color they share
	}

	else #if the colors are different the subclades have different contents
	{
		color <- "black" #set the clade to blue to mark it's mixed contents
	}

	#the colors found need to be put together in the proper order. The current clade has one node for each of it's childern which contains a clade instead of just a chunk.
	#Those nodes need to be given the color of the current clade, but only if they exist. These nodes will appear in the list of colors before all the colors for the nodes in the respective
	#subclades

	if(x$merge[mergeTableRow,1] > 0  && x$merge[mergeTableRow,2] > 0) #if both childern are subclades
	{
		colorList <- c(color, leftList, color, rightList) #both nodes in the current clade exist so add them into the color list
	}

	else if(x$merge[mergeTableRow,1] > 0) #if the right child is a chunk
	{
		colorList <- c(color, leftList, rightList) #there is only a node for the left clade so add that to the color list
	}

	else if(x$merge[mergeTableRow,2] > 0) #if the left child is a chunk
	{
		colorList <- c(leftList, color, rightList) #there is only a node for the right clade so add that to the color list
	}

	else #both children are individual chunks
	{
	colorList <- append(leftColor, rightColor)
	}

	result <- list(colorList=colorList, color=color)
	return(result)
}

#plots a pvclust object
plot.trueTree <- function(x, outputFilename = NULL, print.pv=TRUE, print.num=TRUE, float=0.01,
                         col.pv=c(2,3,8), cex.pv=0.8, font.pv=NULL,
                         col=NULL, cex=NULL, font=NULL, lty=NULL, lwd=NULL,
                         main=NULL, sub=NULL, xlab=NULL, height=800,
                          width=800, specialLabels=NULL, showBP=FALSE,
                          dataset.name, fdoc.collection, ...)
{
    gets.color <- length(levels(fdoc.collection)) <= 6
    if(gets.color){
        colors.vector <- c("red4", "dark green", "navy blue", "mediumpurple", "orange red", "darkgoldenrod4")
        colors <- ifelse(fdoc.collection=="clade1","red4",fdoc.collection)
        colors <- ifelse(fdoc.collection=="clade2","dark green", colors)
        colors <- ifelse(fdoc.collection=="clade3","navy blue",  colors)
        colors <- ifelse(fdoc.collection=="clade4","mediumpurple",colors)
        colors <- ifelse(fdoc.collection=="clade5","orange red",  colors)
        colors <- ifelse(fdoc.collection=="clade6","darkgoldenrod4",  colors)
    }
    else{
        colors <- rep("black", length=length(fdoc.collection))
    }
    names(colors) <- names(fdoc.collection)


    pdf(file=outputFilename, paper="a4r", width=11, height=8.5)

    metaTable <- x$metaTable[[1]] #get metadata out of pvclust object

    main <- paste(dataset.name,  paste("Cluster method: ", x$method, sep=""), paste("Distance: ", x$dist.method), sep = "\n")


  if(is.null(sub))
    #sub=paste("Cluster method: ", x$hclust$method, sep="")

  if(is.null(xlab))
    #xlab=paste("Distance: ", x$hclust$dist.method)

  dend <- as.dendrogram(x) #convert the hclust object into a dendrogram object
  colorList <- generateLineColorList(x, dim(x$merge)[1],
  specialLabels=specialLabels, metaTable = metaTable, colors) #figure out what color each node should be
  colorList <- c(0, colorList$colorList) #the first node checked be dendrapply doesn't seem to be part of the dendrogram so add a dummy value at the start of the list

  assign("currentNode",  1, envir = .GlobalEnv) #currentNode is a global variable to keep track of where in the tree we are
  dend <- dendrapply(dend, lineColor, colorList) #add color to all the nodes in the tree

  #find length of longest chunk name
  maxL <- max( nchar( x$labels ))

  # set margins so there is just enough room for the labels
  # The numbers measure margin size in line units
  # The paramets are the size of the bottom,left,top,right margins
  # On average a margin one line wide seems to have room for about 2.5 characters)
  # so the margin on the bottom is set to the number of lines necessary to display
  # the longest label if there was only 2 characters per line which leave's a decent buffer
  par( mar=c((maxL / 2.0), 2.1, 4.1, 2.1), xpd=TRUE)

  plot(dend, main=main, sub=sub, xlab="", col=col, cex=cex,
       font=font, lty=lty, lwd=lwd, ...)

   if(gets.color){
        legend("topright", inset=c(0, -.1),
               legend = unique(levels(fdoc.collection)),
               fill = colors.vector
               )
    }
  if(!is.null(outputFilename)) #if writing to a file close the connection
  {
	dev.off()
  }
}
