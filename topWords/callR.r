#!/usr/bin/Rscript

source('dist.function.R');

args    <- commandArgs(trailingOnly=TRUE);
ifile   <- args[1];     # file name
clades  <- args[2];     # number of clades
levels  <- args[3];     # number of kw levels
metric  <- args[4];     # distance metric (euclidian by default)
p       <- args[5];     # minkowski power (default 2)
linkage <- args[6];     # method for hclust (ave is default)
dataset <- args[7];		# output file name
distance<- args[8];		# d.metric
tsv     <- TRUE;     	# file is tab delimited (boolean)
filename <- NULL;
filename = runDist(ifile, clades, n.dist.values=levels, metric=metric, p=p,
	linkage=linkage, is.tab.sep=tsv, dataset.name=dataset, d.metric=distance);

cat(filename);
