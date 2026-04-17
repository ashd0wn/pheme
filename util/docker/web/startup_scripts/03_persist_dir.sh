#!/bin/bash

export DEBIAN_FRONTEND=noninteractive

echo "Creating persist directories..."

mkdir -p /var/pheme/storage/uploads \
  /var/pheme/storage/shoutcast2 \
  /var/pheme/storage/stereo_tool \
  /var/pheme/storage/geoip \
  /var/pheme/storage/sftpgo \
  /var/pheme/storage/acme
