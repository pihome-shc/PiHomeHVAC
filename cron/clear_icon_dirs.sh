#!/bin/bash

clear
echo "Clearing Unwanted Icon Directories"

ICON_DIR=/var/www/fonts/ionicons-2.0.1
echo "Trying to DELETE Directory: $ICON_DIR"
if [ -d "$ICON_DIR" ];
 then
     echo "DELETING Directory: $ICON_DIR"
     rm -Rf $ICON_DIR
 else
  echo "Directory: $ICON_DIR Does Not Exist"
fi

ICON_DIR=/var/www/fonts/glyphicons
echo "Trying to DELETE Directory: $ICON_DIR"
if [ -d "$ICON_DIR" ];
 then
     echo "DELETING Directory: $ICON_DIR"
     rm -Rf $ICON_DIR
 else
  echo "Directory: $ICON_DIR Does Not Exist"
fi

ICON_DIR=/var/www/fonts/font-awesome-4.7.0
echo "Trying to DELETE Directory: $ICON_DIR"
if [ -d "$ICON_DIR" ];
 then
     echo "DELETING Directory: $ICON_DIR"
     rm -Rf $ICON_DIR
 else
  echo "Directory: $ICON_DIR Does Not Exist"
fi

ICON_DIR=/var/www/fonts/custom
echo "Trying to DELETE Directory: $ICON_DIR"
if [ -d "$ICON_DIR" ];
 then
     echo "DELETING Directory: $ICON_DIR"
     rm -Rf $ICON_DIR
 else
  echo "Directory: $ICON_DIR Does Not Exist"
fi

echo "Finished Clearing Unwanted Icon Directories"

