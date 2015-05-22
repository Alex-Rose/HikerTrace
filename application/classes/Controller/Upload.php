<?php

    class Controller_Upload extends Controller_Base
    {
        public function before()
        {
            parent::before();
        }
		
		public function action_index()
		{
			$this->content = View::factory('new');
		}
		
		public function action_image()
		{
			$this->id = $this->request->param('id');
			$this->content = View::factory('upload_image');
		}
    }
