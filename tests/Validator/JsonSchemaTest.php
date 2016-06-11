<?php

namespace AbacaphiliacTest\Zend\Validator;

use Abacaphiliac\Zend\Validator\JsonSchema;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamFile;

class JsonSchemaTest extends \PHPUnit_Framework_TestCase
{
    /** @var vfsStreamFile */
    private $schema;
    
    /** @var JsonSchema */
    private $sut;

    protected function setUp()
    {
        parent::setUp();

        $this->schema = vfsStream::newFile('schema.json');

        $schemas = vfsStream::setup('schemas');
        $schemas->addChild($this->schema);

        file_put_contents($this->schema->url(), json_encode(array(
            '$schema' => 'http://json-schema.org/draft-04/schema#',
            'type' => 'object',
            'properties' => array(
                'Foo' => array(
                    'type' => 'string'
                ),
            ),
        )));

        $this->sut = new JsonSchema(array(
            'file' => $this->schema->url(),
        ));
    }

    /**
     * @expectedException \Zend\Validator\Exception\RuntimeException
     */
    public function testNotIsValidDueToUnspecifiedSchemaFile()
    {
        $this->sut->setFile(null);
        
        $this->sut->isValid('{"Foo":"Bar"}');
    }

    /**
     * @expectedException \Zend\Validator\Exception\RuntimeException
     */
    public function testNotIsValidDueToInvalidSchemaFile()
    {
        $this->sut->setFile(__FILE__);
        
        $this->sut->isValid('{"Foo":"Bar"}');
    }

    public function testNotIsValidDueToInvalidSyntax()
    {
        $actual = $this->sut->isValid('InvalidJsonString');

        self::assertFalse($actual);

        self::assertCount(1, $this->sut->getMessages());
    }
    
    public function testIsValidEncodedJson()
    {
        $actual = $this->sut->isValid('{"Foo":"Bar"}');

        self::assertTrue($actual);
    }
    
    public function testIsValidDecodedJson()
    {
        $actual = $this->sut->isValid(json_decode('{"Foo":"Bar"}'));

        self::assertTrue($actual);
    }
    
    public function testIsValidJsonDecodedArray()
    {
        $actual = $this->sut->isValid(json_decode('{"Foo":"Bar"}', true));

        self::assertTrue($actual);
    }
    
    public function testNotIsValidDueToSchemaValidation()
    {
        $actual = $this->sut->isValid('{"Foo":1234}');

        self::assertFalse($actual);

        self::assertCount(1, $this->sut->getMessages());
    }
    
    public function testDefaultMessageFormat()
    {
        $actual = $this->sut->formatError(array(
            'property' => 'foo',
            'constraint' => 'bar',
            'message' => 'error',
        ));
        
        self::assertEquals('[property=foo] [constraint=bar] [message=error]', $actual);
    }
    
    public function testSetFile()
    {
        $sut = new JsonSchema();
        
        self::assertNull($sut->getFile());
        
        $sut->setFile($expected = $this->schema->url());
        
        self::assertEquals($expected, $sut->getFile());
    }
    
    public function testSetErrorKeys()
    {
        self::assertArraySubset(
            array('property', 'constraint', 'message'),
            $this->sut->getErrorKeys()
        );
        
        $this->sut->setErrorKeys(array('foo', 'bar'));

        self::assertArraySubset(
            array('foo', 'bar'),
            $this->sut->getErrorKeys()
        );
    }
    
    public function testSetMessagePrefix()
    {
        self::assertEquals('[', $this->sut->getMessagePrefix());
        
        $this->sut->setMessagePrefix($expected = '{');
        
        self::assertEquals($expected, $this->sut->getMessagePrefix());
    }
    
    public function testSetMessageSuffix()
    {
        self::assertEquals(']', $this->sut->getMessageSuffix());
        
        $this->sut->setMessageSuffix($expected = '}');
        
        self::assertEquals($expected, $this->sut->getMessageSuffix());
    }
    
    public function testSetMessageAttributeDelimiter()
    {
        self::assertEquals('] [', $this->sut->getMessageAttributeDelimiter());
        
        $this->sut->setMessageAttributeDelimiter($expected = '} {');
        
        self::assertEquals($expected, $this->sut->getMessageAttributeDelimiter());
    }
}
