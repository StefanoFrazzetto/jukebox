#!/bin/bash

#DEVICE="$device"
#LOGS_PATH="$logs_path"
#RIPPING_PATH="$ripping_path"
#ENCODING_PATH="$econding_path"

DEVICE=/dev/sr0
LOGS_PATH=/var/www/html/jukebox/ripper/logs
RIPPING_PATH=/var/www/html/jukebox/ripper/ripped
ENCODING_PATH=/var/www/html/jukebox/ripper/encoded

if [ "$DEVICE" == "" ]; then
    echo "You need to provide the device path."
	exit
fi;

## Remove then create the directory where the ripped files will be saved
rm -rf "$RIPPING_PATH"
mkdir -p "$RIPPING_PATH"

## START THE RIPPING PROCESS

# -v --verbose
#   Be absurdly verbose about the auto-sensing and reading process. Good for setup and debugging.

# -B --batch
#   Cdda2wav-style batch output flag; cdparanoia will split the output into multiple files at track boundaries.
#   Output file  names  are prepended with 'track#.'

# -X --abort-on-skip
#   If  the read skips due to imperfect data, a scratch, or whatever, abort reading this track.
#   If output is to a file, delete the partially completed file.

# -d --force-cdrom-device device
#   Force the interface backend to read from device rather than the first readable CDROM drive it finds.
#   This can be  used  to  specify devices of any valid interface type (ATAPI, SCSI, or proprietary).

cdparanoia -B -X -d ${DEVICE} 1:- ${RIPPING_PATH}/ -l ${LOGS_PATH}/cdparanoia.log 2>/dev/null


## GET THE RIPPER TRACKS
TRACKS=$(find ${RIPPING_PATH}/ -type f -name "*.wav")

## Remove then create the directory where the encoded files will be saved
rm -rf "$ENCODING_PATH"
mkdir -p "$ENCODING_PATH"

## ENCODE TRACK BY TRACK
for track in ${TRACKS};
do
	tr=$(basename "$track")
	track_name="${tr%.*}"
	lame --vbr-new --silent "$track" ${ENCODING_PATH}/${track_name}.mp3 > ${LOGS_PATH}/lame.log 2>/dev/null
done