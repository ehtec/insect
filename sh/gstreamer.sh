#!/bin/bash

sleep 30

gst-launch-1.0 alsasrc ! audio/x-raw, endianness=1234, signed=true, width=16, depth=16, rate=44100, channels=1, format=S16LE ! audioresample ! tee name=t \
	t. ! queue ! audioconvert ! audioresample ! tcpserversink host=127.0.0.1 port=3000 \
	t. ! queue ! audioamplify amplification=100 ! webrtcdsp noise-suppression-level=high ! webrtcechoprobe ! audioresample ! audio/x-raw, channels=1, rate=16000 ! opusenc bitrate=20000 ! rtpopuspay ! udpsink host=127.0.0.1 port=5002
