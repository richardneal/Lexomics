#!/usr/bin/Rscript

source( 'clustr1.r' );
#source('mycluster.r');
source('hclusttophylo.r');

# hacky way for now
args <- commandArgs(trailingOnly=TRUE);
ifile <-  args[2];
method <- args[4];
metric <- args[6];
output <- args[8];
title <- args[10];
p <- args[12];
type <- args[14];
addLabels <- args[16];
labelFile <- args[18];

filename <- paste("/tmp/rcluster",runif(1), sep="" );

if(output == "phyloxml")
{
	filename <- paste(filename, ".xml", sep="");
}

myCluster( ifile, method=method, metric=metric, output.type=output,
        outputfile=filename, main=title, p=p, type=type, addLabels=addLabels, labelFile=labelFile);

cat(filename);
