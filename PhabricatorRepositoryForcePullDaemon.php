<?php

final class PhabricatorRepositoryForcePullDaemon
	extends PhabricatorRepositoryPullLocalDaemon {

	public function run() {

		$argv = $this->getArgv();
		array_unshift($argv, __CLASS__);
		$args = new PhutilArgumentParser($argv);
		$args->parse(array(
			array(
				'name'		=> 'repositories',
				'wildcard'	=> true,
				'help'		=> 'Pull specific __repositories__ instead of all.',
			)
		));

		$repo_names = $args->getArg('repositories');

		$repositories = $this->loadRepositories($repo_names);

		shuffle($repositories);
		$repositories = mpull($repositories, null, 'getID');

		foreach ($repositories as $id => $repository){

			if (!$repository->isTracked()) continue;

			 try {
				$callsign = $repository->getCallsign();
				$this->log("Updating repository '{$callsign}'.");

				$this->pullRepository($repository);

				$lock_name = get_class($this).':'.$callsign;
				$lock = PhabricatorGlobalLock::newLock($lock_name);
				$lock->lock();

				try {
					$this->discoverRepository($repository);
				} catch (Exception $ex) {
					$lock->unlock();
					throw $ex;
				}

				$lock->unlock();

			} catch (PhutilLockException $ex) {
				$this->log("Failed to acquire lock.");

			} catch (Exception $ex) {
				phlog($ex);
			}
		}
	}
}

