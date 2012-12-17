#include <stdio.h>
#include "trie-util.h"

unsigned char bp[1024];

int main (void)
{
    uint64_t hdr, pos, root, refpos, son, cnt;
    int len, end, features, version;
    static char prefix[64], *pfxp;

    fread (&hdr, sizeof (pos), 1, stdin);
    printf ("HDR  = %016llx\n", (long long)hdr);

    if (hdr & TRIE_HDRBIT) {
        version  = (hdr & TRIE_VERMASK) >> 32;
        features = hdr & TRIE_OPT_MASK;
        fread (&root, sizeof (root), 1, stdin);
    } else {
        version  = 0;
        root     = hdr;
        features = 0;
    }

    printf ("            VERSION  = %d\n", version);
    printf ("            FEATURES =");
    if (features & TRIE_OPT_COUNT)   printf (" COUNT");
    if (features & TRIE_OPT_RELCHLD) printf (" RELCHLD");
    if (features & TRIE_OPT_CREATE)  printf (" CREATE");
    printf ("\n");
    printf ("            ROOT     = %010lld\n", (long long)root);

    printf ("\n");

    while (!feof (stdin)) {
        pos = ftell64 (stdin);
        sprintf (prefix, "%010lld", (long long)pos);
        pfxp = prefix;
        refpos = pos;
        while (!feof (stdin)) {
            trie_node_read  (stdin, features, &refpos, &pos, &cnt, &len, &end, &son, bp);
            if (!len) break;
            printf ("%s %s ", pfxp, (end ? "*" : " "));
            if (features & TRIE_OPT_COUNT) {
                printf ("%06lld ", (long long)cnt);
            }
            printf ("%010lld [",(long long)son);
            fwrite (bp, 1, len, stdout);
            printf ("]\n");
            pfxp = "          ";
        }
        printf ("\n");
    }

    return 0;
}
