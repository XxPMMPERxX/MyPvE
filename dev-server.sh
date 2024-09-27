#!/usr/bin/env bash

OLD_COMPOSE="docker-compose"
NEW_COMPOSE="docker compose"

if [ -f ./.env ]; then
    source ./.env
fi

if $NEW_COMPOSE version 1> /dev/null 2>&1 ;then
    COMPOSE=$NEW_COMPOSE
elif $OLD_COMPOSE -v 1> /dev/null 2>&1 ; then
    COMPOSE=$OLD_COMPOSE
else
    echo "docker-compose or docker compose が入ってないみたいです"
    exit 1
fi

$COMPOSE run --rm ${CONTAINER_NAME:-pmmp} /usr/bin/start-pocketmine
