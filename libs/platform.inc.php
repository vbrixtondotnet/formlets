<?php
require("session.php");
require("gettimezone.php");

use Aws\Ses\SesClient;
use Aws\Ses\Exception\SesException;

//require("../logic/logic.inc.php");
class Platform
{
    public function __construct() {
        $this->lo = null;
    }

    public function set_csrfguard($url, $linkname) {
        $name = "CSG_" . mt_rand(0, mt_getrandmax());
        $token = $this->csrfguard_generate_token($name);

        $uripart	=explode("?",$_SERVER['REQUEST_URI']);
        $urlpart	=explode("/",$uripart[0]);

        $returnurl = $_SERVER['REQUEST_URI'];
        $returnurl = urlencode($returnurl);

        if (strpos($url, '?')) {
            return $url . "&_t=" . $token . "&_n=" . $name . "&_r=" . $returnurl;
        } else {
            return $url . "?_t=" . $token . "&_n=" . $name . "&_r=" . $returnurl;
        }
    }

    public function validate_csrfguard() {
        if (!$this->csrfguard_validate_token($_GET['_n'], $_GET['_t'])) {
            //header('HTTP/1.0 403 Forbidden');
            $this->csrf_error();
            //exit;
        }
    }


    // special function to check to only accept submits that were intiated on the site
    public function csrfguard_start() {
        if (count($_POST)) {
            if (!isset($_POST['CSName']) or !isset($_POST['CSToken'])) {
                $this->csrf_error();
                exit;
            }
            $name = $this->Xssenc($_POST['CSName']);
            $token = $this->Xssenc($_POST['CSToken']);
            if (!$this->csrfguard_validate_token($name, $token)) {
                $this->csrf_error();
                exit;
            }
        }
        ob_start();
        /* adding double quotes for "csrfguard_inject" to prevent:
              Notice: Use of undefined constant csrfguard_inject - assumed 'csrfguard_inject' */
        register_shutdown_function(array(&$this, "csrfguard_inject"));
    }

    //

    function getPreviousUrl() {

        $uripart	=explode("?",$_SERVER['REQUEST_URI']);
        $urlpart	=explode("/",$uripart[0]);

        $url = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

        if(isset($_GET['_t']) && isset($_GET['_n'])) {
            if($urlpart[1] == 'form' && in_array($urlpart[2], ['duplicate','delete','deactivate','activate'])) {
                $url = '/form/';
            } else if($urlpart[1] == 'response' && in_array($urlpart[2], ['delete'])) {
                $url = '/response/';
            } else if($urlpart[1] == 'email' && in_array($urlpart[4], ['disable','delete','enable'])) {
                $url = '/email/';
            } else if($urlpart[1] == 'team' && in_array($urlpart[2], ['delete-member'])) {
                $url = '/team/';
            } else if($urlpart[1] == 'admin' && in_array($urlpart[3], ['take','block','unblock'])) {
                $url = '/admin/users/';
            }
        }

        return $url;
    }

    public function csrf_error() {
        $previous_url = $_GET['_r'];
        if(!$previous_url) {
            $previous_url = $_POST['CSReturn'];
        }

        header('Location: '.$previous_url);exit;
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <script>(function (w, d, s, l, i) {
                    w[l] = w[l] || [];
                    w[l].push({
                        'gtm.start':
                            new Date().getTime(), event: 'gtm.js'
                    });
                    var f = d.getElementsByTagName(s)[0],
                        j = d.createElement(s), dl = l != 'dataLayer' ? '&l=' + l : '';
                    j.async = true;
                    j.src =
                        'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
                    f.parentNode.insertBefore(j, f);
                })(window, document, 'script', 'dataLayer', 'GTM-5F5XK3B');

                setTimeout(function() {
                    window.location.href='<?php echo $previous_url; ?>';
                }, 3000);
            </script>
            <meta charset="utf-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <title>CSRF ERROR</title>
            <link rel="stylesheet" type="text/css"
                  href="<?php echo $GLOBALS['level']; ?>static/css/marketing.css?<?php echo $_SERVER["CURRENT_VERSION_ID"]; ?>">
            <style type="text/css">
                body {
                    background: #f0f0f2;
                    color: #333;
                    text-align: center;
                }
            </style>
        </head>
        <body>
        <noscript>
            <iframe src="https://www.googletagmanager.com/ns.html?id=GTM-5F5XK3B" height="0" width="0"
                    style="display:none;visibility:hidden"></iframe>
        </noscript>
        <br><br><br><br>
        <img style="display: initial;"
             src="<?php echo $GLOBALS['protocol']; ?>://<?php echo $_SERVER['HTTP_HOST']; ?>/static/img/logo-black.svg">
        <br><br>
        <h1>CSRF ERROR</h1>
        <p>You tried to access an old resource , this could be by submitting a form for a second time, or accessing an
            action link twice. <br>This action has been prevented for security reasons.<br>Please contact <a
                    href="<?php echo $GLOBALS['protocol']; ?>://<?php echo $_SERVER['HTTP_HOST']; ?>/support/">our
                support</a> if you think you wrongfully received this error.</p>

                <p>Reloading page...</p>
        </body>
        </html>
        <?php
    }

    //
    public function csrfguard_inject() {
        $data = ob_get_clean();
        $data = $this->csrfguard_replace_forms($data);
        echo $data;
    }
    //


    //
    public function csrfguard_generate_token($unique_form_name, $form=false) {
        $string = time() . '_' . $GLOBALS['ref']['FORMLETS_KEY'] . '_' . session_id();
        $token = $this->e_crypt($string, 'e');

        return $token;
    }
    //


    //
    public function csrfguard_validate_token($unique_form_name, $token_value) {
        $string = $this->e_crypt($token_value, 'd');

        $string = explode('_', $string);

        if($string[1]==$GLOBALS['ref']['FORMLETS_KEY'] && $string[2] == session_id()) {
            $min = (time() - intval($string[0])) / 60;
            if($min <= 15) { //check if not older than 15 mins
                $result = true;
            } else {
                $result = false;
            }
        } else {
            $result = false;
        }

        //$result = false;

        return $result;
    }
    //


    //
    public function csrfguard_replace_forms($form_data_html) {
        $count = preg_match_all("/<form(.*?)>(.*?)<\\/form>/is", $form_data_html, $matches, PREG_SET_ORDER);

        $uripart	=explode("?",$_SERVER['REQUEST_URI']);
        $urlpart	=explode("/",$uripart[0]);

        $returnurl = $_SERVER['REQUEST_URI'];
        //$returnurl = urlencode($returnurl);

        if (is_array($matches)) {
            foreach ($matches as $m) {
                if (strpos($m[1], "nocsrf") !== false) {
                    continue;
                }
                $name = "CSGuard_" . mt_rand(0, mt_getrandmax());
                $token = $this->csrfguard_generate_token($name, true);
                $form_data_html = str_replace(
                    $m[0],
                    "<form{$m[1]}>
<input type=\"hidden\" name=\"CSReturn\" value=\"{$returnurl}\"/>
<input type=\"hidden\" name=\"CSName\" value=\"{$name}\"/>
<input type=\"hidden\" name=\"CSToken\" value=\"{$token}\" />{$m[2]}</form>",
                    $form_data_html
                );
            }
        }
        return $form_data_html;
    }
    //

function mailprotect($string){
  $string = trim(preg_replace('/\s+/','', $string));
    $string  = substr($string ,0,254);
return $string;

}

function subjectprotect($string){
    $string = trim(preg_replace('/\s+/', ' ', $string));
        $string  = substr($string ,0,254);
    return $string;
}

    // Post mail to sendgrid to be delivered
    // public function sendMail($a) {
    //
    //     if(!is_array($a['to']) && filter_var(trim($a['to']), FILTER_VALIDATE_EMAIL) == false) {
    //         return false;
    //     } else if(is_array($a['to']) && count($a['to']) == 0) {
    //         return false;
    //     }
    //
    //     //check blacklisted words
    //     $lists = $GLOBALS['ref']['mail_blacklist'];
    //
    //     foreach($lists as $list) {
    //         if (stripos($a['body'], $list) !== FALSE) { // Yoshi version
    //             //match found
    //             return false;
    //         }
    //     }
    //
    //     if (!class_exists('SendGrid')) {
    //         require_once("../logic/logic.inc.php");
    //         require("SendGrid.php");
    //         require("Mail.php");
    //     }
    //     $this->sendgrid = new SendGrid($GLOBALS['conf']['sendgrid']['user'], $GLOBALS['conf']['sendgrid']['key']);
    //     $this->email = new Mail();
    //     if (is_array($a['to'])) {
    //         foreach ($a['to'] as $to) {
    //             $this->email->addTo($this->mailprotect($to));
    //         }
    //     } else {
    //         $this->email->addTo($this->mailprotect($a['to']));
    //     }
    //
    //     if (isset($a['cc'])) {
    //         if (is_array($a['cc'])) {
    //             foreach ($a['cc'] as $cc) {
    //                 $this->email->addTo($this->mailprotect($cc));
    //             }
    //         } else {
    //             $this->email->addTo($this->mailprotect($a['cc']));
    //         }
    //     }
    //
    //     if (isset($a['bcc'])) {
    //         if (is_array($a['bcc'])) {
    //             foreach ($a['bcc'] as $bcc) {
    //                 $this->email->addBcc($this->mailprotect($bcc));
    //             }
    //         } else {
    //             $this->email->addBcc($this->mailprotect($a['bcc']));
    //         }
    //     }
    //     if (isset($a['replyTo'])) {
    //         $this->email->setReplyTo($a['replyTo']);
    //     }
    //     if (isset($a['attachments'])) {
    //         $this->email->setAttachments($a['attachments']);
    //     }
    //     $this->email->setFrom('hello@formlets.com');
    //     $this->email->setFromName($a['from']);
    //     $this->email->setSubject($this->subjectprotect($a['subject']));
    //     $this->email->setHtml($a['body']);
    //     $res = $this->sendgrid->send($this->email);
    //     return $res;
    // }

    public function sendMail($a) {
        if(!is_array($a['to']) && filter_var(trim($a['to']), FILTER_VALIDATE_EMAIL) == false) {
            return false;
        } else if(is_array($a['to']) && count($a['to']) == 0) {
            return false;
        }

        //check blacklisted words
        $lists    = $GLOBALS['ref']['mail_blacklist'];
        $iplists  = $GLOBALS['ref']['mail_ip_blacklist'];

        foreach($lists as $list) {
            if (stripos($a['body'], $list) !== FALSE) { // Yoshi version
                //match found
                return false;
            }
        }
        foreach($iplists as $iplist) {
            if (stripos($a['ip'], $iplist) !== FALSE) { // Yoshi version
            //match found
            return false;
        }
      }


        if (!class_exists('Mail')) {
                require_once("../logic/logic.inc.php");
                require("Mail.php");
        }


        $client = SesClient::factory(array(
            'version'=> 'latest',
            'region' => $GLOBALS['conf']['ses']['region'],
            'credentials'=> [
                'key'    => $GLOBALS['conf']['ses']['key'],
                'secret' => $GLOBALS['conf']['ses']['secret'],
            ]
        ));


        $this->email = new Mail();
        if (is_array($a['to'])) {
            foreach ($a['to'] as $to) {
                $this->email->addTo($this->mailprotect($to));
            }
        } else {
            $this->email->addTo($this->mailprotect($a['to']));
        }

        if (isset($a['cc'])) {
            if (is_array($a['cc'])) {
                foreach ($a['cc'] as $cc) {
                    $this->email->addTo($this->mailprotect($cc));
                }
            } else {
                $this->email->addTo($this->mailprotect($a['cc']));
            }
        }

        if (isset($a['bcc'])) {
            if (is_array($a['bcc'])) {
                foreach ($a['bcc'] as $bcc) {
                    $this->email->addBcc($this->mailprotect($bcc));
                }
            } else {
                $this->email->addBcc($this->mailprotect($a['bcc']));
            }
        }
        if (isset($a['replyTo'])) {
            $this->email->setReplyTo($a['replyTo']);
        }
        if (isset($a['attachments'])) {
            $this->email->setAttachments($a['attachments']);
        }

        if(isset($a['from'])) {
            $this->email->setFrom($a['from']);
        } else {
            $this->email->setFrom('hello@formlets.com');
            $this->email->setFromName($a['from']);
        }

        $this->email->setSubject($this->subjectprotect($a['subject']));
        $this->email->setHtml($a['body']);

        $params = array(
          'subject'   => $this->email->getSubject(),
          'from'      => $this->email->getFrom(),
          'to'        => $this->email->getFrom(),
        );

        if($this->email->getHtml()) {
          $params['html'] = $this->email->getHtml();
        }

        if($this->email->getText()) {
          $params['text'] = $this->email->getText();
        }

        if(($fromname = $this->email->getFromName())) {
          $params['fromname'] = $fromname;
        }

        if(($replyto = $this->email->getReplyTo())) {
          $params['replyto'] = $replyto;
        }


        $params['to'] = $this->email->getTos();
        $params['ip'] = $a['ip'];

        if (filter_var($params['from'], FILTER_VALIDATE_EMAIL) == false) {
            $params['from'] = 'hello@formlets.com';
        }

        //var_dump($params);exit;

        $emailData = [
            'Destination' => [
                'ToAddresses' => $params['to'],
            ],
            'Message' => [
                'Body' => [
                    'Html' => [
                        'Charset' => 'UTF-8',
                        'Data' => $params['html'],
                    ],
                ],
                'Subject' => [
                    'Charset' => 'UTF-8',
                    'Data' => $params['subject'],
                ],
            ],
            'Source' => $params['from']
        ];

        if($params['replyto']) {
            $emailData['ReplyToAddresses'] = array($params['replyto']);
        }

        $emailStatus = 'success';

        try {
            $result = $client->sendEmail($emailData);
            $messageId = $result->get('MessageId');
            //return $messageId;
        } catch (\Aws\Ses\Exception\SesException $e) {
            $emailStatus = 'failed';
        }

        $dataLog = array(
            'id'=>$this->insertId(),
            'to'=>implode(',', $params['to']),
            'from'=>$params['from'],
            'subject'=>$params['subject'],
            'body'=>print_r($emailData,true)."ipcheck:".$ipcheck,
            'ip'=>$params['ip'],
            'status'=>$emailStatus
        );

        $this->lo->emailLog($dataLog);

    }
    //


    //
    public function XssEnc($s) {
        return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
    }
    //


    //
    public function unset_session($key) {
        $GLOBALS['sess'][$key] = ' ';
        unset($GLOBALS['sess'][$key]);
        $this->save_session($key, '');
    }
    //


    //
    public function get_from_session($key) {
        if (isset($GLOBALS['sess'][$key])) {
            return $GLOBALS['sess'][$key];
        } else {
            return false;
        }
    }
    //

    //
    public function insertId($length = 16) {
        $varlist = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
        $id = "";
        for ($l = 0; $l < $length; $l++) {
            $rand = ((rand(0, 58 * $length) + substr(microtime(), 2, 6)) % 58);
            $id .= $varlist[$rand];
        }
        return $id;
    }
    //


    //
    public function getIP() {
        $client  = @$_SERVER['HTTP_CLIENT_IP'];
        $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
        $remote  = $_SERVER['REMOTE_ADDR'];

        if(filter_var($client, FILTER_VALIDATE_IP)) {
            $ip = $client;
        }
        elseif(filter_var($forward, FILTER_VALIDATE_IP)) {
            $ip = $forward;
        } else {
            $ip = $remote;
        }

        return $ip;
    }

    //

    public function slugify($string, $options = array(), $delimiter = '-') {

        // Make sure string is in UTF-8 and strip invalid UTF-8 characters
    	$str = mb_convert_encoding((string)$string, 'UTF-8', mb_list_encodings());

    	$defaults = array(
    		'delimiter' => $delimiter,
    		'limit' => null,
    		'lowercase' => true,
    		'replacements' => array(),
    		'transliterate' => true,
    	);

    	// Merge options
    	$options = array_merge($defaults, $options);

    	$char_map = array(
    		// Latin
    		'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'AE', 'Ç' => 'C',
    		'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I',
    		'Ð' => 'D', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ő' => 'O',
    		'Ø' => 'O', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ű' => 'U', 'Ý' => 'Y', 'Þ' => 'TH',
    		'ß' => 'ss',
    		'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'ae', 'ç' => 'c',
    		'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
    		'ð' => 'd', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ő' => 'o',
    		'ø' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u', 'ű' => 'u', 'ý' => 'y', 'þ' => 'th',
    		'ÿ' => 'y',
    		// Latin symbols
    		'©' => '(c)',
    		// Greek
    		'Α' => 'A', 'Β' => 'B', 'Γ' => 'G', 'Δ' => 'D', 'Ε' => 'E', 'Ζ' => 'Z', 'Η' => 'H', 'Θ' => '8',
    		'Ι' => 'I', 'Κ' => 'K', 'Λ' => 'L', 'Μ' => 'M', 'Ν' => 'N', 'Ξ' => '3', 'Ο' => 'O', 'Π' => 'P',
    		'Ρ' => 'R', 'Σ' => 'S', 'Τ' => 'T', 'Υ' => 'Y', 'Φ' => 'F', 'Χ' => 'X', 'Ψ' => 'PS', 'Ω' => 'W',
    		'Ά' => 'A', 'Έ' => 'E', 'Ί' => 'I', 'Ό' => 'O', 'Ύ' => 'Y', 'Ή' => 'H', 'Ώ' => 'W', 'Ϊ' => 'I',
    		'Ϋ' => 'Y',
    		'α' => 'a', 'β' => 'b', 'γ' => 'g', 'δ' => 'd', 'ε' => 'e', 'ζ' => 'z', 'η' => 'h', 'θ' => '8',
    		'ι' => 'i', 'κ' => 'k', 'λ' => 'l', 'μ' => 'm', 'ν' => 'n', 'ξ' => '3', 'ο' => 'o', 'π' => 'p',
    		'ρ' => 'r', 'σ' => 's', 'τ' => 't', 'υ' => 'y', 'φ' => 'f', 'χ' => 'x', 'ψ' => 'ps', 'ω' => 'w',
    		'ά' => 'a', 'έ' => 'e', 'ί' => 'i', 'ό' => 'o', 'ύ' => 'y', 'ή' => 'h', 'ώ' => 'w', 'ς' => 's',
    		'ϊ' => 'i', 'ΰ' => 'y', 'ϋ' => 'y', 'ΐ' => 'i',
    		// Turkish
    		'Ş' => 'S', 'İ' => 'I', 'Ç' => 'C', 'Ü' => 'U', 'Ö' => 'O', 'Ğ' => 'G',
    		'ş' => 's', 'ı' => 'i', 'ç' => 'c', 'ü' => 'u', 'ö' => 'o', 'ğ' => 'g',
    		// Russian
    		'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Е' => 'E', 'Ё' => 'Yo', 'Ж' => 'Zh',
    		'З' => 'Z', 'И' => 'I', 'Й' => 'J', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N', 'О' => 'O',
    		'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T', 'У' => 'U', 'Ф' => 'F', 'Х' => 'H', 'Ц' => 'C',
    		'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Sh', 'Ъ' => '', 'Ы' => 'Y', 'Ь' => '', 'Э' => 'E', 'Ю' => 'Yu',
    		'Я' => 'Ya',
    		'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'yo', 'ж' => 'zh',
    		'з' => 'z', 'и' => 'i', 'й' => 'j', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o',
    		'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c',
    		'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sh', 'ъ' => '', 'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu',
    		'я' => 'ya',
    		// Ukrainian
    		'Є' => 'Ye', 'І' => 'I', 'Ї' => 'Yi', 'Ґ' => 'G',
    		'є' => 'ye', 'і' => 'i', 'ї' => 'yi', 'ґ' => 'g',
    		// Czech
    		'Č' => 'C', 'Ď' => 'D', 'Ě' => 'E', 'Ň' => 'N', 'Ř' => 'R', 'Š' => 'S', 'Ť' => 'T', 'Ů' => 'U',
    		'Ž' => 'Z',
    		'č' => 'c', 'ď' => 'd', 'ě' => 'e', 'ň' => 'n', 'ř' => 'r', 'š' => 's', 'ť' => 't', 'ů' => 'u',
    		'ž' => 'z',
    		// Polish
    		'Ą' => 'A', 'Ć' => 'C', 'Ę' => 'e', 'Ł' => 'L', 'Ń' => 'N', 'Ó' => 'o', 'Ś' => 'S', 'Ź' => 'Z',
    		'Ż' => 'Z',
    		'ą' => 'a', 'ć' => 'c', 'ę' => 'e', 'ł' => 'l', 'ń' => 'n', 'ó' => 'o', 'ś' => 's', 'ź' => 'z',
    		'ż' => 'z',
    		// Latvian
    		'Ā' => 'A', 'Č' => 'C', 'Ē' => 'E', 'Ģ' => 'G', 'Ī' => 'i', 'Ķ' => 'k', 'Ļ' => 'L', 'Ņ' => 'N',
    		'Š' => 'S', 'Ū' => 'u', 'Ž' => 'Z',
    		'ā' => 'a', 'č' => 'c', 'ē' => 'e', 'ģ' => 'g', 'ī' => 'i', 'ķ' => 'k', 'ļ' => 'l', 'ņ' => 'n',
    		'š' => 's', 'ū' => 'u', 'ž' => 'z'
    	);

    	// Make custom replacements
    	$str = preg_replace(array_keys($options['replacements']), $options['replacements'], $str);

    	// Transliterate characters to ASCII
    	if ($options['transliterate']) {
    		$str = str_replace(array_keys($char_map), $char_map, $str);
    	}

        $str = preg_replace('/[^A-z0-9\/_|+ -]/u', '', $str);

    	// Replace non-alphanumeric characters with our delimiter
    	$str = preg_replace('/[^\p{L}\p{Nd}]+/u', $options['delimiter'], $str);

    	// Remove duplicate delimiters
    	$str = preg_replace('/(' . preg_quote($options['delimiter'], '/') . '){2,}/', '$1', $str);

    	// Truncate slug to max. characters
    	$str = mb_substr($str, 0, ($options['limit'] ? $options['limit'] : mb_strlen($str, 'UTF-8')), 'UTF-8');

    	// Remove delimiter from ends
    	$str = trim($str, $options['delimiter']);

    	return $options['lowercase'] ? mb_strtolower($str, 'UTF-8') : $str;
    }


    //-> Define function trans<-
    public function Trans($method, $text) {
        if ($text) {
            $ttext = $text;
            $ttext = str_replace("'", "", $ttext);
            $ttext = str_replace('"', '', $ttext);
            $ttext = str_replace(',', '', $ttext);
            $ttext = str_replace('(', '', $ttext);
            $ttext = str_replace(')', '', $ttext);
            $ttext = str_replace(' ', '_', $ttext);
            $ttext = strtolower($ttext);
            $ttext = substr($ttext, 0, 36);
            if ($GLOBALS["trans"][$method][$ttext]) {
                // the query has been done before :-) yess !!!
                //-> return the value if its in the cache<-
                return $GLOBALS["trans"][$method][$ttext];
            } else {
                $value = nl2br(str_replace('&lt;', '<', str_replace('&quot;', '"', str_replace('&gt;', '>', htmlentities(str_replace("_", " ", $text))))));
                if ($GLOBALS['conf']['env'] <> 'production') {
                    //-> show all values to store in translation file
                    $this->transgen .= '$trans["' . $method . '"]["' . $ttext . '"]="' . $value . '";<br>';
                }
                return $value;
            }
        }
    }

    //

    public function json_validate($string) {
        // decode the JSON data
        $result = json_decode($string);

        // switch and check possible JSON errors
        switch (json_last_error()) {
            case JSON_ERROR_NONE:
                $error = ''; // JSON is valid // No error has occurred
                break;
            case JSON_ERROR_DEPTH:
                $error = 'The maximum stack depth has been exceeded.';
                break;
            case JSON_ERROR_STATE_MISMATCH:
                $error = 'Invalid or malformed JSON.';
                break;
            case JSON_ERROR_CTRL_CHAR:
                $error = 'Control character error, possibly incorrectly encoded.';
                break;
            case JSON_ERROR_SYNTAX:
                $error = 'Syntax error, malformed JSON.';
                break;
            // PHP >= 5.3.3
            case JSON_ERROR_UTF8:
                $error = 'Malformed UTF-8 characters, possibly incorrectly encoded.';
                break;
            // PHP >= 5.5.0
            case JSON_ERROR_RECURSION:
                $error = 'One or more recursive references in the value to be encoded.';
                break;
            // PHP >= 5.5.0
            case JSON_ERROR_INF_OR_NAN:
                $error = 'One or more NAN or INF values in the value to be encoded.';
                break;
            case JSON_ERROR_UNSUPPORTED_TYPE:
                $error = 'A value of a type that cannot be encoded was given.';
                break;
            default:
                $error = 'Unknown JSON error occured.';
                break;
        }

        if ($error !== '') {
            return false;
        }

        // everything is OK
        return is_array($result);
    }

    /**
     * this function will determine ifexists in external data
     * for form responses
     * @param  array $array external data array
     * @param  string $key
     * @param  int $k
     * @return int
     */
    public function data_exists($array, $key, $k = 0) {
        $result = false;
        if (is_array($array)) {
            if (isset($array[$key])) {
                $result = $k;
            } else {
                foreach ($array as $k => $subarray) {
                    $result = $this->data_exists($subarray, $key, $k);
                    if (is_integer($result)) {
                        break;
                    }
                }
            }
        }

        return $result;
    }

    public function isPreviewUser($account) {
        return $account['accountStatus'] == 'PREVIEW';
    }

    public function isFreeAccount($account) {
        return strtolower($account['accountStatus']) == 'free' || strtolower($account['accountStatus']) == 'preview';
    }

    public function canUser($user, $action, $form = null) {
        if (!empty($form)) {
            if ($user['accountRights'] >= $GLOBALS['ref']['user_all_rights']) {
                return true;
            }
            if ($user['permissions']) {
                $permissions = json_decode(str_replace('\"', '"', $user['permissions']), true);
                return $GLOBALS['ref']['rights'][$action] && $permissions[$form['_id']] && $permissions[$form['_id']] & $GLOBALS['ref']['rights'][$action];
            }
        } else {
            return isset($GLOBALS['ref']['rights'][$action]) && ($user['accountRights'] & $GLOBALS['ref']['rights'][$action]);
        }
    }

    public function planDetails($plan) {
        $part = explode('-', $plan);
        if ($part[2] == "OXOPIA") {
            $plan = $part[0];
            $return['interval'] = $part[1];

            if ($part[3]) {
                $return['cur'] = $part[3];
            } else {
                $return['cur'] = 'USD';
            }
        }
        $index = $GLOBALS['ref']['plan_lookup_name'][$plan];
        if ($plan == "FREE" || $plan == "PREVIEW" || empty($plan)) {
            $return['plan'] = 'FREE';
            $return['name'] = 'Free';
            $return['interval'] = 'Monthly';
            $return['cur'] = 'USD';
        } elseif ($plan == "DOLLAR-MONTHLY") {
            $return['name'] = 'Dollar';
            $return['interval'] = 'Monthly';
            $return['cur'] = 'USD';
        } elseif ($plan == "PRO-MONTHLY") {
            $return['name'] = 'Old Professional';
            $return['interval'] = 'Monthly';
            $return['cur'] = 'USD';
        } else {
            $return['plan'] = $plan;
            $return['name'] = $GLOBALS['ref']['plan_lists'][$index]['name'];
        }
        $return['index'] = $index;
        if($plan == 'PREVIEW') {
            $return['index'] = 0;
        }
        return $return;
    }

    public function formHasElement($form, $type) {
        $elements = $form['elements'];
        if (count($elements)) {
            foreach ($elements as $element) {
                if (is_array($type) && in_array($element['type'], $type)) {
                    return true;
                } elseif (!is_array($type) && $element['type'] == $type) {
                    return true;
                }
            }
        }

        return false;
    }

    public function getDatePickerLanguages($form) {
        $elements = $form['elements'];
        $langs = array();
        $type = array('DATE','DATETIME');
        if (count($elements)) {
            foreach ($elements as $element) {
                if (is_array($type) && in_array($element['type'], $type) && $element['pickerLang'] && !in_array($element['pickerLang'], $langs)) {
                    $langs[] = $element['pickerLang'];
                }
            }
        }

        return $langs;
    }

    public function is_base64($s) {
        if (substr($s, 0, 10) === "data:image") {
            $s = str_replace('data:image/png;base64,', '', $s);
            return base64_decode($s, true);
        } else {
            return false;
        }
    }

    public function base64_to_jpeg($base64_string, $output_file) {
        // open the output file for writing
        //$ifp = fopen( $output_file, 'wb' );

        // split the string on commas
        // $data[ 0 ] == "data:image/png;base64"
        // $data[ 1 ] == <actual base64 string>
        $data = explode(',', $base64_string);
        //var_dump($data[1]);exit;
        $data_decode = base64_decode($data[1]);
        //var_dump($data_decode);exit;
        return imagecreatefromstring($data_decode);

        // we could add validation here with ensuring count( $data ) > 1
        // fwrite( $ifp, base64_decode( $data[ 1 ] ) );
        //
        // // clean up the file resource
        // fclose( $ifp );
        //
        // return $output_file;
    }

    public function rand_color() {
        return '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);
    }

    function datediffInWeeks($date1, $date2) {
        if($date1 > $date2) return $this->datediffInWeeks($date2, $date1);
        $first = DateTime::createFromFormat('m/d/Y', $date1);
        $second = DateTime::createFromFormat('m/d/Y', $date2);
        return floor($first->diff($second)->days/7);
    }

    function notempty($var) {
        return ($var==="0"||$var);
    }


    //Session
    function init_session($lo) {
        $this->lo = $lo;
        $id = session_id();
        //var_dump($id."dsdsds");exit;
        $row = $lo->getSessionData($id);
        $count = count($row);
        $idx = $count - 1;
        if(!count($row)) {
            return '';
        }
        //return $row[0]['data'];

        $session = Session::unserialize($row[$idx]['data']);

        //echo json_encode($session);exit;

        unset($session['loginUser']);
        unset($session['loginAccount']);

        $session['loginUser'] = array(
            '_id'=>$row[$idx]['_id'],
            'email'=>$row[$idx]['email'],
            'dateLastlogin'=>$row[$idx]['dateLastlogin'],
            'ip'=>$row[$idx]['ip'],
            'loginCount'=>$row[$idx]['loginCount'],
            'emailVerified'=>$row[$idx]['emailVerified'],
            'firstName'=>$row[$idx]['firstName'],
            'lastName'=>$row[$idx]['lastName'],
            'hybridauth_provider_name'=>$row[$idx]['hybridauth_provider_name'],
            'hybridauth_provider_uid'=>$row[$idx]['hybridauth_provider_uid'],
            'notifyNewslettersEmails'=>$row[$idx]['notifyNewslettersEmails'],
            'notifyTipsEmails'=>$row[$idx]['notifyTipsEmails'],
            'emailValidationToken'=>$row[$idx]['emailValidationToken'],
            'dateEmailvalidation'=>$row[$idx]['dateEmailvalidation'],
            'location'=>$row[$idx]['location'],
            'timezone'=>$row[$idx]['timezone'],
            'dateformat'=>$row[$idx]['dateformat'],
            'use12hr'=>$row[$idx]['use12hr'],
            'phone'=>$row[$idx]['phone'],
            'referer'=>$row[$idx]['referer'],
        );

        if($row[$idx]['admin'] == '1') {
            $session['loginUser']['admin'] = array(
                'userId' => $row[$idx]['adminUserId'],
                'rights' => $row[$idx]['adminRights'],
            );
        } else if($session['admin']) {
            $session['loginUser']['admin'] = $session['admin'];
        }


        $session['loginAccount'] = array(
            '_id'=>$row[$idx]['accountId'],
            'accountStatus'=>$row[$idx]['accountStatus'],
            'planExpiration'=>$row[$idx]['planExpiration'],
            'stripeCustomerId'=>$row[$idx]['stripeCustomerId'],
            'stripeCustomer'=>$row[$idx]['stripeCustomer'],
            'stripeSubscription'=>$row[$idx]['stripeSubscription'],
            'subscriptionWillRenew'=>$row[$idx]['subscriptionWillRenew'],
            'stripeConnect'=>$row[$idx]['stripeConnect'],
            'refundEligible'=>$row[$idx]['refundEligible'],
            'planStart'=>$row[$idx]['planStart'],
            'ccBrand'=>$row[$idx]['ccBrand'],
            'ccLast4'=>$row[$idx]['ccLast4'],
            'companyName'=>$row[$idx]['companyName'],
            'accountRights'=>$row[$idx]['accountRights'],
            'permissions'=>$row[$idx]['permissions'],
            'blocked'=>$row[$idx]['blocked'],
        );

        //echo json_encode($session);exit;
        $GLOBALS['sess'] = $session;

        //var_dump($GLOBALS['sess']);exit;

    }

    function save_session($key, $value=null) {
        $id = session_id();

        if(is_array($key)) {
            foreach($key as $k=>$v) {
                $GLOBALS['sess'][$k] = $v;
            }
        } else {
            $GLOBALS['sess'][$key] = $value;
        }

        unset($GLOBALS['sess']['loginUser']);
        unset($GLOBALS['sess']['loginAccount']);

        $sess = $GLOBALS['sess'];

        $serialized = Session::serialize($sess);
        $this->lo->writeSession($id, $serialized);
    }

    function destroy_session() {
        $id = session_id();
        $this->lo->removeSession($id);
    }

    function e_crypt( $string, $action = 'e' ) {
        // you may change these values to your own
        $secret_key = 'my_simple_secret_key';
        $secret_iv = 'my_simple_secret_iv';

        $output = false;
        $encrypt_method = "AES-256-CBC";
        $key = hash( 'sha256', $secret_key );
        $iv = substr( hash( 'sha256', $secret_iv ), 0, 16 );

        if( $action == 'e' ) {
            $output = base64_encode( openssl_encrypt( $string, $encrypt_method, $key, 0, $iv ) );
        }
        else if( $action == 'd' ){
            $output = openssl_decrypt( base64_decode( $string ), $encrypt_method, $key, 0, $iv );
        }

        return $output;
    }

    function delete_form_files($data, $lo) {
        if($data['form_id']) {
            //remove logo
            $logo = $GLOBALS['conf']['filepath_img'].'/'.$data['form_id'].'_logo.jpg';
            if(file_exists($logo)) {
                unlink($logo);
            }

            //remove form images
            $this->lo = $lo;
            $elements = $this->lo->getFormElement(array('form_id'=>$data['form_id']));
            //var_dump($elements);exit;
            foreach($elements as $element) {
                if($element['type']=='PICTURE') {
                    $pic = $GLOBALS['conf']['filepath_img'].'/'.$element['_id'].'.jpg';
                    if(file_exists($pic)) {
                        unlink($pic);
                    }
                }
            }
        }
    }

    function getElement($elements, $el_id=null) {
        $element=array();
        if (count($elements)) {
            if($el_id) {
                foreach ($elements as $key=>$row) {
                    if ($row['_id']==$el_id) {
                        $element=$row;
                        break;
                    }
                }
            } else {
                $element = $elements;
            }
        }

        return $element;
    }

    function getElementLabel($elements, $el_id) {

        if(!is_array($elements)) {
            $els=json_decode(str_replace('\\"','"',$elements),true);
            if (!$els) {
                $els=json_decode($elements,true);
            }

            $element = $this->getElement($els, $el_id);
        } else {
            $element = $this->getElement($elements, $el_id);
        }

        if (count($element)) {
            $label = isset($element['queryName']) ? $element['queryName'] : $element['label'];
            if(!$label) { $label = $element['inputLabel']; }
            return $label;
        }

        return null;
    }

    function isPastDue($account) {
        return strtotime($account['planExpiration']) <= strtotime("now");
    }

    function getValidDateFormat($format) {
        $dateformat = 'm/d/Y';
        if($format) {
            if($format == 'MM/DD/YYYY') {
                $dateformat = 'm/d/Y';
            } else if($format == 'DD/MM/YYYY') {
                $dateformat = 'd/m/Y';
            } else if($format == 'DD-MM-YYYY') {
                $dateformat = 'd-m-Y';
            } else {
                $dateformat = 'Y-m-d';
            }
        } else {
            $dateformat = 'm/d/Y';
        }

        return $dateformat;
    }

    function getUserDateFormat($user) {
        return $this->getValidDateFormat($user['dateformat']);
    }

    function getUserTimeFormat($user) {
        $timeFormat = 'H:i';
        if($user['use12hr']) {
            $timeFormat = 'h:i A';
        }
        return $timeFormat;
    }

    function getUserLocation() {
        if($GLOBALS['conf']['env'] == 'production') {
            $city = $_SERVER['HTTP_X_APPENGINE_CITY'];
            $country_name = $_SERVER['HTTP_X_APPENGINE_COUNTRY'];
            $region = $_SERVER['HTTP_X_APPENGINE_REGION'];
            $time_zone = get_time_zone($country_name, $region);
        } else {
            $ip = $this->getIp();
            $ipfound = json_decode(file_get_contents("http://freegeoip.net/json/{$ip}"));
            if ($ipfound) {
                if($ipfound->city && $ipfound->country_name) {
                    $city = $ipfound->city;
                    $country_name = $ipfound->country_name;
                } else {
                    $city = null;
                    $country_name = null;
                }
                $time_zone = $ipfound->time_zone;
            } else {
                $city = null;
                $country_name = null;
                $time_zone = '';
            }
        }

        return [
            'city'=>$city,
            'country_name'=>$country_name,
            'time_zone'=>$time_zone
        ];
    }

    function isAdminOfForm($form, $user, $account) {

        if($user['admin']) {
            return true;
        }

        if($account['_id'] == $form['accountId']) {
            return true;
        }

        return false;

    }

    function UrlContentType($url) {

        stream_context_set_default( [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ],
        ]);

        $headers = get_headers($url, 1);
        $type = $headers["Content-Type"];

        return $type;
    }

    function isUrlImage($url) {
        $extension = substr($url, (strrpos($url, '.') + 1));
        $imgExts = array("gif", "jpg", "jpeg", "png", "tiff", "tif");
        return in_array($extension, $imgExts);
    }

    function encrypt($pure_string) {
        $key = $GLOBALS['ref']['FORMLETS_KEY'];
        $encrypted_string=openssl_encrypt($pure_string,"AES-128-ECB",$key);
        return $encrypted_string;
    }

    function decrypt($encrypted_string) {
        $key = $GLOBALS['ref']['FORMLETS_KEY'];
        $decrypted_string=openssl_decrypt($encrypted_string,"AES-128-ECB",$key);
        return $decrypted_string;
    }

}

?>
