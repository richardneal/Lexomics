#!/usr/bin/Rscript

source( 'mycluster.r' );
source( 'hclusttophylo.r' );

# hacky way for now
args <- commandArgs(trailingOnly=TRUE);
ifile <-  args[2];
method <- args[4];
metric <- args[6];
output <- args[8];
title <- args[10];

filename <- paste("/tmp/rcluster",runif(1), sep="" );

if(output == "phyloxml")
{
	filename <- paste(filename, ".xml", sep="");
}
myCluster( ifile, method=method, metric=metric, output.type=output,
        output.file=filename, main=title );

cat(filename);
