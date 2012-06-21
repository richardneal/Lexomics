#this function takes a data file, and from this performs a cluster and
#a Kruskal-Wallis analysis to determine which words are in outlying clades
#have the most influence in separation on the dendro.  Number of cuts to perform
#and how many levels of KW statistic to look at will be passed in

####Input parameters:
##  input.file : is the data file with word counts from diviText
##             : will be client-side
##  n.clades : number of cuts to make with cutree()
##  n.kw.values : how many levels of KW stats to process. default is 1
##  metric : for distance. default is Euclidean
##  p : for hclust.  default is 2
##  linkage : method for hclust.  default is "ave"
##  is.tab.sep : boolean for if file should use tab sep or default
##  output.dir : directory for output file.  output will be client-side of the web-app
##  d.metric : is the metric to use for analysis.  All metrics have a numeric code,
##           : default is 1 for Kruskal-Wallis

####Return Value:
## no return value for now

#runKW("FOTRnoPoetry.tsv", 3, 3)
#runKW("./DAZ/DAZ_totalCounts.txt", 3, 2, is.tab.sep = FALSE, output.dir = "./DAZ/")
## input.file <- "FOTRnoPoetry.tsv"
## n.clades <- 3
## n.kw.values <- 2
## metric <- "euclidean"
## p <- 2
## linkage <- "ave"
## chunks.as.rows <- TRUE
## is.tab.sep <- TRUE

runKW <- function(input.file, n.clades, n.kw.values = 1, metric = "euclidean",
                  p = 2, linkage = "ave", is.tab.sep=TRUE, output.dir = "./", d.metric = 1)
  {

	library(stats)    

	# switch n.clades, n.kw.values, and p to integer values
	n.clades=as.integer(n.clades)
	n.kw.values=as.integer(n.kw.values)
	p=as.real(p)

    # read in the data, changing comment character to nothing so # isn't confused
    if(is.tab.sep == TRUE){
      test.data <- read.table(input.file, header=T,comment.char="",sep="\t",row.names=1)
    }
    else{
      test.data <- read.table(input.file, header=T,comment.char="",row.names=1)
    }

    #move test.data to gen.data. Transpose if neccessary
    if(dim(test.data)[1] < dim(test.data)[2]){
        gen.data <- test.data
      }
    else{
        gen.data <- t(test.data)
      }

    # get the row sums for the gen.data
    rsums <- apply(gen.data,1,sum)

    # create a matrix that gives the row sums as denominators at each element of the data matrix
    # to allow conversion to relative frequencies
    denoms <- matrix(rep(rsums,dim(gen.data)[2]),
                     byrow=F,ncol=dim(gen.data)[2])
    gen.data <- gen.data/denoms

    # compute distances between all vectors, using default "euclidean"
    dist.gen <- dist(gen.data, method=metric, p=p)
    #generate cluster object using "ave" linkage
    hc <- hclust(dist.gen, method=linkage)
    
    ###################################################################################################
    #Kruskal Wallace Test
    ###################################################################################################

    #set directory to output.dir
    setwd(output.dir)
    # stop(n.clades," ",dim(gen.data)[1]);
    #cut dendro into desired number of groups if n.clades is within valid bounds
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

    #get the name of the input file before extension (e.g. get "XXX" from "XXX.YYY")
    f.unlist <- unlist(strsplit(input.file, "\\."))
    f.name <- f.unlist[(length(f.unlist) - 1)]
    f.unlist <- unlist(strsplit(f.name, "\\/"))
    f.name <- f.unlist[length(f.unlist)]
    
    #print list of elements to elements.txt
    heading <- paste("Chunks in each clade after cutting into ", n.clades, " segments:",sep="")
    write(heading, "/tmp/clade-listing.txt")
    for(i in unique(cut))
      {
        write(paste("Elements of Clade ", i, ":"), "/tmp/clade-listing.txt", append=TRUE, sep="")
        write(names(cut[cut==i]), "/tmp/clade-listing.txt",ncolumns=3,append=TRUE, sep = ", ")
      }
    
    
    #creates strings of the clade names based on the the clade number for KW test
    fdoc.collection <- factor(cut, levels=1:n.clades)
    levels(fdoc.collection) <- paste("clade",1:n.clades,sep="")

    #create an array to store KW stat for all of the words
    compare.KW <- array(number.words <- dim(gen.data)[2])

    #fill compare.KW with the results of KW analysis
    word.KW.results <-
    for(j in 1:number.words)
      {
        compare.KW[j] <- as.numeric(kruskal.test(gen.data[,j]~fdoc.collection)$statistic)
      }

    #let's order the results (KW values) them from the biggest to the smallest
    ordered.diff <- order(compare.KW, decreasing = TRUE)

    #sort & store the values of compare.KW from largest to smallest
    sorted.uniquevals.KW <- rev(sort(unique(compare.KW)))

    #stores each of the top n.kw.values stats from KW test
    topscores.KW <- unique(sorted.uniquevals.KW[1:n.kw.values])

    ##n.kw.values is how many levels of the top n KW scores you want to
    ##get.  By setting to the length of sorted.uniquevals.KW you get all
    ##of them, or set it to a smaller int for fewer levels.
    
    
    #create list to store words at each level of KW score
    topwords.KW <- NULL

    #loop through each level of KW scores.  Words at each level are stored as list within the
    # list of lists topwords.KW, where each of the sublists are the list of "top KW words" by index, where
    # index == 1 corresponds to the words with largest KW stat, index == 2
    # corresponds to the words with next largest KW stat,  and so
    # on... Can retrieve these using
    # topwords.KW[[1]], topwords.KW[[2]], etc.
    for(i in 1:n.kw.values)
      {
        topwords.KW <- c(topwords.KW,list(colnames(gen.data)[compare.KW == sorted.uniquevals.KW[i]]))
      }

    #write words at each level to a file
    heading <- paste("List of words at each KW score:")
    write(heading, "./kwWords.txt")
    for(i in 1:n.kw.values)
      {
        section.heading <- paste("Words at KW Score of ", format(topscores.KW[i],digits=4),":",sep="")
        write(section.heading, "./kwWords.txt", append=TRUE)
        cat(topwords.KW[[i]], file="./kwWords.txt",append=TRUE, fill=TRUE)
      }
    
    
    #####create plots for each word at each level of KW
    #define x as the list of which chunks are in which clade
    x <- rep(1:n.clades, n.clade)
    #define n as the number of chunks being plotted
    n <- length(x)

    #store current directory
    current.dir <- getwd()
    #create graphs directory
    if(!file.exists("./graphs")){
      dir.create("./graphs")
    }
    #set working directory to graphs directory
    setwd("./graphs")

    #loop through each list of words at each level of KW score and
    #save the graph for each word as a .pdf file
    
    for(i in 1:n.kw.values)
      {
        for(j in 1:length(topwords.KW[[i]]))
          {
            #open pdf device and set file name for file
            pdf(file=paste(f.name, "_", "KW", round(topscores.KW[i],2), "_",
                  topwords.KW[[i]][j], ".pdf", sep=""))
            #set graphs parameters
            #par(lab=c(x=n.clades, len=1))
            #create plot
            plot(x + (.1)*runif(n, -1, 1), y <-
                 reorder.gen.data[,topwords.KW[[i]][j]],
                 xlim=c(min(x)*.8, max(x)*1.2), ylim=c(0, 1.1*max(y)),
                 pch="*", xlab="Cluster", ylab="Relative Frequency",
                 main=paste(f.name, "\nKW Score: ", round(topscores.KW[i], 2),
                   " | Word[", j, "/", length(topwords.KW[[i]]), "]: ",
                   topwords.KW[[i]][j], sep = ""),
                 axes=FALSE)
            axis(side=1, at=(ux <- unique(x)), labels=as.character(ux)) 
            axis(side=2, at=(uy <- seq(min(y),max(y), length=5)), labels=signif(uy, digits=2))
            box(which = "plot")

            #close pdf device(s)
            graphics.off()
          }
        
      }
    #go back to previous directory
    setwd(current.dir)

  }

## getOrder <- function(n){
##   order <- NULL
##   switch(n,
##          order <- "Largest",
##          order <- "2nd Largest",
##          order <- "3rd Largest"
##          )
## if(is.null(order)){
##   order <- paste(n, "th Largest", sep="")
## }
##   print(order)
## }


