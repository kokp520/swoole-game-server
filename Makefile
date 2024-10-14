all: helloworld test

helloworld:
	- echo "Hello, world!"

test:
	- echo "This is Adi's Makefile"

up:
	docker-compose up -d

build:
	docker-compose build --no-cache && docker-compose up -d