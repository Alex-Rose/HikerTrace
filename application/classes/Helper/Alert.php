<?php

    class Helper_Alert
    {
        public static function success($msg, $dismissible = true)
        {
            return self::alert('success', $msg, $dismissible);
        }

        public static function info($msg, $dismissible = true)
        {
            return self::alert('info', $msg, $dismissible);
        }

        public static function warning($msg, $dismissible = true)
        {
            return self::alert('warning', $msg, $dismissible);
        }

        public static function danger($msg, $dismissible = true)
        {
            return self::alert('danger', $msg, $dismissible);
        }

        public static function alert($class, $msg, $dismissible = true)
        {
            $dismissClass = $dismissible ? 'alert-dismissible' : '';
            $dismissBtn = $dismissible ? '<button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>' : '';
            return '<div class="alert alert-'.$class.' '.$dismissClass.'" role="alert">'.$dismissBtn.$msg.'</div>';
        }
    }