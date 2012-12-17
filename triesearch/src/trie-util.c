/* tree-num.c
 *
 * revision History:
 * 20110527 Included higher Rolf Fokkens
 *          level functions rolf.fokkens@target-holding.nl
 * 20110406 Initial version Rolf Fokkens
 *                          rolf.fokkens@target-holding.nl
 *
 * THIS PROGRAM REQUIRES BETTER ERROR HANDLING
 *
 * It is was written as a Proof of Concept in a pragmatic way. This
 * means that error handling has not been taken care of very well!
 */

#define _FILE_OFFSET_BITS 64

#include "trie-util.h"

#include <unistd.h>
#include <stdlib.h>
#include <malloc.h>
#include <sys/types.h>
#include <sys/wait.h>

#define __USE_GNU
#include <string.h>

int fseek64 (FILE *stream, uint64_t offset, int whence)
{
    return fseeko (stream, (off_t)offset, whence);
}

uint64_t ftell64 (FILE *stream)
{
    return (uint64_t) ftello (stream);
}

/*
 * Numbers inside trie files are variable length encoded. The first byte
 * determines the number of total bytes and the number of stored bits. This is
 * actually determined by the highest bits, which allows the lower bits of
 * the first byte to store data too.
 *
 * First byte    Size   Data size
 *            (bytes)      (bits)
 * 0... ....        1  7 (7 +  0)
 * 10.. ....        2 14 (6 +  8)
 * 110. ....        3 21 (5 + 16)
 * 1110 ....        4 28 (4 + 24)
 * 1111 0...        5 35 (3 + 32)
 * 1111 10..        6 42 (2 + 40)
 * 1111 1100        7 48 (0 + 48)
 * 1111 1101        8 56 (0 + 56)
 * 1111 1110        9 64 (0 + 64)
 */

struct thold {
    u_int64_t top;
    char code, mask, sign;
    int bytes;
};

static struct thold tholds[] = { {0x0000000000000040LL, 0x00, 0x80, 0x40, 1}
                               , {0x0000000000002000LL, 0x80, 0xc0, 0x20, 2}
                               , {0x0000000000100000LL, 0xc0, 0xe0, 0x10, 3}
                               , {0x0000000008000000LL, 0xe0, 0xf0, 0x08, 4}
                               , {0x0000000400000000LL, 0xf0, 0xf8, 0x04, 5}
                               , {0x0000020000000000LL, 0xf8, 0xfc, 0x02, 6}
                               , {0x0000800000000000LL, 0xfc, 0xff, 0x00, 7}
                               , {0x0080000000000000LL, 0xfd, 0xff, 0x00, 8}
                               , {0x0000000000000000LL, 0xfe, 0xff, 0x00, 9}
                               };

uint64_t trie_hdr_read (FILE *fp, int *versionp, int *featp)
{
    uint64_t hdr, root;
    int version, features;

    fseek64 (fp, 0, SEEK_SET);
    fread (&hdr, sizeof (hdr), 1, fp);

    if (hdr & TRIE_HDRBIT) {
        version  = (hdr & TRIE_VERMASK) >> 32;
        features = hdr & TRIE_OPT_MASK;
        fread (&root, sizeof (root), 1, fp);
    } else {
        version  = 0;
        root     = hdr;
        features = 0;
    }

    if (version > TRIE_VERSION) {
        fprintf (stderr, "ERROR: bad version %d\n", version);
        exit (1);
    }

    *versionp = version;
    *featp    = features;

    return root;
}

static int __trie_num_write_init (int64_t num, char **ret);

static int (*trie_num_writep)(int64_t num, char **ret) = __trie_num_write_init;

static int __trie_num_write (int64_t num, char **ret)
{
    int neg, abs, i;
    char *bp;
    static char buf[9];
    struct thold *tp = tholds;

    neg = (num < 0);
    abs = (neg ? ~num : num);

    while (tp->bytes < 9 && abs >= tp->top) tp++;

    bp = buf + 9;
    for (i = tp->bytes - 1; i ; i--) {
        *--bp = num & 0x0ff;
        num = num >> 8;
    }
    *--bp = (num & ~tp->mask) | tp->code;
    *ret  = bp;

    return tp->bytes;
}

#define TNWTAB_RANGE  1023
#define TNWTAB_SIZE   (1 + 2 * TNWTAB_RANGE)
#define TNWTAB_OFFS   TNWTAB_RANGE
#define TNWTAB_MALLOC (9 * TNWTAB_SIZE)

static char **__trie_num_write_table = NULL;
static char *__trie_num_write_malloc = NULL;

static int __trie_num_write_lookup (int64_t num, char **ret)
{
    char *cp;

    if (num >= -TNWTAB_RANGE && num <= TNWTAB_RANGE) {
        cp = __trie_num_write_table [num + TNWTAB_RANGE];
        *ret = cp + 1;
        return cp[0];
    }

    return __trie_num_write (num, ret);
}

static int __trie_num_write_init (int64_t num, char **ret)
{
    char **tabp, *bp, *tp, *malp;
    int  i, len;

    tabp                    = malloc (TNWTAB_SIZE * sizeof (char *));
    __trie_num_write_table  = tabp;

    malp                    = malloc (TNWTAB_MALLOC);
    __trie_num_write_malloc = malp;

    for (i = -TNWTAB_RANGE; i <= TNWTAB_RANGE; i++) {
        len = __trie_num_write (i, &bp);
        tp = malp;
        tp[0] = len;
        memcpy (tp + 1, bp, len);
        *tabp++ = tp;
        malp   += (1 + len);
    }

    trie_num_writep = __trie_num_write_lookup;

    return trie_num_writep (num, ret);
}

static int trie_num_write (char **bufp, int64_t num)
{
    char *bp;
    int len;

    len = trie_num_writep (num, &bp);

    memcpy (*bufp, bp, len);
    (*bufp) += len;
/*
    fwrite (bp, len, 1, fp);
*/
    return len;
}

static int trie_num_read (FILE *fp, int64_t *num)
{
    int i, ch, mask, elm;
    long retval, tmp;

    ch = fgetc (fp);

    if (ch == EOF) {
        exit (1);
    }
    ch = ch & 0x0ff;

    mask = 0x80;
    elm  = 0;

    while (mask & ch) {
        mask >>= 1;
        elm++;
    }
    if (mask < 0x04) {
        elm = 7 + (mask & 3);
    }
  
    retval = ch & ((tholds[elm].sign << 1) - 1);
 
    for (i = 1; i < tholds[elm].bytes; i++) {
        ch = fgetc (fp);
        retval = retval << 8 | (ch & 0x0ff);
    }

    tmp = tholds[elm].top;

    if (tmp & retval) {
        retval |= ~(tmp - 1);
    }

    *num = retval;

    return 0;
}

int trie_node_read (FILE *fp, int features, uint64_t *refposp, uint64_t *posp, uint64_t *cntp, int *lenp, int *endp, uint64_t *sonp, unsigned char *bp)
{
    int64_t  tmp;
    uint64_t refpos, pos, son;
    int      end, len, cnt;

    refpos = *refposp;
    pos    = *posp;

    if (pos != ftell (fp)) {
        fseek64 (fp, pos, SEEK_SET);
    }

    trie_num_read (fp, &tmp);
    if (tmp == 0) {
        *lenp = 0;
        return 0;
    }

    end = (tmp < 0);
    len = (tmp < 0 ? -tmp : tmp);

    trie_num_read (fp, &tmp);
    son = (tmp ? refpos + tmp : 0);

    if (features & TRIE_OPT_COUNT) {
        if (son) {
            trie_num_read (fp, &tmp);
            cnt = tmp;
        } else {
            cnt = (end ? 1 : 0);
        }
    } else {
        cnt = 0;
    }

    if (bp == NULL) {
        pos = ftell (fp) + len;
    } else {
        fread (bp, len, 1, fp);
        pos = ftell (fp);
    }

    if (features & TRIE_OPT_RELCHLD) {
        if (son) refpos = son;
    } else {
        refpos = pos;
    }

    *refposp = refpos;
    *posp    = pos;
    *cntp    = cnt;
    *lenp    = len;
    *endp    = end;
    *sonp    = son;

    return 0;
}

int trie_node_write (FILE *fp, int features, uint64_t *refposp, uint64_t *posp, uint64_t cnt, int len, int end, uint64_t son, unsigned char *datp)
{
    /* Be careful: make sure the #trie_num_write's */
    /* fits. Currently max 3 trie_nums are written */
    static char buf[3*9];
    char        *bp = buf;
    uint64_t    refpos, pos;

    if (len == 0) {
        trie_num_write (&bp, 0);
        fwrite (buf, bp - buf, 1, fp);
        return 0;
    }

    refpos = *refposp;

    trie_num_write (&bp, (end ? -1 : 1) * len);
    trie_num_write (&bp, (son ? son - refpos : 0));

    /* if no sons no sense in counting, since */
    /* the count is always 1 if (end)         */
    if (features & TRIE_OPT_COUNT) {
        if (son) {
            trie_num_write (&bp, cnt);
        }
    }
    fwrite (buf, bp - buf, 1, fp);
    fwrite (datp, 1, len, fp);

    pos = ftell (fp);

    if (features & TRIE_OPT_RELCHLD) {
        if (son) refpos = son;
    } else {
        refpos = pos;
    }

    *refposp = refpos;
    *posp    = pos;

    return 0;
}

/*****************************************************************************
 *                                                                           *
 *                      Higher level trie stuff                              *
 *                                                                           *
 *****************************************************************************/

/*
 * This is all about tracking the state of a trie that's currenty open, i.e.
 * is being searched.
 *
 * The following structs build up the trie:
 * struct trienode
 * struct trie
 *
 * Every trie is represented by a "struct trie", which points to a linked list
 * of "struct trienode". Each trienode points to it's parent trienode until
 * the root trienode which refers to NULL.
 * So the trienode pointed to by "struct trie" is a leaf-node.
 *
 * Only "struct trie" is shared with the outside of this program, without the
 * internal detauls. "struct trienode" is completely hidden.
 */
struct trienode {
    struct trienode *par;
    uint64_t        pos;
    uint64_t        refpos;
    uint64_t        nxtpos;
    unsigned char   *bp;
    struct string   key;
    int             sibn;

    int             nflds;
};

/*
 * "struct triedata" is data of a "struct trie" that is relevant to the trie
 * users. A more detailed description can be found in trie-util.h
 */
struct trie {
    FILE            *fp;
    int             features;
    struct trienode *tnp;
    struct triedata data;
    struct string   key;
    int             alloclen;
    int             sep;

    int             aflds;
};

/*
 * Several keys may be searched in parrallel in the same trie, for example
 * when searching wildcards. The wildcards are expanded to specific key
 * values to be searched in parrallel.
 *
 * This parrallel search is represented by a set of "struct tries". This set
 * of tries is ordered in the sense that triesp[0] always is the trie with
 * the "lowest" result.
 *
 * This ordering is based on a binary heap:
 *     http://en.wikipedia.org/wiki/Binary_heap
 */
struct trieset {
    /* an array with tries */
    struct trie **triesp;
    /* actual # of tries (states) */
    int         ntries;
    /* array size */
    int         atries;
    /* trie file */
    FILE        *fp;

    int         sep;
    int         sepcnt;
};

/*
 * parse_seps () maintains the fields (fldsp) for a trie. It's called
 * whenever a new trienode is visited, needing only to process the
 * data in the trienode to maintain the fields.
 */
static void parse_seps
    (struct trie *triep, struct trienode *tnp, int len, int sep)
{
    int           fnd;
    unsigned char *cp, *t;
    unsigned char **fp;

    triep->data.nflds -= tnp->nflds;
    tnp->nflds         = 0;

    fnd = triep->data.nflds;
    cp  = tnp->bp;
    fp  = triep->data.fldsp + fnd;

    while (fnd < triep->aflds) {
        t = memchr (cp, sep, len);
        if (t == NULL) break;
        t++;
        *fp++ = t;
        len  -= t - cp;
        cp    = t;
        fnd++;
    }

    tnp->nflds        = fnd - triep->data.nflds;
    triep->data.nflds = fnd;

    return;
}

/*
 * trie_next () finds the next entry in a trie.
 *    fpi   is the File Pointer to the index file.
 *    triep points to the trie.
 */

struct triedata *trie_next (struct trie *triep)
{
    struct trienode *tnp, *tmp;
    int tlen, len, cmp, end, buflen;
    uint64_t son, nxtpos, cnt;

    unsigned char *bufp  = triep->data.buf.dat;
    unsigned char *keyp  = triep->key.dat;
    int           keylen = triep->key.len;
    int           version;

    tnp = triep->tnp;

    if (tnp == NULL) {
        tnp = calloc (1, sizeof (struct trienode));
/*
        fseek64 (triep->fp, 0, SEEK_SET);
        fread (&(tnp->nxtpos), sizeof (tnp->nxtpos), 1, triep->fp);
*/
        tnp->nxtpos  = trie_hdr_read (triep->fp, &version, &(triep->features));
        tnp->refpos  = tnp->nxtpos;
        tnp->bp      = bufp;
        tnp->key.dat = keyp;
        tnp->key.len = keylen;
        tnp->sibn    = -1;
        tnp->par     = NULL;
        tnp->nflds   = 0;
    }

    while (tnp != NULL) {
        nxtpos   = tnp->nxtpos;
        tnp->pos = nxtpos;

        trie_node_read (triep->fp, triep->features, &(tnp->refpos), &nxtpos
                       , &cnt, &len, &end, &son, tnp->bp);
        if (!len) {
            triep->data.nflds -= tnp->nflds;
            tmp                = tnp->par;
            free (tnp);
            tnp                = tmp;
            continue;
        }

        tnp->nxtpos = nxtpos;
        tnp->sibn++;

        parse_seps (triep, tnp, len, triep->sep);

        tlen = (tnp->key.len < len ? tnp->key.len : len);
        cmp = memcmp (tnp->bp, tnp->key.dat, tlen);

        if (cmp > 0) break;
        if (cmp == 0) {
            buflen = tnp->bp + len - bufp;
            if (son) {
                tmp = calloc (1, sizeof (struct trienode));
                tmp->par     = tnp;
                tmp->nxtpos  = son;
                tmp->refpos  = son;
                tmp->bp      = tnp->bp      + len;
                tmp->key.dat = tnp->key.dat + tlen;
                tmp->key.len = tnp->key.len - tlen;
                tmp->sibn    = -1;
                tmp->nflds   = 0;

                tnp  = tmp;
            }
            if (buflen >= keylen && end) {
                triep->tnp          = tnp;
                triep->data.buf.len = buflen;

                return &(triep->data);
            }
        }
    }
    while (tnp) {
        tmp = tnp->par;
        free (tnp);
        tnp = tmp;
    }
    triep->tnp          = tnp;
    triep->data.buf.len = 0;

    return NULL;
}

/*
 * trie_init () returns an initialized struct trie:
 *    id:     the id for this trie, any number you like
 *    fp:     the open file for this trie
 *    key:    the key to be found
 *    sep:    the character to separate the fields
 *    endsep: the optional terminating separator to be added to key.
 *            it will be ignored when -1.
 *    aflds:  the maximum number of fields to be identified
 */
struct trie *trie_init
    (int id, FILE *fp, struct string *key, int sep, int endsep, int aflds)
{
    struct trie *triep;
    int         tlen;

    triep = calloc (1, sizeof (struct trie));

    triep->data.id  = id;
    triep->fp       = fp;
    triep->features = 0;
/*
    if (triep->key.dat) free (triep->key.dat);
*/
    tlen = key->len;

    if (endsep != -1) tlen++;

    triep->sep    = sep;

    triep->key.dat = (unsigned char *) malloc (tlen + 1);
    memcpy (triep->key.dat, key->dat, key->len);

    if (endsep != -1) triep->key.dat[key->len] = endsep;

    triep->key.len = tlen;
/*
    if (triep->data.buf.dat == NULL) {
        triep->alloclen      = 2048;
        triep->data.buf.dat  = malloc (triep->alloclen);
    }
*/
    triep->alloclen      = 2048;
    triep->data.buf.dat  = malloc (triep->alloclen);
/*
    if (triep->tnp) {
        struct trienode *tnp, *tmp;
        tnp = triep->tnp;
        while (tnp) {
            tmp = tnp->par;
            free (tnp);
            tnp = tmp;
        }
        triep->tnp    = NULL;
    }
*/
    triep->tnp    = NULL;
/*
    if (triep->data.fldsp == NULL) {
        triep->aflds      = aflds;
        triep->data.fldsp = calloc (aflds, sizeof (unsigned char **));
    }
*/
    triep->aflds      = aflds;
    triep->data.fldsp = calloc (aflds, sizeof (unsigned char **));

    triep->data.nflds    = 1;
    triep->data.fldsp[0] = triep->data.buf.dat;

    return triep;
}

/*
 *  * trie_cleanup () cleans up the (memory) mess after using triep
 *   */
void trie_cleanup (struct trie *triep)
{
    struct trienode *tnp, *tmp;

    free (triep->key.dat);
    free (triep->data.buf.dat);

    tnp = triep->tnp;
    while (tnp) {
        tmp = tnp->par;
        free (tnp);
        tnp = tmp;
    }

    free (triep->data.fldsp);

    free (triep);
}

/*
 * trieset_init () creates a "struct trieset":
 *    fp:     the file pointer to the index file
 *    sep:    the character to separate the fields
 *    sepcnt: the #fields to be skip during merging of the results
 */
struct trieset *trieset_init (FILE *fp, int sep, int sepcnt)
{
    struct trieset *setp;

    setp = calloc (1, sizeof (struct trieset));

    setp->triesp = calloc (16, sizeof (struct trie *));
    setp->atries = 16;
    setp->ntries = 0;
    setp->fp     = fp;
    setp->sep    = sep;
    setp->sepcnt = sepcnt;

    return setp;
}
/*
 * cmpstr () compares strings s1 and s2 with resp. lengths l1 and l2.
 *
 * If s1 <  s2 cmpstr will return a value < 0
 * If s1 == s2 cmpstr will return 0
 * If s1 >  s2 cmpstr will return a value > 0
 */
static int cmpstr (unsigned char *s1, int l1, unsigned char *s2, int l2)
{
    int l, cmp;

    l = (l1 < l2 ? l1 : l2);
    cmp = (l ? memcmp (s1, s2, l) : 0);
    if (cmp) return cmp;
    if (l1 == l2) return 0;
    return (l1 < l2 ? -1 : 1);
}

/*
 * cmptries () compares the results of tries sp1 and sp2 starting from
 * field #sepcnt.
 *
 * If sp1 <  sp2 cmptrie will return a value < 0
 * If sp1 == sp2 cmptrie will return 0
 * If sp1 >  sp2 cmptrie will return a value > 0
 */
static int cmptrie (struct trie *sp1, struct trie *sp2, int sepcnt)
{
    unsigned char *cp1, *cp2;
    int ln1, ln2;

    if (sepcnt >= sp1->data.nflds) {
       if (sepcnt >= sp2->data.nflds) return 0;
       return -1;
    }
    if (sepcnt >= sp2->data.nflds) return 1;

    cp1 = sp1->data.fldsp[sepcnt];
    cp2 = sp2->data.fldsp[sepcnt];
    ln1 = sp1->data.buf.dat + sp1->data.buf.len - cp1;
    ln2 = sp2->data.buf.dat + sp2->data.buf.len - cp2;

    return cmpstr (cp1, ln1, cp2, ln2);
}

/*
 * The ordering of the tries in a trieset is based on a binary heap:
 *     http://en.wikipedia.org/wiki/Binary_heap
 *
 * This trieset orders the tries in a way that guarantees that triesp[0] is
 * always the trie with the "lowest" (current) result.
 *
 * A binary heap is a balanced binary trie which always holds the condition
 * that value (child) < value (parent), and each child (at most) has two
 * parents. There's only one node that is not a parent: the root node. And for
 * that node its value is smaller than any other node in the heap.
 *
 * The heap is represented by an array heap[1..N]. The child - parent relation
 * is defined like this:
 *
 *    child(i)   = int (i / 2) for 1 < i <= N, where i is a node in the heap.
 *
 * Or:
 *
 *    parent1(i) = i*2         for 1 < i*2     <= N
 *    parent2(i) = i*2 + 1     for 1 < i*2 + 1 <= N
 *
 */

/*
 * Of course we don't start our numbering with 1 but with 0 :-)
 * So heap_child and heap_parent help us to deal with that.
 */
static inline int heap_child (int node)
{
    return ((node + 1) >> 1) - 1;
}

static inline int heap_parent (int node)
{
    return ((node + 1) << 1) - 1;
}

inline void swap_tries (struct trie **sp1, struct trie **sp2)
{
    struct trie *tmp;

    tmp = *sp1;
    *sp1 = *sp2;
    *sp2 = tmp;
}

/*
 * heap_pushdown () pushes node node down in the heap until the order
 * inside the heap is restored
 */
static void heap_pushdown (struct trieset *tsetp, int node)
{
    int         child;
    struct trie **triesp;

    triesp = tsetp->triesp;

    while (node) {
        child = heap_child (node);
        if (cmptrie (triesp[child], triesp[node], tsetp->sepcnt) <= 0) {
            break;
        }
        swap_tries (triesp + child, triesp + node);
        node = child;
    }
}

/*
 * heap_pullup () pulls node node up in the heap until the order
 * inside the heap is restored
 */
static void heap_pullup (struct trieset *tsetp, int node)
{
    int         par, ntries;
    struct trie **triesp;

    triesp = tsetp->triesp;
    ntries = tsetp->ntries;

    for (;;) {
        par = heap_parent (node);
        if (par < ntries - 1) {
            if (cmptrie (triesp[par], triesp[par + 1], tsetp->sepcnt) > 0) {
                par++;
            }
        } else {
            if (par >= ntries) break;
        }
        if (cmptrie (triesp[node], triesp[par], tsetp->sepcnt) <= 0) break;
        swap_tries (triesp + node, triesp + par);
        node = par;
    }
}

/*
 * trieset_add () adds a trie to a trieset:
 *    tsetp: The "struct trieset" to which a trie is added
 *    key:   The search key for the new trie
 */
void trieset_add (struct trieset *tsetp, struct string *key)
{
    struct triedata *tdp;

    /* if (tsetp->triesp == NULL) ERROR!!! */

    if (tsetp->ntries == tsetp->atries) {
        /* We need a bigger heap. Double it */

        struct trie **tmp;

        tmp = calloc (2 * tsetp->atries, sizeof (struct trie *));
        memcpy (tmp, tsetp->triesp, tsetp->atries * sizeof (struct trie *));
        tsetp->atries *= 2;
        free (tsetp->triesp);
        tsetp->triesp = tmp;
    }

    tsetp->triesp[tsetp->ntries] =
        trie_init (tsetp->ntries, tsetp->fp, key, tsetp->sep, -1, 16);

    tdp = trie_next (tsetp->triesp[tsetp->ntries]);

    if (tdp == NULL) {
        /* dead end, ignore it right away! */
        trie_cleanup (tsetp->triesp[tsetp->ntries]);
        return;
    }

    tsetp->ntries++;

    /* Order the heap */
    heap_pushdown (tsetp, tsetp->ntries - 1);
}

/*
 * trie_data () returns the "struct triedata" of the trie triep.
 */
struct triedata *trie_data (struct trie *triep)
{
    if (triep->tnp == NULL) return NULL;

    return &(triep->data);
}

/*
 * trieset_data () returns the "struct triedata" of the "lowest" trie
 * in trieset triep.
 */
struct triedata *trieset_data (struct trieset *tsetp)
{
    if (tsetp->ntries == 0) return NULL;

    return trie_data (tsetp->triesp[0]);
}

/*
 * trieset_next () moves to the next search result for the "lowest" trie
 * in trieset triep.
 */
int trieset_next (struct trieset *tsetp)
{
    struct trie     **trp;
    struct triedata *tdp;

    if (tsetp->ntries == 0) return 0;

    trp = tsetp->triesp;

    for (;;) {
        tdp = trie_next (trp[0]);
        if (tdp == NULL) {
            /* This one is done, never wanna see it again */
            trie_cleanup (trp[0]);
            tsetp->ntries--;

            /* Noting left: stop */
            if (tsetp->ntries == 0) return 0;

            /* Put the last one front row */
            trp[0] = trp[tsetp->ntries];
        }
        /* restore the order */
        heap_pullup (tsetp, 0);

        /* if the next one to deliver is different, return this one */
        if (tsetp->ntries == 1 || cmptrie (trp[0], trp[1], tsetp->sepcnt)) {
            /* We never deliver duplicates!! */
            return 1;
        }
        /* Duplicate, so try again */
    }
    /* Empty trieset */
    return 0;
}

/*****************************************************************************
 *                                                                           *
 *                     Quicksort/Mergesort stuff                             *
 *                                                                           *
 *****************************************************************************/

struct sort_tmpfile {
    FILE          *fp;
    char          *name;
    unsigned char *buf;
};

struct sortstate {
    int            ntmp;
    struct sort_tmpfile *tmpp;
};

static int lcompar (const void *lp1, const void *lp2)
{
    char *linep1 = *((char **)lp1);
    char *linep2 = *((char **)lp2);

    return strcmp (linep1, linep2);
}

static char **sort_and_write (int fd, char *sortbuf, char *sortend, int (*accept_line)(unsigned char *, int))
{
    char *cp, **linesp;
    int  i, nlines, alines;
    FILE *fpo;

    nlines = 0;
    alines = 2 + ((sortend - sortbuf) / 80);
    linesp = malloc (alines * sizeof (char **));

    cp = sortbuf;

    while (cp < sortend) {
        char *tcp, **tlp;

        if (nlines == alines - 2) {
            alines <<= 1;
            tlp = malloc (alines * sizeof (char **));
            memcpy (tlp, linesp, nlines * sizeof (char **));
            free (linesp);
            linesp = tlp;
        }

        tcp = memchr (cp, '\n', (sortend - cp));
        tcp = (tcp ? tcp : sortend);
        *tcp++ = '\0';

        if (!accept_line || accept_line ((unsigned char *)cp, 0)) {
            linesp[nlines++] = cp;
        }
/*
        linesp[nlines++] = cp;
        tcp = memchr (cp, '\n', (sortend - cp));
        if (!tcp) break;

        *tcp++ = '\0';
*/
        cp     = tcp;
    }
    linesp[nlines--] = NULL;

    qsort (linesp, nlines, sizeof (char **), lcompar);

    fpo = fdopen (fd, "w");

    for (i = 0; i < nlines; i++) {
        fputs (linesp[i], fpo);
        fputc ('\n', fpo);
    }

    fclose (fpo);

    return linesp;
}

static int wait_fork (int *nchildsp, int maxchilds)
{
    int status, ret, nchilds = *nchildsp;

    while (nchilds >= maxchilds) {
        waitpid(-1, &status, 0);
        nchilds--;
    }

    if (!maxchilds) return 0;

    while (nchilds && waitpid(-1, &status, WNOHANG) > 0) {
        nchilds--;
    }
    ret = fork ();
    if (ret > 0) nchilds++;

    *nchildsp = nchilds;

    return ret;
}

static struct sort_tmpfile *sort_load_unsorted_data (int par, size_t memsiz, FILE *fpi, char *template, int *ntmpp, int (*accept_line)(unsigned char *, int))
{
    struct sort_tmpfile *tmpp;
    size_t              rsiz, memfree;
    char                *sortend, *sortstart, *sortbuf, *tmpnam;
    int                 ntmp, atmp, fd, nchilds = 0;

    ntmp = 0;
    atmp = 16;
    tmpp = malloc (1 + atmp * sizeof (struct sort_tmpfile));

    /* Allow extra '\n' in buffer */
    sortbuf   = malloc (memsiz + 1);
    sortstart = sortbuf;
    memfree   = memsiz;

    for (;;) {
        rsiz = fread (sortstart, 1, memfree, fpi);

        if (rsiz == memfree) {
            sortend = memrchr (sortbuf, '\n', rsiz);
            if (sortend == NULL) {
                fprintf (stderr, "Excessive line length\n");
                exit (1);
            }
            sortend++;
        } else {
            char *tp;

            sortend = sortstart + rsiz;

            tp = sortend - 1;
            if (*tp != '\n') {
                *sortend++ = '\n';
            }
        }
        if (ntmp == atmp - 1) {
            struct sort_tmpfile *tp;
            atmp <<= 1;
            tp = malloc (atmp * sizeof (struct sort_tmpfile));
            memcpy (tp, tmpp, ntmp * sizeof (struct sort_tmpfile));
            free (tmpp);
            tmpp = tp;
        }

        tmpnam  = strdup (template);
        fd      = mkstemp (tmpnam);

        if (wait_fork (&nchilds, par) == 0) {
            sort_and_write (fd, sortbuf, sortend, accept_line);
            exit (0);
        }
        close (fd);

        tmpp[ntmp++].name = tmpnam;

        if (rsiz != memfree) break;

        sortstart = (sortbuf + memsiz) - (sortend - sortbuf);
        memcpy (sortbuf, sortend, sortstart - sortbuf);

        memfree   = (sortbuf + memsiz) - sortstart;
    }

    free (sortbuf);

    wait_fork (&nchilds, 0);

    *ntmpp = ntmp;

    return tmpp;
}

/*
 * The ordering of the temporary files to do mergesort is based on a binary heap:
 *     http://en.wikipedia.org/wiki/Binary_heap
 */

inline void sort_swap_tmpfiles (struct sort_tmpfile *tmpp1, struct sort_tmpfile *tmpp2)
{
    struct sort_tmpfile tmp;

    tmp    = *tmpp1;
    *tmpp1 = *tmpp2;
    *tmpp2 = tmp;
}

static void sort_heap_pushdown (struct sort_tmpfile *tmpp, int ntmp, int node)
{
    int child;

    while (node) {
        child = heap_child (node);
        if (strcmp ((char *)tmpp[child].buf, (char *)tmpp[node].buf) <= 0) break;
        sort_swap_tmpfiles (tmpp + child, tmpp + node);
        node = child;
    }
}

static void sort_heap_pullup (struct sort_tmpfile *tmpp, int ntmp, int node)
{
    int par;

    for (;;) {
        par = heap_parent (node);
        if (par < ntmp - 1) {
            if (strcmp ((char *)tmpp[par].buf, (char *)tmpp[par + 1].buf) > 0) {
                par++;
            }
        } else {
            if (par >= ntmp) break;
        }
        if (strcmp ((char *)tmpp[node].buf, (char *)tmpp[par].buf) <= 0) break;
        sort_swap_tmpfiles (tmpp + node, tmpp + par);
        node = par;
    }
}

unsigned char *sort_init (struct sortstate **sortpp, int par, int memsiz, FILE *fp, char *template, int (*accept_line)(unsigned char *, int))
{
    struct sort_tmpfile *tmpp;
    struct sortstate    *sortp;
    int                 i, ntmp;

    tmpp = sort_load_unsorted_data (par, memsiz, fp, template, &ntmp, accept_line);
    i    = 0;
    while (i < ntmp) {
        tmpp[i].fp  = fopen (tmpp[i].name, "r");
        tmpp[i].buf = malloc (1024);
        if (fgets ((char *)tmpp[i].buf, 1024, tmpp[i].fp)) {
            sort_heap_pushdown (tmpp, ntmp, i);
            i++;
        } else {
            fclose (tmpp[i].fp);
            unlink (tmpp[i].name);
            free (tmpp[i].buf);
            free (tmpp[i].name);
            tmpp[i] = tmpp[--ntmp];
        }
    }
    sortp = malloc (sizeof (struct sortstate));

    sortp->ntmp = ntmp;
    sortp->tmpp = tmpp;

    *sortpp = sortp;

    return (ntmp ? tmpp[0].buf : NULL);
}

unsigned char *sort_nextline (struct sortstate *sortp)
{
    struct sort_tmpfile *tmpp = sortp->tmpp;
    int                 ntmp  = sortp->ntmp;

    while (ntmp && !fgets ((char *)tmpp[0].buf, 1024, tmpp[0].fp)) {
        fclose (tmpp[0].fp);
        unlink (tmpp[0].name);
        free (tmpp[0].buf);
        free (tmpp[0].name);
        tmpp[0] = tmpp[--ntmp];
    }
    sort_heap_pullup (tmpp, ntmp, 0);

    sortp->ntmp = ntmp;
    sortp->tmpp = tmpp;

    return (sortp->ntmp ? sortp->tmpp[0].buf : NULL);
}
