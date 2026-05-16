#!/bin/bash

# Determine the current uploads dir for the installation.
if [ -z "$UPLOADS_DIR" ]; then
  if [ -d "/var/pheme/uploads" ]; then
    export UPLOADS_DIR="/var/pheme/uploads"
  else
    export UPLOADS_DIR="/var/pheme/storage/uploads"
  fi
fi

if [ -z "$ACME_DIR" ]; then
  if [ -d "/var/pheme/acme" ]; then
    export ACME_DIR="/var/pheme/acme"
  else
    export ACME_DIR="/var/pheme/storage/acme"
  fi
fi

# Copy the nginx template to its destination.
dockerize -template "/etc/nginx/nginx.conf.tmpl:/etc/nginx/nginx.conf" \
    -template "/etc/nginx/pheme.conf.tmpl:/etc/nginx/sites-available/default"
