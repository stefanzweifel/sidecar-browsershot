#!/bin/sh

DOWNLOAD_URL=https://raw.githubusercontent.com/googlefonts/noto-emoji/main/fonts/NotoColorEmoji.ttf

curl -o resources/lambda/NotoColorEmoji.ttf $DOWNLOAD_URL
