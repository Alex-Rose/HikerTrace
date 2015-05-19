<?php defined('SYSPATH') or die('No direct script access.');

    class Helper_Mongo
    {
        protected $connection = null;
        protected $db = null;
        protected $collection = null;
        protected $cursor = null;

        function __construct($collection)
        {
            $this->connection = self::connection($collection);
            $this->db = $this->connection->aep;
            $this->collection = $this->db->$collection;
        }

        public function __get($param)
        {
            if ($param == 'cursor')
            {
                return $this->cursor;
            }
        }

        public function query($query, $sort = null)
        {
            if ($sort !== null)
            {
                $this->cursor = $this->collection->find($query)->sort($sort);
            }
            else
            {
                $this->cursor = $this->collection->find($query);
            }
            return $this->cursor;
        }

        public function iterate($function, array &$aggregator = null)
        {
            foreach ($this->cursor as $cur)
            {
                $cur = new Helper_Mongo\Cursor($cur);
                if ($aggregator !== null)
                {
                    array_push($aggregator, $function($cur));
                }
                else
                {
                    $function($cur);
                }
            }
        }

        static function connection()
        {
            $config = include(APPPATH.'/config/mongo'.EXT);

            $attrs = ['username' => $config['default']['connection']['username'], 'password' => $config['default']['connection']['password']];

            $connectionString = 'mongodb://'.$config['default']['connection']['hostname'].'/'.$config['default']['connection']['database'];
            return new MongoClient($connectionString, $attrs);
        }
    }