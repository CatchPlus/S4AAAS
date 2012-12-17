/* trie-lookup
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

#define _FILE_OFFSET_BITS 64

#include <stdlib.h>
#include <string.h>
#include <getopt.h>
#ifndef __USE_BSD
#define __USE_BSD
#endif
#include <stdio.h>
#include "trie-util.h"

void handle_entry (char *bp, int len, FILE *fpd, int idflag)
{
    char *cp, *tp;
    uint64_t pos;
    int ch;

    if (idflag) {
        for (cp = bp; cp < bp + len; cp++) {
            fputc (*cp, stdout);
            if (*cp == '\t') break;
        }
    }

    bp[len] = '\0';
    bp--;

    cp = bp + len - 1;
    while (cp > bp && *cp != '\t') cp--;
    if (*cp == '\t') cp ++;

    pos = strtoll (cp, &tp, 16);

    fseek64 (fpd, (pos ? pos - 1 : 0), SEEK_SET);

    ch = (pos ? fgetc (fpd) : '\n');
    for (;;) {
        ch = fgetc (fpd);
        if (ch == '\n' || ch == EOF) break;
        fputc (ch, stdout);
    }
    fputc ('\n', stdout);
};

int main (int argc, char *argv[])
{
    static char buffer[1024];
    char *bp;
    FILE *fpd;
    char *fullindex = NULL;
    int  idflag = 0;

    for (;;) {
        int option_index = 0;
        int c;
        static struct option long_options[] = {
            {"state",      no_argument, 0, 's'},
            {"full-index", required_argument, 0, 'f'},
            {0, 0, 0, 0}
        };

        c = getopt_long(argc, argv, "sf:",
                        long_options, &option_index);
        if (c == -1)
            break;

        switch (c) {
        case 's':
            idflag = 1;
            break;
        case 'f':
            fullindex = optarg;
            break;
        default:
            printf("?? getopt returned character code 0%o ??\n", c);
        }
    }

    if (optind < argc) {
        fprintf (stderr, "excess arguments\n");
        return 1;
    }

    if (fullindex == NULL) {
        fprintf (stderr, "Missing option --full-index\n");
        return 1;
    }

    fpd = fopen (fullindex, "r");
    if (fpd == NULL) {
        fprintf (stderr, "Unable to open file \"%s\"\n", fullindex);
        return 1;
    }

    bp = malloc (1024);
    setbuffer (fpd, bp, 1024);

    while ((bp = fgets ((char *)buffer, sizeof (buffer), stdin))) {
        handle_entry (buffer, strlen ((char *)buffer), fpd, idflag);
    }

    return 0;
}
