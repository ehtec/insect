#!/bin/bash

sleep 45

#This should be run by insect service at startup

#source the variables for pocketsphinx and gstreamer-1.0
echo "Sourcing environment variables."
source /home/pi/insect/sh/pock.sh

#check if /tmp/server.sock file was not removed correctly last time, deleting if exists
if [ -f /tmp/server.sock ]; then
	echo "File found, removing."
        sudo rm /tmp/server.sock
fi

echo "Starting insect daemon."
/home/pi/insect/bin/server.out

echo "Finished."
