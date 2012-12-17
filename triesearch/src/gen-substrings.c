/* gen-substrings
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

static void error (char *argv0, const char *fmt, ...)
{
    va_list ap;

    va_start (ap, fmt);
    vfprintf (stderr, fmt, ap);
    va_end (ap);

    fprintf (stderr, "\n\n"
"Usage: %s [ --separator=<sep> ]\n\n"
"This will generate a list of all substring word combinations based on\n"
"lines that are read from standard input. For every line only the first\n"
"word is read separated by <sep>.\n"
, argv0);

    exit (1);
}

int main (int argc, char *argv[])
{
    static char buffer[1024];
    char *sepp = NULL;

    for (;;) {
        int option_index = 0;
        int c;
        static struct option long_options[] = {
            {"separator",  required_argument, 0, 'p'},
            {0, 0, 0, 0}
        };

        c = getopt_long(argc, argv, "s::",
                        long_options, &option_index);
        if (c == -1)
            break;

        switch (c) {
        case 'p':
            if (sepp) {
                error (argv[0], "Multiple argument --separator");
            }
            sepp = optarg;
            break;
        default:
            error(argv[0], "?? getopt returned character code 0%o ??\n", c);
        }
    }

    if (sepp == NULL || sepp[0] == '\0') sepp = "\t";

    if (sepp[1] != '\0') {
        error (argv[0], "Only single character allowed --separator");
    }

    while (fgets (buffer, sizeof (buffer), stdin)) {
        char *sp, *ep, *tp;

        for (ep = buffer; *ep != '\0' && *ep != *sepp && *ep != '\n'; ep++);
        if (ep == buffer) continue;
        tp = ep;
        while (tp > buffer + 1) {
            for (sp = buffer; sp < tp - 1; sp++) {
                fwrite (sp, 1, tp - sp, stdout);
                putc (*sepp, stdout);
                fwrite (buffer, 1, ep - buffer, stdout);
                putc ('\n', stdout);
            }
            tp--;
        }
    }

    return 0;
}
