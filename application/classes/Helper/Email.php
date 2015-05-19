<?php defined('SYSPATH') or die('No direct script access.');

class Helper_Email
{
    protected $header;
    protected $unsubURL;

    function __construct()
    {
        $this->to           = '';
        $this->cc           = '';
        $this->bcc          = '';
        $this->from         = '';
        $this->senderName   = '';
        $this->subject      = '';
        $this->addTag       = true;
        $this->tag          = '[AEP]';
        $this->content      = null;

        $this->unsubURL     = null;
        $this->header       = '';
    }

    public function send()
    {
        if (!filter_var($this->to, FILTER_VALIDATE_EMAIL))
        {
            return false;
        }

        $this->makeHeader();

        if ($this->addTag)
        {
            $this->subject = $this->tag.' '.$this->subject;
        }
        return mail(utf8_decode($this->to), utf8_decode($this->subject), utf8_decode($this->content), utf8_decode($this->header));
    }

    protected function makeHeader()
    {
        $this->header 	 = "From: $this->senderName <$this->from>\r\n";
        if ($this->cc != '')
            $this->header	.= "Bcc: ".strip_tags($this->cc)."\r\n";
        if ($this->bcc != '')
            $this->header	.= "Bcc: ".strip_tags($this->bcc)."\r\n";
        $this->header 	.= "Reply-To: ". strip_tags($this->from)."\r\n";
        $this->header 	.= "MIME-Version: 1.0\r\n";
        $this->header 	.= "Content-Type: text/html; charset=UTF8\r\n";
    }

    public static function sendmail($from, $to, $bcc, $subject, $content)
    {
        $email = new Helper_Email();
        $email->to = $to;
        $email->from = $from;
        $email->subject = $subject;
        $email->bcc = $bcc;
        $email->content = $content;

        $email->send();
    }
}
