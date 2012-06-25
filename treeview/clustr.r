#!/usr/bin/Rscript

source( 'clustr1.r' );
#source('mycluster.r');
source('hclusttophylo.r');

# hacky way for now
args <- commandArgs(trailingOnly=TRUE);
ifile <-  args[1];
method <- args[2];
metric <- args[3];
output <- args[4];
title <- args[5];
p <- args[6];
type <- args[7];
labelFile <- args[8];
scrubtags <- args[9];
divitags <- args[10];

filename <- paste("/tmp/rcluster",runif(1), sep="" );

if(output == "phyloxml")
{
	filename <- paste(filename, ".xml", sep="");
}

rownames<-myCluster( ifile, method=method, metric=metric, output.type=output,
        outputfile=filename, main=title, p=p, type=type, labelFile=labelFile,
	scrubtags=scrubtags, divitags=divitags);

cat(filename,rownames,sep=",");

