#!/bin/bash

while true; do
  certbot renew --post-hook "/etc/init.d/nginx reload"
  sleep 86400
done
