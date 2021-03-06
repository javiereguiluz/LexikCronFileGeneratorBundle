<?php

namespace Lexik\Bundle\CronFileGeneratorBundle\Tests\Cron;

use Lexik\Bundle\CronFileGeneratorBundle\Cron\Configuration;
use Lexik\Bundle\CronFileGeneratorBundle\Cron\DumpFile;
use PHPUnit\Framework\TestCase;
use Twig\Environment;

class DumpFileTest extends TestCase
{
    private $outputDir;

    protected function setUp()
    {
        $this->outputDir = \sys_get_temp_dir().'/lexik';
        @\mkdir($this->outputDir);
    }

    protected function tearDown()
    {
        @\unlink($this->outputDir.'/cron_file');
        \rmdir($this->outputDir);
    }

    public function testDumpFile()
    {
        $templating = $this->createMock(Environment::class);
        $templating->expects($this->any())->method('render')->willReturn('content');

        $configuration = new Configuration([
            'env_available' => [
                'staging', 'prod',
            ],
            'user' => [
                'staging' => 'project_staging',
                'prod' => 'project_prod',
            ],
            'php_version' => 'php7.3',
            'absolute_path' => [
                'staging' => 'path/to/staging',
                'prod' => 'path/to/prod',
            ],
            'output_path' => $this->outputDir.'/cron_file',
            'crons' => [
                [
                    'name' => 'test',
                    'command' => 'app:test',
                    'env' => [
                        'staging' => '* * * * *',
                        'prod' => '* 5 * * *',
                    ],
                ],
            ],
        ]);

        $dumpFile = new DumpFile($templating, $configuration);
        $dumpFile->init('staging');

        $this->assertEquals('content', $dumpFile->dryRun());

        $this->assertEquals($this->outputDir.'/cron_file', $dumpFile->dumpFile());
    }

    /**
     * @expectedException \Lexik\Bundle\CronFileGeneratorBundle\Exception\CronEmptyException
     */
    public function testEmptyCron()
    {
        $templating = $this->createMock(Environment::class);
        $templating->expects($this->never())->method('render');

        $configuration = new Configuration([
            'env_available' => [
                'staging',
            ],
            'user' => [
                'staging' => 'project_staging',
            ],
            'php_version' => 'php7.3',
            'absolute_path' => [
                'staging' => 'path/to/staging',
            ],
            'output_path' => $this->outputDir.'/cron_file',
            'crons' => [],
        ]);

        $dumpFile = new DumpFile($templating, $configuration);
        $dumpFile->init('staging');
    }
}
