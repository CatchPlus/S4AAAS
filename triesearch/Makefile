CFLAGS=-O0 -g
SRCDIR=src
BINDIR=bin
OBJDIR=obj

BINS=$(BINDIR)/gen-raw-index $(BINDIR)/gen-raw-lines $(BINDIR)/sel-fields $(BINDIR)/trie-build $(BINDIR)/trie-parse $(BINDIR)/trie-lookup $(BINDIR)/trie-search $(BINDIR)/trie-search7 $(BINDIR)/trie-words $(BINDIR)/gen-substrings $(BINDIR)/dedup $(BINDIR)/trie-dump $(BINDIR)/trie-build2 $(BINDIR)/sort-diff $(BINDIR)/sort
#BINS=$(BINDIR)/trie-build $(BINDIR)/trie-parse $(BINDIR)/trie-dump $(BINDIR)/trie-search $(BINDIR)/trie-search7
OBJS=$(OBJDIR)/trie-util.o $(OBJDIR)/monk-crawl.o

all: $(OBJDIR) $(BINDIR) $(BINS)

tar:
	./maketar.sh
clean:
	rm -f $(BINS) $(OBJS)

$(BINDIR):
	mkdir -p $(BINDIR)

$(OBJDIR):
	mkdir -p $(OBJDIR)

$(BINDIR)/gen-raw-index: $(SRCDIR)/gen-raw-index.c $(OBJDIR)/monk-crawl.o
	cc $(CFLAGS) -Wall -pthread -o $(BINDIR)/gen-raw-index $(OBJDIR)/monk-crawl.o $(SRCDIR)/gen-raw-index.c

$(BINDIR)/gen-raw-lines: $(SRCDIR)/gen-raw-lines.c $(OBJDIR)/monk-crawl.o
	cc $(CFLAGS) -Wall -pthread -o $(BINDIR)/gen-raw-lines $(OBJDIR)/monk-crawl.o $(SRCDIR)/gen-raw-lines.c

$(BINDIR)/sel-fields: $(SRCDIR)/sel-fields.c
	cc $(CFLAGS) -Wall -o $(BINDIR)/sel-fields $(SRCDIR)/sel-fields.c

$(BINDIR)/trie-build: $(SRCDIR)/trie-build.c $(OBJDIR)/trie-util.o
	cc $(CFLAGS) -Wall -o $(BINDIR)/trie-build $(OBJDIR)/trie-util.o $(SRCDIR)/trie-build.c

$(BINDIR)/trie-build2: $(SRCDIR)/trie-build2.c $(OBJDIR)/trie-util.o
	cc $(CFLAGS) -Wall -o $(BINDIR)/trie-build2 $(OBJDIR)/trie-util.o $(SRCDIR)/trie-build2.c

$(BINDIR)/trie-parse: $(SRCDIR)/trie-parse.c $(OBJDIR)/trie-util.o
	cc $(CFLAGS) -Wall -o $(BINDIR)/trie-parse $(OBJDIR)/trie-util.o $(SRCDIR)/trie-parse.c

$(BINDIR)/trie-search: $(SRCDIR)/trie-search.c $(OBJDIR)/trie-util.o
	cc $(CFLAGS) -Wall -o $(BINDIR)/trie-search $(OBJDIR)/trie-util.o $(SRCDIR)/trie-search.c

$(BINDIR)/trie-search7: $(SRCDIR)/trie-search7.c $(OBJDIR)/trie-util.o
	cc $(CFLAGS) -Wall -o $(BINDIR)/trie-search7 $(OBJDIR)/trie-util.o $(SRCDIR)/trie-search7.c

$(BINDIR)/trie-lookup: $(SRCDIR)/trie-lookup.c $(OBJDIR)/trie-util.o
	cc $(CFLAGS) -Wall -o $(BINDIR)/trie-lookup $(OBJDIR)/trie-util.o $(SRCDIR)/trie-lookup.c

$(BINDIR)/trie-words: $(SRCDIR)/trie-words.c $(OBJDIR)/trie-util.o
	cc $(CFLAGS) -Wall -o $(BINDIR)/trie-words $(OBJDIR)/trie-util.o $(SRCDIR)/trie-words.c

$(BINDIR)/gen-substrings: $(SRCDIR)/gen-substrings.c
	cc $(CFLAGS) -Wall -o $(BINDIR)/gen-substrings $(SRCDIR)/gen-substrings.c

$(BINDIR)/dedup: $(SRCDIR)/dedup.c $(OBJDIR)/trie-util.o
	cc $(CFLAGS) -Wall -o $(BINDIR)/dedup $(OBJDIR)/trie-util.o $(SRCDIR)/dedup.c 

$(BINDIR)/trie-dump: $(SRCDIR)/trie-dump.c $(OBJDIR)/trie-util.o
	cc $(CFLAGS) -Wall -o $(BINDIR)/trie-dump $(OBJDIR)/trie-util.o $(SRCDIR)/trie-dump.c

$(BINDIR)/sort: $(SRCDIR)/sort.c $(OBJDIR)/trie-util.o
	cc $(CFLAGS) -Wall -o $(BINDIR)/sort $(OBJDIR)/trie-util.o $(SRCDIR)/sort.c

$(BINDIR)/sort-diff: $(SRCDIR)/sort-diff.c
	cc $(CFLAGS) -Wall -o $(BINDIR)/sort-diff $(SRCDIR)/sort-diff.c

$(OBJDIR)/trie-util.o: $(SRCDIR)/trie-util.c $(SRCDIR)/trie-util.h
	cc $(CFLAGS) -Wall -c -o $(OBJDIR)/trie-util.o $(SRCDIR)/trie-util.c

$(OBJDIR)/monk-crawl.o: $(SRCDIR)/monk-crawl.c $(SRCDIR)/monk-crawl.h
	cc $(CFLAGS) -Wall -c -o $(OBJDIR)/monk-crawl.o $(SRCDIR)/monk-crawl.c
