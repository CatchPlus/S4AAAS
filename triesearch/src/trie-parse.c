/* trie-parse.c
 *
 * revision History:
 * 20110406 Initial version Rolf Fokkens
 *                          rolf.fokkens@target-holding.nl
 *
 * THIS PROGRAM REQUIRES BETTER ERROR HANDLING
 *
 * It is was written as a Proof of Concept in a pragmatic way. This
 * means that error handling has not been taken care of very well!
 *
 * USAGE
 *
 *     ./trie-parse --trie <trie>
 *
 * DESCRIPTION
 *
 * trie-parse shows the trie structure of a trie file <trie> in a somewhat
 * readable way, like this:
 *
 *  A
 *   A
 *    A
 * *   AAA
 * *   BBC
 * *   CDA
 *    B
 * *   BCC
 * *   CDD
 * * BBCAA
 *
 * Each extra indent means the line is a child of the previous line.
 * Same indent means the line is a sibling of the previous line.
 * An asterisk (*) at the beginning of the lines means the line is a
 * leaf node.
 */

#include <stdlib.h>
#include <stdio.h>
#include <getopt.h>
#include <stdarg.h>
#include "trie-util.h"

void dump_trie (FILE *fp, int features, int lvl, uint64_t refpos, uint64_t pos, unsigned char *buffer, unsigned char *bp)
{
    uint64_t son, curpos, cnt;
    int      len, end;
    unsigned char *cp;

    if (!pos) return;

    curpos = pos;

    trie_node_read (fp, features, &refpos, &curpos, &cnt, &len, &end, &son, bp);

    if (!len) return;

    printf ("%010ld", (long)pos);
    printf (" %s", (end ? "*" : " "));
    for (cp = buffer; cp < bp; cp++) putchar (*cp == '\t' ? '\t' : ' ');
    fwrite (bp, len, 1, stdout);
    printf ("\n");

    dump_trie (fp, features, lvl + 1, son,    son,    buffer, bp + len);
    dump_trie (fp, features, lvl,     refpos, curpos, buffer, bp);
}

static void error (char *argv0, const char *fmt, ...)
{
    va_list ap;

    va_start (ap, fmt);
    vfprintf (stderr, fmt, ap);
    va_end (ap);

    fprintf (stderr, "\n\nUsage: %s --trie <trie>\n\n", argv0);
    fprintf (stderr, "This will dump the tree structure of trie file <trie>.\n");

    exit (1);
}

int main (int argc, char *argv[])
{
    FILE *fp;
    uint64_t pos;
    char *trie = NULL;
    int  version, features;

    static unsigned char buffer[1024];

    for (;;) {
        int option_index = 0;
        int c;
        static struct option long_options[] = {
            {"trie",  required_argument, 0, 't'},
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

    fp = fopen (trie, "r");
    if (fp == NULL) error (argv[0], "Unable to open file \"%s\"\n", argv[1]);

    pos = trie_hdr_read (fp, &version, &features);

    dump_trie (fp, features, 0, pos, pos, buffer, buffer);

    return 0;
}
