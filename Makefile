
all: clean coverage

clean:
	rm -rf artifacts/*

test:
	vendor/bin/phpunit

docs:
	vendor/bin/phpdoc -d ./src -t ./docs --sourcecode --template clean --no-interaction

coverage:
	vendor/bin/phpunit --coverage-html=artifacts/coverage

.PHONY: all clean test docs coverage
