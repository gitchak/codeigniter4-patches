<?php

use Tatter\Patches\BaseHandler;

class BaseHandlerTest extends \Tests\Support\VirtualTestCase
{
	public function testIsDefinedVirtualPath()
	{
		$test = defined('VIRTUALPATH');

		$this->assertTrue($test);
	}

	public function testConstructCreatesWorkspace()
	{
		$patches = new BaseHandler($this->config);

		$this->assertTrue(is_dir($this->config->basePath));
	}

	public function testGetWorkspace()
	{
		$patches = new BaseHandler($this->config);

		$this->assertTrue(is_dir($patches->getWorkspace()));
	}

	public function testSetWorkspaceCreatesDirectory()
	{
		$patches = new BaseHandler($this->config);

		$patches->setWorkspace(VIRTUALPATH . 'foo');

		$this->assertTrue(is_dir(VIRTUALPATH . 'foo'));
	}

	public function testGatherSourcesFindsAll()
	{
		$patches = new BaseHandler($this->config);

		$this->assertEquals(['TestSource', 'Framework'], array_keys($patches->getSources()));
	}

	public function testIgnoringSkipsSource()
	{
		$this->config->ignoredSources[] = 'Framework';
		$patches = new BaseHandler($this->config);

		$this->assertEquals(['TestSource'], array_keys($patches->getSources()));	
	}

	public function testGetSourcesReturnsInstances()
	{
		$patches = new BaseHandler($this->config);
		$sources = $patches->getSources();

		$this->assertInstanceOf('Tatter\Patches\Interfaces\SourceInterface', reset($sources));
	}

	public function testGatherPaths()
	{
		$this->config->ignoredSources[] = 'Framework';

		$patches = new BaseHandler($this->config);
		$paths   = $patches->gatherPaths();

		$expected = [
			[
				'from' => SUPPORTPATH . 'files/nested/cat.jpg',
				'to'   => 'tester/nested/cat.jpg',
			],
			[
				'from' => SUPPORTPATH . 'files/lorem.txt',
				'to'   => 'tester/lorem.txt',
			],
		];

		$this->assertCount(2, $paths);
		$this->assertEquals($expected, $paths);
	}

	public function testCopyPathCreatesDirectory()
	{
		$patches = new BaseHandler($this->config);
		$patches->copyPath(SUPPORTPATH . 'files/lorem.txt', VIRTUALPATH . 'foobar/lorem.txt');

		$this->assertTrue(is_dir(VIRTUALPATH . 'foobar'));
	}

	public function testCopyPathCopiesFile()
	{
		$patches = new BaseHandler($this->config);
		$patches->copyPath(SUPPORTPATH . 'files/lorem.txt', VIRTUALPATH . 'foobar/lorem.txt');

		$this->assertTrue(is_file(VIRTUALPATH . 'foobar/lorem.txt'));
	}

	public function testCopyPathsCopiesFilesToDestination()
	{
		$patches = new BaseHandler($this->config);
		$patches->copyPaths(VIRTUALPATH . 'foo');

		$this->assertTrue(file_exists(VIRTUALPATH . 'foo/tester/lorem.txt'));
	}

	public function testCopyPathsReturnsPaths()
	{
		$this->config->ignoredSources[] = 'Framework';

		$patches = new BaseHandler($this->config);

		$paths = $patches->copyPaths(VIRTUALPATH . 'foo');

		$expected = [
			'tester/nested/cat.jpg',
			'tester/lorem.txt',
		];

		$this->assertEquals($expected, $paths);
	}

	public function testIsSameFileSucceeds()
	{
		$patches = new BaseHandler($this->config);
		$patches->copyPath(SUPPORTPATH . 'files/lorem.txt', VIRTUALPATH . 'foobar/lorem.txt');

		$this->assertTrue($patches->isSameFile(SUPPORTPATH . 'files/lorem.txt', VIRTUALPATH . 'foobar/lorem.txt'));
	}

	public function testIsSameFileFailsDifferentFiles()
	{
		$patches = new BaseHandler($this->config);

		$this->assertFalse($patches->isSameFile(SUPPORTPATH . 'files/lorem.txt', SUPPORTPATH . 'files/nested/cat.jpg'));
	}

	public function testIsSameFileFailsFileMissing()
	{
		$patches = new BaseHandler($this->config);

		$this->assertFalse($patches->isSameFile(SUPPORTPATH . 'files/lorem.txt', SUPPORTPATH . 'notafile.pdf'));
	}
}
