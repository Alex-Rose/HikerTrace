<?php
namespace Helper_Mongo
{
    class Cursor implements \ArrayAccess
    {
        protected $cursor = null;

        function __construct($cursor)
        {
            $this->cursor = $cursor;
        }

        public function __get($param)
        {
            if ($param != 'cursor')
            {
                return $this->offsetGet($param);
            }
            else
            {
                return $this->cursor;
            }
        }

        public function offsetExists($offset)
        {
            return array_key_exists($offset, $this->cursor);
        }

        public function offsetGet($offset)
        {
            if ($this->offsetExists($offset))
            {
                return $this->cursor[$offset];
            }
            else
            {
                return null;
            }
        }

        public function offsetSet($offset, $value)
        {
            throw new \Exception('Method not supported');
        }

        public function offsetUnset($offset)
        {
            throw new \Exception('Method not supported');
        }
    }
}