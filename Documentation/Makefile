SRCS=$(wildcard *.adoc)
HTML=$(SRCS:%.adoc=%.html)

build: $(HTML)

%.html: %.adoc
	asciidoctor -n -r asciidoctor-diagram $<

watch:
	while inotifywait -e close_write $(SRCS) ; do make ; done
