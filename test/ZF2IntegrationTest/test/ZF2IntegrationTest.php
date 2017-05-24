<?php

namespace ZF2IntegrationTest;

use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

class ZF2IntegrationTest extends TestCase
{
    public function testIntegration()
    {
        $port = getenv('ENLITEPRO_TEST_PORT') ?: '8081';
        
        $process = new Process(sprintf(
            'php -S 0.0.0.0:%s -t %s/public %s/public/index.php',
            $port,
            dirname(__DIR__),
            dirname(__DIR__)
        ));
        
        $process->start();
        
        sleep(1);
        
        self::assertTrue($process->isRunning());
    
        $logFile = 'data/log/application.log';
        
        @unlink($logFile);
        
        self::assertFileNotExists($logFile);
        
        $client = new Client();
        
        $response = $client->request('GET', 'http://localhost:' . $port . '/');
        
        self::assertSame(200, $response->getStatusCode());
        
        self::assertFileExists($logFile);
        self::assertContains('default.INFO: Application\IndexController::indexAction', file_get_contents($logFile));
    }
}
