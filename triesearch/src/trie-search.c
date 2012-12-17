/* trie-search
 *
 * revision History:
 * 20110406 Initial version Rolf Fokkens
 *                          rolf.fokkens@target-holding.nl
 *
 * THIS PROGRAM REQUIRES BETTER ERROR HANDLING
 *
 * It is was written as a Proof of Concept in a pragmatic way. This
 * means that error handling has not been taken care of very well!
 */

#include <stdlib.h>
#include <string.h>
#ifndef __USE_BSD
#define __USE_BSD
#endif
#include <stdio.h>
#include "trie-util.h"

void find_tree (FILE *fpi, int features, uint64_t pos, unsigned char *buffer, unsigned char *bp, char *keyp, int keylen)
{
    uint64_t son, refpos, cnt;
    int      len, end, tlen, cmp;

    if (!pos) return;

    refpos = pos;

    for (;;) {
        trie_node_read (fpi, features, &refpos, &pos, &cnt, &len, &end, &son, bp);
        if (!len) break;
/*
        pos  = ftell (fpi);
*/

        tlen = (keylen < len ? keylen : len);
        cmp  = memcmp (bp, keyp, tlen);
        if (cmp > 0) break;
        if (cmp == 0) {
            if (end) {
                fwrite (buffer, bp - buffer + len, 1, stdout);
                fputc ('\n', stdout);
            }
            find_tree (fpi, features, son, buffer, bp + len, keyp + tlen, keylen - tlen);
        }
    }
}

int main (int argc, char *argv[])
{
    FILE *fpi;
    long pos;
    char *keyp;
    int  keylen;
    int  version, features;

    static unsigned char buffer[1024];

    if (argc < 2) {
        fprintf (stderr, "missing argument\n");
        exit (1);
    }

    fpi = fopen (argv[1], "r");
    if (fpi == NULL) {
        fprintf (stderr, "Unable to open file \"%s\"\n", argv[1]);
        return 1;
    }

    {
        char *buf = malloc (4096);
        setbuffer (fpi, buf, 4096);
    }

    pos = trie_hdr_read (fpi, &version, &features);

    keyp = (argc >= 3 ? argv[2] : "");
    keylen = strlen (keyp);

    find_tree (fpi, features, pos, buffer, buffer, keyp, keylen);

    return 0;
}
