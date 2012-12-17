/*
 * revision History:
 * 20110406 Initial version Rolf Fokkens
 *                          rolf.fokkens@target-holding.nl
 *
 * THIS PROGRAM REQUIRES BETTER ERROR HANDLING
 *
 * It is written as a Proof of Concept in a pragmatic way. This
 * means that error handling has not been taken care of very well!
 *
 * USAGE
 *
 *     ./trie-search2 --trie=<trie> [--substrings=<subs>] [--key=<key>] [--state [=state>]]
 *
 * DESCRIPTION
 *
 * This will search the trie file <trie> for all entries with prefix string
 * <key> if specified by the --key option. If the option --state is specified,
 * all output lines are prefixed by the respective state strings. These state
 * strings can be specified by <state>. If specified the search will (re)start
 * right after the specified <state>.
 * Multiple --key <key> entries may be specified. In this case the results for
 * each <key> will be merged. If multiple <key> are specified, then multiple
 * --state <state> may be specified as well. In this case the order of the
 * --key <key> entries is important and should be the same as the last time.
 *
 */

#define __USE_BSD
#define __USE_POSIX
#define _FILE_OFFSET_BITS 64

#include "trie-util.h"
#include <stdio.h>

#include <unistd.h>
#include <stdlib.h>
#include <string.h>
#include <getopt.h>
#include <stdarg.h>
#include <mcheck.h>
#include <limits.h>

#define WLDLEFT  0x01
#define WLDRIGHT 0x02

/*
void state_dump (struct trie *sp)
{
    void sub_dump (unsigned char *bp, unsigned char *ebp, struct trienode *tnp)
    {
        unsigned char *tp;

        sub_dump (bp, tnp->bp, tnp->par);

        for (tp = bp; tp < tnp->bp; tp++) putchar (*tp == '\t' ? '\t' : ' ');

        if (ebp == NULL) {
            printf ("|%lld", (long long)(tnp->nxtpos));
        } else {
            for (tp = tnp->bp; tp < ebp; tp++) putchar (*tp);
        }
        putchar ('\n');
    }

    sub_dump (sp->data.buf.dat, NULL, sp->tnp);
}
*/

/*
int parse_state (FILE *fpi, struct string *isp, struct trie *triep, int nstates)
{
    struct trienode *tnp, *par;
    struct trie    *sp;
    int             top, ch, kid, len, end;
    uint64_t        root, pos, nxtpos, son;
    unsigned char   *cp;
    unsigned char   *bp  = triep->buf.dat;
    unsigned char   *kp  = triep->key.dat;
    int             klen = triep->key.len;
    int             sibn;

    root = trie_hdr_read (fpi);

    for (;;) {
        kid = strtol ((char *)(isp->dat), (char **)&cp, 10) - 1;
        if (cp == isp->dat || *cp != ':') return 1;
        if (kid < 0 || kid >= nstates) return 1;
        cp++;
        sp  = triep + kid;

        tnp = NULL;
        pos = root;
        
        for (;;) {
            if (pos == 0) return 1;
            par = tnp;

            ch = *cp++;

            if (ch >= '*' && ch <= '-') {
                top = ((ch - '*') & 0x03) << 6;
                ch  = *cp++;
            } else {
                top = 0;
            }
            if (ch < '0') return 1;
            if (ch <= '9') {
                ch -= '0';
            } else {
                if (ch < '@') return 1;
                if (ch <= 'Z') {
                    ch -= '@' - 10;
                } else {
                    if (ch == '_') {
                        ch = 37;
                    } else {
                        if (ch < 'a' || ch > 'z') return 1;
                        ch -= 'a' - 38;
                    }
                }
            }
            ch |= top;
            sibn = ch;
            while (ch--) {
                trie_node_read (fpi, &pos, &len, &end, &son, NULL);
                if (!len) return 1;
                pos = ftell64 (fpi) + len;
            }
            nxtpos = pos;
            trie_node_read (fpi, &nxtpos, &len, &end, &son, bp);

            tnp = calloc (1, sizeof (struct trienode));
            tnp->pos    = pos;
            tnp->nxtpos = nxtpos;
            tnp->par    = par;
            tnp->bp     = bp;
            bp += len;
            if (len > klen) len = klen;
            kp   += len;
            klen -= len;
            tnp->key.dat = kp;
            tnp->key.len = len;
            tnp->sibn    = sibn;

            triep->tnp    = tnp;

            if (*cp == '\0' || *cp == ',') break;

            pos = son;
        }
        if (*cp != ',') break;
        cp++;
    }
    triep->buf.len = bp - triep->buf.dat;

    return (*cp != '\0');
}
*/

/*
void write_state (FILE *of, struct trie *triep)
{
    void sub_print (FILE *of, struct trienode *tnp)
    {
        int sibn;

        if (tnp == NULL) return;

        sub_print (of, tnp->par);

        sibn = tnp->sibn;

        if (sibn > 63) {
            int top;

            top = (sibn >> 6) & 0x03;
            sibn &= 63;
            fprintf (of, "%c", '*' + top);
        }

        sibn += '0';
        if (sibn > '9') sibn += '@' - '9' - 1;
        if (sibn > 'Z') sibn += '_' - 'Z' - 1;
        if (sibn > '_') sibn += 'a' - '_' - 1;

        fprintf (of, "%c", sibn);
    }

    sub_print (of, triep->tnp);
}
*/

static int try_match (int wildcard, struct string *keyp, struct string *fndp)
{
    unsigned char *cp = fndp->dat;
    int           len = fndp->len;

    /* should not happen!! */
    if (len < keyp->len) return 0;

    switch (wildcard) {
    case 0:
       if (keyp->len != len) return 0;
       break;
    case WLDRIGHT:
       break;
    case WLDLEFT:
       cp += len - keyp->len;
       break;
    default:
       return 1;
    }

    return (memcmp (keyp->dat, cp, keyp->len) == 0);
}

static void add_expanded_state
    ( struct trieset *tsetp, int sep
    , struct string *prep, struct string *infp, struct string *sufp)
{
    struct string key;
    unsigned char *t;

    key.len = prep->len + infp->len + sufp->len;
    key.dat = malloc (key.len);
    t = key.dat;

    memcpy (t, prep->dat, prep->len);
    t += prep->len;

    memcpy (t, infp->dat, infp->len);
    t += infp->len;

    memcpy (t, sufp->dat, sufp->len);

    trieset_add (tsetp, &key);

    free (key.dat);
}

void  expand_key (struct string *keyp, struct trieset *tsetp, FILE *fps, int sep)
{
    unsigned char c, *b, *p, *e, *t;
    struct string pre = {0, NULL, 0}, inf = {0, NULL, 0}, suf = {0, NULL, 0};
    struct trie   *subtriep;
    int           phase    = 0;
    int           nsep     = 0;
    int           wildcard = 0;

    b = keyp->dat;
    e = b + keyp->len;

    p = b;
    t = b;

    while (p < e) {
        c = *p++;
        if (c == '(' && phase == 0) {
            pre.dat = b;
            pre.len = t - b;
            phase   = 1;
            b       = t;
            continue;
        }
        if (phase == 1) {
            if (c == ')') {
                inf.dat = b;
                inf.len = t - b;
                phase   = 2;
                b       = t;
                continue;
            }
            if (wildcard & WLDRIGHT) continue;
            if (c == '*') {
                if (t == b) {
                    wildcard |= WLDLEFT;
                } else {
                    wildcard |= WLDRIGHT;
                }
                continue;
            }
        }
        if (c == '\t') {
            nsep++;
            continue;
        }
        if (c == '\\') {
            if (p == e) break;
            c = *p++;
            switch (c) {
            case 't':
                *t++ = '\t';
                nsep++;
                break;
            default:
                *t++ = c;
                break;
            }
            continue;
        }
        *t++ = c;
    }

    /*
     * No substring index specified or no (..XXX..) at all, so
     * the key's just it.
     */
    if (fps == NULL || phase != 2) {
        keyp->len = t - keyp->dat;
        trieset_add (tsetp, keyp);
        return;
    }

    suf.dat = b;
    suf.len = t - b;

    /*
     * If no wildcard at all but still a (XXX) don't bother finding
     * all strings. Just look for the key without the brackets.
     */
    if (wildcard == 0) {
        add_expanded_state (tsetp, sep, &pre, &inf, &suf);
        return;
    }

    /*
     * actually (XXX) means just XXX. Opposed to (*XXX*) which
     * means any word where XXX is a substring.
     *
     * So doing the subtriep loop is silly, but that's something
     * to be optimized later.
     */

    subtriep = trie_init (1, fps, &inf, '\t', '\t', 3);
    for (;;) {
        struct triedata *tdp;

        tdp = trie_next (subtriep);

        if (tdp == NULL) break;

        if (tdp->nflds == 2) {
            struct string fnd;

            fnd.dat = tdp->fldsp[1];
            fnd.len = tdp->buf.dat + tdp->buf.len - fnd.dat;

            if (try_match (wildcard, &inf, &fnd)) {
                add_expanded_state (tsetp, sep, &pre, &fnd, &suf);
            }
        }
    }
    trie_cleanup (subtriep);
}

struct rule_elm {
    int elm;
    struct string pattern;
    int wildcard;
};

struct parsed_rule {
    int type;
    int nelms;
    struct rule_elm *elmsp;
};

void parse_rules (char sep, int nrules, struct string *rulesp, struct parsed_rule **parsedp)
{
    struct rule_elm *elmsp;
    int nelms;
    int aelms;
    struct parsed_rule *parsp, *pp;;
   
    parsp = calloc (nrules, sizeof (struct parsed_rule));
    pp    = parsp;
 
    while (nrules--) {
        unsigned char *dat, *cp, *bp, *dp;
        int           len, elmct;
        int           wildcard;

        aelms = 4;
        nelms = 0;
        elmsp = calloc (aelms, sizeof (struct rule_elm));

        dat = rulesp->dat;
        len = rulesp->len;

        cp = dat;
        bp = cp;
        dp = cp;

        elmct    = 0;
        wildcard = 0;

        while (len) {
            unsigned char ch;

            ch = *cp++;
            len--;
            if (ch == '*') {
                if (dp == bp) {
                    wildcard |= WLDLEFT;
                } else {
                    wildcard |= WLDRIGHT;
                }
            } else {
                if (ch == '\\' && len) {
                    ch = *cp++;
                    len--;
                    if (ch == 't') ch = '\t';
                }
                if (ch != sep && !(wildcard & 0x02)) *dp++ = ch;
            }
            if (ch == sep || !len) {
                int ln = dp - bp;

                if (ln || !wildcard) {
                    struct rule_elm *tmp;

                    if (nelms == aelms) {
                        tmp = calloc (aelms * 2, sizeof (struct rule_elm));
                        memcpy (tmp, elmsp, aelms * sizeof (struct rule_elm));
                        free (elmsp);
                        elmsp  = tmp;
                        aelms *= 2;
                    }
                    tmp = elmsp + nelms++;
                    tmp->elm = elmct;
                    tmp->pattern.len = ln;
                    tmp->pattern.dat = malloc (ln);
                    memcpy (tmp->pattern.dat, bp, ln);
                    tmp->wildcard = wildcard;
                }
                
                bp = cp;
                dp = cp;
                wildcard = 0;

                elmct++;
            }
        }
        pp->type  = rulesp->tag;
        pp->nelms = nelms;
        pp->elmsp = elmsp;

        pp++;
        rulesp++;
    }

    *parsedp = parsp;
};

static int match_rules (struct triedata *tdatp, int nrules, struct parsed_rule *parsedp)
{
    unsigned char *buf    = tdatp->buf.dat;
    int           len     = tdatp->buf.len;
    unsigned char **fldsp = tdatp->fldsp;
    int           nflds   = tdatp->nflds;

    while (nrules--) {
        int             nelms  = parsedp->nelms;
        struct rule_elm *elmsp = parsedp->elmsp;

        while (nelms) {
            int cmp, tlen;
            int elm = elmsp->elm;

            if (elm >= nflds) break;

            tlen =   (elm == nflds - 1 ? buf + len : fldsp[elm + 1] - 1)
                   - fldsp[elm];
            if (elmsp->pattern.len != tlen) break;

            cmp = memcmp (fldsp[elm], elmsp->pattern.dat, tlen);
            if (cmp) break;

            elmsp++;
            nelms--;
        }
        if (nelms == 0) return (parsedp->type == 'a');
        parsedp++;
    }
    return 1;
};

static void add_string (int tag, char *string, struct string **stringsp, int *nstringsp, int *astringsp)
{
    struct string *sp;

    if (*stringsp == NULL) {
        *astringsp = 16;
        *stringsp  = calloc (*astringsp, sizeof (struct string));
        *nstringsp = 0;
    }

    if (*nstringsp == *astringsp) {
        sp = calloc (2 * *astringsp, sizeof (struct string));
        memcpy (sp, *stringsp, *astringsp * sizeof (struct string));
        *astringsp *= 2;
        cfree (*stringsp);
        *stringsp = sp;
    }

    sp = (*stringsp) + (*nstringsp)++;

    sp->tag = tag;
    sp->len = strlen (string);
    sp->dat = malloc (sp->len + 1);
    memcpy (sp->dat, string, sp->len);
    sp->dat[sp->len] = '\0';
}

static void error (char *argv0, const char *fmt, ...)
{
    va_list ap;

    va_start (ap, fmt);
    vfprintf (stderr, fmt, ap);
    va_end (ap);

    fprintf (stderr, "\n\n"
"Usage: %s --trie=<trie> [--key=<key>] [--keyoninput] [--separator=<sep>] "
"[--substrings=<subs>] [--state[=<state>]]\n\n"
"This will search the trie file <trie> for all entries with prefix string\n"
"<key> if specified by the --key option. If the option --state is specified,\n"
"all output lines are prefixed by the respective state strings. These state\n"
"strings can be specified by <state>. If specified the search will (re)start\n"
"right after the specified <state>\n"
"\n"
"Multiple --key=<key> entries may be specified. In this case the results\n"
"for each <key> will be merged\n"
"If multiple <key> are specified, then multiple --state=<state> may also\n"
"be specified. In this case the order of the --key=<key> entries is\n"
"important and should be the same as the last time\n"
"As an alternative to the --key option the --keyoninput may als be specified.\n"
"In this case the key(s) will be read from standard input. Like with the\n"
"--key option the order of the keys is important if combined with --state\n"
"If --substitude=<subs> is specified keys specified by --keys are treated\n"
"as substrings based on the substring definitions in the <subs> trie file.\n"
, argv0);

    exit (1);
}

int main (int argc, char *argv[])
{
    FILE *fpi, *fps = NULL;
    char *buf;
/*
    struct string *isp;
*/
    int  i;
    char *trie              = NULL;
    char *subs              = NULL;
    char *sepp              = NULL;
    int  sepcnt             = -1;
    int  limit              = -1;
    int  offset             = -1;
    int  count              = 0;
    int  pstate             = 0;
    int  keyoninput         = 0;
    struct string *keysp    = NULL;
    int  nkeys              = 0;
    int  akeys              = 0;
    struct string *rulesp   = NULL;
    int  nrules             = 0;
    int  arules             = 0;
/*
    struct string *istatesp = NULL;
    int  nistates           = 0;
    int  aistates           = 0;
*/
    struct trieset *tsetp;
/*
    struct trie *statesp   = NULL;
    int nstates             = 0;
    int statesalloc         = 0;
*/
    struct parsed_rule *parsedp;
    int outcnt;

    for (;;) {
        int option_index = 0;
        int c;
        static struct option long_options[] = {
            {"state",      optional_argument, 0, 's'},
            {"trie",       required_argument, 0, 't'},
            {"key",        required_argument, 0, 'k'},
            {"keyoninput", no_argument,       0, 'i'},
            {"substrings", required_argument, 0, 'S'},
            {"separator",  required_argument, 0, 'p'},
            {"mergeskip",  required_argument, 0, 'M'},
            {"accept",     required_argument, 0, 'a'},
            {"reject",     required_argument, 0, 'r'},
            {"offset",     required_argument, 0, 'o'},
            {"limit",      required_argument, 0, 'l'},
            {"count",      no_argument,       0, 'c'},
            {0, 0, 0, 0}
        };

        c = getopt_long(argc, argv, "s::t:k:iS:p:M:",
                        long_options, &option_index);
        if (c == -1)
            break;

        switch (c) {
        case 's':
/*
            if (optarg) add_string (0, optarg, &istatesp, &nistates, &aistates);
*/
            pstate = 1;
            break;
        case 't':
            trie = optarg;
            break;
        case 'k':
            add_string (0, optarg, &keysp, &nkeys, &akeys);
            break;
        case 'i':
            keyoninput = 1;
            break;
        case 'S':
            subs = optarg;
            break;
        case 'p':
            if (sepp) {
                error (argv[0], "Multiple argument --separator");
            }
            sepp = optarg;
            break;
        case 'M':
            if (sepcnt != -1) {
                error (argv[0], "Multiple argument --mergeskip");
            }
            sepcnt = atol (optarg);
            if (sepcnt < 0) {
                error (argv[0], "Bad value for --mergeskip");
            }
            break;
        case 'a':
            add_string ('a', optarg, &rulesp, &nrules, &arules);
            break;
        case 'r':
            add_string ('r', optarg, &rulesp, &nrules, &arules);
            break;
        case 'o':
            if (offset != -1) {
                error (argv[0], "Multiple argument --offset");
            }
            offset = atol (optarg);
            if (offset < 0) {
                error (argv[0], "Bad value for --offset");
            }
            break;
        case 'l':
            if (limit != -1) {
                error (argv[0], "Multiple argument --limit");
            }
            limit = atol (optarg);
            if (limit < 1) {
                error (argv[0], "Bad value for --limit");
            }
            break;
        case 'c':
            count = 1;
            break;
        default:
            printf("?? getopt returned character code 0%o ??\n", c);
        }
    }

    if (optind < argc) error (argv[0], "Excess argument");

    if (trie == NULL)  error (argv[0], "Missing argument --trie");

    if (nkeys && keyoninput) {
        error (argv[0], "Contradictive options --key and --keyoninput");
    }
    if (count && offset > -1) {
        error (argv[0], "Contradictive options --count and --offset");
    }
    limit = ( limit > -1 
            ? limit + (offset > -1 ? offset : 0)
            : INT_MAX
            );

    if (sepp == NULL || sepp[0] == '\0') sepp = "\t";

    if (sepp[1] != '\0') {
        error (argv[0], "Only single character allowed --separator");
    }

    parse_rules (sepp[0], nrules, rulesp, &parsedp);

    fpi = fopen (trie, "r");
    if (fpi == NULL) {
        error (argv[0], "Unable to open file \"%s\"", trie);
    }

    buf = malloc (4096);
    setbuffer (fpi, buf, 4096);

    if (subs) {
        fps = fopen (subs, "r");
        if (fps == NULL) {
            error (argv[0], "Unable to open file \"%s\"", subs);
        }
    }

    if (keyoninput) {
        char buffer[1024];
        int  len;

        while (fgets (buffer, sizeof (buffer), stdin)) {
            len = strlen (buffer);
            if (len < 2) continue;
            buffer[len - 1] = '\0';
            add_string (0, buffer, &keysp, &nkeys, &akeys);
        }
    }

    if (sepcnt == -1) sepcnt = 1;

    tsetp = trieset_init (fpi, sepp[0], sepcnt);

    for (i = 0; i < nkeys; i++) {
        expand_key (keysp + i, tsetp, fps, sepp[0]);
    }

    outcnt = 0;
    for (;;) {
        struct string   buf;
        int             id;
        struct triedata *tdp;

        tdp = trieset_data (tsetp);

        if (tdp == NULL) break;

        id    = tdp->id;
        buf   = tdp->buf;

        if (pstate) {
            printf ("%d\t", id);
/*
            printf ("%d:", (int) (sp - statesp) + 1);
            write_state (stdout, sp);
*/
            fputc ('\t', stdout);
        }
        if (match_rules (tdp, nrules, parsedp)) {
            if (!count && outcnt >= offset) {
                fwrite (buf.dat, 1, buf.len, stdout);
                fputc ('\n', stdout);
            }
            outcnt++;
            if (outcnt >= limit) break;
        }
        trieset_next (tsetp);
    }

    if (count) printf ("%d\n", outcnt);

    return 0;
}
