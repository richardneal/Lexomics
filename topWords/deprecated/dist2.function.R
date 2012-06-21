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
##  output.file : filename for output file.  will be the name of the
##                zipped output file. (.zip will be added automatically)
##  d.metric : is the metric to use for analysis.  All metrics will have a numeric code,
##           : default is 1

####Return Value:
## the path & filename of the zipped output file

####test calls
#runDist("FOTRnoPoetry.tsv", 2, 2)
#print(fname <- runDist("/home/raffle/work/merge_transpose_GenAB1500.tsv",
#                       2, 10, output.file = "DAZ_450-dist", d.metric=3))
#print(fname <- runDist("/home/raffle/work/example.tsv", 2, 4, d.metric=3))


runDist <- function(input.file, n.clades, n.dist.values = 1, metric = "euclidean",
                  p = 2, linkage = "ave", is.tab.sep=TRUE, output.file = "dist-output", d.metric = 1)
  {
    #load libraries needed
    library(stats)

	# be sure proper things are numeric
	n.clades=as.integer(n.clades)
	n.dist.values=as.integer(n.dist.values)
	p=as.real(p)
	d.metric=as.integer(d.metric)
	

    #set working directory to tmp/randomwhatever
    tmp.dir <- paste("/tmp/", (1000 * round(runif(1, 0.001, 1), 3)), sep="")
    if(!file.exists(tmp.dir)){
      dir.create(tmp.dir)
    }
    #set working directory to graphs directory
    setwd(tmp.dir)

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
    #Distance Test
    ###################################################################################################

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

    #get the name of the input file before extension & after directory(e.g. get "FFF" from "DDD/FFF.xxx")
    f.unlist <- unlist(strsplit(input.file, "\\."))
    f.name <- f.unlist[(length(f.unlist) - 1)]
    f.unlist <- unlist(strsplit(f.name, "\\/"))
    f.name <- f.unlist[length(f.unlist)]
    
    
    #print list of elements to elements.txt
    heading <- paste("Chunks in each clade after cutting into ", n.clades, " clades:",sep="")
    write(heading, "./clade-listing.txt")
    for(i in unique(cut))
      {
        write(paste("\nElements of Clade ", i, ":", sep=""), "./clade-listing.txt", append=TRUE)
        write(names(cut[cut==i]), "./clade-listing.txt",ncolumns=3,append=TRUE, sep = ", ")
      }
    
    
    #creates strings of the clade names based on the the clade number for KW test
    fdoc.collection <- factor(cut, levels=1:n.clades)
    levels(fdoc.collection) <- paste("clade",1:n.clades,sep="")

    #create an array to store KW stat for all of the words
    compare.dist <- array(number.words <- dim(gen.data)[2])

    #switch on d.metric to decide which distance metric to use to fill
    #compare.dist
    switch(d.metric,
           compare.dist <- getKW(number.words, fdoc.collection,
                                 gen.data, compare.dist),
           compare.dist <- getAnova(number.words, fdoc.collection,
                                    gen.data, compare.dist),
           compare.dist <- myMethod(gen.data,fdoc.collection)
          )
    #switch on d.metric to get d.metric.name
    switch(d.metric,
           d.metric.name <- "KRUSKAL-WALLIS",
           d.metric.name <- "ANOVA-F",
           d.metric.name <- "SYDNEY-SCORE"
          )

    #let's order the results (KW values) them from the biggest to the smallest
    ordered.diff <- order(compare.dist, decreasing = TRUE)

    #sort & store the values of compare.dist from largest to smallest
    sorted.uniquevals.dist <- rev(sort(unique(compare.dist)))

    #stores each of the top n.dist.values stats from KW test
    topscores.dist <- unique(sorted.uniquevals.dist[1:n.dist.values])

    ##n.dist.values is how many levels of the top n KW scores you want to
    ##get.  By setting to the length of sorted.uniquevals.dist you get all
    ##of them, or set it to a smaller int for fewer levels.
    
    
    #create list to store words at each level of KW score
    topwords.dist <- NULL

    #loop through each level of KW scores.  Words at each level are stored as list within the
    # list of lists topwords.dist, where each of the sublists are the list of "top KW words" by index, where
    # index == 1 corresponds to the words with largest KW stat, index == 2
    # corresponds to the words with next largest KW stat,  and so
    # on... Can retrieve these using
    # topwords.dist[[1]], topwords.dist[[2]], etc.
    for(i in 1:n.dist.values)
      {
        topwords.dist <- c(topwords.dist,list(colnames(gen.data)[compare.dist == sorted.uniquevals.dist[i]]))
      }

    #write words at each level to a file
    heading <- paste("LIST OF WORDS FOR EACH ", d.metric.name, "  DISTANCE SCORE.\n")
    write(heading, "./words-list.txt")
    for(i in 1:n.dist.values)
      {
        section.heading <- paste("\n - Words at ", getOrder(i), " ", d.metric.name, " score (",
                                 round(sorted.uniquevals.dist[i],2), ") - \n", sep="") 
        write(section.heading, "./words-list.txt", append=TRUE)
        cat(topwords.dist[[i]], file="./words-list.txt",append=TRUE, fill=TRUE, sep=", ")
      }
    
    
    #####create plots for each word at each level of KW
    #define x as the list of which chunks are in which clade
    x <- rep(1:n.clades, n.clade)
    #define n as the number of chunks being plotted
    n <- length(x)

    #create graphs directory
    if(!file.exists("./graphs")){
      dir.create("./graphs")
    }
    #set working directory to graphs directory
    setwd("./graphs")

    #loop through each list of words at each level of KW score and
    #save the graph for each word as a .pdf file
    
    for(i in 1:n.dist.values)
      {
        for(j in 1:length(topwords.dist[[i]]))
          {
            #open pdf device and set file name for file
            pdf(file=paste(f.name, "_", d.metric.name,"_", round(topscores.dist[i],2), "_",
                  topwords.dist[[i]][j], ".pdf", sep=""))
            #set graphs parameters
            #create plot
            plot(x + (.1)*runif(n, -1, 1), y <-
                 reorder.gen.data[,topwords.dist[[i]][j]],
                 xlim=c(min(x)*.8, max(x)*1.2), ylim=c(0, 1.1*max(y)),
                 pch="*", xlab="Cluster", ylab="Relative Frequency",
                 main=paste(f.name, " | ",  getOrder(i), " Distance \n", d.metric.name, "  Score: ",
                   round(topscores.dist[i], 2), " | Word[", j, "/",
                   length(topwords.dist[[i]]), "]: ", topwords.dist[[i]][j], sep = ""),
                 axes=FALSE)
            axis(side=1, at=(ux <- unique(x)), labels=as.character(ux)) 
            axis(side=2, at=(uy <- seq(min(y),max(y), length=5)), labels=signif(uy, digits=2))
            box(which = "plot")

            #close pdf device(s)
            graphics.off()
          }
        
      }
    #set working directory back to tmp.dif
    setwd(tmp.dir)
    #zip contents of working directory
    zip(output.file, "./")

    #get rid of uncompressed data including output txt files & graphs directory
    file.remove("clade-listing.txt")
    file.remove("words-list.txt")
    unlink("./graphs/", recursive=TRUE)

    #return output file name & path
    return(paste(tmp.dir, "/", output.file, ".zip", sep=""))
  }

getOrder <- function(n){
  order <- NULL
  switch(n,
         order <- "LARGEST",
         order <- "2nd LARGEST",
         order <- "3rd LARGEST"
         )
  if(is.null(order)){
    order <- paste(n, "th LARGEST", sep="")
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

## This function takes gen.data and fdoc.collection to find the
## Anova-F scores for gen.data.  The function returns compare.dist
getAnova <- function(number.words, fdoc.collection, gen.data, compare.dist){
  ##tell the user if anova is a stupid choice.
  for(j in 1:number.words)
    {
      compare.dist[j] <- as.numeric(anova(lm(gen.data[,j]~as.factor(fdoc.collection)))$F[1])
    }
  return(compare.dist)
}

myMethod <- function (gen.data,fdoc.collection){

	clade1.indeces <- (1:dim(gen.data)[1])[fdoc.collection==levels(fdoc.collection)[1]]
	clade2.indeces <- (1:dim(gen.data)[1])[fdoc.collection==levels(fdoc.collection)[2]]

	I <- array (dim(gen.data)[2])



	for ( q in 1:dim(gen.data)[2]){
            temp <-NULL
            for (i in clade1.indeces){
                for (j in clade2.indeces){
                    temp<-c(temp,abs(gen.data[i,q]-gen.data[j,q]))
	 	}
            }
	I[q]<- mean(temp)
        ## y <-  outer(gen.data[clade1.indeces,q],gen.data[clade2.indeces,q],FUN="-")
        ## I[q] <- mean(abs(y))

	}
        return(I)
}
