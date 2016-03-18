<?php 
/*
 * Copyright 2013 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
include_once "templates/base.php";
echo pageHeader("Simple API Access");

/************************************************
  Make a simple API request using a key. In this
  example we're not making a request as a
  specific user, but simply indicating that the
  request comes from our application, and hence
  should use our quota, which is higher than the
  anonymous quota (which is limited per IP).
 ************************************************/
require_once realpath(dirname(__FILE__) . '/../src/Google/autoload.php');

$client = new Google_Client();

// Get your credentials from the console
$client->setClientId('458069412576-2tkk9indg17n0jnm1hqig1stusn6r1ts.apps.googleusercontent.com');
$client->setClientSecret('nAq-ZE5Ih_xb2sn5IZEudCbK');
$client->setRedirectUri('http://www.webmechanic.in/drive_test');
$client->setScopes(array('https://www.googleapis.com/auth/drive.file'));

// session_start();

if (isset($_GET['code']) || (isset($_SESSION['access_token']) && $_SESSION['access_token'])) {
    if (isset($_GET['code'])) {
        $client->authenticate($_GET['code']);
        $_SESSION['access_token'] = $client->getAccessToken();
    } else
        $client->setAccessToken($_SESSION['access_token']);

    $service = new Google_Service_Drive($client);

    //Insert a file
    $file = new Google_Service_Drive_DriveFile();
    $file->setTitle(uniqid().'.jpg');
    $file->setDescription('A test document');
    $file->setMimeType('image/jpeg');

    $data = file_get_contents('a.jpg');

    $createdFile = $service->files->insert($file, array(
          'data' => $data,
          'mimeType' => 'image/jpeg',
          'uploadType' => 'multipart'
        ));

    echo "<pre>";
    print_r($createdFile);

} else {
    $authUrl = $client->createAuthUrl();
    header('Location: ' . $authUrl);
    exit();
}
