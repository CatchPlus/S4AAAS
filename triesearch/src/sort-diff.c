/* trie-diff.c
 *
 * This program builds generates a difference of two sorted files
 *
 * revision History:
 * 20110824 Initial version         Rolf Fokkens
 *                                  rolf.fokkens@target-holding.nl
 *
 * THIS PROGRAM REQUIRES BETTER ERROR HANDLING
 *
 * It is written as a Proof of Concept in a pragmatic way. This
 * means that error handling has not been taken care of very well!
 */
#include <stdlib.h>
#include <unistd.h>
#include <stdio.h>
#include <malloc.h>
#include <getopt.h>
#include <stdarg.h>
#include <sys/types.h>
#include <sys/wait.h>


#define __USE_GNU
#include <string.h>

char *fgets_check (char **oldpp, char **newpp, FILE *fp, char *fname)
{
    char *ret;

    if (*newpp == NULL) *newpp = malloc (1024);

    ret = fgets (*newpp, 1024, fp);

    if (!ret) return NULL;

    if (*oldpp == NULL) {
        *oldpp = *newpp;
        *newpp = NULL;
        return ret;
    }

    if (strcmp (*oldpp, *newpp) > 0) {
        fprintf (stderr, "File \"%s\" not well sorted\n", fname);
        exit (1);
    }
    ret    = *newpp;
    *newpp = *oldpp;
    *oldpp = ret;

    return ret;
}

int main (int argc, char *argv[])
{
    int  i;
    FILE *fps[2];
    char *bufs[2] = {NULL, NULL}, *ret[2], *tbuf = NULL;

    if (argc < 3) {
        fprintf (stderr, "Missing argument(s)\n");
        exit (1);
    }
    for (i = 1; i < 3; i++) {
        fps[i-1] = fopen (argv[i], "r");
        if (fps[i-1] == NULL) {
            fprintf (stderr, "Unable to open \"%s\"\n", argv[i]);
            exit (1);
        }
    }
    for (i = 0; i < 2; i++) {
/*
        bufs[i] = malloc (1024);
        ret[i] = fgets (bufs[i], 1024, fps[i]);
*/
        ret[i] = fgets_check (&bufs[i], &tbuf, fps[i], argv[i+1]);
    }
    while (ret[0] && ret[1]) {
        int cmp, keep, flush;

        cmp = strcmp (bufs[0], bufs[1]);

        if (cmp == 0) {
            keep  = -1;
            flush = -1;
        } else
        if (cmp < 0) {
            keep  = 1;
            flush = 0;
        } else {
            keep  = 0;
            flush = 1;
        }
        for (i = 0; i < 2; i++) {
            if (i == flush) {
                printf ("%c%s", (flush ? '+' : '-'), bufs[i]);
            }
            if (i != keep) {
/*
                ret[i] = fgets (bufs[i], 1024, fps[i]);
*/
                ret[i] = fgets_check (&bufs[i], &tbuf, fps[i], argv[i+1]);
            }
        }
    }

    for (i = 0; i < 2; i++) {
        while (ret[i]) {
            printf ("%c%s", (i ? '+' : '-'), bufs[i]);
            ret[i] = fgets (bufs[i], 1024, fps[i]);
        }
    }

    for (i = 0; i < 2; i++) {
        free (bufs[i]);
        fclose (fps[i]);
    }

    return 0;
}
