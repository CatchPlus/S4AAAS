#!/bin/bash

ROOT=/gpfs2/monk/www

escape ()
{
    awk '
    BEGIN {
        m["\n"] = " ";
        m["\r"] = " ";
        m["\t"] = " ";

    }
    {
        total = total " ";
        for (i = 1; i <= length ($0); i++) {
            s = substr ($0, i, 1);
            if (s in m) s = m[s];
            total = total s;
        }
    } END {
        print substr (total, 2);
    }' $1
}

for BOOKDIR in $ROOT/*/Txt
do
    BOOK=`dirname $BOOKDIR`
    BOOK=`basename $BOOK`

    for FILE in $BOOKDIR/*.txt
    do
        LNUM=`basename $FILE | sed 's/\(.*_\)\([0-9]*\)\([.]txt$\)/\2/'`
        [ "$LNUM" == "*.txt" ] && continue
        echo $BOOK $PAGE $LNUM `escape $FILE`
    done
done
