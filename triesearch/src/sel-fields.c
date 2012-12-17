#include <stdlib.h>
#include <stdio.h>
#include <string.h>
#include <ctype.h>
#include <getopt.h>
#include <stdarg.h>
#include <stdint.h>

/* sel-fields
 *
 * revision History:
 * 20110406 Initial version Rolf Fokkens
 *                          rolf.fokkens@target-holding.nl
 *
 * A utility to select fields from every line based on
 * TAB separation. All lines are read from standard input, all
 * output is sent to standard output.
 *
 * Each line is extended with an additional field POS, also TAB
 * separated from the rest. The POS field contains the (file)
 * position of the original line. This can be used later to
 * retrieve the full line.
 *
 * USAGE
 *
 *     ./sel-fields <F1> <F2> <F3> ... | ./tree-build <INDEX>
 *
 * DESCRIPTION
 *
 * This reads lines from standard input, and writes 
 * fields <F1>, <F2>, F3> ..  to standard output. In general the
 * output can be 'piped' to the tree-build utility.
 *
 * Example: ./sel-fields 3 1 2 3 < monk.lis | ./tree-build monk.tree
 *
 * This reades lines from standard input, and writes fields 3, 1,
 * 2 and 3 (again) to tree-build, which creates a search tree
 * 'monk.tree'.
 *
 * THIS PROGRAM REQUIRES BETTER ERROR HANDLING
 *
 * It is was written as a Proof of Concept in a pragmatic way. This
 * means that error handling has not been taken care of very well!
 */

struct fieldmap {
    int map;
    int tolower;
};

static void error (char *argv0, const char *fmt, ...)
{
    va_list ap;

    va_start (ap, fmt);
    vfprintf (stderr, fmt, ap);
    va_end (ap);

    fprintf (stderr, "\n\nUsage: %s --fields=<fields>\n\n", argv0);
    fprintf (stderr, "This will select specified fields for each line.\n");
    fprintf (stderr, "from standard input.\n");

    exit (1);
}

int main (int argc, char *argv[])
{
    char     ibuf [1024], obuf [1024],*ibp, *obp, *cp, *ep;
    char     **fields, *fieldp = NULL;
    int      i, j, fldcnt, nfld, fmax, nfmap;
    uint64_t linepos, curpos;
    int      nopos = 0;

    struct fieldmap *fieldmap, *fm;

    for (;;) {
        int option_index = 0;
        int c;
        static struct option long_options[] = {
            {"fields",  required_argument, 0, 'f'},
            {"nopos",   no_argument,       0, 'n'},
            {0, 0, 0, 0}
        };

        c = getopt_long(argc, argv, "f:n",
                        long_options, &option_index);
        if (c == -1)
            break;

        switch (c) {
        case 'f':
            if (fieldp) {
                error (argv[0], "Multiple argument --fields");
            }
            fieldp = optarg;
            break;
        case 'n':
            nopos = 1;
            break;
        default:
            error (argv[0], "?? getopt returned character code 0%o ??\n", c);
        }
    }

    if (optind < argc)  error (argv[0], "Excess argument");
    if (fieldp == NULL) error (argv[0], "Missing argument --field");

    nfmap    = 16;
    fieldmap = calloc (nfmap, sizeof (struct fieldmap));

    nfld = 0;
    fmax = 0;
    fm   = fieldmap;
    cp    = fieldp;
    while (*cp != 0) {
        if (nfld == nfmap) {
            struct fieldmap *tmp;

            tmp = calloc (nfmap * 2, sizeof (struct fieldmap));
            memcpy (tmp, fieldmap, nfmap);
            nfmap <<= 1;
            cfree (fieldmap);
            fieldmap = tmp;
        }
        j = strtol (cp, &ep, 10);
        if (j < 1 || ep == cp) error (argv[0], "Bad --field argument");
        if (j > fmax) fmax = j;
        cp = ep;
        fm->map = j;
        if ((*cp == 'l') || (*cp == 'L')) {
            fm->tolower = 1;
            cp++;
        } else {
            fm->tolower = 0;
        }
        fm++;
        nfld++;
        if (*cp != ',') break;
        cp++;
    }
    if (*cp != 0) error (argv[0], "Bad --field argument");

    nfld = fm - fieldmap;

    fields   = calloc (fmax + 1, sizeof (char **));

    ibp = ibuf;
    obp = obuf;
    fields[0] = ibuf;
    curpos  = 0;
    linepos = 0;

    while (fgets (ibuf, sizeof (ibuf), stdin)) {
        linepos = curpos;
        fldcnt  = 1;
        ibp     = ibuf;

        for (;;) {
            char c;
            c = *ibp++;
            if (c == '\0') break;
            if (c == '\t' || c == '\n') {
                if (fldcnt <= fmax) fields[fldcnt++] = ibp;
            }
            curpos++;
        }
        obp = obuf;

        for (i = 0, fm=fieldmap; i < nfld; i++, fm++) {
            char c;
            int  len;

            if (fm->map >= fldcnt) continue;
            cp = fields[fm->map - 1];
            ep = fields[fm->map] - 1;
            len = ep - cp;
            if (fm->tolower) {
                while (len--) {
                    c = *cp++;
                    if (c >= 'A' && c <= 'Z') c += ('a' - 'A');
                    *obp++ = c;
                }
            } else {
                memcpy (obp, cp, len);
                obp += len;
            }
            *obp++ = '\t';
        }

        fwrite (obuf, 1, obp - obuf, stdout);
        if (nopos) {
            printf ("\n");
        } else {
            printf ("%llx\n", (long long) linepos);
        }
    }

    return 0;
}
