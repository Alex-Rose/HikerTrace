<?php defined('SYSPATH') or die('No direct script access.');

    abstract class Controller_Async extends Controller_Base
    {
        protected $data = [];

        protected $writeJson = true;

        public function before()
        {
            $this->response->headers('Content-Type', 'application/json');
            $this->render = false;
            $this->data['success'] = false;
            parent::before();
        }

        public function after()
        {
            if ($this->writeJson)
            {
                echo json_encode($this->data);
            }
            parent::after();
        }

    }