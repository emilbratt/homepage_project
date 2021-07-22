#!/usr/bin/env bash

_REPO_MAINTAINER='emilbratt'
_REPO_NAME='homepage_project'
_TAG='latest'
_PORT='80:80'

_attach () {
  docker attach $_REPO_NAME
}

_build () {
  docker build -t $_REPO_MAINTAINER/$_REPO_NAME:$_TAG ./
}

_remove () {
  docker rm $_REPO_NAME
}

_delete () {
  docker rmi "$_REPO_MAINTAINER/$_REPO_NAME:$_TAG"
}

_run () {
  docker run -d \
    -p $_PORT \
    -it \
    -d \
    --mount type=bind,source="$(pwd)"/src,target=/var/www/html \
    --restart unless-stopped \
    --name $_REPO_NAME \
    $_REPO_MAINTAINER/$_REPO_NAME:$_TAG
}

_start () {
  docker start $_REPO_NAME
}

_stop () {
  docker stop $_REPO_NAME
}

_exec () {
  echo -e 'Type command\n' && read _CMD
  docker exec -it $_REPO_NAME $_CMD
}

_history () {
  docker history $_REPO_MAINTAINER/$_REPO_NAME
}

_inspect () {
  docker inspect $_REPO_MAINTAINER/$_REPO_NAME
}

_list_options () {
  # prints out options loaded from arguments
  # takes an arbitrary amount of args, they will all be printed on separate lines
  echo -e '\n----------------------------------------------'
  for optn in "$@"
  do
    echo "$optn"
  done
  echo -e '----------------------------------------------\n'
}
_list_options '1. build' '2. run image' '3. start container' '4. stop continer' \
  '5. remove container' '6. attach to continer' '7. see processes' '8. see images' \
  '9. execute command' '10. delete image' '11. image history' '12. Inspect container'

read _OPTN

if [[ $_OPTN == 1 ]]; then
  _build  && echo 'ok' || echo 'error'

elif [[ $_OPTN == 2 ]]; then
  _run    && echo 'ok' || echo 'error'

elif [[ $_OPTN == 3 ]]; then
  _start  && echo 'ok' || echo 'error'

elif [[ $_OPTN == 4 ]]; then
  _stop   && echo 'ok' || echo 'error'

elif [[ $_OPTN == 5 ]]; then
  _remove

elif [[ $_OPTN == 6 ]]; then
  _attach

elif [[ $_OPTN == 7 ]]; then
  docker ps

elif [[ $_OPTN == 8 ]]; then
  docker images

elif [[ $_OPTN == 9 ]]; then
  _exec

elif [[ $_OPTN == 10 ]]; then
  _delete

elif [[ $_OPTN == 11 ]]; then
  _history

elif [[ $_OPTN == 12 ]]; then
  _inspect

fi
