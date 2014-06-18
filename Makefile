
all: clean coverage

clean:
	rm -rf artifacts/*

test:
	vendor/bin/phpunit

coverage:
	vendor/bin/phpunit --coverage-html=artifacts/coverage

.PHONY: all clean test coverage
