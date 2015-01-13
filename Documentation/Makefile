SRCS=$(wildcard *.adoc)
HTML=$(SRCS:%.adoc=%.html)
PDFS=$(SRCS:%.adoc=%.pdf)

html: $(HTML)

pdf: $(PDFS)

%.html: %.adoc
	asciidoctor -n -r asciidoctor-diagram $<

%.pdf: %.html
	wkhtmltopdf -L 20 -R 20 -T 30 -B 50 $< $@

clean:
	rm -f $(HTML) $(PDFS)

watch:
	while inotifywait -e close_write $(SRCS) ; do make ; done
