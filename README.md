Plesk Backup Script
===================

PHP Command Line Tool to create FTP backups in Plesk. Written in PHP for Linux servers (RHEL and CentOS). 
Can be called via cron job from the command line in addition to any automated backups scheduled via the Plesk web interface.


### Usage

- download the backup.php file and save it on your server
- add execute permissions (chmod +x backup.php)
- amend your FTP details
- set the desired amount of backups you'd like to keep on the FTP server (default is 30)
- if necessary, amend the path to the Plesk tools directory (on CentOS it's /usr/local/psa/bin)
- if desired, change the backup prefix (it's BACKUP- by default)
- invoke the script manually or via cron - no parameters are necessary

Backups are saved as .tar files and can be restored via the Plesk web interface. The file name will be BACKUP-YYYYMMDD-HHMMSS.tar.
For the automatic deletion to work properly, make sure you have a sub folder that is not using any other files - otherwise the latest backup may be deleted.
To specify a subfolder, you can pass it as part of the prefix parameter, for example:

    $prefix = 'subfolder/BACKUP-'; 

To specify a subfolder on the remote FTP space, change the variable $remote_dir like this:

    $remote_dir = "/path/to/userdir";
    
To exclude domains, change the variable $excludedomains. Multiple domains can be excluded with comma like this:

    $excludedomains = "somedomain.com,anotherdomain.com";

Backups are created for the entire server. If you only want to backup a single domain (say domain.com), please change line 75 of the script from

    	$command = $pleskbin . "pleskbackup server --exclude-domain=$excludedomains --output-file=";
    	
to

    	$command = $pleskbin . 'pleskbackup domains-name domain.com --output-file=';
    	
In future versions I may add additional parameters in the config section for such options.


### Reasons for writing this script

As of Plesk 12 you can only schedule a single automated backup and you must decide if this is a local or an FTP backup. 
Some of us would like to scheudle both options becasue both can have their advantages:

- an FTP backup is stored off-site, which is great for disaster recovery.
- a local backup on the other hand can be quicker to create and to restore
- a local server wide backup creates individual domain backups automatically, whereas an FTP backup does not

The Plesk team are working on additional backup options which may come in future versions of Plesk - but right this is as good as it gets.


### Known Issues

This script relies on the pleskbackup CLI tool which will automatically create a local backup without encryption, then send it to the FTP server.
Sadly it does this without maintaining a proper timestamp on the FTP server (all remote backups appear to have been created on the 1st of November 1999).
Future versions may include a fix for this by manually uploading the package to FTP which maintains the timestamp.
