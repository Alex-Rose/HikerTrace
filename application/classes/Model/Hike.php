<?php defined('SYSPATH') or die('No direct script access.');

    class Model_Hike extends ORM
    {
        protected $_table_name  = 'hike';
        protected $_primary_key = 'ID';

        protected $_has_many 	= ['image' => ['model' => 'Image', 'foreign_key' => 'hikeID']];
    }