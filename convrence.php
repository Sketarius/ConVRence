<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');  
require_once('lib/Cgi.php');
require_once('lib/Login.php');
require_once('lib/Html.php');
require_once('lib/App.php');
session_start();

function main() 
{
	$theApp = null;
	if(isset($_SESSION['theApp'])) {
		$theApp = unserialize($_SESSION['theApp']);
	} else {
		$theApp = new App();
	}

	$f = $theApp->getCgi()->getValue('f');

	// If the request is coming from the ConVRence application process it.
	if(strcmp($f, 'conv_request')  == 0) {
		// Login Request
		if(strcmp($theApp->getCgi()->getValue('s'), 'app_login') == 0) {
			$username = $theApp->getCgi()->getValue('username');
			$password = md5($theApp->getCgi()->getValue('password'));

			if($theApp->getLogin()->doLogin($username, $password)) {
				echo "1";
			} else {
				echo "0";
			}
		}

		// Create Conference Instance
		if(strcmp($theApp->getCgi()->getValue('s'), 'create_conference') == 0) {
			$roomName = $theApp->getCgi()->getValue('roomName');
			$userName = $theApp->getCgi()->getValue('userName');

			if($conv_id = $theApp->getLogin()->createRoom($roomName, $userName)) {
				echo $conv_id;
			} else {
				echo "0";
			}
		}

		// Get Users Slides XML
		if(strcmp($theApp->getCgi()->getValue('s'), 'get_slides') == 0) {
			$userName = $theApp->getCgi()->getValue('userName');

			if($theApp->getLogin()->getUserSlides($userName)) {

			} else {
				echo "0";
			}
		}

		// Get Custom Avatar XML
		if(strcmp($theApp->getCgi()->getValue('s'), 'get_avatar') == 0) {
			$userName = $theApp->getCgi()->getValue('userName');
			if(!$theApp->getLogin()->getCustomAvatar($userName)) {
				echo "0";
			}
		}
	// If the request is coming from a browser render it.
	} else {
		$url = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		$theApp->getHTML()->setPageTitle("ConVRence");
		$theApp->getHTML()->addCSS('css/main.css');
		$theApp->getHTML()->displayHeader();
		echo "<div id=\"conv_title\"><img alt='logo' src='assets/conv_logo_800w.png' /></div>\n";

		// Attempt to log in.
		if(strcmp($theApp->getCgi()->getValue('f'), 'web_login') == 0) {
			$username = $theApp->getCgi()->getValue('username');
			$password = md5($theApp->getCgi()->getValue('password'));

			if($theApp->getLogin()->doLogin($username, $password)) {
				$_SESSION['theApp'] = serialize($theApp);
			} else {
				echo "Incorrect Username or Password <br />\n";
			}
		}

		// User is already logged in.
		if($theApp->getLogin()->isLoggedIn()) {
			$prog_name = "convrence.php";
			//$prog_name = $theApp->getHTML()->getProgName($url);	
			//echo "prog_name = $prog_name";
			printf("<div id=\"conv_menu\"> <a href=\"%s\">Dashboard</a> | <a href=\"%s?f=slide_management\">Slide Management</a> | <a href=\"%s?f=avatar_manage\">Avatar Management</a> | <a href=\"%s?f=web_logout\">Logout</a></div>", $prog_name, $prog_name, $prog_name, $prog_name);
			
			if(strcmp($theApp->getCgi()->getValue('f'), 'web_logout') == 0) {
				echo "You are now logged out.";
				session_destroy();
			} else if(strcmp($theApp->getCgi()->getValue('f'), 'slide_management') == 0) {
				slide_management($theApp, $prog_name);
			} else if(strcmp($theApp->getCgi()->getValue('f'), 'file_upload') == 0) {
				// Upload stuff here:
				//echo "uploading";
				$filename = $_FILES["zip_file"]["name"];
				$source = $_FILES["zip_file"]["tmp_name"];
				$type = $_FILES["zip_file"]["type"];

				$name = explode(".", $filename);
				$accepted_types = array('application/zip', 'application/x-zip-compressed', 'multipart/x-zip', 'application/x-compressed');

				foreach($accepted_types as $mime_type) {
					if($mime_type == $type) {
						$okay = true;
						break;
					} 
				}

				$continue = strtolower($name[1]) == 'zip' ? true : false;
				if(!$continue) {
					echo "The file you are trying to upload is not a .zip file. Please try again.";
				} else {
					$target_path = "user_slide_zips/".$theApp->getLogin()->getUsername() . "_" . $filename;
					if(move_uploaded_file($source, $target_path)) {
						$zip = new ZipArchive();
						$x = $zip->open($target_path);

						// If zip file is open
						if($x === true) {
							// If directory exists already wipe it for new one.
							if(file_exists("user_slides/".$theApp->getLogin()->getUsername())) {
								exec("rm -R "."user_slides/".$theApp->getLogin()->getUsername());
							}

							// Extract files to user's folder.
							$zip->extractTo("user_slides/".$theApp->getLogin()->getUsername());
							$zip->close();

							// Get array of files that are now in user's folder.
							$scan_dir = scandir("user_slides/".$theApp->getLogin()->getUsername());
							natsort($scan_dir);


							//echo "User ID is: " . $theApp->getLogin()->getUserID($theApp->getLogin()->getUsername()) . "<br />";

							$theApp->getLogin()->setUserSlides($theApp->getLogin()->getUserID($theApp->getLogin()->getUsername()), $scan_dir);							
							

							unlink($target_path);
						}
					}
				}

				slide_management($theApp, $prog_name);
			} else if(strcmp($theApp->getCgi()->getValue('f'), 'avatar_manage') == 0) {
				echo "<div id=\"conv_dashboard\">";
				echo "	<div class=\"center\"> <h1>Avatar Management</h1> </div>";
				echo "	<div class=\"left\">";
				echo "	</div>";
				echo "	<div class=\"right\" style=\"margin-right: 20px;\">";
				echo "		<table>\n";
				echo "			<tr>";
				echo "				<th>";
				echo "					Face:";
				echo "				</th>";
				echo "				<td>";
				echo "					<select>";
				echo "					</select>";
				echo "				</td>";
				echo "			</tr>";
				echo "		</table>\n";
				echo "	</div>";
				echo "</div>";
			}
			else {
				echo "<div id=\"conv_dashboard\"> Dashboard here </div>";
			}

		// User is not logged in.
		} else {
			
			$theApp->getHTML()->beginForm('POST', $theApp->getHTML()->getProgName($url));
			echo "<input type=\"hidden\" name=\"f\" value=\"web_login\">\n";
			echo "<div id=\"conv_login\">\n";
			echo "<table>\n";
			echo "	<tr>\n";
			echo "		<td>\n";
			echo "			Username: ";
			echo "		</td>\n";
			echo "		<td>\n";
			$theApp->getHTML()->inputItem("username", "textbox", "");
			echo "		</td>\n";
			echo "	</tr>\n";
			echo "	<tr>\n";
			echo "		<td>\n";
			echo "			Password: ";
			echo "		</td>\n";
			echo "		<td>\n";
			$theApp->getHTML()->inputItem("password", "password", "");
			echo "		</td>\n";
			echo "	</tr>\n";
			echo "<tr><td></td><td><input type=\"submit\" value=\"Login\" /></td></tr>\n";
			echo "</table>\n";
			echo "</div>\n";
			$theApp->getHTML()->endForm();
		}
		$theApp->getHTML()->displayFooter();
	}
}

function slide_management($theApp, $prog_name) {
	$slides_result = $theApp->getLogin()->getUsersSlidesResult($theApp->getLogin()->getUsername());
	echo "<div id=\"conv_dashboard\">\n";
	echo "	<h1 class=\"center\"> Slide Management </h1>";
	echo "	<div class=\"left\">";
	printf("	<form action=\"%s\" method=\"post\" enctype=\"multipart/form-data\">\n", $prog_name);
	echo "<input type=\"hidden\" name=\"f\" value=\"file_upload\">\n";
	echo "		Upload *.zip file containing *.png slides <br /> <input type=\"file\" name=\"zip_file\">";
	echo "<input type=\"submit\" value=\"upload\" />";
	echo "		</form>\n";
	echo "	</div>";
	printf("<div class=\"right\"> %s existing slides: ", $theApp->getLogin()->getUsername());
	echo "		<table>\n";
	echo "			<tr>\n";
	echo "				<th> Slide # </th>\n";
	echo "				<th> Slide URL: </th>\n";
	echo "			</tr>\n";
	$slide_number = 1;
	while($row = $slides_result->fetch_assoc()) {
		echo "<tr>";
		printf("	<td> %s </td>\n", $slide_number);
		printf("	<td> <a href = \"%s\">%s</a> </td>\n", $row['slide_url'], $row['slide_url']);
		echo "</tr>";
		$slide_number++;
	}
	echo "		</table>\n";
	echo " </div>";
	echo " <div style=\"clear:both;\"> </div>";
	echo "</div>\n";
}

main();
?>