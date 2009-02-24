<?php if(!eregi("MSIE", $_SERVER['HTTP_USER_AGENT']) && !eregi("Internet Explorer", $_SERVER['HTTP_USER_AGENT'])) { ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<?php } ?>
<html>
<head>
<title><?php echo SITE_NAME; ?> - Login</title>
<link href="style.css" rel="stylesheet" type="text/css">
</head>
<body>
<div id="mainBox" style="width: auto;">

<div id="header">
<?php echo SITE_NAME.'<font id="subheader">'.SITE_BETA.'</font>'; ?>
</div>
<h3>Login</h3>
<?php if($wrongPassword) { ?>
<p style="color: red; font-size: 8pt; font-weight: bold; ">Incorrect username and/or password.</p>
<?php } ?>
<p>
<form method="POST" onSubmit="document.getElementById('loginbutton').disabled = true;">
<?php if($databaseLogin) { ?>
<p style="color: orange; font-size: 8pt; font-weight: bold; ">Please enter the database password. This is a different password from the user password.<br><i>Note: if this is your first time scanning the music directory, the page may take a while to load.</i></p>
<label>Database Password: <input type="password" name="dbpassword"<?php if($dbpassword != "") echo ' value="'.$dbpassword.'"'; ?>></label><br>
<?php } else { ?>
<label>Username: <input type="text" name="username"<?php if($username != "") echo ' value="'.$username.'"'; ?>></label><br>
<label>Password: <input type="password" name="password"<?php if($password != "") echo ' value="'.$password.'"'; ?>></label><br>
<?php } ?>
<input type="submit" value="Login" id="loginbutton">
</form>
</p>
<br>
<span id="copyright">ZX2C4 Music is &copy; Copyright 2008-2009 Jason A. Donenfeld. All Rights Reserved.</span>
</div>
</body>
</html>
