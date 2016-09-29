

pdf:
	# improve list spacing
	sed 's/\\begin{itemize}/\\begin{itemize}[noitemsep,nolistsep]/' guide.tex > guide-mod.tex
	sed -i 's/\\include{guide}/\\include{guide-mod}/' main.tex
	# run pdflatex twice to generate TOC correctly
	pdflatex -halt-on-error main.tex
	pdflatex -halt-on-error main.tex
	mv main.pdf guide.pdf

clean:
	-rm -f *.aux *.out *.log *.toc guide-mod.tex
