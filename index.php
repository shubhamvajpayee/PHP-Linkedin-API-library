<?php

/*************** Imp Links 
https://www.linkedin.com/secure/developer (Register & get api keys for your app)
https://developer.linkedin.com/documents/profile-fields (Profile Fields)
http://developer.linkedin.com/documents/throttle-limits (Throtle Limits for api)
http://developer.linkedin.com/documents/request-and-response-headers (Response Headers)
https://developer.linkedin.com/documents/code-samples (Code samples)
*****************/

/*** NOTE : Access token once issued remains valid for 2 months so Linkedin doesnot provide facility to refresh tokens ***/



// Change these

define('API_KEY',      'xxxxxxxxxx'); //Your API key
define('API_SECRET',   'xxxxxxxxxxxx'); //Your API secret
define('REDIRECT_URI', 'http://www.example.com/index.php');
define('SCOPE',        'r_fullprofile r_emailaddress rw_nus w_messages');  // Set of permissions
define('STATE',      'AsdsujjhYOYbhdbwigwmmvdht');                                                 //Nonce (any long string)

// You'll probably use a database
session_name('linkedin');
session_start();

// OAuth 2 Control Flow
if (isset($_GET['error'])) {
	// LinkedIn returned an error
	print $_GET['error'] . ': ' . $_GET['error_description'];
	exit;
} elseif (isset($_GET['code'])) {
	// User authorized your application
       	if ($_SESSION['state'] == $_GET['state']) {

		// Get token so you can make API calls
		$accesstoken = getAccessToken();
               

	} else {
		// CSRF attack? Or did you mix up your states?
		exit;
	}
} else { 
	if ((empty($_SESSION['expires_at'])) || (time() > $_SESSION['expires_at'])) {
		// Token has expired, clear the state
		$_SESSION = array();
	}
	if (empty($_SESSION['access_token'])) {
		// Start authorization process
		getAuthorizationCode();
	}
}

// Congratulations! You have a valid token. Now your profile 
//For full profile elements refer https://developer.linkedin.com/documents/profile-fields

$user = fetch('GET', '/v1/people/~:(firstName,lastName,id,email-address)'); //Fields specified here will only be retrived 
print "Hello $user->firstName $user->lastName"."<br>";
print " $user->id"."<br>";
print " $user->email-address"."<br>";


//Posting Status to Linkedin Wall 



$postdata = '<share>
  <comment>Check out the LinkedIn Share API!</comment>
  <content>
    <title>LinkedIn Developers Documentation On Using the Share API</title>
    <description>Leverage the Share API to maximize engagement on user-generated content on LinkedIn</description>
    <submitted-url>https://developer.linkedin.com/documents/share-api</submitted-url>
    <submitted-image-url>http://m3.licdn.com/media/p/3/000/124/1a6/089a29a.png</submitted-image-url> 
  </content>
  <visibility> 
    <code>anyone</code> 
  </visibility>
</share>';
                $url = "https://api.linkedin.com/v1/people/~/shares?oauth2_access_token=".$accesstoken;
                
		$ch = curl_init($url);
		
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$postdata);
		curl_setopt($ch, CURLOPT_HTTPHEADER,array('Content-type: text/xml'));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($ch);
                 $info = curl_getinfo($ch);
		curl_close ($ch);
                
                       $status = $info['http_code'];

		
                     echo $status;




$email = "shubham.xtreamerider@gmail.com"; // Recieving User's Email

$postdata = '<?xml version=\'1.0\' encoding=\'UTF-8\'?>
<mailbox-item>
  <recipients>
    <recipient>
      <person path="/people/email='.$email.'">
        <first-name>Jason</first-name>
        <last-name>Ramsey</last-name>
      </person>
    </recipient>
  </recipients>
  <subject>Invitation to Connect</subject>
  <body>Please join my professional network on LinkedIn.</body>
  <item-content>
    <invitation-request>
      <connect-type>friend</connect-type>
    </invitation-request>
  </item-content>
</mailbox-item>';

                $url = "https://api.linkedin.com/v1/people/~/mailbox?oauth2_access_token=".$accesstoken;
               // echo $url;
		$ch = curl_init($url);
		
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$postdata);
		curl_setopt($ch, CURLOPT_HTTPHEADER,array('Content-type: text/xml'));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($ch);
                 $info = curl_getinfo($ch);
		curl_close ($ch);
                
                       $status = $info['http_code'];

		
                     echo $status;

exit;


function getAuthorizationCode() {
	$params = array('response_type' => 'code',
					'client_id' => API_KEY,
					'scope' => SCOPE,
					'state' => STATE, // unique long string
					'redirect_uri' => REDIRECT_URI,
			  );

	// Authentication request
	$url = 'https://www.linkedin.com/uas/oauth2/authorization?' . http_build_query($params);
	
	// Needed to identify request when it returns to us
	$_SESSION['state'] = $params['state'];

	// Redirect user to authenticate
	header("Location: $url");
	exit;
}
	
function getAccessToken() {
	$params = array('grant_type' => 'authorization_code',
                                        'code' => $_GET['code'],
					'client_id' => API_KEY,
                                        'client_secret' => API_SECRET,
                                        'redirect_uri' => REDIRECT_URI,
                                        
					
			  );
	
	// Access Token request
	$url = 'https://www.linkedin.com/uas/oauth2/accessToken?' . http_build_query($params);
	
	// Tell streams to make a POST request
	$context = stream_context_create(
					array('http' => 
						array('method' => 'POST',
	                    )
	                )
	            );

	// Retrieve access token information
	$response = file_get_contents($url,false,$context);

	// Native PHP object, please
	$token = json_decode($response);

	// Store access token and expiration time
	$_SESSION['access_token'] = $token->access_token; // guard this! 
	$_SESSION['expires_in']   = $token->expires_in; // relative time (in seconds)
	$_SESSION['expires_at']   = time() + $_SESSION['expires_in']; // absolute time
 
$accesstoken = $token->access_token;
	return $accesstoken;
}

function fetch($method, $resource, $body = '') {
	$params = array('oauth2_access_token' => $_SESSION['access_token'],
					'format' => 'json',
			  );
	
	// Need to use HTTPS
	$url = 'https://api.linkedin.com' . $resource . '?' . http_build_query($params);
	// Tell streams to make a (GET, POST, PUT, or DELETE) request
	$context = stream_context_create(
					array('http' => 
						array('method' => $method,
	                    )
	                )
	            );


	// Hocus Pocus
	$response = file_get_contents($url, false, $context);

	// Native PHP object, please
	return json_decode($response);
}





?>
