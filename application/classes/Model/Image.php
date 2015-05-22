<?php defined('SYSPATH') or die('No direct script access.');

    class Model_Image extends ORM
    {
        protected $_table_name  = 'image';
        protected $_primary_key = 'ID';

        protected $_belongs_to 	= ['hike' => ['model' => 'Hike', 'foreign_key' => 'hikeID']];
    }