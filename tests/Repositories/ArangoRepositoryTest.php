<?php

namespace AppTests\Repositories;

use PHPUnit\Framework\TestCase;
use triagens\ArangoDb\CollectionHandler;
use triagens\ArangoDb\DocumentHandler;
use triagens\ArangoDb\ServerException;

class ArangoRepositoryTest extends TestCase
{
    protected $app;
    protected $repository;
    protected $collectionHandler;
    protected $documentHandler;

    public function setUp()
    {
        $this->app = $this->createApplication();
        $this->repository = new Repository($this->app);
        $this->collectionHandler = new CollectionHandler($this->app['arango']);
        $this->documentHandler = new DocumentHandler($this->app['arango']);
    }

    public function tearDown()
    {
        $this->repository->resetCollection();
    }

    public function createApplication()
    {
        return require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'bootstrap.php';
    }

    private function makeDocument()
    {
        return [
            'data' => 'fake',
            'value' => 'foo',
            'test' => random_int(1, 10)
        ];
    }

    public function testCreateCollection()
    {
        $this->assertTrue($this->collectionHandler->has($this->repository->getCollection()));
    }

    /**
     * Test validation method on Repository implementation
     */
    public function testValidate()
    {
        $document = [
            'docAttribute' => 'Anything'
        ];

        $this->assertFalse($this->repository->validate($document));

        $document['test'] = 1;

        $this->assertTrue($this->repository->validate($document));
    }

    public function testSaveDocument()
    {
        $document = $this->makeDocument();

        $this->assertGreaterThan(0, $this->repository->save($document));
    }

    public function testFind()
    {
        $document = $this->makeDocument();

        $id = $this->repository->save($document);

        $document = $this->repository->find($id);

        $this->assertInstanceOf('triagens\ArangoDb\Document', $document);
        $this->assertNull($this->repository->find(random_int(1, 10)));
    }

    public function testGetAll()
    {
        $this->repository->save($this->makeDocument());
        $this->repository->save($this->makeDocument());

        $all = $this->repository->all();
        $this->assertInstanceOf('triagens\ArangoDb\Cursor', $all);
        $this->assertEquals($all->getCount(), 2);
    }
}
