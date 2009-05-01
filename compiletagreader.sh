#!/bin/sh
loc=`readlink -f $0`
cd `dirname $loc `
mkdir tagreader_build
cd tagreader_build
if ! which cmake ; then
	wget http://www.cmake.org/files/v2.6/cmake-2.6.3.tar.gz -O cmake.tar.gz &&
	tar xzf cmake.tar.gz &&
	cd cmake-2.6.3 &&
	./configure &&
	if which gmake; then
		gmake
	elif which make; then
		make
	else
		exit 1
	fi
	cmakepath=`readlink -f bin/cmake`
	cmakedir=`dirname $cmakepath`
	export PATH="$cmakedir:$PATH"
	cd ..
fi
wget "http://git.zx2c4.com/?p=taglib-tagreader.git;a=snapshot;h=HEAD;sf=tgz" -O tagreader.tar.gz &&
tar xzf tagreader.tar.gz &&
cd taglib-tagreader &&
./build.sh &&
mv build/tagreader ../..
cd ../..
rm -rf tagreader_build
