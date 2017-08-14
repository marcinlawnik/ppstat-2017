#!/bin/bash

shopt -s nullglob
FILES=raw/pdf/*.pdf

for f in $FILES
do
    echo "Przetwarzam plik $f"
    NEWFILENAME=$(basename $f .pdf).csv
    echo "$NEWFILENAME"
    pdftotext -layout $f - | grep Przyjęty$ | sed 's/^[^0-9]//' | awk 'NF == 7 {print $2","$3","$4","$5","$6} NF == 6 {print $2","$3",,"$4","$5}' > converted/csv/$NEWFILENAME

done
