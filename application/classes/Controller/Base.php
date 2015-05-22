<?php

    class Controller_Base extends Controller_Template
    {
        public $template = 'layout/base';

        protected $header = 'layout/header';
        protected $content = '';
        protected $footer = 'layout/footer';

        protected $render = true;

        protected $view = array();


        public function __construct($request, $response)
        {
			// Default image
			$this->imageHeader = URL::site('assets/img/home.jpg');
            parent::__construct($request, $response);
        }

        public function __set($name, $value)
        {
            $this->view[$name] = $value;
        }

        public function __get($name)
        {
            if (!array_key_exists($name, $this->view)) {
                return null;
            }
            return $this->view[$name];
        }

        public function before()
        {
            if (Kohana::$profiling === true)
            {
                $benchmark = Profiler::start('Application', __FUNCTION__);
            }

            $this->handleClosedSite();

            parent::before();

            $this->view['session'] = Session::instance();
            $this->view['request'] = $this->request;
            $this->view['response'] = $this->response;
        }

        public function after()
        {
            if (!$this->render)
                return;

            if (Kohana::$profiling === TRUE)
                $benchmark = Profiler::start('Application', __FUNCTION__);

            setlocale (LC_TIME, 'fr_FR.utf8','fra');
            date_default_timezone_set('America/Montreal');

            // If no template was set, use controller/action as the default view.
            if ($this->content === '') {
                $this->content = Request::current()->controller();

                if (Request::current()->action() != 'index') {
                    $this->content .= '-'.Request::current()->action();
                }
            }

            $this->template->set($this->view);

            $this->template->footer = View::factory($this->footer);
            $this->template->footer->set($this->view);
            $this->template->footer = $this->template->footer->render();

            if (!isset($this->template->content))
            {
                $this->template->content = ($this->content instanceof View) ? $this->content : View::factory($this->content);
                $this->template->content->set($this->view);
                $this->template->content = $this->template->content->render();
            }

            $this->action = $this->request->action();
            $this->option = $this->request->param('id');
            $this->template->header = View::factory($this->header);
            $this->template->header->set($this->view);
            $this->template->header = $this->template->header->render();

            $body = $this->template->render();

            // Set response body
            $this->response->body($body);

            $this->auto_render = false;
            parent::after();
        }

        public function redirect_to($url = '', $code = 302)
        {
            $this->request->redirect($url, $code);
        }

        public function render($action)
        {
            $this->content = Request::current()->controller() . '/' . $action;
            $called_action = 'action_' . $action;
            $this->$called_action();
        }

        private function handleClosedSite()
        {
            if (Kohana::$environment == Kohana::PRODUCTION)
            {
                if ($this->request->controller() != 'Maintenance')
                {
                    $maintenance = ORM::factory('Parameter')->where('key', '=', 'MAINTENANCE_CODE')->find();

                    switch ($maintenance->value)
                    {
                        case 'BRB':
                            $this->request->redirect('Maintenance/brb');
                            break;
                        case '5MIN':
                            $this->request->redirect('Maintenance/five');
                            break;
                        case 'OFF':
                            $this->request->redirect('Maintenance/off');
                            break;
                        case 'BROKEN':
                            $this->request->redirect('Maintenance/broken');
                            break;
                        case 1:
                        case 'DEFAULT':
                            $this->request->redirect('Maintenance');
                            break;
                        default :
                            break;
                    }
                }
            }
        }

        protected function checkAccess($roles)
        {
            if (!$this->user->isLogged())
            {
                Session::instance()->set('redirect', $this->request->uri());
                HTTP::redirect('login');
            }

            if (!$this->user->isAdmin($roles))
            {
                HTTP::redirect('home/denied');
            }
        }
    }