#!/usr/bin/php
<?php
//
// FTP Backup Script for Plesk
// v1.2 - 05/20/2017
// ======================================
// Extended by Benjamin Chojnowski - https://github.com/jicao/Plesk-Backup
// Extended by Michael Papesch - https://github.com/Cranke/Plesk-Backup
// Original by Jay Versluis - http://wpguru.co.uk
// find the latest version here: https://github.com/versluis/Plesk-Backup
// ======================================
// License: GNU General Public License v2 or later
// License URI: http://www.gnu.org/licenses/gpl-2.0.html

echo "\nFTP Backup for Plesk v1.2\n---------------------------------\n\n";
// exit "Terminating\n\n";

// add your FTP credentials here
$ftp_server = "";
$ftp_user = "";
$ftp_password = "";

// exclude Domains Domain1,Domain2
# $excludedomains = "somedomain.com,anotherdomain.com";
$excludedomains = "";

// exclude pattern
# $excludepattern = "/some/path/to*";
$excludepattern = "";

// Remote directory
# $remote_dir = "/path/to/userdir";
$remote_dir = "";

// maximum number of backup files to keep on FTP
$maxbackups = 7;

// add a prefix for your backup
$prefix = 'backup_';

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
ftp_chdir($conn_id, $remote_dir);
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
	global $ftp_server, $ftp_user, $ftp_password, $pleskbin, $excludedomains, $excludepattern, $remote_dir;
	$filename = createfilename() . ".tar";
	if(!empty($excludedomains)) { $exc_dom = '--exclude-domain='.$excludedomains; }
	else { $exc_dom = ''; }
	if(!empty($excludepattern)) { $exc_pat = '--exclude-pattern='.$excludepattern; }
	else { $exc_pat = ''; }
	$command = $pleskbin . "pleskbackup server $exc_dom $exc_pat --output-file=";
	$command = $command . "ftp://$ftp_user:$ftp_password@$ftp_server";
	$command = $command . "$remote_dir/$filename";
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
	return $prefix . date('ymdHi');
}

// clear out PMM sessions (currently not in use)
function cleanpmm () {
	echo "Cleaning out old PMM sessions...\n\n";
	exec("rm -rf /usr/local/psa/PMM/sessions/*");
}

?>
