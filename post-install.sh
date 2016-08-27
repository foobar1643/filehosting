#!/bin/bash

# videojs
mkdir -p ./public/media/js/videojs
mkdir -p ./public/media/css/videojs
mkdir -p ./public/media/flash/videojs
mkdir -p ./public/media/css/videojs/font
cp ./vendor/videojs/video.js/dist/video.min.js ./public/media/js/videojs/video.min.js
cp ./vendor/videojs/video.js/dist/video-js.min.css ./public/media/css/videojs/video-js.min.css
cp ./vendor/videojs/video.js/dist/video-js.swf ./public/media/flash/videojs/video-js.swf
cp -r ./vendor/videojs/video.js/dist/font/* ./public/media/css/videojs/font

# bootstrap
mkdir -p ./public/media/bootstrap/css
mkdir -p ./public/media/bootstrap/js
mkdir -p ./public/media/bootstrap/fonts
cp ./vendor/twbs/bootstrap/dist/css/bootstrap.min.css ./public/media/bootstrap/css/bootstrap.min.css
cp ./vendor/twbs/bootstrap/dist/js/bootstrap.min.js ./public/media/bootstrap/js/bootstrap.min.js
cp -r ./vendor/twbs/bootstrap/dist/fonts/* ./public/media/bootstrap/fonts

# jquery
mkdir -p ./public/media/js/jquery
cp ./vendor/components/jquery/jquery.min.js ./public/media/js/jquery/jquery.min.js

# chmod
chmod 0777 ./public/media/flash/videojs/video-js.swf
chmod -R 0777 ./storage
chmod -R 0777 ./public/thumbnails