<?php
$loggedIn = false;
$is_error = false;
//Please fill the following variables with your Facebook App specific data
$app_id = '###############';
$client_id = '################################';
$access_token = "";

//status is 1 when Facebook sends the user back to this page
if($_GET['status']=='1') {
	//Redirected from Facebook
	if(isset($_GET['code'])) {
		$loggedIn = true;
		$is_error = false;

		$code = $_GET['code'];

		//get the access_token from Facebook
		$link = "https://graph.facebook.com/oauth/access_token?client_id=".$app_id."&redirect_uri=".urlencode("http://demo.webdevhub.net/facebook/srvrPicUpload/?status=1")."&client_secret=".$client_id."&code=".$code;
		$result = file_get_contents($link);

		//Regular expression for separating access_token from the response we get from facebook
		/*the following Regular expression says "Get me two groups of text such that first one is 
		after a string called 'access_token=' & before a string called '&expires='(string 2) and 
		the other group whatever is left after string 2" **/
		$regex = "/access_token=(.*)&expires=(.*)/";
		preg_match($regex,$result,$matches);
		$access_token = $matches[1]; //access_token

		//get all the albums of the loggedIn user
		$link1 = "https://graph.facebook.com/me/albums?access_token=".$access_token;
		$result = file_get_contents($link1);
		
		$json = json_decode($result);

		$albums = array();
		$albums_id = array();
		$data = $json->data;
		if($data!="") {
			$i = 0;
			foreach($json->data as &$album) {
				$albums[$i] = $album->name;
				$albums_id[$i] = $album->id;
				$i += 1;
			}
		}
	}
	else if($_GET['error_reason']=='user_denied'){
		//if the user has denied permission then display accordingly
		$loggedIn = false;
		$is_error = true;
		$msg = "You have denied access permissions to the app.";
		
	}
}
?>

<html>
	<head>
		<title>Facebook Picture Upload</title>
		<link rel="stylesheet" href="style.css" />
	</head>
	<body>
		<div id="wrapper">
			<h1>Upload Picture to Facebook</h1>
			<h2>via server upload</h2>
			<?php //check if the user is loggedIn
			if(!$loggedIn){ ?>
				<a class="btn1" href="<?php echo "https://www.facebook.com/dialog/oauth?client_id=175679282469826&redirect_uri=".urlencode("http://demo.webdevhub.net/facebook/srvrPicUpload/?status=1")."&scope=publish_stream,user_photos" ;?>">Login</a>
				<br/><br/>

			<?php 
			    //check if there is any error to be reported
			if($is_error) {?>
				<span class="error"><?php echo $msg;?></span>
			<?php } ?>

			<?php } //if the user is loggedIn and there is no error then display the image upload form
			else if($loggedIn && !$is_error){?>
				<form action="upload.php?code=<?php echo $access_token; ?>" enctype="multipart/form-data" method="post">
					<input type="file" name="image" class="btn"/><br/>
					<select name="album" class="btn">
					<?php
					  $k = 0;
					  foreach($albums as &$i) {
						echo '<option value="'.$albums_id[$k].'">'.$i.'</option>';
						$k += 1;
					  }
					  if($k > 0) {
						echo '<option value="me">'.'No Albums'.'</option>';
					  }
					?>
					</select><br/>
					<input type="text" name="caption" class="btn"/><br/>
					<input type="submit" value="Upload..." class="btn"/><br/>
				</form>
			<?php } ?>
		</div>
	</body>
</html>