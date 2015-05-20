<?php

    class Controller_Home extends Controller_Base
    {
        public function before()
        {
			$this->imageHeader = URL::site('assets/img/home.jpg');
            parent::before();
        }
		
		public function action_index()
		{
			$this->content = View::factory('home');
		}
		
		public function action_first()
		{
			$this->content = View::factory('report');
		}
    }
