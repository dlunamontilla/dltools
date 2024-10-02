main:
	php -S 0.0.0.0:4000 -t public/

push:
	git add .; git commit -a; git push origin dev; git checkout master; git merge dev; git push origin master; git checkout dev

preview-doc:
	php -S localhost:3700 -t docs/.build/

doc:
	phpdoc run --ignore "vendor/"