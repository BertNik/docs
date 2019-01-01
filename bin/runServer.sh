#!/bin/bash
cd ../; nohup php -S docs:9111 > /dev/null 2>&1 &  echo $! > server.pid &&  mv server.pid bin/ && cd bin/  
