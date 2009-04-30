===ZX2C4 Music===

=Requirements=
--Posix--
ZX2C4 Music is written for Posix servers, like Linux or BSD.
--PHP--
ZX2C4 Music is written in PHP5. It is recommended that if you have access to
your php.ini, you enable zlib output compression at the php level, instead of
buffering all content at the server level, like in the case of Dreamhost. If you
don't have access to your php.ini, ZX2C4 Music will still work, but you won't
receive as snappy output when updating the database with new music.
--MySQL--
Any MySQL compatable with the standard php libraries should work.
--TagLib--
ZX2C4 Music relies on a small C++ program which uses taglib. Included with this
archive is a statically linked Linux ELF executable compiled on i386. This
executable should have executable permissions. If you require compilation on a
differnt platform, you may download the source at
http://git.zx2c4.com/?p=taglib-tagreader.git;a=snapshot;h=HEAD;sf=tgz.
Compilation requires CMake, and the relevant statically linked binary is titled
"tagreader". Build using ./build.sh, and when it completes move
"build/tagreader" into the root directory of ZX2C4 Music.
--FFMpeg--
ZX2C4 Music uses ffmpeg for transcoding non-mp3 files to mp3 for the built-in
flash music player. If your server does not already have ffmpeg installed and in
its PATH, you should install it and add ffmpeg to your server's PATH.

=Installation=
0. Aquire a snapshot of the git archive from
http://git.zx2c4.com/?p=zx2c4music.git;a=snapshot;h=HEAD;sf=tgz
1. Find the username, password, server host (which is usually "localhost"), and
database name of either an existing database or a new database.
2. Upload music to your server into one directory, and learn the absolute path
of that directory. (If you have ssh access, cd into the music directory and type
"pwd" (without quotes). This will return the absolute path of the directory.)
3. Rename settings.php.example to settings.php and customize the file in a text
editor, supplying it with the neccessary information. Make sure to read all the
comments.
4. Upload the directory containing ZX2C4 Music to your server.
5. Login to ZX2C4 Music using the user password and see that there is no music
in the database.
6. Visit the update database link at the bottom of the page and authenticate
using the database password.

=URL Tricks=
*) http://music.zx2c4.com/?query=John+Coltrane will show a search for John
Coltrane, after prompting the user for the password
*) http://music.zx2c4.com/?query=John+Coltrane&username=Apple&password=Sauce
will show a search for John Coltrane, after trying to login using username Apple
and password Sauce

=Tips=
I upload my music using rsync over ssh:
rsync -avz /home/zx2c4/Music/ --delete-excluded --progress --compress-level=9 \
zx2c4.com:/home/zx2c4com/Personal/Music
