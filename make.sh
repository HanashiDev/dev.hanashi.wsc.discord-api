#!/bin/bash
PACKAGE_NAME=dev.hanashi.wsc.discord-api
PACKAGE_TYPES=(acptemplates files)

for i in "${PACKAGE_TYPES[@]}"
do
    rm -rf ${i}.tar
    7z a -ttar -mx=9 ${i}.tar ./${i}/*
done

rm -rf ${PACKAGE_NAME}.tar ${PACKAGE_NAME}.tar.gz
7z a -ttar -mx=9 ${PACKAGE_NAME}.tar ./* -x!acptemplates -x!files -x!templates -x!${PACKAGE_NAME}.tar -x!${PACKAGE_NAME}.tar.gz -x!.git -x!.gitignore -x!make.sh -x!make.bat -x!.phpcs.xml -x!.github

for i in "${PACKAGE_TYPES[@]}"
do
    rm -rf ${i}.tar
done
