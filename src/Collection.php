<?php


namespace FireCentaur;


class Collection implements \ArrayAccess, \Iterator, \Countable, \JsonSerializable
{
    protected $array;

    private $index = 0;

    /**
     * @inheritDoc
     */
    public function offsetExists($offset)
    {
        return isset($this->array[$offset]);
    }

    /**
     * @inheritDoc
     */
    public function offsetGet($offset)
    {
        return $this->array[$offset];
    }

    /**
     * @inheritDoc
     */
    public function offsetSet($offset, $value)
    {
        if ($offset) {
            $this->array[$offset] = $value;
        } else {
            $this->array[] = $value;
        }
    }

    /**
     * @inheritDoc
     */
    public function offsetUnset($offset)
    {
        unset($this->array[$offset]);
    }

    /**
     * @inheritDoc
     */
    public function current()
    {
        return $this->array[$this->index];
    }

    /**
     * @inheritDoc
     */
    public function next()
    {
        $this->index++;
    }

    /**
     * @inheritDoc
     */
    public function key()
    {
        return $this->index;
    }

    /**
     * @inheritDoc
     */
    public function valid()
    {
        return isset($this->array[$this->index]);
    }

    /**
     * @inheritDoc
     */
    public function rewind()
    {
        $this->index = 0;
    }

    /**
     * @inheritDoc
     */
    public function count()
    {
        return count($this->array);
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return $this->array;
    }
}