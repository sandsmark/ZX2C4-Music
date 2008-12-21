mkdir fetch
cd fetch
scp zx2c4.com:/home/zx2c4com/music.zx2c4.com/\*.php .
scp zx2c4.com:/home/zx2c4com/music.zx2c4.com/\*.js .
scp zx2c4.com:/home/zx2c4com/music.zx2c4.com/\*.css .
rm settings.php
rm source.php
mv * ..
cd ..
rm -rf fetch
git diff
