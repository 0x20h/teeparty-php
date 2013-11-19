<?php
namespace Teeparty\Task;

/**
 * Interface for workers.
 */
class Context implements \ArrayAccess, \Serializable {

    private $data;

    public function __construct(array $data)
    {
        $this->validate($data);
        $this->data = $data;
    }

    public function offsetGet($offset)
    {
        if (!$this->offsetExists($offset) {
            return null;
        }
        
        return $this->data[$offset];
    }
   
    
    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }


    public function offsetSet($offset, $value)
    {
    }
 
    public function offsetUnset($offset)
    {
    }

    public function serialize()
    {
        return serialize($this->data);
    }

    public function unserialize($data)
    {
        $this->data = unserialize($data);
    }

    /**
     * Check data values for objects and validate serializable
     */
    private function validate(array $data)
    {
        foreach ($data as $item) {
            if (is_object($item) && ! ($item instanceof \Serializable)) {
                throw new Exception('cannot store object of type '.get_class($item));
            }

            if (is_array($item) {
                $this->validate($item);
            }
        }
    }
}
