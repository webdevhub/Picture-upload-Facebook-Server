<?php
$is_error = true;
$error = "";

//check if its a POST request or not
if(count($_POST)!=0) {
	$code = $_GET['code'];
	//check if the user has uploaded any image or not
	if($_FILES['image']['name']!="") {
		if(move_uploaded_file($_FILES['image']['tmp_name'],'pic/'.$_FILES['image']['name'])) {

			$album = $_POST["album"];
			  
			$name = $_FILES['image']['name'];

			$extValid = checkExt($name);

			if($extValid) {
			        $caption = $_POST['caption'];
				$result = postViaCurl("https://graph.facebook.com/$album/photos",$code,$name,$caption);
				$is_error = false;
			}
			else {
				$error = "You have not uploaded a valid image file.";
				$is_error = true;
			}
		}
		else {
		//display this message if there was an error in moving the image file
			$error = "oops..there was error";
			$is_error = true;
		}
	}
	else {
	//if the user has not uploaded any file
		$error = "You have not uploaded any file";
		$is_error = true;
	}
}
else {
      //if the user trying to access this page directly without passing any POST content
	$error = "Please pass the required parameters.";
	$is_error = true;
}

//this is the function which does all the magic
function postViaCurl($link,$code,$name,$caption) {
	$ch = curl_init();
	//set the URL
	curl_setopt($ch,CURLOPT_URL,$link);
	curl_setopt($ch, CURLOPT_HEADER, false); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
	curl_setopt($ch,CURLOPT_POST,3);
	//feed in the data
	curl_setopt($ch, CURLOPT_POSTFIELDS, array("access_token"=>"$code","source"=>"@"."pic/$name","message"=>"$caption"));
	//post the data
	$result = curl_exec($ch);
	curl_close($ch);
	return $result;
}

//this function basically checks whether posted image is in correct format or not
function checkExt($name) {
	$extValid = false;
	$regex = "/.*\.(.*)/";
	preg_match($regex,$name,$matches);
	$ext = $matches[1];
	$extValid = false;
	if($ext=="jpg"||$ext=="png"||$ext=="gif"||$ext=="jpeg"||$ext=="JPG") {
		$extValid = true;
	}
	return $extValid;
}
?>


<html>
	<head>
		<title>Facebook Picture Upload</title>
		<link rel="stylesheet" href="style.css" />
	</head>
	<body>
		<div id="wrapper">

		<?php if($is_error) 
			echo '<span class="error">'.$error.'</span>';
		else	
			echo $result; ?>
		</div>
	</body>
</html>