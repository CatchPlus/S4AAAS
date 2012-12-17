#include <unistd.h>
#include <stdlib.h>
#include <malloc.h>
#include <getopt.h>
#include <stdio.h>
#include <stdarg.h>
#include <sys/types.h>
#include <sys/wait.h>

#define __USE_GNU
#include <string.h>

#include "trie-util.h"

static void error (char *argv0, const char *fmt, ...)
{
    va_list ap;

    va_start (ap, fmt);
    vfprintf (stderr, fmt, ap);
    va_end (ap);

    fprintf (stderr, "\n\n"
"Usage: %s [--tmpdir=<tmpdir>] [--mem=<mem>] [--parallel=<parallel>]\n\n"
"This will sort stdin and write the result to stdout. Sorting will be done\n"
"by reading fragments of <mem> MB, sorting it in memory and writing it to \n"
"temporary files in <dir>. <mem> is 128 by default and <dir> is /tmp by\n"
"default. The sorting in memory can be done in parallel by specifying <par>\n"
"for the number of parallel processes. <par> is 1 by default, which means no\n"
"parallel processing will be done.\n\n"
"Be aware that the total memory footprint can be as big as <par> * <mem>!\n"
, argv0);

    exit (1);
}



int main (int argc, char *argv[])
{
    struct sortstate *sortp;
    char *linep, *tdirbuf;
    char *tmpdir = "/tmp";
    int  mem     = 128;
    int  par     = 1;


    for (;;) {
        int option_index = 0;
        int c;
        static struct option long_options[] = {
            {"tmpdir",   required_argument, 0, 'd'},
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
    sprintf (tdirbuf, "%s/sort-XXXXXX", tmpdir);

    linep = (char *)sort_init (&sortp, par, mem *1024 * 1024, stdin, tdirbuf, NULL);

    while (linep) {
        fputs (linep, stdout);
        linep = (char *)sort_nextline (sortp);
    }

    return 0;
}
