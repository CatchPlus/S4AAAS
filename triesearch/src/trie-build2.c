/* trie-build2.c
 *
 * This program builds a trie based on data on standard input. It
 * produces a trie as specified by the --trie argument.
 *
 * It is very similar to the trie-build.c, the main difference is
 * the small memory footprint. In contrast to trie-build.c the
 * trie-build2.c program holds only a small part of the trie in
 * memory by writing parts of it to disk whenever possible. This
 * is possible because trie-build2.c sorts the data on disk
 * before building the trie.
 * 
 * revision History:
 * 20110824 Now the input is sorted Rolf Fokkens
 *          first, after which it   rolf.fokkens@target-holding.nl
 *          is used to build the
 *          trie with a small
 *          memory footprint
 *
 * 20110810 Version which assumes   Rolf Fokkens
 *          sorted input, reducing  rolf.fokkens@target-holding.nl
 *          the memory footprint
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
#include <unistd.h>
#include <stdio.h>
#include <malloc.h>
#include <getopt.h>
#include <stdarg.h>
#include <sys/types.h>
#include <sys/wait.h>


#define __USE_GNU
#include <string.h>

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
    uint64_t subcnt;
    /* Length of the substring pointed to by dat    */
    int      len;
    /* Flag to mark if this (also) is an end string */
    char     end;
    /* The substring itself                         */
    unsigned char dat[1];
};

static void delnode (struct memnode *np)
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

    np           = malloc (sizeof (struct memnode) + len);
    np->len      = len;
    np->sib      = NULL;
    np->child    = NULL;
    np->childpos = 0;
    np->end      = 0;
    np->subcnt   = 0;
    memcpy (np->dat, dat, len);

    return np;
}

static uint64_t flush_node (FILE *fp, int features, struct memnode *mp, uint64_t *cntp)
{
    uint64_t curpos, refpos, pos, cnt, tcnt;
    struct memnode *tp;

    if (mp == NULL) return 0;

    tp  = mp;

    while (tp) {
        if (tp->child) {
            tp->childpos = flush_node (fp, features, tp->child, &(tp->subcnt));
            tp->child    = NULL;
        }
        tp   = tp->sib;
    }

    curpos = ftell64 (fp);
    pos    = curpos;
    refpos = pos;
    cnt    = 0;

    while (mp) {
        tcnt = mp->subcnt;
        if (mp->end) tcnt++;

        trie_node_write (fp, features, &refpos, &pos, tcnt, mp->len, mp->end, mp->childpos, mp->dat);

        cnt += tcnt;

        tp   = mp;        
        mp   = mp->sib;
        delnode (tp);
    }
    trie_node_write (fp, features, NULL, NULL, 0, 0, 0, 0, NULL);

    *cntp = cnt;

    return curpos;
}

/* Given the fact that the input data should be ordered, some situations
 * should not happen.
 */
static void abort_error (void)
{
    fprintf (stderr, "ERROR: The input data is not ordered\n");
    exit (1);
}

/*
 * This addline version assumes sorted input. This means that whenever
 * a split occurs all subtrees can be flushed.
 */
static void addline_trie (FILE *fp, int features, struct memnode **mpp, unsigned char *dat, int len)
{
    /* Previous sib. Points to the last sib */
    /* visited, or NULL is this is the first*/
    /* sib in the node.                     */
    struct memnode *prvsibp = NULL;

    while (len && dat[len-1] == '\n') len--;

    if (!len) return;

    for (;;) {
        int l1, l2, ct;
        unsigned char *d1, *d2;
        struct memnode *mp, *n1, *n2;

        mp = *mpp;
        /* In case of an empty trie: build one */
        if (mp == NULL) {
            mp = newnode (dat, len);
            mp->end = 1;
            *mpp = mp;
            /* As the input is sorted, we won't*/
            /* change the older sibs! So flush */
            /* and forget prvsibp's subtrie    */
            if (prvsibp) {
                prvsibp->childpos = flush_node (fp, features, prvsibp->child, &(prvsibp->subcnt));
                prvsibp->child    = NULL;
                fflush (fp);
            }
            return;
        }

        /* Move to the next sib if appropriate */
        if (len && dat[0] > mp->dat[0]) {
            prvsibp = mp;
            mpp     = &(mp->sib);
            continue;
        }

        /* Introduce a new sib if appropriate  */
        if (len && dat[0] < mp->dat[0]) {
            abort_error ();
        }
        /* We found the right sib */
        ct = 0;
        d1 = dat;
        l1 = len;
        d2 = mp->dat;
        l2 = mp->len;

        /* At what pos do the sib and the new  */
        /* value differ                        */
        while (l1 && l2 && *d1 == *d2) {
            ct++;
            d1++;
            l1--;
            d2++;
            l2--;
        }

        if (!l2) {
            /* No sib string data left         */
            if (!l1) {
                /* No input string left as well*/
                /* So we have a match!         */

                if (mp->child || mp->childpos) {
                    abort_error ();
                }
                mp->end = 1;
                return;
            }
            /* Some input string data is left  */
            /* so the string is a child of sib   */
            /* Continue there                  */
            mpp     = &(mp->child);
            dat     = d1;
            len     = l1;
            prvsibp = NULL;
            continue;
        }
        /* There's unmatched sib string data   */
        /* left, so split the node             */

        if (!l1) {
            abort_error ();
        }

        /* New node n2 is the non matching tail*/
        /* of mp                               */
        n2 = newnode (d2, l2);
        n2->child = mp->child;
        n2->end   = mp->end;

        /* New node n1 replaces mp, which has  */
        /* The prefix length ct of mp          */
        n1 = newnode (mp->dat, ct);
        n1->sib   = mp->sib;
        n1->child = n2;
        *mpp = n1;
        /* mp is obsolete now                  */
        delnode (mp);

        /* If there's no input string data left*/
        /* Then the mp replacement n1 is an    */
        /* endpoint!                           */
        if (!l1) {
            n1->end = 1;
            return;
        };

        /* Now move on to the child, where the   */
        /* remainder of the input string will  */
        /* reside                              */
        mpp     = &(n1->child);
        dat     = d1;
        len     = l1;
        prvsibp = NULL;
    }
}

static int build_triefile (int par, int memmb, char *template, FILE *fpi, FILE *fpo)
{
    uint64_t         hdr, cnt, root = 0;
    int              features;
    struct memnode   *rootp = NULL;
    unsigned char    *bp;
    struct sortstate *sortp;

    features = TRIE_OPT_COUNT | TRIE_OPT_RELCHLD;
    hdr =   TRIE_HDRBIT | (TRIE_VERSION << 32)
          | features | TRIE_OPT_CREATE;

    /* Create a valid header, excep for  */
    /* root. Mark this by TRIE_OPT_CREATE */
    /* This allows trie-dump, during     */
    /* trie-build                        */
    fseek64 (fpo, 0, SEEK_SET);
    fwrite (&hdr,  sizeof (hdr),  1, fpo);
    fwrite (&root, sizeof (root), 1, fpo);
    fflush (fpo);

    bp = sort_init (&sortp, par, memmb * 1024 * 1024, fpi, template, NULL);

    while (bp) {
        addline_trie (fpo, features, &rootp, bp, strlen ((char *)bp) - 1);
        bp = sort_nextline (sortp);
    }

    free (sortp);

    root = flush_node (fpo, features, rootp, &cnt);
    hdr =   TRIE_HDRBIT | (TRIE_VERSION << 32)
          | features;

    fflush (fpo);

    fseek64 (fpo, 0, SEEK_SET);
    fwrite (&hdr,  sizeof (hdr),  1, fpo);
    fwrite (&root, sizeof (root), 1, fpo);

    fflush (fpo);

    return 0;
}

static void error (char *argv0, const char *fmt, ...)
{
    va_list ap;

    va_start (ap, fmt);
    vfprintf (stderr, fmt, ap);
    va_end (ap);

    fprintf (stderr, "\n\n"
"Usage: %s --trie=<trie> [--tmpdir=<tmpdir>] [--mem=<mem>] "
"[--parallel=<parallel>]\n\n"
"This will build the trie file <trie> based on standard input data. It will\n"
"do so by sorting all input data first and then building the actual trie.\n"
"Sorting will be done by reading fragments of <mem> MB, sorting it in\n"
"memory and writing it to temporary files in <dir>. <mem> is 128 by default\n"
"and <dir> is /tmp by default. The sorting in memory can be done in parallel\n"
"by specifying <par> for the number of parallel processes. <par> is 1 by\n"
"default, which means no parallel processing will be done.\n\n"
"Be aware that the total memory footprint can be as big as <par> * <mem>!\n"
, argv0);

    exit (1);
}

int main (int argc, char *argv[])
{
    char *trie   = NULL;
    char *tmpdir = "/tmp";
    int  mem     = 128;
    int  par     = 1;

    char *tdirbuf;
    FILE *fpi, *fpo;

    for (;;) {
        int option_index = 0;
        int c;
        static struct option long_options[] = {
            {"tmpdir",   required_argument, 0, 'd'},
            {"trie",     required_argument, 0, 't'},
            {"mem",      required_argument, 0, 'm'},
            {"parallel", required_argument, 0, 'p'},
            {0, 0, 0, 0}
        };

        c = getopt_long(argc, argv, "t:",
                        long_options, &option_index);
        if (c == -1)
            break;

        switch (c) {
        case 'd':
            tmpdir = optarg;
            break;
        case 't':
            trie = optarg;
            break;
        case 'm':
            mem = atol (optarg);
            break;
        case 'p':
            par = atol (optarg);
            break;
        default:
            printf("?? getopt returned character code 0%o ??\n", c);
        }
    }

    if (optind < argc)          error (argv[0], "Excess argument");

    if (trie == NULL)           error (argv[0], "Missing argument --trie");

    if (mem < 1 || mem > 2048) error (argv[0], "Invalid --mem value");

    if (par < 1 || par > 16)   error (argv[0], "Invalid --par value");

    tdirbuf = malloc (strlen (tmpdir) + 16);
    sprintf (tdirbuf, "%s/trie-build-XXXXXX", tmpdir);

    fpi = stdin;

    fpo = fopen (trie, "w");
    if (fpo == NULL) {
        fprintf (stderr, "Unable to open file \"%s\"\n", trie);
        exit (1);
    }
    build_triefile (par, mem, tdirbuf, fpi, fpo);

    fclose (fpo);

    return 0;
}
