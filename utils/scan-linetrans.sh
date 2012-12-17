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

for BOOKDIR in $ROOT/*/Pages
do
    BOOK=`dirname $BOOKDIR`
    BOOK=`basename $BOOK`
    for PAGEDIR in $BOOKDIR/*/Lines/Txt
    do
        PAGE=`dirname $PAGEDIR`
        PAGE=`dirname $PAGE`
        PAGE=`basename $PAGE | sed 's/\(.*[_]\)\([^_]*\)/\2/'`

        for FILE in $PAGEDIR/*.txt
        do
            BASE=`basename $FILE`
            [ "$BASE" == "*.txt" ] && continue
            LNUM=`echo $BASE | sed 's/\(.*-line-\)\([0-9]*\)\(-.*$\)/\2/'`
            Y1=`echo $BASE | sed 's/\(.*-y1=\)\([0-9]*\)\([^0-9].*$\)/\2/'`
            Y2=`echo $BASE | sed 's/\(.*-y2=\)\([0-9]*\)\([^0-9].*$\)/\2/'`
            echo $BOOK $PAGE $LNUM $Y1 $Y2 `escape $FILE`
        done
    done
done

