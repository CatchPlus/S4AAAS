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
#define MONKROOT "/srv/www/htdocs/monk"
#define BUFSIZE 1024
#define MAXCHILDS 16

#define TESTABORT_() { check_stdout (0); exit (0); }
#define TESTABORT() {}

static int do_init = 1;

static sem_t mutex;

static int read_bookdir (char *filename);

static int init (void)
{
    if (!do_init) return 0;

    read_bookdir ("bookdir.lis");

    sem_init (&mutex,1,1);

    do_init = 0;

    return 0;
}

int prefix_match (char *pfx, int lpfx, char *str)
{
    int lstr = strlen (str);

    if (lstr < lpfx) return 0;

    return (memcmp (pfx, str, lpfx) == 0);
}

struct dirinfo {
    char *dir;
    char *dat[3];
};

struct dirinfo *dirinfop = NULL;

int read_bookdir (char *filename)
{
    FILE *fp;
    int adirs = 8;
    int ndirs = 0;
    dirinfop = malloc (adirs * sizeof (struct dirinfo));

    fp = fopen (filename, "r");

    if (fp == NULL) {
        fprintf (stderr, "Unable to open file %s\n", filename);
        exit (1);
    }
    for (;;) {
        char *cp;
        int  i;

        if (ndirs + 1 == adirs) {
            adirs <<= 1;
            struct dirinfo *dip = malloc (adirs * sizeof (struct dirinfo));
            memcpy (dip, dirinfop, ndirs * sizeof (struct dirinfo));
            free (dirinfop);
            dirinfop = dip;
        }
        dirinfop[ndirs].dir = malloc (256);
        if (fgets (dirinfop[ndirs].dir, 256, fp) == NULL) break;

        cp = dirinfop[ndirs].dir;
        i  = 0;
        for (;;) {
            while (*cp != '\0' && *cp != ':' && *cp != '\n') cp++;
            if (i == 3 || *cp != ':') break;
            *cp++ = '\0';
            dirinfop[ndirs].dat[i++] = cp;
        }
        if (i != 3 || (*cp != '\0' && *cp != '\n')) {
            fprintf (stderr, "Bad line %d in file %s\n", ndirs, filename);
            exit (1);
        }
        *cp = '\0';
        ndirs++;
    }
    free (dirinfop[ndirs].dir);
    dirinfop[ndirs].dir = NULL;

    return 0;
}

static int child_cnt = 0;

void wait_childs (int maxchilds, int addcnt)
{
    child_cnt += addcnt;

    while (child_cnt >= maxchilds) {
        pid_t pid;
        int status;

        for (;;) {
            pid = wait (&status);
            if (pid > 0) break;
            if (errno != EINTR) {
                perror ("wait");
                exit (1);
            };
        }
        child_cnt--;
    }
}

static char  *stdout_ptr;
static size_t stdout_siz;

void init_stdout (void)
{
    int fd;

    stdout = open_memstream (&stdout_ptr, &stdout_siz);

    fd = dup (STDOUT_FILENO);
    dup2 (fd, STDOUT_FILENO);
    close (fd);

    fcntl (STDOUT_FILENO, F_SETFL, O_APPEND);
}

char *rec_human (char *str)
{
    if (!strcmp (str, "HUMAN" )) return "H";
    if (!strcmp (str, "JAVA"  )) return "H";
    if (!strcmp (str, "RECOG" )) return "M";
    if (!strcmp (str, "RECOGe")) return "M";
    if (!strcmp (str, "MINED" )) return "M";
    return NULL;
}

void check_stdout (int minflush)
{
    fflush (stdout);

    if (stdout_siz < minflush) return;

    sem_wait (&mutex);
/*
    write (STDOUT_FILENO, "BEGIN\n", 6);
*/
    write (STDOUT_FILENO, stdout_ptr, stdout_siz);
/*
    write (STDOUT_FILENO, "END\n", 4);
*/
    sem_post (&mutex);

    rewind (stdout);
}

/*
 * MONK stores data in directories
 */

int scan_subdir (struct dirinfo *dip, DIR *dirp, char *mask[], char *path1, char *path2, int (*process)(struct dirinfo *, char *, char *), int forkdepth)
{
    struct dirent *dp;
    DIR *sdirp;
    char *path3, *cmask;
    int retval;

    *path2++ = '/';
    cmask = *mask++;

    while ((dp = readdir(dirp))) {
        if (strcmp (dp->d_name, ".")  == 0) continue;
        if (strcmp (dp->d_name, "..") == 0) continue;

        if (cmask == NULL) {
            strcpy (path2, dp->d_name);
            process (dip, path1, path2);
            check_stdout (100);
            continue;
        }
        if (cmask[0] != '\0' && strcmp (dp->d_name, cmask)) continue;

        strcpy (path2, dp->d_name);
        sdirp = opendir (path1);
        if (sdirp == NULL) continue;

        path3 = path2 + strlen (path2);

        if (forkdepth == 0) {
            pid_t pid = fork ();
            
            if (pid == 0) {
                init_stdout ();
                retval = scan_subdir (dip, sdirp, mask, path1, path3, process, forkdepth - 1);
                check_stdout (0);
                exit (retval);
            } else {
                wait_childs (MAXCHILDS, 1);
            }
            retval = 0;
            /* Parent moves on here */
        } else {
            retval = scan_subdir (dip, sdirp, mask, path1, path3, process, forkdepth - 1);
        }

        closedir (sdirp);

        if (retval) return retval;
    }
    return 0;
}

int scan_dir (char *mask[], int (*process)(struct dirinfo *, char *, char *))
{
    DIR *dirp;
    char path[BUFSIZE];
    int retval;

    struct dirinfo *dip;

    init ();

    for (dip = dirinfop; dip->dir; dip++) {
        sprintf (path, "%s/%s", MONKROOT, dip->dir);

        dirp = opendir (path);

        if (dirp == NULL) continue;

        retval = scan_subdir (dip, dirp, mask, path, path + strlen (path), process, 1);

        closedir (dirp);

        if (retval) break;
    }
    wait_childs (1, 0);

    return retval;
}

char *navi_split (char *navid, int split_y)
{
    static char tmp1[BUFSIZE];
    static char tmp2[BUFSIZE];
    static char bufy[BUFSIZE];
    static char *elms[16], **ep;
    char *cp, *up, *rp, ch;
    int sepcnt = 0;

    ep = elms;
    *ep++ = tmp1;
    cp = tmp1;
    up = NULL;
    while (*navid && sepcnt < 14) {
        ch = *navid++;
        if (ch == '_') up = cp;
        if (ch == '-') {
            if (sepcnt == 1) {
                if (up) {
                    *up++ = '\0';
                    *ep++ = up;
                    sepcnt++;
                    ch = '\0';
                    up = NULL;
                }
            } else {
                ch = '\0';
            }
            if (ch == '\0') sepcnt++;
        }
        *cp++ = ch;
        if (ch == '\0') *ep++ = cp;
    }
    *cp++ = '\0';
    if (sepcnt < 4) return NULL;

    if (strcmp (elms[3], "line")) return NULL;

    if (split_y) {
        if (strncmp (elms[5], "y1=", 3) || strncmp (elms[6], "y2=", 3)) return NULL;
        sprintf (bufy, "%s\t%s", elms[5]+3, elms[6]+3);
        rp = "";
    } else {
        if (sepcnt > 8) {
            rp = rec_human (elms[8]);
            if (rp == NULL) return NULL;
        } else {
            rp = "";
        }
        if (strncmp (elms[9], "x=", 2) || strncmp (elms[11], "w=", 2)) return NULL;
        sprintf (bufy, "%s\t%s\t%s\t%s", elms[9]+2, elms[11]+2, rp, (sepcnt >  8 ? elms[8]  : ""));
    }

    sprintf (tmp2, "%s%s-%s\t%s\t%s\t%s", rp, elms[0], elms[1], elms[2], elms[4]
                 , bufy);

    return tmp2;
}

char *stolower (char *cp)
{
    static char buf[BUFSIZE];
    char *bp = buf;

    while (*cp) *bp++ = tolower (*cp++);
    *bp = '\0';

    return buf;
}
