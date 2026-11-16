#/bin/sh

sudo -u postgres pgbench -i - s 60 sbtest
sudo -u postgres pgbench -c 64 -j 2 -t 1000 sbtest