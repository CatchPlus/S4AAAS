/* tree-num.h
 *
 * revision History:
 * 20110824 Header includes a    Rolf Fokkens
 *          trie version now     rolf.fokkens@target-holding.nl
 * 20110820 Include higher level Rolf Fokkens
 *          stuff as well        rolf.fokkens@target-holding.nl
 * 20110406 Initial version      Rolf Fokkens
 *                               rolf.fokkens@target-holding.nl
 *
 * THIS PROGRAM REQUIRES BETTER ERROR HANDLING
 *
 * It is was written as a Proof of Concept in a pragmatic way. This
 * means that error handling has not been taken care of very well!
 */

#ifndef H_TREE_NUM
#define H_TREE_NUM

#include <stdint.h>
#include <stdio.h>

/*
 * Trie versions 1 and up have a header which specifies the options
 * that apply. Version 0 starts with a 64 bit value specifying
 * the root position of the trie. For versions 1 and bit 63 of this
 * 64 bit (TRIE_HDRBIT) value is 1, which means the first 64 bit value
 * specifies a header. For version 0 TRIE_HDRBIT is 0, becauses tries
 * (up till now) ar (much) smaller than 2^63.
 * For versions 1 and up the second 64 bit value specifies the root
 * position of the trie.
 */

/* The current TRIE version                                   */
#define TRIE_VERSION ((uint64_t)1)

/* The bit indicating if we have a header or not              */
#define TRIE_HDRBIT   (((uint64_t)1) << 63)
/* The upper 32 bits of the header specify the actual version */
#define TRIE_VERMASK  ((((uint64_t)-1) << 32) & ~TRIE_HDRBIT)
/* Options are specified in the lower 32 bits                 */
#define TRIE_OPT_MASK (~TRIE_VERMASK & ~TRIE_HDRBIT)

/* Each bit in the  lower 32 bits of the header specifies a
 * certain option.  Currently the following options exist:
 * TRIE_OPT_COUNT   each trienode has a counter that counts the
 *                  total number of child (offspring) values in
 *                  the trie.
 * TRIE_OPT_RELCHLD The file positions of the siblings children
 *                  are reletive to the file position of the
 *                  previous siblings child.
 * TRIE_OPT_CREATE  The trie file is currently being created,
 *                  or creation has been aborted. This means that
 *                  the trie file probably is corrupt.
 *                  This 'option' is turned off as the final step
 *                  of trie creation.
 */
#define TRIE_OPT_COUNT   ((uint64_t) (1 << 0))
#define TRIE_OPT_RELCHLD ((uint64_t) (1 << 1))
#define TRIE_OPT_CREATE  ((uint64_t) (1 << 2))

int fseek64 (FILE *stream, uint64_t offset, int whence);
uint64_t ftell64 (FILE *stream);

uint64_t trie_hdr_read (FILE *fp, int *versionp, int *featuresp);

int trie_node_read  (FILE *fp, int features, uint64_t *refposp, uint64_t *posp, uint64_t *cntp, int *lenp, int *endp, uint64_t *sonp, unsigned char *bp);
int trie_node_write (FILE *fp, int features, uint64_t *refposp, uint64_t *posp, uint64_t cnt,   int len,   int end,   uint64_t son,   unsigned char *bp);

/*****************************************************************************
 *                                                                           *
 *                      Higher level trie stuff                              *
 *                                                                           *
 *****************************************************************************/

struct string {
    int           tag;
    unsigned char *dat;
    int           len;
};

/*
 * "struct trie" is all about tracking the state of a trie that's currenty
 * open, i.e. is being searched.
 *
 * Tries have the following operations:
 *     trie_find ()  Find the next trie entry based on a given key
 */
struct trie;

/*
 * "struct triedata" is data of a "struct trie" that is relevant to the trie
 * users.
 */
struct triedata {
    /* an id that's assigned on creation */
    int             id;

    /* a buffer that holds the current result data */
    struct string   buf;

    /* the number of fields (based on separator) that are in buf */
    int             nflds;

    /* pointers for each field in buf. Each field fldsp[i] implicitly ends */
    /* at the start of the next field fldsp[i+1].                          */
    unsigned char   **fldsp;
};

/*
 * trie_init () initializes a struct trie:
 *    id:     the id for this trie, any number you like
 *    fp:     the open file for this trie
 *    key:    the key to be found
 *    sep:    the character to separate the fields
 *    endsep: the optional terminating separator to be added to key.
 *            it will be ignored when -1.
 *    aflds:  the maximum number of fields to be identified
 */
struct trie *trie_init
    (int id, FILE *fp, struct string *key, int sep, int endsep, int aflds);

/*
 * trie_next () finds the next entry in a trie.
 *    fpi:   is the File Pointer to the index file.
 *    triep: points to the trie.
 */
struct triedata *trie_next (struct trie *triep);

/*
 * trie_data () returns the "struct triedata" of the trie triep.
 */
struct triedata *trie_data (struct trie *triep);

/*
 * trie_cleanup () cleans up the (memory) mess after using triep
 */
void trie_cleanup (struct trie *triep);

/*
 * Several keys may be searched in parrallel in the same trie, for example
 * when searching wildcards. The wildcards are expanded to specific key
 * values to be searched in parrallel.
 *
 * This parrallel search is represented by a set of 
 */
struct trieset;

/*
 * trieset_init () creates a "struct trieset":
 *    fp:     The file pointer to the index file
 *    sep:    the character to separate the fields
 *    sepcnt: The #fields to be skip during merging of the results
 */
struct trieset *trieset_init (FILE *fp, int sep, int sepcnt);

/*
 * trieset_add () adds a trie to a trieset:
 *    tsetp: The "struct trieset" to which a trie is added
 *    key:   The search key for the new trie
 */
void trieset_add (struct trieset *tsetp, struct string *key);

/*
 * trieset_data () returns the "struct triedata" of the "lowest" trie
 * in trieset triep.
 */
struct triedata *trieset_data (struct trieset *tsetp);

/*
 * trieset_next () moves to the next search result for the "lowest" trie
 * in trieset triep.
 */
int trieset_next (struct trieset *tsetp);

/*****************************************************************************
 *                                                                           *
 *                     Quicksort/Mergesort stuff                             *
 *                                                                           *
 *****************************************************************************/

struct sortstate;

unsigned char *sort_init (struct sortstate **sortpp, int par, int memsiz, FILE *fp, char *template, int (*accept_line)(unsigned char *, int));
unsigned char *sort_nextline (struct sortstate *sortp);

#endif /* H_TREE_NUM */
