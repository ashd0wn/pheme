#!/bin/bash
set -e
set -x

apt-get install -y --no-install-recommends sudo

# Workaround for sudo errors in containers, see: https://github.com/sudo-project/sudo/issues/42
echo "Set disable_coredump false" >> /etc/sudo.conf

adduser --home /var/pheme --disabled-password --gecos "" pheme

usermod -aG www-data pheme

mkdir -p /var/pheme/www /var/pheme/stations /var/pheme/www_tmp \
  /var/pheme/docs \
  /var/pheme/backups \
  /var/pheme/dbip \
  /var/pheme/storage/uploads \
  /var/pheme/storage/shoutcast2 \
  /var/pheme/storage/stereo_tool \
  /var/pheme/storage/geoip \
  /var/pheme/storage/sftpgo \
  /var/pheme/storage/acme

chown -R pheme:pheme /var/pheme
chmod -R 777 /var/pheme/www_tmp

echo 'pheme ALL=(ALL) NOPASSWD: ALL' >> /etc/sudoers
