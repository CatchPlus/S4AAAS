#!/bin/bash

gen-list ()
{
cat << EOF
cliwoc-Adm_177_1177b
cliwoc-Adm_177_1177
cliwoc-Adm_177_1189
GeldArch-rekeningen-1425
GV-Appingedam_8149
navis2
navis-H2_7823_0001-1094
navis
navis-medieval-text-Leuven
navis-NL-HaNA_2.02.04_3960
navis-NL-HaNA_2.02.04_3965
navis-NL-HaNA_2.02.14_7813
navis-NL-HaNA_2.02.14_7815
navis-NL-HaNA_2.02.14_7816
navis-NL-HaNA_2.02.14_7819
navis-NL-HaNA_2.02.14_7820
navis-NL-HaNA_2.02.14_7822
navis-NL-HaNA_2.02.14_7825
navis-NL-HaNA_2.02.14_7826
navis-NL-HaNA_2.02.14_7827
navis-NL-HaNA_2.02.14_7828
PV-Astro-1932
SAL7316
SAL7453
SAL7751
EOF
}

gen-list \
| while read DIR
  do
      for file in $DIR/*.jpg
      do
          mkdir -p small/$DIR

          DST=`basename $file \
               | sed 's/\(^.*_\)\([^_].*\)\([.]jpg$\)/\2/' \
               | awk '{ printf ("%04d", $1 + 0)}' `
          WIDTH=`identify $file | awk '{split ($3,a,"x"); print a[1]}'`
          convert -resize 800 $file small/$DIR/$DST.jpg

          echo "$DIR;$DST.jpg;$WIDTH" >> $0.log
      done
  done
