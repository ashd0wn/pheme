#!/bin/bash
set -e
set -x

mkdir -p /var/pheme/sftpgo/persist \
  /var/pheme/sftpgo/backups \
  /var/pheme/sftpgo/env.d

cp /bd_build/web/sftpgo/sftpgo.json /var/pheme/sftpgo/sftpgo.json

touch /var/pheme/sftpgo/sftpgo.db
chown -R pheme:pheme /var/pheme/sftpgo

# Create sftpgo temp dir
mkdir -p /tmp/sftpgo_temp
touch /tmp/sftpgo_temp/.tmpreaper
chmod -R 777 /tmp/sftpgo_temp
