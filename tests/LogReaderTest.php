<?php

namespace Stevebauman\LogReader\Tests;

use Mockery as m;
use Illuminate\Support\Facades\Cache;
use Stevebauman\LogReader\LogReader;

class LogReaderTest extends TestCase
{
    /**
     * Stores the mocked app instance
     *
     * @var
     */
    protected $app;

    /**
     * Stores the current log reader instance
     *
     * @var LogReader
     */
    protected $logReader;

    /**
     * Stores the stubs directory path
     *
     * @var string
     */
    protected $stubsPath = '';

    /**
     * Stores the stubs log path
     *
     * @var string
     */
    protected $stubsLogPath = '';

    public function setUp()
    {
        parent::setUp();

        $this->setPaths();

        $this->setLogReader();

        $this->insertStubsOnSingleLog();

        $this->insertStubsOnDateLog();
    }

    public function tearDown()
    {
        parent::tearDown();

        $this->removeStubsOnSingleLog();

        $this->removeStubsOnDateLog();
    }

    protected function setPaths()
    {
        $this->stubsPath = __DIR__ . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR;

        $this->stubsLogPath = $this->stubsPath . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR;
    }

    protected function setLogReader()
    {
        $this->logReader = new LogReader();

        $this->logReader->setLogPath($this->stubsLogPath);
    }

    protected function insertStubsOnSingleLog()
    {
        $readyContent = file_get_contents($this->stubsPath.'ready-log.log');

        file_put_contents($this->stubsLogPath.'laravel.log', $readyContent);
    }

    protected function insertStubsOnDateLog()
    {
        $readyContent = file_get_contents($this->stubsPath.'ready-log.log');

        file_put_contents($this->stubsLogPath.'laravel-2015-03-20.log', $readyContent);
    }

    protected function removeStubsOnSingleLog()
    {
        file_put_contents($this->stubsLogPath.'laravel.log', '');
    }

    protected function removeStubsOnDateLog()
    {
        file_put_contents($this->stubsLogPath.'laravel-2015-03-20.log', '');
    }

    public function testGet()
    {
        Cache::shouldReceive('has')->times(8)->andReturn(false);

        $entries = $this->logReader->get();

        $this->assertEquals(8, $entries->count());
        $this->assertInstanceOf('Illuminate\Support\Collection', $entries);
    }

    public function testFind()
    {
        $entry = $this->logReader->get()->first();

        $foundEntry = $this->logReader->find($entry->id);

        $this->assertEquals($foundEntry->id, $entry->id);
        $this->assertEquals($foundEntry->header, $entry->header);
        $this->assertEquals($foundEntry->date, $entry->date);
    }

    public function testMarkRead()
    {
        $entry = $this->logReader->get()->first();

        Cache::shouldReceive('rememberForever')->once()->andReturn($entry);

        $this->assertEquals($entry, $entry->markRead());
    }

    public function testMarkAllRead()
    {
        $marked = $this->logReader->markRead();

        $this->assertEquals(8, $marked);
    }

    public function testDelete()
    {
        $entry = $this->logReader->get()->first();

        $entry->delete();

        $entries = $this->logReader->get();

        $this->assertEquals(7, $entries->count());
    }

    public function testDeleteAll()
    {
        $deleted = $this->logReader->delete();

        $this->assertEquals(8, $deleted);
    }

    public function testLevelGet()
    {
        $entries = $this->logReader->level('info')->get();

        $this->assertEquals(1, $entries->count());
    }

    public function testDateGet()
    {
        $date = strtotime('2015-03-20');

        $entries = $this->logReader->date($date)->get();

        $this->assertEquals(8, $entries->count());
    }

    public function testMethodsGet()
    {
        $date = strtotime('2015-03-20');

        $entries = $this->logReader->date($date)->level('info')->get();

        $this->assertEquals(1, $entries->count());
    }

    public function testOrderByDateGetDesc()
    {
        $entries = $this->logReader->orderBy('date', 'desc')->get();

        $levelStr = '';

        foreach($entries as $entry)
        {
            $levelStr .= $entry->level;
        }

        $this->assertEquals('debugnoticealertcriticalemergencyerrorwarninginfo', $levelStr);
    }

    public function testOrderByDateGetAsc()
    {
        $entries = $this->logReader->orderBy('date', 'asc')->get();

        $levelStr = '';

        foreach($entries as $entry)
        {
            $levelStr .= $entry->level;
        }

        $this->assertEquals('infowarningerroremergencycriticalalertnoticedebug', $levelStr);
    }

    public function testOrderByLevelGetDesc()
    {
        $entries = $this->logReader->orderBy('level', 'desc')->get();

        $levelStr = '';

        foreach($entries as $entry)
        {
            $levelStr .= $entry->level;
        }

        $this->assertEquals('warningnoticeinfoerroremergencydebugcriticalalert', $levelStr);
    }

    public function testOrderByLevelGetAsc()
    {
        $entries = $this->logReader->orderBy('level', 'asc')->get();

        $levelStr = '';

        foreach($entries as $entry)
        {
            $levelStr .= $entry->level;
        }

        $this->assertEquals('alertcriticaldebugemergencyerrorinfonoticewarning', $levelStr);
    }

    public function testSetLogPathFailure()
    {
        $this->logReader->setLogPath('test');

        $this->setExpectedException('Stevebauman\LogReader\Exceptions\UnableToRetrieveLogFilesException');

        $this->logReader->get();
    }

    public function testDateFailure()
    {
        $this->setExpectedException('Stevebauman\LogReader\Exceptions\InvalidTimestampException');

        $this->logReader->date('test')->get();
    }
}
