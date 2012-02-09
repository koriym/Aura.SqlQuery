<?php
namespace Aura\Sql;
use PDO;

/**
 * Test class for Profiler.
 * Generated by PHPUnit on 2012-02-02 at 14:41:36.
 */
class ProfilerTest extends \PHPUnit_Framework_TestCase
{
    protected $profiler;
    
    protected $pdo;
    
    protected function setUp()
    {
        $this->pdo = new Pdo('sqlite::memory:');
        $this->pdo->query('CREATE TABLE test (id INTEGER, name VARCHAR(16))');
        $this->profiler = new Profiler;
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    public function testSetAndIsActive()
    {
        $this->assertFalse($this->profiler->isActive());
        $this->profiler->setActive(true);
        $this->assertTrue($this->profiler->isActive());
        $this->profiler->setActive(false);
        $this->assertFalse($this->profiler->isActive());
    }

    public function testExec()
    {
        $text = 'SELECT * FROM test';
        $stmt = $this->pdo->prepare($text);
        $data = ['foo' => 'bar'];
        $this->profiler->exec($stmt, $data);
        
        // should be nothing in the profile
        $expect = [];
        $actual = $this->profiler->getProfiles();
        $this->assertSame($expect, $actual);
        
        // now make it active
        $this->profiler->setActive(true);
        $this->profiler->exec($stmt, $data);
        $actual = $this->profiler->getProfiles();
        $this->assertSame(1, count($actual));
        $this->assertSame($text, $actual[0]->text);
        $this->assertSame($data, $actual[0]->data);
    }

    public function testCall()
    {
        $pdo = $this->pdo;
        $func = function() use ($pdo) {
            return $pdo->query("SELECT * FROM test");
        };
        
        $text = '__SELECT__';
        
        $this->profiler->call($func, $text);
        
        // should be nothing in the profile
        $expect = [];
        $actual = $this->profiler->getProfiles();
        $this->assertSame($expect, $actual);
        
        // now make it active
        $this->profiler->setActive(true);
        $this->profiler->call($func, $text);
        $actual = $this->profiler->getProfiles();
        $this->assertSame(1, count($actual));
        $this->assertSame($text, $actual[0]->text);
    }
}
