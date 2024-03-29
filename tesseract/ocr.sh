#!/bin/bash
 
# Created by Firas MR.
# Website: https://mydominanthemisphere.wordpress.com
# Adapted by Bart Jongejan

# ./ocr.sh <PDFfile> <language> <tessdatadir> <output>
#    0         1         2            3          4

# define variables
SCRIPT_NAME=`basename "$0" .sh`
 
# make a temporary directory
 
TMP_DIR=$(mktemp -d -t tess-XXXXXXXXXX)
 
# ...
 
# copy PDF to temporary directory
 
cp $1 $TMP_DIR
 
# change current working directory to temporary directory
 
pushd $TMP_DIR
 
# use Imagemagick tool to read PDF pages at a pixel denisty of
# 150 ppi in greyscale mode and output TIFF files at a pixel
# depth of 8. Tesseract will misbehave with pixel depth > 8
# or with color images.
 
# convert * -density 40 -depth 8 -colorspace gray -verbose -background white -alpha Off p%02d.tif
# convert * -alpha Off p%02d.tif
#convert -verbose -density 150 -trim * -quality 100 -sharpen 0x1.0 -alpha Off -depth 8 -colorspace gray p%02d.tif
#convert -verbose -density 72 -trim * -quality 100 -sharpen 0x1.0 -alpha Off -depth 8 -colorspace gray p%02d.tif
#convert -verbose -density 200 -trim * -quality 100 -sharpen 0x1.0 -alpha Off -depth 8 -colorspace gray p%02d.tif
#convert -verbose -trim * -quality 100 -sharpen 0x1.0 -alpha Off -depth 8 -colorspace gray p%02d.tif
#convert -verbose -trim -quality 100 -sharpen 0x1.0 -alpha Off -density 300 * p%02d.tif
#SECURITY RISKS WITH IMAGEMAGICK+GHOSTSCRIPT FORMATS GS AND PDF! convert -verbose -trim -quality 100 -alpha Off -density 300 * p%02d.tif #***** -density 300 is sometimes too high, something chokes.
#convert -verbose -trim -quality 100 -alpha Off -density 200 * p%02d.tif

pdftoppm $1 p
 
# For every TIFF file listed in numerical order in the temporary
# directory (contd)
 
##for i in `ls *.tif | sort -tp -k2n`;
for i in `ls *.ppm | sort -tp -k3n`;
 
do
 
# strip away full path to file and file extension
 
## BASE=`basename "$i" .tif`;
 BASE=`basename "$i" .ppm`;
 
# run Tesseract using the English language on each TIFF file
 
## tesseract --tessdata-dir $3 "${BASE}.tif" "${BASE}" -l $2
 tesseract --tessdata-dir $3 "${BASE}.ppm" "${BASE}" -l $2
 
# append output of each resulting TXT file into an output file with
# pagebreak marks at then end of each page
 
 cat ${BASE}.txt | tee -a $4;
# echo "[pagebreak]" | tee -a $4;
 
# remove all TIFF and TXT files
 
rm ${BASE}.*;
 
done
 
# remove any remaining files (eg. PDF, etc.)
 
rm *
 
# change to parent directory
 
popd
 
# remove temporary directory
 
rm -rf $TMP_DIR
