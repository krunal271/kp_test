<!DOCTYPE html>
<html>
	<head>
		<style type="text/css">
		td, th {
				padding: 8px;
		}

		.file_detail{
			float: left;
			width: 60%;
			border:1px solid #000;
			margin-left:10px;
			padding:10px;
		}
		</style>

		<script type="text/javascript">
			// Your Client ID can be retrieved from your project in the Google
			// Developer Console, https://console.developers.google.com
			var CLIENT_ID = '458069412576-2tkk9indg17n0jnm1hqig1stusn6r1ts.apps.googleusercontent.com';

			var SCOPES = ['https://www.googleapis.com/auth/drive'];

			/**
			 * Check if current user has authorized this application.
			 */
			function checkAuth() {
				gapi.auth.authorize(
					{
						'client_id': CLIENT_ID,
						'scope': SCOPES.join(' '),
						'immediate': true
					}, handleAuthResult);
			}

			/**
			 * Handle response from authorization server.
			 *
			 * @param {Object} authResult Authorization result.
			 */
			function handleAuthResult(authResult) {
				var authorizeDiv = document.getElementById('authorize-div');
				if (authResult && !authResult.error) {
					// Hide auth UI, then load client library.
					authorizeDiv.style.display = 'none';
					loadDriveApi();
				} else {
					// Show auth UI, allowing the user to initiate authorization by
					// clicking authorize button.
					authorizeDiv.style.display = 'inline';
				}
			}

			/**
			 * Initiate auth flow in response to user clicking authorize button.
			 *
			 * @param {Event} event Button click event.
			 */
			function handleAuthClick(event) {
				gapi.auth.authorize(
					{client_id: CLIENT_ID, scope: SCOPES, immediate: false},
					handleAuthResult);
				return false;
			}

			/**
			 * Load Drive API client library.
			 */
			function loadDriveApi() {
				gapi.client.load('drive', 'v2', listFiles);
			}

			/**
			 * Print files.
			 */
			function listFiles() {
				var request = gapi.client.drive.files.list({
						'maxResults': 10,
						// 'q' : "mimeType='application/vnd.google-apps.document' or mimeType='application/vnd.openxmlformats-officedocument.wordprocessingml.document'" 
						'q' : "mimeType='application/vnd.google-apps.document'" 
					});

					request.execute(function(resp) {

						if(resp.nextPageToken)
						{
							var onclick_func = 'loadDriveApiMore("'+resp.nextPageToken+'")';
							$("#next_page").show();
							$("#next_page").attr("onclick",onclick_func);
						}

						var files = resp.items;

						if (files && files.length > 0) {
							for (var i = 0; i < files.length; i++) {
								var file = files[i];
								console.log(file);
								var file_print = "";
								if(file.exportLinks)
								{
									var exportLinks = file.exportLinks;
									var exportLink_html = exportLinks["text/html"];
									var file_print = 'masterDownloadFile("'+exportLink_html+'",callback)';
								}
								else
								{
									var exportLinks = file.downloadUrl;
									// var exportLink_html = exportLinks["text/html"];
									var file_print = 'masterDownloadFile("'+exportLinks+'",callback)';
								}

								var file_data = "<tr><td><img src='"+file.iconLink+"' />&nbsp;"+file.title+"</td><td><a href="+file.alternateLink+" target='_blank'>Drive</a> &nbsp;&nbsp;&nbsp;&nbsp; <a href='javascript:void(0);' onclick='"+file_print+"'>Browser</a></td></tr>";
								appendPre(file_data);
								// appendPre(file.title + ' (' + file.id + ')');
							}
						} else {
							appendPre('No files found.');
						}
					});
			}


			/**
			 * Load Drive API client library.
			 */
			function loadDriveApiMore(nextPageToken) {
				gapi.client.load('drive', 'v2', listMoreFiles(nextPageToken));
			}


			/**
			* 
			* listMoreFiles(nextPageToken)
			*
			*/
			function listMoreFiles(nextPageToken)
			{
				var request = gapi.client.drive.files.list({
					'maxResults': 10,
					'q' : "mimeType='application/vnd.google-apps.document'",
					'pageToken' : nextPageToken 
				});

				request.execute(function(resp) {

					if(resp.nextPageToken)
					{
						var onclick_func = 'listMoreFiles("'+resp.nextPageToken+'")';
						$("#next_page").show();
						$("#next_page").attr("onclick",onclick_func);
					}
					else
					{
						$("#next_page").hide();    
					}

					var files = resp.items;
					if (files && files.length > 0) {
						for (var i = 0; i < files.length; i++) {
							var file = files[i];
							// console.log(file);
							var file_print = 'masterDownloadFile("'+file.downloadUrl+'",callback)';
							var file_data = "<tr><td><img src='"+file.iconLink+"' />&nbsp;"+file.title+"</td><td><a href="+file.alternateLink+" target='_blank'>Drive</a> &nbsp;&nbsp;&nbsp;&nbsp; <a href='javascript:void(0);' onclick='"+file_print+"'>Browser</a></td></tr>";
							appendPre(file_data);
							// appendPre(file.title + ' (' + file.id + ')');
						}
					} else {
						appendPre('No files found.');
					}
				});
			}


			/**
			 * Append a pre element to the body containing the given message
			 * as its text node.
			 *
			 * @param {string} message Text to be placed in pre element.
			 */
			function appendPre(message) {
				var pre = $("#output").append(message);
				// var textContent = document.createTextNode(message + '\n');
				// document.getElementById('output').innerHTML = textContent;
				// pre.appendChild(textContent);
			}


		/**
		 * Print a file's metadata.
		 *
		 * @param {String} fileId ID of the file to print metadata for.
		 */
		function printFile(fileId) {
			var request = gapi.client.drive.files.get({
					'fileId': fileId
			});
			request.execute(function(resp) {
				if (!resp.error) {
					console.log('Title: ' + resp.title);
					console.log('Description: ' + resp.description);
					console.log('MIME type: ' + resp.mimeType);
				} else if (resp.error.code == 401) {
					// Access token might have expired.
					checkAuth();
				} else {
					console.log('An error occured: ' + resp.error.message);
				}
			});
		}


		/**
		 * Download a file's content.
		 *
		 * @param {File} file Drive File instance.
		 * @param {Function} callback Function to call when the request is complete.
		 */
		// function downloadFile(downloadUrl) {
		// 	var form_data = {
		// 		downloadUrl : downloadUrl
		// 	}
		// 	$.ajax({
		// 		url: 'http://www.webmechanic.in/drive_test/download.php',
		// 		data: form_data,
		// 		type : "POST",
		// 		success: function(response) {
		// 			$(".file_detail").html(response);
		// 		}
		// 	});
		// }


		/**
		* 
		* masterDownloadFile(downloadUrl)
		*
		*/

		function masterDownloadFile(downloadUrl, callback) {
		  if (downloadUrl) {
		    var accessToken = gapi.auth.getToken().access_token;
		    var xhr = new XMLHttpRequest();
		    xhr.open('GET', downloadUrl);
		    xhr.setRequestHeader('Authorization', 'Bearer ' + accessToken);
		    xhr.onload = function() {
		      callback(xhr.responseText);
		    };
		    xhr.onerror = function() {
		      callback("Error");
		    };
		    xhr.send();
		  } else {
		    callback("No Download Url find");
		  }
		}

		/**
		*
		* callback(str) 
		*
		*/

		function callback(str)
		{
			console.log(str);
			$(".file_detail").html(str);
		}

		</script>
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
		<script src="https://apis.google.com/js/client.js?onload=checkAuth"></script>
	</head>
	<body>
		<div id="authorize-div" style="display: none">
			<span>Authorize access to Drive API</span>
			<!--Button for the user to click to initiate auth sequence -->
			<button id="authorize-button" onclick="handleAuthClick(event)">
				Authorize
			</button>
		</div>
				<h2>Files</h2>
				<div id="files" style="float:left;">
					<div class="file_list" style="float:left;width: 36%;">
						<table border="1" width="100%" cellspacing="0" cellpadding="0" id="output">
							<tr>
								<th>File Name</th>
								<th>Action</th>
							</tr>
						</table>
					</div>
					<div class="file_detail">
							<!-- <center>* Click on briwser view to content of your file</center> -->
							Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
							tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam,
							quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo
							consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse
							cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non
							proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
					</div>
				</div>
				<a href="javascript:void(0);" id="next_page" style="display:none;">Load more files...</a>
		</body>
</html>