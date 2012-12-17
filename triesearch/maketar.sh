#!/bin/bash

BASENAME="triesearch-`date +%Y%m%d`"
TMP=`mktemp -d /tmp/maketar-XXXXXX`
TDIR="$TMP/$BASENAME"
DEST="${PWD}"/../"${BASENAME}.tgz"

mkdir "${TDIR}"

for i in EXAMPLES README src maketar.sh cre-indices.sh Makefile
do
    cp -a "$i" "${TDIR}"
done

for i in maketar.sh cre-indices.sh
do
    chmod +x "${TDIR}/$i"
done

for i in bin obj
do
    mkdir "${TDIR}/$i"
done

(cd "${TMP}"; tar czf "${DEST}" "${BASENAME}")

echo Created ${DEST}

rm -Rf $TDIR
