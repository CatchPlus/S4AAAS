/* trie-words
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
#include <stdarg.h>
#include <getopt.h>
#include "trie-util.h"

void find_words (FILE *fpi, int features, uint64_t pos, unsigned char *buffer, unsigned char *bp)
{
    int           len, end;
    unsigned char *cp;
    uint64_t      refpos, son, cnt;

    if (!pos) return;

    refpos = pos;

    for (;;) {
        trie_node_read (fpi, features, &refpos, &pos, &cnt, &len, &end, &son, bp);
        if (!len) break;

        cp = (unsigned char *) memchr (bp, '\t', len);

        if (cp != NULL) {
            fwrite (buffer, cp - buffer, 1, stdout);
            fputc ('\n', stdout);
        } else {
            find_words (fpi, features, son, buffer, bp + len);
        }
    }
}

static void error (char *argv0, const char *fmt, ...)
{
    va_list ap;

    va_start (ap, fmt);
    vfprintf (stderr, fmt, ap);
    va_end (ap);

    fprintf (stderr, "\n\n"
"Usage: %s --trie=<trie>\n\n"
"This will print all words before the first TAB in trie file <trie>.\n"
"to standard output.\n"
, argv0);

    exit (1);
}

int main (int argc, char *argv[])
{
    FILE *fpi;
    long pos;
    char *trie = NULL;
    int  version, features;

    static unsigned char buffer[1024];

    for (;;) {
        int option_index = 0;
        int c;
        static struct option long_options[] = {
            {"trie", required_argument, 0, 't'},
            {0, 0, 0, 0}
        };

        c = getopt_long(argc, argv, "t:",
                        long_options, &option_index);
        if (c == -1)
            break;

        switch (c) {
        case 't':
            trie = optarg;
            break;
        default:
            printf("?? getopt returned character code 0%o ??\n", c);
        }
    }

    if (optind < argc) error (argv[0], "Excess argument");

    if (trie == NULL)  error (argv[0], "Missing argument --trie");

    fpi = fopen (trie, "r");
    if (fpi == NULL) {
        fprintf (stderr, "Unable to open file \"%s\"\n", trie);
        return 1;
    }

    {
        char *buf = malloc (4096);
        setbuffer (fpi, buf, 4096);
    }

    pos = trie_hdr_read (fpi, &version, &features);

    find_words (fpi, features, pos, buffer, buffer);

    return 0;
}
