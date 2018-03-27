#!/usr/bin/env bash

# Set environment variables for dev
export HOST_GID=$(id -g)
export HOST_UID=$(id -u)
export PROJECT_NAME='kennisnet'
export APP_PORT=${APP_PORT:-80}
export DB_PORT=${DB_PORT:-3306}
export DB_ROOT_PASS=${DB_ROOT_PASS:-${PROJECT_NAME}}
export DB_NAME=${DB_NAME:-${PROJECT_NAME}}
export DB_USER=${DB_USER:-${PROJECT_NAME}}
export DB_PASS=${DB_PASS:-${PROJECT_NAME}}
export DB_HOST=${DB_HOST:-mysql}
export APP_IMAGE_NAME="${PROJECT_NAME}_web_image"

export DATABASE_URL=${DATABASE_URL:-"mysql://${DB_USER}:${DB_PASS}@${DB_HOST}:${DB_PORT}/${DB_NAME}"}
export $(egrep -v '^#' .env | xargs);

# Decide which docker-compose file to use
COMPOSE_FILE="dev"
TTY=""

COMPOSE="docker-compose -f docker/docker-compose.$COMPOSE_FILE.yml"
STATUS=$($COMMAND 2> /dev/null)

# If we pass any arguments...
if [ $# -gt 0 ];then

    if [ "$1" == "build" ]; then
        shift 1
        $COMPOSE down
        docker build ./docker/app -t $APP_IMAGE_NAME --build-arg "local_user_id=${HOST_UID}" --build-arg "local_user_group_id=${HOST_GID}"
        $COMPOSE build "$@"

    elif [ "$1" == "console" ]; then
        shift 1
        $COMPOSE exec web bash

    elif [ "$1" == "node" ]; then
        shift 1
        $COMPOSE exec node bash

    elif [ "$1" == "composer" ]; then
        shift 1
        $COMPOSE run --rm $TTY \
            -w /var/www/html \
            web \
            composer "$@"
        if [[ $STATUS != "running" ]]; then
            $COMPOSE down &> /dev/null
        fi

   elif [ "$1" == "unit-test" ]; then
        shift 1
        $COMPOSE run --rm $TTY \
            -w /var/www/html \
            web \
            ./vendor/bin/phpunit "$@"
            $COMPOSE down &> /dev/nul
    elif [ "$1" == "restart-phpreact" ]; then
        curl "http://user:123@127.0.0.1:9001/index.html?processname=reactphp:reactphp-0&action=restart" &> /dev/null
        curl "http://user:123@127.0.0.1:9001/index.html?processname=reactphp:reactphp-1&action=restart" &> /dev/null
        curl "http://user:123@127.0.0.1:9001/index.html?processname=reactphp:reactphp-2&action=restart" &> /dev/null
        curl "http://user:123@127.0.0.1:9001/index.html?processname=reactphp:reactphp-2&action=restart" &> /dev/null
    else
        $COMPOSE "$@"
    fi

else
    $COMPOSE ps
fi