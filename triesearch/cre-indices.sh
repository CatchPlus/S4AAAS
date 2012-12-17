#!/bin/bash

BIN=bin
DST=indices-`date +%Y%m%d`
TMP=${DST}/tmp
TMP2=`mktemp /tmp/cre-indices-XXXXXX`
MEM=64

mkdir -p ${DST}
mkdir -p ${TMP}

LANG=C

#
# Generate bookdir.lis first
#
cat > $TMP2 << EOF
<?php

// <book directory>:<book id>:<book institution>:<book collection>
require '/target/gpfs2/monk/srv/htdocs/rest/include/navis-id2path.php';
\$institutions = institutions();
foreach (collections() as \$k => \$v)
  print \$k . ':' . str_replace('_%04d-line-%03d', '', bookdir2linepattern(\$k)) . ':' . \$institutions[\$k] . ':' . \$v . "\n";
?>
EOF

php $TMP2 > bookdir.lis

rm $TMP2

#
# First create the big master index file containing evrything
# This file is text based, and not suitable for fast search
# but it's intended to be very complete!
#
# It extracts the data from the Monk directories.
#
# The file layout is like:
# W recht       Misc Leuven navis-medieval-text-Leuven 0002 051  0354 RECOGe navis-medieval-text-Leuven_0002-line-051-y1=2360-y2=2478-zone-RECOGe-x=0354-y=0-w=127-h=119-ybas=77-nink=1899-besthyp 0.1433766471
# L recht       NA   KdK    navis-H24001_7824          1358 024              navis-H24001_7824_1358-line-024-y1=3543-y2=3711
# P Rechtbanken NA   KdK    navis-NL-HaNA_2.02.04_3960 0927 PAGE             navis-NL-HaNA_2.02.04_3960_0927-line-PAGE
#
# All fields are separated by tab's.
#
echo "`date`: Creating index-full.txt"
###include x after RECOG|HUMAN.. etc for compatibility reasons until Anco has fixed this
### ${BIN}/gen-raw-index | ${BIN}/dedup "--tmpdir=${TMP}" --mem=${MEM} --par=4 | ${BIN}/sel-fields --nopos --fields=1,12,3,4,5,6,7,11,13,14 > ${DST}/index-full.txt
${BIN}/gen-raw-index | ${BIN}/dedup "--tmpdir=${TMP}" --mem=${MEM} --par=4 \
| ${BIN}/sel-fields --nopos --fields=1,12,3,4,5,6,7,11,8,13,14 \
| ${BIN}/sort "--tmpdir=${TMP}" --mem=${MEM} --par=4 > ${DST}/index-full.txt

#
# Build search trie index-bylabel3 for index-full.txt
# This index (only) contains th fields 2(lowercase), 3, 4, 5, 6, 7, 1 and 8. e.g:
# recht       NA KdK navis-H24001_7824          1358 024       L        1c6a67a1d
# recht       NA KdK navis-NL-HaNA_2.02.04_3960 0065 004  0354 W RECOGe afb871dd
# rechtbanken NA KdK navis-NL-HaNA_2.02.04_3960 0927 PAGE      P        1c683141d
#
# The index is used 
#
# The last field is the position of the line in index-full.txt in HEX.
#
echo "`date`: Creating index-bylabel*.trie"
#echo "`date`: Creating index-bylabel3.trie"
###include x after RECOG|HUMAN.. etc for compatibility reasons until Anco has fixed this
###${BIN}/sel-fields --fields=2l,3,4,5,6,7,1,8 < ${DST}/index-full.txt | ${BIN}/trie-build2 --trie=${DST}/index-bylabel3.trie "--tmpdir=${TMP}" --mem=${MEM} --par=4 &
${BIN}/sel-fields --fields=2l,3,4,5,6,7,9,1,8 < ${DST}/index-full.txt | ${BIN}/trie-build2 --trie=${DST}/index-bylabel3.trie "--tmpdir=${TMP}" --mem=${MEM} --par=4 &

#
# Build search trie index-bylabel4 for index-full.txt
# This index (only) contains th fields 1, 8, 2(lowercase), 3, 4, 5, 6, 7 and 9. e.g:
# L        recht       NA   KdK    navis-H24001_7824          1358 024       1c6a67a1d
# P        rechtbanken NA   KdK    navis-NL-HaNA_2.02.04_3960 0927 PAGE      1c683141d
# W RECOGe recht       Misc Leuven navis-medieval-text-Leuven 0002 051  0543 d7003d8
#
# The index is used for searching for words when L/W/P and HUMAN/JAVA/RECOGe/RECOG etc are specified
#

#echo "`date`: Creating index-bylabel4.trie"
###include x after RECOG|HUMAN.. etc for compatibility reasons until Anco has fixed this
###${BIN}/sel-fields --fields=1,8,2l,3,4,5,6,7 < ${DST}/index-full.txt | ${BIN}/trie-build2 --trie=${DST}/index-bylabel4.trie "--tmpdir=${TMP}" --mem=${MEM} --par=4 &
${BIN}/sel-fields --fields=1,8,2l,3,4,5,6,7,9 < ${DST}/index-full.txt | ${BIN}/trie-build2 --trie=${DST}/index-bylabel4.trie "--tmpdir=${TMP}" --mem=${MEM} --par=4 &

#echo "`date`: Creating index-bypage.trie"
${BIN}/sel-fields --fields=1,3,4,5,6,7,9,8 < ${DST}/index-full.txt | grep "^W" | ${BIN}/trie-build2 --trie=${DST}/index-bypage.trie --tmpdir=${TMP} --mem=${MEM} --par=4 &
# Wait until processes are finished...
wait


#
# Build a trie containing just a list of words, used for suggestions in the UI
#
# It extracts these words efficiently from index-bylabel3.trie because the first field
# of this index is the word (label).
#
echo "`date`: Creating index-words.trie"
${BIN}/trie-words --trie=${DST}/index-bylabel3.trie | ${BIN}/trie-build2 --mem=${MEM} --trie=${DST}/index-words.trie

#
# Build a trie containing a list of substings and the words the substrings are in:
#
# aap  aap
# aa   aap
# ap   aap
# noot noot
# noo  noot
# no   noot
# oot  noot
# oo   noot
# ot   noot
#
# Substrings shorter than two characters are not considered.
#
# This index is used to do substring searches.
#
echo "`date`: Creating index-substrings.trie"
${BIN}/trie-search7 --trie=${DST}/index-words.trie --key= | ${BIN}/gen-substrings | ${BIN}/trie-build2 --mem=${MEM} --trie=${DST}/index-substrings.trie

#
# Build a trie containing all the top linestrips of pages
#
echo "`date`: creating index-topline.trie"
${BIN}/trie-search7 --trie=${DST}/index-bylabel4.trie --key=P | bin/sel-fields --fields=6,7 --nopos | sort -u \
| awk '{
      if (ARGIND==1) {
          split ($0, a, ":");
          map[a[2]] = a[1];
      } else {
          print "/gpfs2/monk/www/" map[$1] "/Pages/*_" $2 "/Lines/web-grey/*-line-001-";
      }
      
  }' bookdir.lis - \
| while read pfx
  do
      basename ${pfx}*
  done \
| grep "jpg$" | sed 's/[.]jpg$//' \
| ${BIN}/trie-build2 --trie=${DST}/index-topline.trie

echo "`date`: Done"

rmdir ${TMP}
