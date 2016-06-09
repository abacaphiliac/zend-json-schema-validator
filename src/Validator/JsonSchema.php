<?php

namespace Abacaphiliac\Zend\Validator;

use JsonSchema\RefResolver;
use JsonSchema\Uri\UriResolver;
use JsonSchema\Uri\UriRetriever;
use JsonSchema\Validator;
use Zend\Json\Exception\RuntimeException;
use Zend\Json\Json;
use Zend\Validator\AbstractValidator;
use Zend\Validator\Exception;

class JsonSchema extends AbstractValidator
{
    /** @var string */
    private $file;
    
    /** @var string[] */
    private $messages = array();

    /** @var string[] */
    private $errorKeys = array();
    
    /** @var string */
    private $messagePrefix = '[';
    
    /** @var string */
    private $messageSuffix = ']';
    
    /** @var string */
    private $messageAttributeDelimiter = '] [';

    /**
     * JsonSchema constructor.
     * @param array|null|\Traversable $options
     */
    public function __construct($options = null)
    {
        $this->errorKeys = array(
            'property',
            'constraint',
            'message',
        );
        
        parent::__construct($options);
    }

    /**
     * Returns true if and only if $value meets the validation requirements
     *
     * If $value fails validation, then this method returns false, and
     * getMessages() will return an array of messages that explain why the
     * validation failed.
     *
     * @param  mixed $value
     * @return bool
     * @throws Exception\RuntimeException If validation of $value is impossible
     */
    public function isValid($value)
    {
        $schema = $this->getSchema();
        
        try {
            $decoded = $this->decodeValue($value);
        } catch (RuntimeException $e) {
            $this->messages = array(
                $e->getMessage(),
            );
            return false;
        }
        
        $validator = new Validator();
        $validator->check($decoded, $schema);

        if ($validator->isValid()) {
            return true;
        }
        
        $this->messages = array_map(array($this, 'formatError'), $validator->getErrors());
        
        return false;
    }

    /**
     * @param mixed[] $error
     * @return string
     */
    public function formatError(array $error)
    {
        // Get the error properties that should be returned to the user.
        $properties = array_intersect_key($error, array_flip($this->errorKeys));

        // Format error properties as bracket-delimited key-value-pairs.
        $message = urldecode(http_build_query($properties, null, $this->messageAttributeDelimiter));

        // Validation error may be returned as JSON. Replace quotes so that the message looks nice.
        return str_replace('"', "'", $this->messagePrefix . $message . $this->messageSuffix);
    }

    /**
     * @return \stdClass
     * @throws \Zend\Validator\Exception\RuntimeException
     */
    private function getSchema()
    {
        $file = $this->file;
        if (!$file) {
            throw new Exception\RuntimeException('Validator option `file` is required and cannot be empty.');
        }

        $url = parse_url($file);
        if (!array_key_exists('scheme', $url)) {
            $file = 'file://' . realpath($file);
        }

        $refResolver = new RefResolver(new UriRetriever(), new UriResolver());

        try {
            return $refResolver->resolve($file);
        } catch (\Exception $e) {
            throw new Exception\RuntimeException(
                sprintf('Could not load JSON Schema: %s', $e->getMessage()),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * @param mixed $value
     * @return \stdClass
     * @throws \Zend\Json\Exception\RuntimeException
     */
    private function decodeValue($value)
    {
        $encoded = $value;
        if (!is_scalar($value)) {
            $encoded = Json::encode($value);
        }

        return Json::decode($encoded);
    }

    /**
     * @return string[]
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param string $file
     */
    public function setFile($file)
    {
        $this->file = $file;
    }

    /**
     * @return string[]
     */
    public function getErrorKeys()
    {
        return $this->errorKeys;
    }

    /**
     * @param string[] $errorKeys
     */
    public function setErrorKeys(array $errorKeys)
    {
        $this->errorKeys = $errorKeys;
    }

    /**
     * @return string
     */
    public function getMessagePrefix()
    {
        return $this->messagePrefix;
    }

    /**
     * @param string $messagePrefix
     */
    public function setMessagePrefix($messagePrefix)
    {
        $this->messagePrefix = $messagePrefix;
    }

    /**
     * @return string
     */
    public function getMessageSuffix()
    {
        return $this->messageSuffix;
    }

    /**
     * @param string $messageSuffix
     */
    public function setMessageSuffix($messageSuffix)
    {
        $this->messageSuffix = $messageSuffix;
    }

    /**
     * @return string
     */
    public function getMessageAttributeDelimiter()
    {
        return $this->messageAttributeDelimiter;
    }

    /**
     * @param string $messageAttributeDelimiter
     */
    public function setMessageAttributeDelimiter($messageAttributeDelimiter)
    {
        $this->messageAttributeDelimiter = $messageAttributeDelimiter;
    }
}
