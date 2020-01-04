<?php

namespace EnliteMonologTest\Module;

use EnliteMonolog\Module;
use PHPUnit\Framework\TestCase;

/**
 * @covers \EnliteMonolog\Module
 */
class ModuleTest extends TestCase
{
    /** @var Module */
    private $sut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sut = new Module();
    }
    
    public function testGetAutoloaderConfig()
    {
        $actual = $this->sut->getAutoloaderConfig();
        
        self::assertIsArray($actual);
    }
    
    public function testAutoloaderConfigIsSerializable()
    {
        self::assertIsArray(unserialize(serialize($this->sut->getAutoloaderConfig())));
    }
    
    public function testGetConfig()
    {
        $actual = $this->sut->getConfig();
        
        self::assertIsArray($actual);
    }
    
    public function testConfigIsSerializable()
    {
        self::assertIsArray(unserialize(serialize($this->sut->getConfig())));
    }
}
