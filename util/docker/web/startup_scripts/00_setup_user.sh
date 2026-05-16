#!/bin/bash

PHEME_PUID="${PHEME_PUID:-1000}"
PHEME_PGID="${PHEME_PGID:-1000}"

PUID="${PUID:-$PHEME_PUID}"
PGID="${PGID:-$PHEME_PGID}"

groupmod -o -g "$PGID" pheme
usermod -o -u "$PUID" pheme

echo "Docker 'pheme' User UID: $(id -u pheme)"
echo "Docker 'pheme' User GID: $(id -g pheme)"
