#include <stdio.h>
#include <semaphore.h>
#include <stdlib.h>
#include <string.h>
#include <dirent.h>
#include <unistd.h>
#include <poll.h>
#include <errno.h>
#include <fcntl.h>
#include <ctype.h>
#include <sys/types.h>
#include <sys/wait.h>
#include <sys/stat.h>

#include "monk-crawl.h"

/* gen-raw-index
 *
 * revision History:
 * 20110406 Initial version Rolf Fokkens
 *                          rolf.fokkens@target-holding.nl
 *
 * This utility scans all wordlabels, page transcriptions
 * and line transcriptions from MONK and writes them to standard
 * output.
 *
 * Each line has the following layout:
 * (W|L|P) OWNER COLLECTION BOOK PAGES LINE RECOG? NAVISID DISTANCE
 * The fields are tab separated
 *
 * Usage: ./gen-raw-index > <INDEXFILE>
 * Example: ./gen-raw-index > monk-index.lis
 *
 * THIS PROGRAM MAY REQUIRE OPTIMIZATIONS!
 *
 * This program was written to experiment with an alternative way
 * to index the Monk data. Especially the requirements to include
 * line and page transcriptions were the reason to write this
 * program.
 *
 * THIS PROGRAM REQUIRES BETTER ERROR HANDLING
 *
 * It is written as a Proof of Concept in a pragmatic way. This
 * means that error handling has not been taken care of very well!
 */

#define MONKROOT "/srv/www/htdocs/monk"
#define BUFSIZE 1024
#define MAXCHILDS 16

#define TESTABORT_() { check_stdout (0); exit (0); }
#define TESTABORT() {}

char id_buffer[BUFSIZE];
char txt_buffer[BUFSIZE];

static int process_linestrips (struct dirinfo *dip, char *path, char *basename)
{
    char *split;

    split = navi_split (basename, 1);

    if (split) printf ("%s %s\n", basename, split);

    return 0;
}

static char *mask_linestrips[] = {"Pages", "", "Lines", NULL};

int main (int argc, char *argv[])
{
    scan_dir (mask_linestrips, process_linestrips);

    return 0;
}

