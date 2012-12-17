
struct sortstate;

unsigned char *sort_init (struct sortstate **sortpp, int par, int memsiz, FILE *fp, char *template);
unsigned char *sort_nextline (struct sortstate *sortp);
