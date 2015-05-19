<?php defined('SYSPATH') or die('No direct script access.');

    class Helper_CURL
    {
        const SERVER = 'http://step.polymtl.ca/~alexrose/sondage/';

        public $serviceID = 1;

        function __construct()
        {
            $this->handle = null;
            $this->url = '';
            $this->post = [];
            $this->response = '';
            $this->responseBody = '';
            $this->responseData = [];
            $this->cookies = [];
            $this->user = Model_User::current();
            $this->account = $this->user->connectedAccount->where('serviceID', '=', $this->serviceID)->where('userID', '=', $this->user->pk())->find();
            $this->headerSize = 0;
            $this->code = 400;
            $this->service = ORM::factory('Service', $this->serviceID);
        }

        public function getResponseData($key)
        {
            if (array_key_exists($key, $this->responseData))
            {
                return $this->responseData[$key];
            }

            return null;
        }

        protected function initRequest()
        {
            if ($this->url == '')
            {
                throw new Exception('Cannot initiate curl : URL is empty');
            }
            $this->handle = curl_init($this->url);
            curl_setopt($this->handle, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($this->handle, CURLOPT_HEADER, 1);
            if ($this->account->loaded() && $this->account->session != null)
            {
                curl_setopt($this->handle, CURLOPT_COOKIE, 'session=' . $this->account->session);
//                Model_Log::Log("Initiated request with cookie ".$this->account->session, 'DEBUG');
            }


            $this->setPost();
        }

        protected function setPost()
        {
            if (count($this->post) > 0)
            {
                curl_setopt($this->handle, CURLOPT_POST, true);
                curl_setopt($this->handle, CURLOPT_POSTFIELDS, $this->post);
            }
        }

        protected function parseResponse()
        {
            $this->code = curl_getinfo($this->handle, CURLINFO_HTTP_CODE);
            $this->headerSize = curl_getinfo($this->handle, CURLINFO_HEADER_SIZE);

            $this->responseBody = substr($this->response, $this->headerSize);

            $this->responseData = json_decode($this->responseBody, true);
            if ($this->responseData == null)
            {
                $this->responseData = [];
            }
        }

        protected function getCookie($response)
        {
            preg_match('/^Set-Cookie:\s*([^;]*)/mi', $response, $m);

            $cookies = null;

            if (count($m) >= 1)
            {
                parse_str($m[1], $cookies);

                if (array_key_exists('session', $cookies))
                {
                    $this->account->session = $cookies['session'];
                    $this->account->save();
                }
            }
            return $cookies;
        }

        public function execute($retry = false)
        {
            $this->initRequest();
            $this->response = curl_exec($this->handle);
            $this->parseResponse();

            if ($this->account->loaded())
            {
                $this->cookies = $this->getCookie($this->response);
            }

            if ($this->getResponseData('login') !== null)
            {
                if ($this->getResponseData('login') == false && !$retry)
                {
                    $curl = new Helper_CURL();
                    $curl->login();

                    $this->account = $this->user->connectedAccount->where('serviceID', '=', $this->serviceID)->where('userID', '=', $this->user->pk())->find();
                    $this->execute(true);
                }
            }
        }

        public function executePublic()
        {
            $this->initRequest();
            $this->response = curl_exec($this->handle);
            $this->parseResponse();
        }

        public function login()
        {
            if ($this->user->loaded())
            {
                if ($this->account->loaded())
                {
//                    Model_Log::Log('Attempt to login', 'DEBUG');
                    $this->url = $this->makeURL('APIAuth/login');

                    $this->post = ['email' => $this->user->detail->email,
                        'pass' => $this->account->remoteKey,
                        'uid' => $this->account->remoteUserID,
                        'remoteUID' => $this->user->pk()];

                    $this->initRequest();
                    $this->response = curl_exec($this->handle);
                    $this->parseResponse();

                    if ($this->account->loaded())
                    {
                        $this->cookies = $this->getCookie($this->response);
//                        Model_Log::Log('Setting cookie to '.$this->cookies , 'DEBUG');
                    }
                }
                else
                {
                    throw new Exception('Cannot login with curl : User has no connected account');
                }
            }
            else
            {
                throw new Exception('Cannot login with curl : User is not logged in');
            }
        }

        public function register()
        {
            if ($this->user->loaded())
            {
                if (!$this->account->loaded())
                {
                    $this->url = $this->makeURL('APIAuth/register');

                    $this->post = ['email' => $this->user->detail->email,
                        'service' => 'Application AEP',
                        'remoteUID' => $this->user->pk()];

                    $this->execute();

                    if (array_key_exists('uid', $this->responseData))
                    {
                        $this->account = ORM::factory('ConnectedAccount');
                        $this->account->userID = $this->user->pk();
                        $this->account->serviceID = $this->serviceID;
                        $this->account->remoteUserID = $this->responseData['uid'];
                        $this->account->remoteKey = md5($this->user->pk() . $this->user->detail->email . $this->service->salt);
                        $this->account->save();
                    }
                    else
                    {
                        throw new Exception('Cannot register with curl : Invalid answer from server');
                    }
                }
                else
                {
                    throw new Exception('Cannot register with curl : User already has an account');
                }
            }
            else
            {
                throw new Exception('Cannot register with curl : User is not logged in');
            }
        }

        protected function checkLogin()
        {
            $this->url = $this->makeURL('APIAuth/checkLogin');
            $this->execute();
        }

        protected function makeURL($param)
        {
            return self::SERVER.$param;
        }

        // Relative path
        public function setUrl($url)
        {
            $this->url = $this->makeURL($url);
        }

        public function getSalt()
        {
            return $this->service->salt;
        }
    }
