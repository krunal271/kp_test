<?php

	$downloadUrl = "";
	if(isset($_POST['downloadUrl']) && !empty($_POST['downloadUrl']))
	{
		$downloadUrl = $_POST['downloadUrl'];
	}

	// $fileId = '1ZdR3L3qP4Bkq8noWLJHSr_iBau0DNT4Kli4SxNc2YEo';
	// $content = $driveService->files->export($fileId, 'text/html', array('alt' => 'media' ));

	$content = file_get_contents($downloadUrl);
	echo $content;

?>