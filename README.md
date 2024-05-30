# bigmelo-api

Api to use bigmelo

## API Docs

php artisan l5-swagger:generate
URL: http://API-URL/docs/

# Setup

## Prerequisites

There are a few things you need to install upfront to run the app:

- [Docker](https://www.docker.com/products/docker-desktop/) - Install and run Docker on your machine.

## Installation

```bash
$ docker-compose up
```

Once the container is up and running, you need to access the container, install the dependencies, and run the migrations:

To know the container name:

```bash
$ docker ps
```

From the list of containers, copy the container name of the `bigmelo-api-bigmelo-api` image.

To access the container:

```bash
$ docker exec -it <container_name> /bin/bash
```

To install node the dependencies:

```bash
$ npm install
```

**Note**: The env file is copied to the `src` automatically from the `.env.example` file. You can change the values in the `.env` file. Ask to the team for the values.

To install the PHP dependencies:

```bash
$ composer install
```

to run the migrations:

```bash
$ php artisan migrate --seed
```
