#!/usr/bin/php
<?php
//
// FTP Backup Script for Plesk
// v1.0 - 05/09/2014
// 
// by Jay Versluis - http://wpguru.co.uk
// find the latest version here:
// 
//
echo "\nFTP Backup for Plesk v1.0\n---------------------------------\n\n";
// exit "Terminating\n\n";

// add your FTP credentials here
$ftp_server = "ftpserver";
$ftp_user = "ftpuser";
$ftp_password = "password";

// maximum number of backup files to keep on FTP
$maxbackups = 30;

// add a prefix for your backup
$prefix = 'BACKUP-'; 

// PLESK utilities directory
$pleskbin = "/usr/local/psa/bin/";

//------------------------------------------
// NO NEED TO EDIT ANYTHING BEYOND THIS LINE
// -----------------------------------------

// do a backup
createbackup();

// create FTP connection
$conn_id = ftp_connect($ftp_server);
$login_result = ftp_login($conn_id, $ftp_user, $ftp_password);
$contents = ftp_nlist($conn_id, ".");

// sort the array
rsort($contents);

// show all entries
directory($contents);

// delete first item
echo "\nCleaning up directory, leaving only $maxbackups...\n";
cleanupftp();

// close connection - we're done here!
ftp_close($conn_id);

// --------------------------------------

//
// FUNCTIONS
//

// create backup to FTP repo 
function createbackup() {
	global $ftp_server, $ftp_user, $ftp_password, $pleskbin;
	$filename = createfilename() . ".tar";
	$command = $pleskbin . 'pleskbackup server --output-file=';
	$command = $command . "ftp://$ftp_user:$ftp_password@$ftp_server";
	$command = $command . "/$filename";
	 
	 /*
	 if(exec($command)) {
		 echo "Backup was created successfully.\n";
	 } else {
		 echo "Houston, we had a problem with the backup command:\n $command \n";
	 }
	 */
	 echo "Creating Backup with filename: $filename \n\n";
	 exec($command);
}

// delete a single backup on FTP
function deletebackup ($file) {
	global $conn_id;
	echo "Deleting ".$file."...\n";
	if (ftp_delete($conn_id, $file)) {
		  echo "success!\n";
	} else {
		  echo "That didn't work... dang!\n";
	}
	return;
}

// delete all but the latest $maxbackups
function cleanupftp() {
	
	global $maxbackups, $conn_id;
	$contents = ftp_nlist($conn_id, ".");
	rsort($contents);
	$currentbackups = count($contents);
	
	if ($currentbackups > $maxbackups) {
		
		// delete overspill items
		for ($i = $maxbackups; $i < $currentbackups; $i++) {
			deletebackup($contents[$i]);
		}
	}
	
	// list directory
	$contents = ftp_nlist($conn_id, ".");
	directory($contents);
}

// show current dirtectory
function directory($contents) {
	
	echo "Here comes the directory listing: \n";
	foreach($contents as $entry) {
		echo $entry . "\n";
	}
	return;
}

// create a filename for the backup
function createfilename() {
	
	global $prefix;
	date_default_timezone_set('UTC');
	return $prefix . date('ymd-His');
}

// clear out PMM sessions (currently not in use)
function cleanpmm () {
	echo "Cleaning out old PMM sessions...\n\n";
	exec("rm -rf /usr/local/psa/PMM/sessions/*");
}

?>
