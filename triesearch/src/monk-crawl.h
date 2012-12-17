#ifndef H_MONKCRAWL
#define H_MONKCRAWL

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
extern int prefix_match (char *pfx, int lpfx, char *str);

struct dirinfo {
    char *dir;
    char *dat[3];
};

extern int scan_dir (char *mask[], int (*process)(struct dirinfo *, char *, char *));

extern char *navi_split (char *navid, int split_y);

extern char *stolower (char *cp);

#endif /* H_MONKCRAWL */
