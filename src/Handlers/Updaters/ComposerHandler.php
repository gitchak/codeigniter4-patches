<?php namespace Tatter\Patches\Handlers\Updaters;

use Composer\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Tatter\Patches\Codex;
use Tatter\Patches\Handlers\BaseHandler;
use Tatter\Patches\Exceptions\UpdateException;
use Tatter\Patches\Interfaces\UpdaterInterface;

class ComposerHandler extends BaseHandler implements UpdaterInterface
{
	/**
	 * Call Composer programmatically to update all vendor files
	 * https://stackoverflow.com/questions/17219436/run-composer-with-a-php-script-in-browser#25208897
	 *
	 * @throws UpdateException
	 */
	public function update()
	{
		$application = new Application();
		$params      = [
			'command'       => 'update',
			'--working-dir' => $this->codex->config->rootPath,
		];
		
		// Suppress Composer output during testing
		if (ENVIRONMENT === 'testing')
		{
			$params['--quiet'] = true;
		}

		$input = new ArrayInput($params);

		// Prevent $application->run() from exiting the script
		$application->setAutoExit(false);

		// Returns int 0 if everything went fine, or an error code
		$result = $application->run($input);

		if ($result !== 0)
		{
			throw UpdateException::forComposerFailure($result);
		}
	}
}
