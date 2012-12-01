# Phabricator-ForcePull

This addon is a semi-nasty hack to allow you to force your phabricator install to pull and ingest changes from specific repos, so that you can 
build it into your post-commit handlers and not have to wait the minimum 15 seconds for repo pulls.

## Installation

* Copy `PhabricatorRepositoryForcePullDaemon.php` into `phabricator/src/applications/repository/daemon/`
* Modify `phabricator/src/applications/repository/daemon/PhabricatorRepositoryPullLocalDaemon.php` to remove the 'final' from the class
* Run `arc liberate phabricator/src/` to rebuild the class map

You can now trigger an immediate pull like this:

    PHABRICATOR_ENV=custom/myconf ./phabricator/bin/phd launch RepositorySinglePullDaemon -- A

Where `A` is the callsign of the repo you want to pull from.
