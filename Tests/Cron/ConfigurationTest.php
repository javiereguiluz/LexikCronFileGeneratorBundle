<?php

namespace Lexik\Bundle\CronFileGeneratorBundle\Tests\Cron;

use Lexik\Bundle\CronFileGeneratorBundle\Cron\Configuration;
use Lexik\Bundle\CronFileGeneratorBundle\Cron\Cron;
use PHPUnit\Framework\TestCase;

class ConfigurationTest extends TestCase
{
    public function testFullConfig()
    {
        $configuration = new Configuration($this->getFullConfig());

        $this->assertEquals('project_staging', $configuration->initWithEnv('staging')->getUser());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Env not availables. Use this: staging
     */
    public function testInitEnvIsAvailable()
    {
        $configuration = new Configuration($this->getFullConfig());
        $configuration->initWithEnv('test');
    }

    public function testInit()
    {
        $configuration = new Configuration($this->getFullConfig());
        $configuration->initWithEnv('staging');
        $this->assertEquals('project_staging', $configuration->getUser());
        $this->assertEquals('path/to/staging', $configuration->getAbsolutePath());
        $this->assertEquals('php7.3', $configuration->getPhpVersion());
        $this->assertEquals('staging', $configuration->getEnv());
        $this->assertEquals('path/to/cron_file', $configuration->getOutpath());

        /** @var Cron $cron */
        foreach ($configuration->getCrons() as $cron) {
            $this->assertEquals('test', $cron->getName());
            $this->assertEquals('app:test', $cron->getCommand());
            $this->assertEquals('* * * * *', $cron->getExpression());
        }
    }

    public function testEmptyCrons()
    {
        $configuration = new Configuration([
            'env_available' => [
                'staging',
            ],
            'user' => [
                'staging' => 'project_staging',
            ],
            'absolute_path' => [
                'staging' => 'path/to/staging',
            ],
            'crons' => [],
        ]);

        $this->assertEmpty($configuration->getCrons());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Env not availables. Use this: staging
     */
    public function testWithBadEnv()
    {
        new Configuration([
            'env_available' => [
                'staging',
            ],
            'user' => [
                'prod' => 'project_staging',
            ],
        ]);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage You have missing env. Use this: staging, prod
     */
    public function testWithMissingEnv()
    {
        new Configuration([
            'env_available' => [
                'staging', 'prod',
            ],
            'user' => [
                'staging' => 'project_staging',
            ],
        ]);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Env not availables. Use this: staging
     */
    public function testWithBadCronEnv()
    {
        new Configuration([
            'env_available' => [
                'staging',
            ],
            'user' => [
                'staging' => 'project_staging',
            ],
            'absolute_path' => [
                'staging' => 'path/to/staging',
            ],
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
    }

    private function getFullConfig()
    {
        return [
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
            'output_path' => 'path/to/cron_file',
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
        ];
    }
}
