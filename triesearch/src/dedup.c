#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <getopt.h>
#include <stdarg.h>
#include "trie-util.h"

#define GL_NOWORD   -1
#define GL_BADLINE  -2
#define GL_EOF       0
#define GL_DIFFERENT 1
#define GL_SAME      2

int match_line (char *refp, char *bufp, int *x1p, int *x2p, char *hmp, float *dp)
{
    char *cp, hm;
    int sepct = 0;
    int dif, tx, tw;
    float td;

    dif = 0;

    if (bufp[0] != 'W') return GL_NOWORD;

    for (cp = bufp; *cp != '\0'; cp++) {
        if (*cp == '\t') {
            sepct++;
            if (sepct == 7) break;
        }
        if (refp && *refp++ != *cp) dif = 1;
    }
    if (*cp == '\0') return GL_BADLINE;

    {
        int i = sscanf (cp, "%d\t%d\t%c\t%*s\t%*s\t%*s\%f", &tx, &tw, &hm, &td);

        if (i != 4) return GL_BADLINE;
    }
/*
    if (sscanf (cp, "%d\t%d\t%c\t%*s\t%*s\t%*s\%f", &tx, &tw, &hm, &td) != 4) return -1;
*/

    *x1p = tx;
    *x2p = tx + tw;
    *hmp = hm;
    *dp  = td;

    return (dif ? GL_DIFFERENT : GL_SAME);
}

static void error (char *argv0, const char *fmt, ...)
{
    va_list ap;

    va_start (ap, fmt);
    vfprintf (stderr, fmt, ap);
    va_end (ap);

    fprintf (stderr, "\n\n"
"Usage: %s [--tmpdir=<tmpdir>] [--mem=<mem>] [--parallel=<parallel>]\n\n"
"This will deduplicate a raw Monk index data on standard input as\n"
"generated by gen-raw-index.\n"
"It will do so by sorting all input data first and then do the actual\n"
"deduplication. Sorting will be done by reading fragments of <mem> MB,\n"
"sorting it in memory and writing it to temporary files in <dir>. <mem> is\n"
"128 by default and <dir> is /tmp by default. The sorting in memory can\n"
"be done in parallel by specifying <par> for the number of parallel\n"
"processes. <par> is 1 by default, which means no parallel processing\n"
"will be done.\n\n"
"Be aware that the total memory footprint can be as big as <par> * <mem>!\n"
, argv0);



    exit (1);
}
int main (int argc, char *argv[])
{
    int rv, x1, x2, nx1, nx2;
    float d, nd;
    char buf1[1024], buf2[1024], hm, nhm;
    char *bestp = buf1, *newp = buf2;
    struct sortstate *sortp;
    char *tdirbuf;

    char *tmpdir = "/tmp";
    int  mem     = 128;
    int  par     = 1;

    for (;;) {
        int option_index = 0;
        int c;
        static struct option long_options[] = {
            {"mem",      required_argument, 0, 'm'},
            {"parallel", required_argument, 0, 'p'},
            {"tmpdir",   required_argument, 0, 'd'},
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

    if (mem < 1 || mem > 2048) error (argv[0], "Invalid --mem value");

    if (par < 1 || par > 16)   error (argv[0], "Invalid --par value");

    tdirbuf = malloc (strlen (tmpdir) + 16);
    sprintf (tdirbuf, "%s/dedup-XXXXXX", tmpdir);

    bestp = (char *)sort_init (&sortp, par, mem * 1024 * 1024, stdin, tdirbuf, NULL);

    while (bestp) {
        rv = match_line (NULL, bestp, &x1, &x2, &hm, &d);
        if (rv == GL_NOWORD) {
            fputs (bestp, stdout);
        } else if (rv != GL_BADLINE) {
            break;
        }
        bestp = (char *)sort_nextline (sortp);
    }

    if (!bestp) return 0;

    strcpy (buf1, bestp);
    bestp = buf1;

    for (;;) {
        newp = (char *)sort_nextline (sortp);
        if (!newp) break;

        rv = match_line (bestp, newp, &nx1, &nx2, &nhm, &nd);

        if (rv == GL_BADLINE) continue;
        if (rv == GL_NOWORD) {
            fputs (newp, stdout);
            continue;
        }
        if (rv == GL_SAME && x1 <= nx1 && nx1 <= x2) {
            if (nhm < hm || (nhm == hm && nd < d)) {
                strcpy (bestp, newp);
                x1 = nx1;
                x2 = nx2;
                d  = nd;
                hm = nhm;
            }
            continue;
        }
        fputs (bestp, stdout);
        strcpy (bestp, newp);
        x1 = nx1;
        x2 = nx2;
        d  = nd;
        hm = nhm;
    }

    fputs (bestp, stdout);

    return 0;
}
