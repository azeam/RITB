<?php session_start(); ?>
<!doctype html>

<html lang="en">
<head>
	<meta charset="utf-8">
	<title>The Blog - Login</title>
	<meta name="description" content="Login">
	<meta name="author" content="Dennis Hägg">
	<link href="https://fonts.googleapis.com/css2?family=Lato:wght@400&display=swap" rel="stylesheet">
  	<link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@300&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="../css/common.css">
</head>

<body>
<div class="logindiv">
	<div class="logindivinner">
		<script>
		function showRegister() {
			var login = document.getElementById("loginForm");
			var register = document.getElementById("registerForm");
			var php = document.getElementById("phpoutput");
			php.innerHTML = '';
			register.style.display = "block";
			login.style.display = "none";
			
		}
		function showLogin() {
			var login = document.getElementById("loginForm");
			var register = document.getElementById("registerForm");
			var php = document.getElementById("phpoutput");
			php.innerHTML = '';
			login.style.display = "block";
			register.style.display = "none";
		}                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        
		</script>
		<div class="loginLinks">
			<a href="#" onclick="showLogin()">Log in</a>&nbsp;|&nbsp;<a href="#" onclick="showRegister()">Register</a>
		</div>
		<form method="post" id="registerForm">
			<input type="text" name="name" autocomplete="username" placeholder="Enter username" required>
			<br>
			<input type="password" name="password" placeholder="Enter password" minlength=8 autocomplete="current-password" required>
			<br>
			<input type="text" name="botcheck" placeholder="3 + 3 = ?" required>
			<br>
			<br>
			<input type="submit" name="register" value="Register">
		</form>

		<form method="post" id="loginForm">
			<input type="text" name="name" placeholder="Enter username" autocomplete="username" required>
			<br>
			<input type="password" name="password" placeholder="Enter password" minlength=8 autocomplete="current-password" required>
			<br>
			<input type="text" name="botcheck" placeholder="3 + 3 = ?" required>
			<br>
			<br>
			<input type="submit" name="login" value="Log in">
		</form>
		
		<br>
		<div id="phpoutput">
			<?php
			require_once("db.php");
			function login($user, $password) {
				$db = db_connect();
				// get user
				$sql = 'SELECT * FROM user WHERE username=\'' . db_escape($db, $user) . '\'';
				$result = db_select($db, $sql);
				if (!empty($result)) {
					$sqlpass = 'SELECT password FROM user WHERE username=\'' . db_escape($db, $user) . '\'';
					$result = db_select($db, $sqlpass);
					// check hashed_password and send to adminpage if ok
					if(password_verify($password, $result[0]["password"])) {
						$_SESSION['username'] = $user;
						echo "<script> location.replace('admin.php'); </script>";
						exit();
					}
					else {
						echo("Wrong username or password");
					}
				}
				else {
					echo("Wrong username or password");
				}
				db_disconnect($db);
			}

			function register($user, $password) {
				$db = db_connect();
				$sql = 'SELECT * FROM user WHERE username=\'' . db_escape($db, $user) . '\'';
				$result = db_select($db, $sql);
				if (empty($result)) {
					// hash password
					$hashed_password = password_hash($password, PASSWORD_DEFAULT);
					$sql = 'INSERT INTO user (username, password, presentation, image, imgdesc, dispname) ';
					$sql .= 'VALUES (\'' . db_escape($db, $user) . '\', \'' . db_escape($db, $hashed_password) . '\', "No user presentation", "../images/placeholder.png", "Placeholder", \'' . db_escape($db, $user) . '\')';
					$result = db_query($db, $sql);
					if (!empty($result)) {
						echo("User registration complete, please login");
					}
				}
				else {
					echo("User already exists, please login");
				}
				db_disconnect($db);
			}
			
			if (isset($_POST["name"]) && isset($_POST["password"]) && isset($_POST["botcheck"])) {
				if ($_POST["name"] != NULL && $_POST["password"] != NULL && $_POST["botcheck"] != NULL) {
					if (strpos($_POST["name"], '<script>') !== false || strpos($_POST["password"], '<script>') !== false || strpos($_POST["botcheck"], '<script>') !== false) {
						echo "Don't do that!";
					}
					else {
						$name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
						$name = trim($name);
						$botcheck = filter_var($_POST['botcheck'], FILTER_SANITIZE_STRING);
						$botcheck = trim($botcheck);
						$botcheck = strtolower($botcheck);
						if (ctype_alnum($name) && ctype_alnum($botcheck)){
							if ($botcheck == 'six' || $botcheck == '6') {
								if (isset($_POST["login"])) {
									login($name, $_POST["password"]);
								}
								if (isset($_POST["register"])) {
									register($name, $_POST["password"]);
								}
							}
							else {
								echo 'Try that equation again (hint: it is 6)';
							}
						}
						else {
							echo 'Fields contain invalid characters';
						}
					}
				}
			}
			?>
		</div>
	</div>
</div>
<?php include "../footer.php"; ?>
</body>
</html>