<?php
require_once realpath(dirname(__FILE__) . '/gac/src/Google/autoload.php');

$client = new Google_Client();

session_start();

$client->setClientId('458069412576-2tkk9indg17n0jnm1hqig1stusn6r1ts.apps.googleusercontent.com');
$client->setClientSecret('nAq-ZE5Ih_xb2sn5IZEudCbK');
$client->setRedirectUri('http://www.webmechanic.in/drive_test');
$client->setScopes(array('https://www.googleapis.com/auth/drive.metadata.readonly'));


if (isset($_GET['code']) || (isset($_SESSION['access_token']) && $_SESSION['access_token'])) {
		if (isset($_GET['code'])) {
				$client->authenticate($_GET['code']);
				$_SESSION['access_token'] = $client->getAccessToken();
		} else
				$client->setAccessToken($_SESSION['access_token']);

		$service = new Google_Service_Drive($client);

		//*******************   Insert a file   ******************//
		// $file = new Google_Service_Drive_DriveFile();
		// $file->setTitle('a.png');
		// $file->setDescription('A test document');
		// $file->setMimeType('image/png');

		// $data = file_get_contents('a.png');

		// $createdFile = $service->files->insert($file, array(
		//       'data' => $data,
		//       'mimeType' => 'image/png',
		//       'uploadType' => 'multipart'
		//     ));

		// echo "<pre>";
		// print_r($createdFile);

		$all_files = "";
		$all_files = retrieveAllFiles($service);
		echo "<pre>";
		print_r($all_files);
		die;

		echo "<h2>My Google Drive Files</h2>";
		echo "<table border='1' width='100%' cellspacing='0' cellpadding='0'>";
		foreach ($all_files as $dp) {
			echo "<tr>";
			echo "<td>".$dp['title']."</td>";
			echo "<td><a href=".$dp['alternateLink']." target='_blank'> View </a>&nbsp;&nbsp;&nbsp;<a href='javascript:void(0)'> Delete </a></td>";
			echo "</tr>";
		}
		echo "</table>";

} else {
		$authUrl = $client->createAuthUrl();
		header('Location: ' . $authUrl);
		exit();
}


/**
 * Retrieve a list of File resources.
 *
 * @param Google_Service_Drive $service Drive API service instance.
 * @return Array List of Google_Service_Drive_DriveFile resources.
 */
function retrieveAllFiles($service) {
	$result = array();
	$pageToken = NULL;

	do {
		try {
			$parameters = array();
			if ($pageToken) {
				$parameters['pageToken'] = $pageToken;
			}
			$files = $service->files->listFiles($parameters);

			$result = array_merge($result, $files->getItems());
			$pageToken = $files->getNextPageToken();
		} catch (Exception $e) {
			print "An error occurred: " . $e->getMessage();
			$pageToken = NULL;
		}
	} while ($pageToken);
	return $result;
}

echo "test";



echo "Krunal";