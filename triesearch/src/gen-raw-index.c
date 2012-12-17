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

static int process_wordzones (struct dirinfo *dip, char *path, char *basename)
{
    static char *ext = "linlist";
    static char tmp1[BUFSIZE];
    static char tmp2[BUFSIZE];

    char *cp, *distp, *navi = dip->dat[0];
    int   ch, sepcnt, nlen = strlen (navi);
    FILE *fp;

    strcpy (tmp1, basename);

    cp = strchr (tmp1, '.');
    if (cp == NULL) return 0;

    *cp++ = '\0';

    if (strcmp (cp, ext)) return 0;

    fp = fopen (path, "r");
    if (fp == NULL) return -1;

    sepcnt = 0;
    cp     = tmp2;
    distp  = NULL;
    for (;;) {
        ch = fgetc (fp);
        if (ch == '\n' || ch == EOF) {
            *cp++ = '\0';
            if (sepcnt == 1 && tmp1[0] != '\0') {
                char *splitp = navi_split (tmp2, 0);
                float dist = atof (distp);

                if (splitp && (splitp[0] == 'H' || dist < 0.4) && prefix_match (navi, nlen, tmp2)) {
/* Temporary suppress distp */
/* distp = ""; */
/*
printf ("[%d]W\t%s\t%s\t%s\t%s\t%s\t%s[%d]\n", p, tmp1, ctx[2], ctx[1], splitp, tmp2, distp, p);
*/
                    printf ("W\t%s\t%s\t%s\t%s\t%s\t%s\t%s\n", stolower (tmp1), dip->dat[1], dip->dat[2], splitp + 1, tmp1, tmp2, distp);
                    TESTABORT ();
                }
            }
            if (ch == EOF) break;
            sepcnt = 0;
            cp     = tmp2;
            distp  = NULL;
        } else {
            if (ch == ' ') {
                *cp++ = '\0';
                distp = cp;
                sepcnt++;
            } else {
                *cp++ = ch;
            }
        }
    }

    fclose (fp);

    return 0;
}

static int process_linpag (struct dirinfo *dip, char typ, char *path, char *basename, char *tail)
{
    char *bp, *tp, *ep = NULL;
    int ch, lno = 0;
    FILE *fp;
    char *navi = dip->dat[0];
    int   nlen = strlen (navi);

    tp = basename + strlen (basename) - strlen (tail);
    if (tp < basename) return 0;
    if (strcmp (tp, tail)) return 0;

    fp = fopen (path, "r");
    if (fp == NULL) return 0;

    ch = fgetc (fp);
    while  (ch != EOF && ep == NULL) {
         lno++;
         bp = id_buffer;
         while (ch != EOF && ch != '\n' && ch != ' ') {
             *bp++ = ch;
             ch = fgetc (fp);
         }
         if (ch != ' ') {
             ep = "Bad navis ID";
             break;
         }
         *bp = 0;
         while (ch == ' ') ch = fgetc (fp);
         tp = "<txt>";
         while (*tp && (ch == *tp++)) ch = fgetc (fp);
         if (*tp) {
             ep = "bad <txt> tag";
             break;
         }
         bp = txt_buffer;
         while (ch != EOF && ch != '\n') {
             *bp++ = ch;
             ch = fgetc (fp);
         }
         bp--;
         tp = ">txt/<";
         while (*tp && (bp >= txt_buffer) && *tp == *bp) {
             tp++;
             bp--;
         }
         if (*tp) {
             ep = "bad </txt> tag";
             break;
         }
         *++bp = 0;
         {
             char *splitp = navi_split (id_buffer, 0);
             if (splitp && prefix_match (navi, nlen, id_buffer)) {
/*
printf ("[%d]%c\t%s\t%s\t%s\t%s\t%s[%d]\n", p, typ, txt_buffer, ctx[2], ctx[1], splitp, id_buffer, p);
*/
                 printf ("%c\t%s\t%s\t%s\t%s\t%s\t%s\n", typ, stolower (txt_buffer), dip->dat[1], dip->dat[2], splitp, txt_buffer, id_buffer);
                 TESTABORT ();
             }
         }
         if (ch == '\n') ch = fgetc (fp);
    } 
    fclose (fp);

    if (ep) {
        fprintf (stderr, "Error at line %d: %s\n", lno, ep);
    }
    return (ep != NULL);
}

static int process_lines (struct dirinfo *dip, char *path, char *basename)
{
    int retval;

    retval = process_linpag (dip, 'L', path, basename, "labeled-line.IDs+txt");
    if (retval) return retval;

    return process_linpag (dip, 'P', path, basename, "labeled-page.IDs+txt");
}

static char *mask_wordzones[]  = {"Wrdlist", "Sordex", NULL};
static char *mask_lines[]      = {"Index", NULL};

int main (int argc, char *argv[])
{
    scan_dir (mask_wordzones,  process_wordzones);
    scan_dir (mask_lines,      process_lines);

    return 0;
}

