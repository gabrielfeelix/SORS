#!/bin/bash

PROJECT_PATH="/home/gabrielfelix/multi-tenant-architecture"
CONTAINER_NAME="multi-tenant-architecture_multi-tenant.sistema_1"

# Comandos que gerenciam o Sail (docker-compose)
case "$1" in
  up|down|start|stop|restart|ps|logs|build)
    cd "$PROJECT_PATH"
    "$PROJECT_PATH/vendor/bin/sail" "$@"
    ;;
  *)
    # Outros comandos rodam dentro do container
    docker exec -it "$CONTAINER_NAME" "$@"
    ;;
esac
