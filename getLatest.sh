mkdir fetch
cd fetch
scp zx2c4.com:/home/zx2c4com/music.zx2c4.com/\*.php .
rm settings.php
mv * ..
cd ..
rm -rf fetch
