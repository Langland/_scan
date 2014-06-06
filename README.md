Show a merged view of client folders in all the people's repos.

Have 
_scan/client1/job1/link:"dev1.client1.job1"
dev1/clients/client1/job1/...


There could potentially be multiple links in the job folder, if two devs had a project with the same name.

CONVENTIONS

- Start all job folders with the job number
- Make your repo name "lld_" (lima, lima, delta, underscore) and your Developer initials, e.g. mine would be lld_MSt
- Have a folder in the root of your repo named "clients"
- Inside clients folder, have a folder for each client. We should coordinate these names so they are the same
- Inside each named client folder, have all the job folders on that level
- When you check out these developer repos, place them next to the _scan repo in the same folder


RUNNING SCAN

1. Open a terminal window
2. Navigate to the _scan folder
3. Run the scan file by entering
$ php scan.php
(note optional arguments for prefix and clients folder name, arguments are passed after the command, separated by spaces, e.g.
$ php scan.php myPrefix projects
4. The result is that the _scan folder is populated so that we have visibility of all jobs in all repos, with links to each



