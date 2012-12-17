/* trie-build.c
 *
 * This program builds a trie based on data on standard input. The
 * trie is built in memory during the processing of standard input.
 * After EOF on standard input the in memory trie is flushed to 
 * a file as specified by the --trie argument.
 *
 * A consequence of this approach is the big memory footprint
 * because the trie is built in memory.
 *
 * revision History:
 * 20110824 Better comments in the  Rolf Fokkens
 *          header.                 rolf.fokkens@target-holding.nl
 *
 * 20110406 Initial version         Rolf Fokkens
 *                                  rolf.fokkens@target-holding.nl
 *
 * THIS PROGRAM REQUIRES BETTER ERROR HANDLING
 *
 * It is was written as a Proof of Concept in a pragmatic way. This
 * means that error handling has not been taken care of very well!
 */

#include <stdlib.h>
#include <string.h>
#include <stdio.h>
#include <malloc.h>
#include <getopt.h>
#include <stdarg.h>
#include "trie-util.h"

int nodecount = 0;
size_t nodesize = 0;

/*
 * A general introduction of tries can be found in the README file.
 *
 * The in memory trie consists of "struct memnode" elements. Each
 * memnode contains a substring of a line to be encoded in a trie. 
 * The prefix of the the line (the part left of the substring) is
 * encoded in the ancesters (parent, grand parent etc.). The suffix
 * of the line (the part right of the substring) is endoded in the
 * offspring (children and grandchildren etc) of the trie.
 *
 * Each memnode has sibling memnodes sharing the same prefix but with
 * different substrings. All siblings are sorted on substring.
 *
 * The encoded substring is in memnode.dat, it's length is memnode.len.
 *
 * The childs are pointed to by memnode.child, which is NULL if no
 * children exist.
 *
 * The next (ordered) sibling is pointed to memnode.sib, which is NULL
 * if no sibling exists.
 */
struct memnode {
    /* Pointers to child and sibling                */
    struct   memnode *sib, *child;
    /* The file pos of the child in the trie file   */
    uint64_t childpos;
    /* The total number of entries in the offspring */
    /* and this memnode itself                      */
    uint64_t cnt;
    /* Length of the substring pointed to by dat    */
    int      len;
    /* Flag to mark if this (also) is an end string */
    char     end;
    /* The substring itself                         */
    unsigned char dat[1];
};

uint64_t trie_write_sub (FILE *fp, int features, struct memnode *mp, uint64_t *cntp)
{
    uint64_t refpos, curpos, pos;
    struct memnode *tp;
    uint64_t cnt, tcnt;

    if (mp == NULL) {
        *cntp = 0;
        return 0;
    }

    cnt = 0;
    tp  = mp;
    while (tp) {
        tp->childpos = trie_write_sub (fp, features, tp->child, &tcnt);
        if (tp->end) tcnt++;
        tp->cnt = tcnt;
        cnt    += tcnt;
        tp      = tp->sib;
    }

    curpos = ftell64 (fp);

    pos    = curpos;
    refpos = pos;
    tp     = mp;

    while (tp) {
        trie_node_write (fp, features, &refpos, &pos, tp->cnt, tp->len, tp->end, tp->childpos, tp->dat);
        tp = tp->sib;
    }

    trie_node_write (fp, 0, NULL, NULL, 0, 0, 0, 0, NULL);

    *cntp = cnt;

    return curpos;
}

int trie_write (FILE *fp, int features, struct memnode *rootp)
{
    uint64_t root, hdr, cnt;

    fseek64 (fp, 2 * sizeof (root), SEEK_SET);

    root = trie_write_sub (fp, features, rootp, &cnt);

    fseek64 (fp, 0, SEEK_SET);

    hdr = TRIE_HDRBIT | (TRIE_VERSION << 32) | features;

    fwrite (&hdr,  sizeof (hdr),  1, fp);
    fwrite (&root, sizeof (root), 1, fp);

    fclose (fp);

    return 0;
}

void delnode (struct memnode *np)
{
    nodecount--;
    nodesize -= sizeof (struct memnode) + np->len;

    free (np);
}

struct memnode *newnode (unsigned char *dat, int len)
{
    struct memnode *np;

    nodecount++;
    nodesize += sizeof (struct memnode) + len;

    np        = malloc (sizeof (struct memnode) + len);
    np->len   = len;
    np->sib   = NULL;
    np->child = NULL;
    np->end   = 0;
    memcpy (np->dat, dat, len);

    return np;
}

/*
 * Process a line pointed to dat with length len and include it in 
 * the trie pointed to by 
 */
void process_line (unsigned char *dat, int len, struct memnode **rootpp)
{
    struct memnode **mpp;
/*
    mpp = &memroot;
*/
    mpp = rootpp;

    if (!len) return;

    if (dat[len-1] == '\n') len--;

    for (;;) {
        int l1, l2, ct;
        unsigned char *d1, *d2;
        struct memnode *mp, *n1, *n2;

        mp = *mpp;
        if (mp == NULL) {
            mp = newnode (dat, len);
            mp->end = 1;
            *mpp = mp;
            return;
        }

        if (len && dat[0] > mp->dat[0]) {
            mpp = &(mp->sib);
            continue;
        }

        if (len && dat[0] < mp->dat[0]) {
            n1 = newnode (dat, len);
            n1->sib = mp;
            n1->end = 1;
            *mpp = n1;
            return;
        }

        ct = 0;
        d1 = dat;
        l1 = len;
        d2 = mp->dat;
        l2 = mp->len;

        while (l1 && l2 && *d1 == *d2) {
            ct++;
            d1++;
            l1--;
            d2++;
            l2--;
        }

        if (!l2) {
            /* string ends in tree */
            if (!l1) {
                /* string ends in input too */
                mp->end = 1;
                return;
            }
            /* move to the child in tree */
            mpp = &(mp->child);
            dat = d1;
            len = l1;
            continue;
        }

        /* split the node */
        n2 = newnode (d2, l2);
        n2->child = mp->child;
        n2->end   = mp->end;

        n1 = newnode (mp->dat, ct);
        n1->sib   = mp->sib;
        n1->child = n2;
        *mpp = n1;
        delnode (mp);

        mpp = &(n1->child);
        if (!l1) {
            n1->end = 1;
            return;
        };

        dat = d1;
        len = l1;
    }
}

static void error (char *argv0, const char *fmt, ...)
{
    va_list ap;

    va_start (ap, fmt);
    vfprintf (stderr, fmt, ap);
    va_end (ap);

    fprintf (stderr, "\n\nUsage: %s --trie=<trie>\n\n", argv0);
    fprintf (stderr, "This will build a trie file <trie> by reading.\n");
    fprintf (stderr, "data from standard input.\n");

    exit (1);
}

int main (int argc, char *argv[])
{
    static unsigned char linebuffer[1024];
    char *bp, *trie = NULL;
    FILE *fpi, *fpo;
    struct memnode *rootp = NULL;

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

    fpi = stdin;

    fpo = fopen (trie, "w");
    if (fpo == NULL) {
        fprintf (stderr, "Unable to open file \"%s\"\n", trie);
        exit (1);
    }

    while ((bp = fgets ((char *)linebuffer, sizeof (linebuffer), fpi))) {
        process_line (linebuffer, strlen ((char *)linebuffer), &rootp);
    }
    fclose (fpi);

    trie_write (fpo, TRIE_OPT_RELCHLD | TRIE_OPT_COUNT, rootp);

    return 0;
}
