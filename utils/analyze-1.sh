#!/bin/bash

BIN=../triesearch
DAT=../indices

MYSQLCRED=`php get-credentials.php`

export MYSQL_HOST=${MYSQLCRED%%$'\t'*}
MYSQLCRED=${MYSQLCRED#*$'\t'}
export MYSQL_USER=${MYSQLCRED%%$'\t'*}
MYSQLCRED=${MYSQLCRED#*$'\t'}
export MYSQL_PASSWORD=${MYSQLCRED%%$'\t'*}
MYSQLCRED=${MYSQLCRED#*$'\t'}
export MYSQL_DATABASE=${MYSQLCRED}

echo show tables | mysql -h $MYSQL_HOST -u $MYSQL_USER -D $MYSQL_DATABASE

exit 0

export MYSQL_PWD=$

echo $MYSQLCRED

exit 0

#W	NA	KdK	navis-NL-HaNA_2.02.04_3960	0188	007	2196	RECOG	13583ef1

${BIN}/trie-search7 --trie=${DAT}/index-bypage.trie --key= \
| awk -F'\t' '{
      id = $1 "|" $2 "|" $3 "|" $4 "|" $5;
      if (id != lid) {
          if (lid != "") {
              print lid "|" tpag "|" thum;
          }
          lid  = id;
          tpag = 0;
          thum = 0;
      }
      tpag++;
      if ($8 == "HUMAN") thum++;
  }'
