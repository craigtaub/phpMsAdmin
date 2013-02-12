phpMSAdmin 0.18
Written By: Adam Voigt (avoigt@phpmsadmin.org)


#### General ####

This Program is released under the GNU GPL license, please
read LICENSE.txt if you have questions as to what you can
or cannot do with this software.

This software is BETA quality, which means that none of the
features have been extensively tested. This software has gone
through preliminary testing however, and nothing APPEARS to
cause any dataloss in the test databases. However, your results
may vary, and should you discover a hole in the program, please
email your results to the above email address with instructions
on what you were doing, and how you produced the error. If you
correct the problem through source modification, please send
the file you updated and I will merge the bug fix into the main
source tree.

I suggest you check back often to the phpMSAdmin website
(http://phpmsadmin.sf.net) to download new releases as
they appear (which will be quite often during initial phases
until all bugs and security fixes that are discovered, are
merged in).

See the information below for installation procedures. A more
detailed manual is available in the docs directory in
"Open Office", "Microsoft Word", "PDF", and "HTML" formats.


#### What Doesn't Work ####

The only feature I've run into that doesn't consistently seem
to work is user account modification.
You are highly suggested to avoid this area, and while
this feature will most likely just not work (rather then cause
loss of existing permissions/users/data), it's best not to chance
it on production database servers.


#### Installation ####

Installation is fairly simple, just extract the files from
the tar file (WinZip will do this for the windows folks).

Adjust permissions (recursively) for the entire directory,
it should be owned by the webserver user/group.

Optional / Recommended: Install a HTACCESS file for authentication
beyond what comes with the software it self. While not stricly
necessary, it does add another layer of protection, especially with
the security system as-of-yet, not extensively tested.

Optional / Recommended: Copy the program files to an HTTPS accessible
directory, so that all communications will be encrypted.


#### Use ####

Windows:

Install a datasource for your SQL server using the "Data Sources (ODBC)"
control panel, under Administrative Tools. Use the datasource name you used in
setting up the datasource, for the datasource name in the actual phpMSAdmin
software.

Linux / Unix:

Install FreeTDS (0.63 tested to work).
phpMSAdmin has the capability to auto-parse DSN's setup in your freetds.conf,
if you wish to enable this feature, you must be sure that the "config.php"
file, in the "inc" directory, has the correct path to your freetds.conf. If it
does not, or if you do not wish to use this feature, you may set the "detectionoff"
setting in the config.php to "true" and you may simply enter the name of the datasource
in phpMSAdmin, ensuring it matches the name of the datasource in your freetds.conf file.