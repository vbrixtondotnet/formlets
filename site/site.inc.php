<?php
require("output_elements.inc.php");
require("output_admin.inc.php");
require("output.inc.php");

use Aws\Ses\SesClient;
use Aws\Ses\Exception\SesException;

class site extends output {
//
    function Init() {

      $this->interfaces=array('index', 'f', 'pricing', 'privacy', 'terms', 'editor', 'forms', 'signup', 'login', 'logout', 'checkemail', 'newpassword', 'password', 'form', 'submission', 'response','responsedetail', 'template', 'settings', 'user', '__api', 'img', 'support', 'admin', 'cron', 'support', 'stats', 'api', 'file', 'logo', 'pdf', 'htmlsubmission', 'csv', 'demo', 'resendemailvalidation', 'emailok', 'email', 'themes', 'advancethemes', 'images', 'accountdeleted', 'saveorremovetemplate', 'supportimg', 'formtemplates', 'try', 'footer', 'embed', 'team', 'migrate6611gvbn', 'base64', 'newaccount','switch','datasource','acceptinvite','gotodatasource', 'integrations', 'features','generateuploadurl','deletefile', 'getdatasource', 'secretfileuploadtest', 'updateautoresponder');
      $this->interfaces_internal=array('form', 'template', 'user', 'submission', 'response', 'responsedetail', 'editor', 'admin', 'settings', 'pdf', 'csv', '__api', 'demo', 'email', 'themes', 'advancethemes', 'accountdeleted', 'saveorremovetemplate', 'team', 'newaccount','switch','datasource','gotodatasource', 'integrations', 'updateautoresponder');
      $this->interfaces_img=$GLOBALS['ref']["NO_DB_URI"];

      date_default_timezone_set('Europe/Dublin');

        if(!in_array($GLOBALS['urlpart'][1],$this->interfaces_img)) {

            // when we are not serving images

            include("../libs/platform.inc.php");
            include("../logic/logic.inc.php");
            include("../libs/session.inc.php");
            session_start();
            $this->pl=new Platform;
            $this->lo=new Logic;

            $this->pl->init_session($this->lo);

            $this->isAdmin=false;
            if (isset($GLOBALS['sess']['loginUser'])) {
                $this->lUser=$GLOBALS['sess']['loginUser'];
                $this->lAccount=$GLOBALS['sess']['loginAccount'] ? $GLOBALS['sess']['loginAccount'] : $this->getAccount();
                $this->lAccountOwner=isset($GLOBALS['sess']['loginAccountOwner']) ? $GLOBALS['sess']['loginAccountOwner'] : $this->getAccount();
                //var_dump($this->lAccountOwner);exit;
                $this->uid=$this->lUser['_id'] ?: $this->lUser['id'];
                $this->uname=$this->lUser['firstName']." ".$this->lUser['lastName'];
                $this->uemail=$this->lUser['email'];
                $this->uaccountstatus=$this->lAccount['accountStatus'];
                $this->account=$this->pl->planDetails($this->lAccount['accountStatus']);
                if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
                    $this->clientip = $_SERVER['HTTP_CLIENT_IP'];
                } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                    $this->clientip = $_SERVER['HTTP_X_FORWARDED_FOR'];
                } else {
                    $this->clientip = $_SERVER['REMOTE_ADDR'];
                }
                if($GLOBALS['urlpart'][1]!='__api' && $GLOBALS['urlpart'][1]!='forms') {
                    $this->accounts=$this->lo->getAccounts(array('userid'=>$this->lUser['_id']));
                } else {
                    $this->accounts=[];
                }
                //echo json_encode($this->accounts);exit;
                $this->hasOwnAccount = $this->hasOwnAccount($this->accounts);
                $this->uindex=$GLOBALS['ref']['plan_lookup'][$this->uaccountstatus];
                $this->isAdmin=(($this->lUser['admin']) && ($this->lUser['admin']['rights'] == 15));
                $this->fowner=null;
            }

            if (($this->lUser['email']) && ($this->lUser['emailVerified'] == 0)) {
                // check if validation is not done in other client
                if ($this->lo->emailverified($this->uid)) {
                    $this->lUser['emailVerified']=1;
                    $this->pl->save_session('success_message', $this->pl->trans('system', 'Thank you for the email verification. you can now publish forms'));
                }
            }

            $this->urlpart=array_map(array($this->pl, 'Xssenc'), $GLOBALS['urlpart']);
            $this->availableplans=$this->availablePlanList();
            $this->forms=array();
            $this->page=array(
                'title' => ucwords($this->urlpart[1])
            );
            $this->dateFormats=array('MM/DD/YYYY', 'DD/MM/YYYY', 'YYYY-MM-DD');
            $this->adminmenu=array('stats', 'support', 'features', 'users', 'templates' => 'Form Templates', 'phishing' => 'possible phishing form', 'ccform' => 'Credit card detection', 'formimages'=> 'Review Form Images', 'topviews'=> 'Top views form', 'topresponse'=> 'Top response form');

            if (!$this->pl->canUser($this->lAccount, 'manage_account')) {
                $this->interfaces_menu=array('form/' => 'Forms', 'response/' => 'Responses', 'email/' => 'Autoresponders', 'datasource/' => 'Data Sources', 'team/' => 'Team', 'settings/account/' => 'Account');
            } else {
                $this->interfaces_menu=array('form/' => 'Forms', 'response/' => 'Responses', 'email/' => 'Autoresponders', 'datasource/' => 'Data Sources', 'team/' => 'Team', 'settings/subscription/' => 'Account');
            }

            //$this->objects=array('product','image','stock','tag','type','shop','webpage','pos','brand','locality','region','country','continent','user');

        } else {
            // images are being served
            $this->urlpart=$GLOBALS['urlpart'];
        }

         if(!$this->urlpart[1]){ $this->urlpart[1]='index'; }

        if(in_array($this->urlpart[1],$this->interfaces)){
            if((!isset($this->lUser)) && (in_array($this->urlpart[1],$this->interfaces_internal))){
              // you need a logged in user to access these pages
                if($GLOBALS['urlpart'][1]=="__api") {
                    if($GLOBALS['urlpart'][2]=='_stripeWebHook') {
                        //allow
                    } else {
                        echo '{"status":"loggedout"}';
                        exit;
                    }
                } else {
                    header('location:'.$GLOBALS['level'].'login/?red='.$this->pl->Xssenc($_SERVER['REQUEST_URI']));
                    exit;
                }
            }

            //$this->pl->csrfguard_start();

              $processmethod="process".$this->urlpart[1];
              if(method_exists($this,$processmethod)){
                   $this->$processmethod();
              }

              $outmethod="output".$this->urlpart[1];
              if(method_exists($this,$outmethod)){
                   $this->$outmethod();
              }

         } else {
            $this->output404();
        }

    }
    // end of Init

    function getStripePlanId($index, $mode, $cur) {
        if ($cur == 'USD') {
            if($index == 0) {
                return 'FREE';
            }

            return $GLOBALS['ref']['plan_lists'][$index]['plan'].'-'.$mode.'-OXOPIA';
        }
        return $GLOBALS['ref']['plan_lists'][$index]['plan'].'-'.$mode.'-OXOPIA-'.$cur;
    }

    function planDetails($plan) {
        $part=explode('-', $plan);
        if ($part[2] == "OXOPIA") {
            $plan=$part[0];
            $return['interval']=$part[1];

            if ($part[3]) {
                $return['cur']=$part[4];
            } else {
                $return['cur']='USD';
            }
        }
        $index=$GLOBALS['ref']['plan_lookup_name'][$plan];
        if ($plan == "FREE" || $plan == "PREVIEW" || empty($plan)) {
            $return['plan']='FREE';
            $return['name']='Free';
            $return['interval']='Monthly';
            $return['cur']='USD';
        } else {
            if ($plan == "DOLLAR-MONTHLY") {
                $return['name']='Dollar';
                $return['interval']='Monthly';
                $return['cur']='USD';
            } else {
                if ($plan == "PRO-MONTHLY") {
                    $return['name']='Old Professional';
                    $return['interval']='Monthly';
                    $return['cur']='USD';
                } else {
                    $return['plan']=$plan;
                    $return['name']=$GLOBALS['ref']['plan_lists'][$index]['name'];
                }
            }
        }

        $return['index']=$index;

        $return['descr']=$GLOBALS['ref']['plan_lists'][$index]['descr'];

        return $return;
    }

    function availablePlanList() {

        $list=$GLOBALS['ref']['plan_lists'];
        $lookup=$GLOBALS['ref']['plan_lookup'];

        return array("list" => $list, "lookup" => $lookup);
    }

    function sendStripeConfEmail($request) {
        $body='';
        $subject='';
        if ($request['type'] == 'customer.subscription.deleted') {
            $plan_id="FREE";
        } else {
            $plan_id=$request['data']['object']['plan']['id'];
        }

        //get user
        $row=$this->lo->_getUsers(array('stripeCustomerId' => $request['data']['object']['customer']));
        $account=$this->planDetails($plan_id);
        $data['type']=$request['type'];

        if ($data['type'] == 'invoice.payment_failed') {
            $data['current_plan']['id']=$request['data']['object']['plan'];
            $data['update_type']='payment_failed';
        } else {
            //determined what is the action either upgrade or downgrade or newly created
            $data['current_plan']=$request['data']['object']['plan'];
            if ($data['type'] == 'customer.subscription.updated') {
                $data['previous_plan']=$request['data']['previous_attributes']['plan'];
                //upgrade or downgrade by comparing the charge amount
                $data['update_type']=$data['current_plan']['amount'] > $data['previous_plan']['amount'] ? 'upgrade' : 'downgrade';
            } elseif ($data['type'] == 'customer.subscription.created') { //because stripe might charge new, the type is customer.subscription.created but has previous subscription
                if ($row[0]['stripeSubscription']) {
                    $data['previous_plan']=json_encode($row[0]['stripeSubscription'])['plan'];
                    $data['update_type']=$data['current_plan']['amount'] > $data['previous_plan']['amount'] ? 'upgrade' : 'downgrade';
                } else {
                    $data['update_type']='upgrade';
                }
            } else { //subcsription deleted
                $data['current_plan']['id']='FREE';
                $data['update_type']='downgrade';
                $data['type']='customer.subscription.updated';
            }
        }


        if ($data['update_type'] == 'downgrade') { //when the user downgraded, we will update the form state based on their new subscription
            $this->lo->updateFormsAfterDowngraded($row[0], $data['current_plan']['id']);
        }

        if($data['update_type'] == 'upgrade') {
            $update_text = 'Upgrade';
        } else if($data['update_type'] == 'payment_failed') {
            $update_text = 'Payment Failed';
        } else {
            $update_text = 'Downgraded';
        }

        if ($request['type'] == 'customer.subscription.deleted') {
            $subject='Account Subscription Cancelled';
            $data['update_type']='downgrade';
        } else if($request['type'] == 'invoice.payment_failed') {
            $subject='Account Subscription Payment Failed';
            $data['update_type']='payment_failed';
        } else {
            $subject='Account '.$update_text.' to  '.$account['name'];
        }

        if (empty($account['name'])) {
            //this is the fix for stripe webhook for not paying customer, and we don't send a success email
            return false;
        } else {
              $email = trim(preg_replace('/\s+/','', $row[0]['email']));
            $emailData=array(
                'body' => $this->outputStripeMail($account, $data),
                'subject' => $subject,
                'from' => 'hello@formlets.com',
                'to' => $email
            );

            return $this->pl->sendMail($emailData);
        }
    }
    //

    /**
     * update user stripe information
     * @param  \Stripe\Customer $customer
     * @return void
     */
    private function _saveUserStripeData(\Stripe\Customer $customer) {
        //stripe customer details
        $details=array(
            'customer_id' => $customer->id,
            'cc_brand' => $customer->sources->data[0]->brand,
            'cc_last_4' => $customer->sources->data[0]->last4
        );

        // $GLOBALS['sess']['loginAccount']['ccBrand']=$details['cc_brand'];
        // $GLOBALS['sess']['loginAccount']['ccLast4']=$details['cc_last_4'];
        // $GLOBALS['sess']['loginAccount']["stripeCustomerId"]=$details['customer_id'];
        // $this->pl->save_session($GLOBALS['sess']);
        //then save it
        if ($this->lAccount) {
            $this->lo->_saveStripeId($this->lAccount['_id'], $details);
        }
    }

    /**
     * get font awesome class for specific cc brand
     * @param  String $brand
     * @return String
     */
    function ccIcon($brand) {
        switch ($brand) {
            case 'JCB':
                return 'fab fa-cc-jcb';
                break;
            case 'Visa':
            case 'Visa (debit)':
                return 'fab fa-cc-visa';
                break;
            case 'MasterCard':
            case 'MasterCard (debit)':
            case 'MasterCard (prepaid)':
                return 'fab fa-cc-mastercard';
                break;
            case 'American Express':
                return 'fab fa-cc-amex';
                break;
            case 'Discover':
                return 'fab fa-cc-discover';
                break;
            case 'Diners Club':
                return 'fab fa-cc-diners-club';
                break;
            default:
                return 'fab fa-credit-card';
                break;
        }
    }

    function processStats() {
        $this->stats = array();
        if(!$this->urlpart[2]) {
            header('Location: /response/');
            exit;
        }

        if($this->lUser) {

            if($this->urlpart[3] && in_array($this->urlpart[3], array('private', 'public'))) {
                if ($this->pl->isPreviewUser($this->lAccount)) {
                    $this->pl->save_session('error_message', $this->pl->trans($m, 'You are in preview mode. Please').' <a href="/settings/account/">'.$this->pl->trans($m, 'Register to make changes').'</a>');
                    header('Location: /stats/'.$this->urlpart[2].'/');
                    exit;
                } else {
                    $this->lo->updateFormStats(array("uid"=>$this->lAccountOwner['_id'], "form_id"=>$this->urlpart[2], "stats"=>$this->urlpart[3]));
                    $this->pl->save_session('success_message', $this->pl->trans($m, 'Response stats have been made ' . $this->urlpart[3]));
                    header('Location: /stats/'.$this->urlpart[2].'/');
                    exit;
                }

            }

            $this->forms=$this->lo->_listForms(array("uid"=>$this->lAccountOwner['_id'], "form_id"=>$this->urlpart[2]));
        } else {
            $this->forms=$this->lo->_listForms(array("form_id"=>$this->urlpart[2]));
            if(count($this->forms) == 0) {
                echo $this->output404();exit;
            }
        }

        foreach($this->forms as $form) {
            $this->submissions = $submissions = $this->lo->getSubmissions(array('formid'=>$form['_id'], 'all'=>true));
            $maxDate = $submissions[0]['dateCreated'];
            $minDate = $submissions[count($submissions) - 1]['dateCreated'];
            $datediff = strtotime($maxDate) - strtotime($minDate);
            $diffInDays = floor($datediff / (60*60*24));
            $diffInMonths = floor($datediff / (60*60*24*30));

            $dateformat = $this->pl->getUserDateFormat($this->lUser);

            usort($submissions, function($a, $b) {
                return strtotime($a['dateCreated']) - strtotime($b['dateCreated']);
            });
            if($diffInDays <= 30) {
                //display by day
                $d = array();
                $date = date($dateFormat, strtotime($minDate));
                $d[$date] = 0;
                for($ctr=1;$ctr<$diffInDays;$ctr++) {
                    $date = date($dateFormat, strtotime($minDate . ' ' . $ctr . ' days'));
                    $d[$date] = 0;
                }
                foreach($submissions as $submission) {
                    $date = date($dateFormat, strtotime($submission['dateCreated']));
                    if(!isset($d[$date])) {
                        $d[$date] = 1;
                    } else {
                        $d[$date] = $d[$date] + 1;
                    }
                }
            } else if($diffInMonths <= 6) {
                //display by weeks
                $d = array();
                $date = date('W\n\d \w\e\e\k \o\f Y', strtotime($minDate));
                $d[$date] = 0;
                $diffInWeeks = $this->pl->datediffInWeeks(date('m/d/Y',strtotime($minDate)), date('m/d/Y',strtotime($maxDate)));
                for($ctr=1;$ctr<$diffInWeeks;$ctr++) {
                    $date = date('W\n\d \w\e\e\k \o\f Y', strtotime($minDate . ' ' . $ctr . ' weeks'));
                    $d[$date] = 0;
                }
                foreach($submissions as $submission) {
                    $date = date('W\n\d \w\e\e\k \o\f Y', strtotime($submission['dateCreated']));
                    if(!isset($d[$date])) {
                        $d[$date] = 1;
                    } else {
                        $d[$date] = $d[$date] + 1;
                    }
                }
            } else {
                $d = array();
                $year1 = date('Y', strtotime($minDate));
                $year2 = date('Y', strtotime($maxDate));
                $month1 = date('m', strtotime($minDate));
                $month2 = date('m', strtotime($maxDate));
                $diff = (($year2 - $year1) * 12) + ($month2 - $month1);
                $nofmonths = 12;
                if($diff > $nofmonths) {
                    $nofmonths = $diff;
                }
                $date = date('F Y', strtotime($minDate));
                $d[$date] = 0;
                for($ctr=1;$ctr<$nofmonths;$ctr++) {
                    $date = date('F Y', strtotime($minDate . ' ' . $ctr . ' months'));
                    $d[$date] = 0;
                }
                foreach($submissions as $submission) {
                    $date = date('F Y', strtotime($submission['dateCreated']));
                    if(!isset($d[$date])) {
                        $d[$date] = 1;
                    } else {
                        $d[$date] = $d[$date] + 1;
                    }
                }
            }
            $labels = array();
            $data = array();
            foreach($d as $key=>$d1) {
                $labels[] = $key;
                $data[] = $d1;
            }
            $this->stats[$form['_id']] = array(
                'labels'=>$labels,
                'data'=>$data,
                'backgroundColor'=>'#9cc12f'
            );

            $this->additionalGraphs[$form['_id']] = array();

            $elements = $this->lo->getFormElement(array('form_id'=>$form['_id']));

            //echo json_encode($elements);exit;

            if($this->pl->formHasElement(array('elements'=>$elements), ['RADIO','CHECKBOX','SELECT', 'SWITCH', 'INPUTTABLE','STARRATING'])) {
                $optionElements = array();
                $optionElementIds = array();
                foreach($elements as $element) {
                    if(in_array($element['type'], ['RADIO','CHECKBOX','SELECT', 'SWITCH'])) {
                        $optionElements[] = [
                            'id'=>$element['_id'],
                            'title'=>$element['inputLabel'],
                            'options'=>$element['optionsList'],
                            'type'=>$element['type']
                        ];

                        $optionElementIds[] = $element['_id'];
                    } else if(in_array($element['type'], ['INPUTTABLE'])) {
                        //echo json_encode($element);exit;
                        foreach($element['questionList'] as $question) {
                            $optionElements[] = [
                                'id'=>$element['_id'] . '_' . $this->pl->slugify($question['label']),
                                'title'=>$element['inputLabel'] . ' ' . $question['label'],
                                'options'=>$element['answerList'],
                                'type'=>$element['type']
                            ];

                            $optionElementIds[] = $element['_id'] . '_' . $this->pl->slugify($question['label']);
                        }

                    } else if(in_array($element['type'], ['STARRATING'])) {
                        $optionElements[] = [
                            'id'=>$element['_id'],
                            'title'=>$element['inputLabel'],
                            'options'=>array(
                                array('label'=>1),
                                array('label'=>2),
                                array('label'=>3),
                                array('label'=>4),
                                array('label'=>5)
                            ),
                            'type'=>$element['type']
                        ];

                        $optionElementIds[] = $element['_id'];
                    }
                }

                //echo json_encode($optionElements);exit;

                foreach($optionElements as $oel) {
                    $labels = array();
                    foreach($oel['options'] as $opt) {
                        $labels[] = $opt['value'] ? $opt['value']:$opt['label'];
                    }
                    $this->additionalGraphs[$form['_id']][$oel['id']] = array(
                        'labels' => $labels,
                        'data'=> array(),
                        'title'=> $oel['title'],
                        'backgroundColor'=>'#9cc12f',
                        'type'=>$oel['type'],
                        'totalSubmissions'=>count($submissions)
                    );
                }
                $x=0;
                foreach($submissions as $submission) {
                    $encrypted = $submission['encrypted'];
                    $sdata = $submission['data'];
                    $sdata = json_decode(str_replace('\\','',$sdata), true);
                    if(!$sdata) {
                        $sdata = json_decode($sdata, true);
                        if(!$sdata) {
                            $sdata = json_decode($submission['data'],true);
                        }
                    }
                    $dlabels = array();
                    if($sdata) {
                        $x++;
                        foreach($sdata as $d) {
                            if($encrypted) {
                                $d['value'] = $this->pl->decrypt($d['value']);
                            }
                            //echo json_encode($d);exit;
                            if(in_array($d['_id'], $optionElementIds)) {
                                $dlabels = $this->additionalGraphs[$form['_id']][$d['_id']]['labels'];
                                //var_dump($dlabels);exit;
                                $ddata = $this->additionalGraphs[$form['_id']][$d['_id']]['data'];

                                $idx = array_search($d['value'], $dlabels);

                                $newData = array();
                                foreach($dlabels as $k=>$dlabel) {
                                    $newData[$k] = isset($ddata[$k]) ? $ddata[$k]:0;
                                }

                                if(is_array($d['value'])) { $d['value'] = implode(',',$d['value']); }
                                $valueArray = explode(',',$d['value']);
                                if(count($valueArray) > 1 && in_array($this->additionalGraphs[$form['_id']][$d['_id']]['type'],['CHECKBOX','SWITCH'])) {
                                    foreach($valueArray as $v) {
                                        $v = trim($v);
                                        $idx = array_search($v, $dlabels);
                                        $newData[$idx] = $newData[$idx] + 1;
                                        $this->additionalGraphs[$form['_id']][$d['_id']]['data'] = $newData;
                                    }
                                } else {
                                    $newData[$idx] = $newData[$idx] + 1;
                                    $this->additionalGraphs[$form['_id']][$d['_id']]['data'] = $newData;
                                }
                            }
                        }
                    }

                }

                //var_dump($x);exit;
                //echo json_encode($this->additionalGraphs);exit;
            }

        }
    }

    function processResponsedetail() {
        $formId = $this->urlpart[2];
        $responseId = $this->urlpart[3];
        if(!$formId && !$responseId) {
            $this->Output404();exit;
        }

        $response = $this->lo->getSubmissions(array('formid'=>$formId, 'id'=>$responseId, 'viewdetail'=>true));

        if(count($response) == 0 && $formId) {
            header("Location: /response/".$formId."/");exit;
        }

        $this->response = $response[0];
    }

    function processResponse() {

        $m="processsubmission";
        $uri='/response/';

        $formid = $this->urlpart[2];
    	if($formid){
        	$this->forms=$this->lo->_listForms(array('uid'=>$this->lAccountOwner['_id'],'form_id'=>$formid));
      	} else {
        	$this->forms=$this->lo->_listForms(array("uid"=>$this->lAccountOwner['_id']));
      	}

        if($_POST['type'] == 'responseStatus' && isset($_GET['ajax'])) {
            $statuses = json_decode($this->forms[0]['responseStatusLists'],true);
            if($_POST['action'] == 'new') {
                $statuses[] = array(
                    '_id'=>$this->pl->insertId(6),
                    'label'=>$_POST['label']
                );
            } else if($_POST['action'] == 'edit') {
                foreach($statuses as &$status) {
                    if($status['_id'] == $_POST['status']) {
                        $status['label'] = $_POST['label'];
                    }
                }
            } else if($_POST['action'] == 'delete') {
                foreach($statuses as $k=>&$status) {
                    if($status['_id'] == $_POST['status']) {
                        unset($statuses[$k]);
                    }
                }
            }

            $this->lo->updateFormStatusLists(array(
                'formId'=>$this->forms[0]['_id'],
                'lists'=>json_encode($statuses)
            ));

            echo json_encode($statuses);exit;
        }

        if($_POST['type'] == 'moveStatus' && isset($_GET['ajax'])) {
            $this->lo->updateResponseStatus(array(
                'responseId'=>$_POST['responseId'],
                'status'=>$_POST['status']
            ));
            exit;
        }

        if (($this->urlpart[2] != 'delete') && ($this->urlpart[2])) {
            $uri .= $this->urlpart[2].'/';
        }
        if ($this->urlpart[3] && ($this->urlpart[2] != 'delete') && is_numeric($this->urlpart[3])) {
            $uri .= $this->urlpart[3].'/';
        }

        if (($this->urlpart[2] == 'delete') && $this->urlpart[3]) {
            $this->pl->validate_csrfguard();
            $this->lo->deletesubmission(array('id' => $this->urlpart[3]));
            $this->pl->save_session('success_message', $this->pl->trans($m, 'Response successfully deleted.'));
            $uri.=$_GET['f'].'/';
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit;
        } else if($this->urlpart[3] == 'datasource') {
            if(isset($_GET['getColumns'])) {
                $_GET['getColumns'] = $_GET['getColumns'] ?:'no-exists';
                $datasource = $this->lo->_listDatasources(array('uid'=>$this->lAccountOwner['_id'],'source_id'=>$_GET['getColumns']));
                if(!$datasource[0]['columns']) {
                    $datasource[0]['columns'] = '["Label","Value"]';
                }
                echo json_encode($datasource);exit;
            } else if($this->urlpart[4] && $this->urlpart[5]=='delete') {
                $connector_id = $this->urlpart[4];
                $this->lo->removeDatasourceConnector(array('id'=>$connector_id));
                $this->pl->save_session('success_message', $this->pl->trans($m, 'Connector successfully deleted.'));
                header('Location: /response/'.$this->urlpart[2].'/datasource/');
                exit;
            } else if($this->urlpart[4] && $this->urlpart[4]=='import') {
                $connectors = $this->lo->getDatasourceConnector(array('formId'=>$this->urlpart[2], 'accountId'=>$this->lAccount['_id']));
                if(count($connectors)) {

                    $datasource = $this->lo->_listDatasources(array('uid'=>$this->lAccountOwner['_id'],'source_id'=>$connectors[0]['datasourceId']));

                    if(!$datasource[0]['columns']) {
                        $datasource[0]['columns'] = '["Label","Value"]';
                    }
                    $columns = json_decode($datasource[0]['columns']);

                    $ces = array();
                    foreach($connectors as $connector) {
                        $ces[$connector['elementId']] = $connector['datasourceColumn'];
                    }

                    if($connectors[0]['import']) {

                    } else {
                        $submissions = $this->lo->getSubmissions(array(
                            'formid'=>$this->urlpart[2],
                            'all'=>true
                        ));

                        $lists = array();
                        $ctr=0;
                        foreach($submissions as $submission) {
                            $lists[$ctr] = array();
                            $submission['data'] = str_replace('\r\n', '<br>', $submission['data']);
                            $rdata = json_decode(str_replace('\\','',$submission['data']), true);
                            if(!$rdata) {
                                $rdata = json_decode($rows['data'][$r]['data'], true);
                            }

                            foreach($rdata as $data) {
                                if(isset($ces[$data['_id']])) {
                                    if(strtolower($ces[$data['_id']]) == 'label') {
                                        $lists[$ctr]['label'] = $data['value'];
                                    } else if(strtolower($ces[$data['_id']]) == 'value') {
                                        $lists[$ctr]['value'] = $data['value'];
                                    } else {
                                        $key = array_search($ces[$data['_id']], $columns);
                                        if($key > 1) {
                                            $lists[$ctr]['column_'.($key+1)] = $data['value'];
                                        } else {
                                            if($key == 0) {
                                                $lists[$ctr]['label'] = $data['value'];
                                            } else if($key == 1) {
                                                $lists[$ctr]['value'] = $data['value'];
                                            }
                                        }
                                    }
                                }
                            }
                            $ctr++;
                        }

                        $updated = $this->lo->saveDatasource(array(
                            'old_id'=>$datasource[0]['_id'],
                            'title'=>$datasource[0]['title'],
                            'data'=>json_encode($lists,JSON_UNESCAPED_UNICODE),
                            'columns'=>$datasource[0]['columns']
                        ));

                        $this->lo->saveDatasourceConnector(array('old_formId'=>$this->urlpart[2], 'import'=>1));

                        $this->pl->save_session('success_message', $this->pl->trans($m, 'Old responses has been imported'));
                        header('Location: /response/'.$this->urlpart[2].'/datasource/');exit;
                    }
                }
            } else if($_POST) {
                if($_POST['type'] == 'newConnector' && isset($_GET['ajax'])) {
                    $this->lo->removeDatasourceConnector(array('formId'=>$this->urlpart[2]));

                    $elements = $_POST['element'];
                    foreach($elements as $k=>$element) {
                        $column = $_POST['column'][$k];
                        if($column) {
                            $connector = array();
                            $connector['id'] = $this->pl->insertId();
                            $connector['accountId'] = $this->lAccount['_id'];
                            $connector['formId'] = $this->urlpart[2];
                            $connector['elementId'] = $element;
                            $connector['datasourceId'] = $this->pl->Xssenc($_POST['datasourceId']);
                            $connector['datasourceColumn'] = $column;

                            $this->lo->saveDatasourceConnector($connector);
                        }
                    }
                } else if($_POST['type'] == 'newConnector' && !isset($_GET['ajax'])) {
                    $this->lo->removeDatasourceConnector(array('formId'=>$this->urlpart[2]));
                    $elements = $_POST['element'];
                    $connector_element = array();
                    $ds_columns = array();
                    foreach($elements as $k=>$element) {
                        $column = $_POST['newColumns_input'][$k];
                        if(!$column) {
                            $column = $_POST['newColumns_select'][$k];
                        }

                        $column = trim($column);

                        if(!in_array($column, $ds_columns) && $column) {
                            $ds_columns[] = $column;
                        }

                        $connector_element[$element] = $column;
                    }

                    $ds = $this->lo->_listDatasources(array('uid'=>$this->lAccountOwner['_id'], 'accountId'=>$this->lAccount['_id']));
                    $ds_title = 'List ' . (count($ds)+1);

                    $sourceId = $this->pl->insertId();
                    $newsource = $this->lo->saveDatasource(array(
                        'id'=>$sourceId,
                        'uid'=>$this->lAccountOwner['_id'],
                        'accountId'=>$this->lAccount['_id'],
                        'title'=>$ds_title,
                        'data'=>'',
                        'columns'=>json_encode($ds_columns, JSON_UNESCAPED_UNICODE)
                    ));

                    foreach($connector_element as $kce=>$ce) {
                        if($ce) {
                            $connector = array();
                            $connector['id'] = $this->pl->insertId();
                            $connector['accountId'] = $this->lAccount['_id'];
                            $connector['formId'] = $this->urlpart[2];
                            $connector['elementId'] = $kce;
                            $connector['datasourceId'] = $sourceId;
                            $connector['datasourceColumn'] = $ce;

                            $this->lo->saveDatasourceConnector($connector);
                        }
                    }

                    header('Location: /response/'.$this->urlpart[2].'/datasource/');
                    exit;
                }
            }
        }

        if ($_POST['response']) {
            //$GLOBALS['sess']['response_select']=$_POST['response'];
            $uri='/response/'.$this->urlpart[2].'/'.$_POST['response'].'/';
            header('Location: '.$uri);
            exit;
        }
    }

    public function processGotodatasource() {
        $link = $this->lo->getDatasourcelink(array('elementId'=>$this->urlpart[2]));
        if(count($link)) {
            header('Location: /datasource/'.$link[0]['datasourceId'].'/');exit;
        } else {
            $this->output404();
            exit;
        }
    }

    public function processIntegrations() {
        $integrationId = $this->urlpart[2];
        $this->integrations = null;
        if($integrationId && $integrationId!='new') {
            $this->integrations = $this->lo->_listIntegrations(array('uid'=>$this->lAccountOwner['_id'],'integration_id'=>$integrationId));
        }
        if($_POST) {
            if($integrationId == 'new') {
                $title = $this->pl->Xssenc($_POST['title']);
                $type = $this->pl->Xssenc($_POST['type']);

                if(empty($title) || empty($type)) {
                    $this->pl->save_session('error_message', 'All fields are required.');
                    header('Location: /integrations/new/');exit;
                }

                $integrationnewid = $this->pl->insertId();

                $updated = $this->lo->saveIntegration(array(
                    'id'=>$integrationnewid,
                    'uid'=>$this->lAccountOwner['_id'],
                    'title'=>$title,
                    'type'=>$type,
                    'configs'=>'',
                ));


                header('Location: /integrations/'.$integrationnewid.'/');exit;

            } else if(isset($_POST['formId']) && isset($_POST['elementId'])) {

                $sourceLinks = $this->lo->getIntegrationlink(array('formId'=>$_POST['formId'],'elementId'=>$_POST['elementId']));
                if(count($sourceLinks)) {
                    $this->pl->save_session('error_message', 'Element already linked');
                    header('Location: /integrations/'.$integrationId.'/');exit;
                } else {
                    $newlink = $this->lo->saveIntegrationlink(array(
                        'id'=>$this->pl->insertId(),
                        'formId'=>$_POST['formId'],
                        'elementId'=>$_POST['elementId'],
                        'formConfigsId'=>$integrationId
                    ));

                    $this->pl->save_session('success_message', 'Element has been linked');
                    header('Location: /integrations/'.$integrationId.'/');exit;
                }
            } else {
                if(isset($_GET['ajax']) && $_GET['ajax']=='true') {
                    $title = $this->pl->Xssenc($_POST['title']);
                    $cs = $_POST['columns'];

                    $data = array();

                    foreach($cs as $c) {
                        $data[$c] = $_POST[$c];
                    }

                    $updated = $this->lo->saveIntegration(array(
                        'old_id'=>$integrationId,
                        'title'=>$title,
                        'configs'=>json_encode($data,JSON_UNESCAPED_UNICODE),
                    ));
                }
            }
        }

        if($this->urlpart[3] == 'remove' && $this->urlpart[4]) {
            $remove = $this->lo->removeDatasourcelink(array('id'=>$this->urlpart[4]));
            $this->pl->save_session('success_message', 'Element has been unlinked');
            header('Location: /datasource/'.$datasourceId.'/');exit;
        }
    }

    public function processDatasource() {
        $datasourceId = $this->urlpart[2];
        $this->datasource = null;
        if($datasourceId) {
            $this->datasource = $this->lo->_listDatasources(array('uid'=>$this->lAccountOwner['_id'],'source_id'=>$datasourceId));
        }

        if($this->urlpart[3] == 'delete') {
            if($this->datasource[0]['count']) {
                $this->pl->save_session('error_message', 'Unable to delete datasource because there are forms linked to it.');
                header('Location: /datasource/');exit;
            } else {
                $this->lo->removeDatasource(array('id'=>$datasourceId, 'uid'=>$this->lAccountOwner['_id'], 'accountId'=>$this->lAccount['_id']));
                $this->pl->save_session('success_message', 'Datasource has been deleted');
                header('Location: /datasource/');exit;
            }
        } else {
            if($_POST) {
                if(isset($_POST['formId']) && isset($_POST['elementId'])) {

                    $sourceLinks = $this->lo->getDatasourcelink(array('formId'=>$_POST['formId'],'elementId'=>$_POST['elementId']));
                    if(count($sourceLinks)) {
                        $this->pl->save_session('error_message', 'Element already linked');
                        header('Location: /datasource/'.$datasourceId.'/');exit;
                    } else {
                        $newlink = $this->lo->saveDatasourcelink(array(
                            'id'=>$this->pl->insertId(),
                            'formId'=>$_POST['formId'],
                            'elementId'=>$_POST['elementId'],
                            'datasourceId'=>$datasourceId
                        ));

                        $this->pl->save_session('success_message', 'Element has been linked');
                        header('Location: /datasource/'.$datasourceId.'/');exit;
                    }
                } else {
                    if(isset($_GET['ajax']) && $_GET['ajax']=='true') {
                        $title = $this->pl->Xssenc($_POST['title']);
                        $labels = $_POST['labels'];
                        $values = $_POST['values'];
                        $cs = $_POST['columns'];

                        $data = array();
                        $x=0;
                        foreach($labels as $key=>$label) {
                            $data[$x] = array(
                                'label'=>$label,
                                'value'=>$values[$key]
                            );
                            $ctr=1;
                            foreach($cs as $c) {
                                if($ctr > 2) {
                                    $data[$x]['column_'.$ctr] = $_POST['column_'.$ctr][$x];
                                }
                                $ctr++;
                            }
                            $x++;
                        }

                        $updated = $this->lo->saveDatasource(array(
                            'old_id'=>$datasourceId,
                            'title'=>$title,
                            'data'=>json_encode($data,JSON_UNESCAPED_UNICODE),
                            'columns'=>json_encode($cs,JSON_UNESCAPED_UNICODE),
                        ));
                    }
                }
            }

            if($this->urlpart[3] == 'remove' && $this->urlpart[4]) {
                $remove = $this->lo->removeDatasourcelink(array('id'=>$this->urlpart[4]));
                $this->pl->save_session('success_message', 'Element has been unlinked');
                header('Location: /datasource/'.$datasourceId.'/');exit;
            }
        }


    }

    function _stripeErrorHandler($exception, $ajax=false) {
        $body=$exception->getJsonBody();
        $err=$body['error'];
        if ($ajax) {
            return $err;
        } else {
            $this->pl->save_session('error_message', $err['message']);
        }
    }

    //
    function processSubscription() {
        require_once('../libs/stripe-php-3.20.0/init.php');
        //var_dump($this->lAccount);
        $cur=$_GET['cur'];
        $mode=$_GET['mode'];
        if (!$cur) {
            $cur="USD";
        }
        if (!$mode) {
            $mode="MONTHLY";
        }

        $plan_id=$this->urlpart[4];
        if($_GET['success'] == 'y' && !$plan_id) {
            $plan_id = $_GET['plan'];
        }
        $thelist=$this->availableplans["list"];
        $plan=$this->getStripePlanId($plan_id, $mode, $cur);

        if($_GET['success'] == 'y') {
            $this->lAccount['accountStatus'] = $plan;
        }

        $this->account=$this->pl->planDetails($this->lAccount['accountStatus']);

        //keys will be set on config files
        \Stripe\Stripe::setApiKey($GLOBALS["conf"]["stripe_secret_key"]);

        //if has customer has stripe id but do CC details, we fetch it and update
        if ($this->lAccount["stripeCustomerId"]) {
            try {
                //after save, retrieve the customer again from stripe to get the updated card details
                $customer=\Stripe\Customer::retrieve($this->lAccount["stripeCustomerId"]);
                $this->subscriptionStatus = $customer->subscriptions->data[0]->status;
                if(!$this->lUser["ccBrand"] && !$this->lUser["ccLast4"]) {
                    $this->_saveUserStripeData($customer);
                }
            } catch(\Stripe\Error\InvalidRequest $e) {}
        }

        if ($this->urlpart[3] == 'change' && ($plan_id || $plan_id == '0') && $plan_id <> "card") {
            if ($plan_id != '0' && $plan_id != 3 && $plan_id != 4 && $plan_id != 5 && $plan_id != 6 && $plan_id != 7 && $plan_id != 8 && $plan_id != 9) {
                header('location: /settings/subscription/');
                exit;
            }
        }

        if ($this->urlpart[3] == 'change' && ($plan_id || $plan_id == '0') && $plan_id <> "card" && $this->pl->isFreeAccount($this->lAccount) == false && !$_GET['success'] == 'y') {
            $subscriptionSaved=false;
            $subscription=$this->lAccount["stripeSubscription"];
            if ($subscription) {
                $subscription_id=json_decode($subscription)->id;
                if ($plan_id == '0') { //if downgrade to free account, cancel subscription
                    $subscription=\Stripe\Subscription::retrieve($subscription_id);
                    try {
                        if ($subscription->status == 'active' || $subscription->status == 'past_due' || $subscription->status == 'unpaid') {
                            $subscription->cancel();
                        }
                        //update user session, no need to logout

                        $data['id']=$this->lAccount['_id'];
                        $data['stripeSubscription']=NULL;
                        $data['accountStatus']="FREE";
                        $data['planExpiration']=date('Y-m-d H:i:s');
                        $data['stripeCustomerId']=NULL;
                        $data['ccBrand']=NULL;
                        $data['ccLast4']=NULL;
                        $this->lo->updateAccount($data);

                        header('location: /settings/subscription/?success=y&cur='.$cur.'&mode='.$mode.'&plan='.$plan_id);
                        exit;
                    } catch(\Stripe\Error\InvalidRequest $e) {
                        $data['id']=$this->lAccount['_id'];
                        $data['stripeSubscription']=NULL;
                        $data['accountStatus']="FREE";
                        $data['planExpiration']=date('Y-m-d H:i:s');
                        $data['stripeCustomerId']=NULL;
                        $data['ccBrand']=NULL;
                        $data['ccLast4']=NULL;
                        $this->lo->updateAccount($data);

                        header('location: /settings/subscription/?success=y&cur='.$cur.'&mode='.$mode.'&plan='.$plan_id);
                        exit;
                    }
                } else {
                    $subscription=\Stripe\Subscription::retrieve($subscription_id);
                    $subscription->plan=$plan;
                    if ($subscription->save()) { //if subscription successfully saved
                        $subscriptionSaved=true; //set flag to true
                    }
                }

            } else if($plan_id <> '0') {
                $subscription=\Stripe\Subscription::create(array(
                    "customer" => $this->lAccount["stripeCustomerId"],
                    "plan" => $plan,
                ));

                if ($subscription) { //if subscription successfully saved
                    $subscriptionSaved=true; //set flag to true
                }
            } else if($plan_id == '0') {
                $data['id']=$this->lAccount['_id'];
                $data['stripeSubscription']=NULL;
                $data['accountStatus']="FREE";
                $data['planExpiration']=date('Y-m-d H:i:s');
                $data['stripeCustomerId']=NULL;
                $data['ccBrand']=NULL;
                $data['ccLast4']=NULL;
                $this->lo->updateAccount($data);
                header('location: /settings/subscription/?success=y&cur='.$cur.'&mode='.$mode.'&plan='.$plan_id);exit;
            }

            if ($subscriptionSaved) { //if there is no error when saving new subscription
                //update user session, no need to logout
                $GLOBALS['sess']['loginAccount']['stripeSubscription']=json_encode($subscription);
                if (!$subscription->plan->id) {
                    $GLOBALS['sess']['loginAccount']['accountStatus']=$subscription->plan;
                } else {
                    $GLOBALS['sess']['loginAccount']['accountStatus']=$subscription->plan->id;
                }
                $GLOBALS['sess']['loginAccount']['planExpiration']=date('Y-m-d H:i:s', $subscription->current_period_end);
            }

            //$this->pl->save_session($GLOBALS['sess']);

            header('location: /settings/subscription/?success=y&cur='.$cur.'&mode='.$mode.'&plan='.$plan_id);
            exit;
        }

        if (isset($_POST['submitType']) && $_POST['submitType'] == 'changeCard') {
            try {
                $token=$_POST['stripeToken'];
                $customer=\Stripe\Customer::retrieve($this->lAccount["stripeCustomerId"]);
                $customer->source=$token;
                $customer->save();

                //after save, retrieve the customer again from stripe to get the updated card details
                $customer=\Stripe\Customer::retrieve($this->lAccount["stripeCustomerId"]);
                $this->_saveUserStripeData($customer);
            } catch (\Stripe\Error\Card $e) {
                // Since it's a decline, \Stripe\Error\Card will be caught
                $this->_stripeErrorHandler($e);
            } catch (\Stripe\Error\RateLimit $e) {
                // Too many requests made to the API too quickly
                $this->_stripeErrorHandler($e);
            } catch (\Stripe\Error\InvalidRequest $e) {
                // Invalid parameters were supplied to Stripe's API
                $this->_stripeErrorHandler($e);
            } catch (\Stripe\Error\Authentication $e) {
                // Authentication with Stripe's API failed
                $this->_stripeErrorHandler($e);
                // (maybe you changed API keys recently)
            } catch (\Stripe\Error\ApiConnection $e) {
                // Network communication with Stripe failed
                $this->_stripeErrorHandler($e);
            } catch (\Stripe\Error\Base $e) {
                // Display a very generic error to the user, and maybe send
                $this->_stripeErrorHandler($e);
            } catch (Exception $e) {
                // Something else happened, completely unrelated to Stripe
                $GLOBALS['sess']['error_message']='Something went wrong, please contact us';
                $this->pl->save_session('error_message', 'Something went wrong, please contact us');
            }

            //after assigning the new session of CC details, redirect to account page
            header('location: /settings/subscription/');
            exit;
        }

        if ($_POST) {
            $token=$_POST['stripeToken'];
            $couponCode=$_POST['couponCode'];
            $subscriptionSaved=false;
            $err=false;
            try {
                if ($this->lAccount["stripeCustomerId"]) {
                    $subscription=$this->lAccount["stripeSubscription"];
                    if ($subscription) {
                        $subscription_id=json_decode($subscription)->id;
                        $subscription=\Stripe\Subscription::retrieve($subscription_id);
                        $subscription->plan=$plan;

                        if($couponCode) {
                            $subscription->coupon=$couponCode;
                        }

                        if ($subscription->save()) { //if subscription successfully saved
                            $subscriptionSaved=true; //set flag to true
                        }
                    } else {
                        $sData = array(
                            "customer" => $this->lAccount["stripeCustomerId"],
                            "plan" => $plan,
                        );

                        if($couponCode) {
                            $sData['coupon'] = $couponCode;
                        }

                        $subscription=\Stripe\Subscription::create($sData);

                        if ($subscription) { //if subscription successfully saved
                            $subscriptionSaved=true; //set flag to true
                        }
                    }

                    //after updating the subscription, we would update the user CC details
                    //retrieve customer
                    $customer=\Stripe\Customer::retrieve($subscription->customer);

                    $this->_saveUserStripeData($customer);
                } else {
                    // creating customer
                    $customer=\Stripe\Customer::create(array(
                        'email' => $this->uemail,
                        'source' => $token,
                    ));

                    $sData = array(
                        "customer" => $customer->id,
                        "plan" => $plan,
                    );

                    if($couponCode) {
                        $sData['coupon'] = $couponCode;
                    }

                    $subscription=\Stripe\Subscription::create($sData);

                    $this->_saveUserStripeData($customer);

                    if ($subscription) { //if subscription successfully saved
                        $subscriptionSaved=true; //set flag to true
                    }
                }

                if ($subscriptionSaved) { //if there is no error when saving new subscription
                    //update user session, no need to logout
                    $GLOBALS['sess']['loginAccount']['stripeSubscription']=json_encode($subscription);
                    if (!$subscription->plan->id) {
                        $GLOBALS['sess']['loginAccount']['accountStatus']=$subscription->plan;
                    } else {
                        $GLOBALS['sess']['loginAccount']['accountStatus']=$subscription->plan->id;
                    }
                    $GLOBALS['sess']['loginAccount']['planExpiration']=date('Y-m-d H:i:s', $subscription->current_period_end);
                    //$this->pl->save_session($GLOBALS['sess']);
                }
            } catch (\Stripe\Error\Card $e) {
                $err=true;
                // Since it's a decline, \Stripe\Error\Card will be caught
                $this->_stripeErrorHandler($e);
            } catch (\Stripe\Error\RateLimit $e) {
                $err=true;
                // Too many requests made to the API too quickly
                $this->_stripeErrorHandler($e);
            } catch (\Stripe\Error\InvalidRequest $e) {
                $err=true;
                // Invalid parameters were supplied to Stripe's API
                $this->_stripeErrorHandler($e);
            } catch (\Stripe\Error\Authentication $e) {
                $err=true;
                // Authentication with Stripe's API failed
                $this->_stripeErrorHandler($e);
                // (maybe you changed API keys recently)
            } catch (\Stripe\Error\ApiConnection $e) {
                $err=true;
                // Network communication with Stripe failed
                $this->_stripeErrorHandler($e);
            } catch (\Stripe\Error\Base $e) {
                $err=true;
                // Display a very generic error to the user, and maybe send
                $this->_stripeErrorHandler($e);
            } catch (Exception $e) {
                $err=true;
                // Something else happened, completely unrelated to Stripe
                $this->pl->save_session('error_message', 'Something went wrong, please contact us');
            }

            if ($err) {
                header('location: /settings/subscription/');
            } else {
                header('location: /settings/subscription/change/'.$plan_id.'/?cur='.$cur.'&mode='.$mode.'&success=y');
            }
            exit;
        }
    }

function processindex(){

  if (!$GLOBALS['sess']['referer']) {
      if ($_SERVER["HTTP_REFERER"]) {
          $this->pl->save_session('referer', $_SERVER["HTTP_REFERER"]);
      }
  }

}


    function processAccount() {
        $m="processaccount";
        $currentEmail=$this->lUser['email'];
        $data['_id']=$this->lUser['_id'];

        if($this->urlpart[3]=='plan') {

            if(!$this->pl->isPreviewUser($this->lAccount)) {
                header('location: /settings/subscription/');
                exit;
            }

            if($_POST && !$this->urlpart[4]) {
                $plan = $_POST['formletsPlan'];
                $plan = explode('_', $plan);
                if($plan[0] == '0') {
                    $this->lo->updateAccount(array('id'=>$this->lAccount['_id'], 'accountStatus'=>'FREE'));
                    header('location: /settings/subscription/?newuser=true');
                    exit;
                } else {
                    header('location: /settings/account/plan/'.$plan[0].'/?cur='.$plan[1].'&mode='.$plan[2].'&newuser=true');exit;
                }
            }

            if($this->urlpart[4]) {
                $index=$this->urlpart[4];
                $this->plid = $index;

                if ($_POST) {

                    require_once('../libs/stripe-php-3.20.0/init.php');

                    \Stripe\Stripe::setApiKey($GLOBALS["conf"]["stripe_secret_key"]);

                    $cur=$_GET['cur'];
                    $mode=$_GET['mode'];
                    if (!$cur) {
                        $cur="USD";
                    }
                    if (!$mode) {
                        $mode="MONTHLY";
                    }

                    $plan=$this->getStripePlanId($index, $mode, $cur);

                    $token=$_POST['stripeToken'];
                    $couponCode=$_POST['couponCode'];
                    $subscriptionSaved=false;
                    $err=false;

                    try {
                        // creating customer
                        $customer=\Stripe\Customer::create(array(
                            'email' => $this->uemail,
                            'source' => $token,
                        ));

                        $sData = array(
                            "customer" => $customer->id,
                            "plan" => $plan,
                        );

                        if($couponCode) {
                            $sData['coupon'] = $couponCode;
                        }

                        $subscription=\Stripe\Subscription::create($sData);

                        //$this->_saveUserStripeData($customer);

                        if ($subscription) { //if subscription successfully saved
                            $subscriptionSaved=true; //set flag to true
                        }

                        if ($subscriptionSaved) { //if there is no error when saving new subscription
                            //update user session, no need to logout
                            $GLOBALS['sess']['loginAccount']['stripeSubscription']=json_encode($subscription);
                            if (!$subscription->plan->id) {
                                $GLOBALS['sess']['loginAccount']['accountStatus']=$subscription->plan;
                            } else {
                                $GLOBALS['sess']['loginAccount']['accountStatus']=$subscription->plan->id;
                            }
                            $GLOBALS['sess']['loginAccount']['planExpiration']=date('Y-m-d H:i:s', $subscription->current_period_end);
                            //$this->pl->save_session($GLOBALS['sess']);
                        }
                    } catch (\Stripe\Error\Card $e) {
                        $err=true;
                        // Since it's a decline, \Stripe\Error\Card will be caught
                        $this->_stripeErrorHandler($e);
                    } catch (\Stripe\Error\RateLimit $e) {
                        $err=true;
                        // Too many requests made to the API too quickly
                        $this->_stripeErrorHandler($e);
                    } catch (\Stripe\Error\InvalidRequest $e) {
                        $err=true;
                        // Invalid parameters were supplied to Stripe's API
                        $this->_stripeErrorHandler($e);
                    } catch (\Stripe\Error\Authentication $e) {
                        $err=true;
                        // Authentication with Stripe's API failed
                        $this->_stripeErrorHandler($e);
                        // (maybe you changed API keys recently)
                    } catch (\Stripe\Error\ApiConnection $e) {
                        $err=true;
                        // Network communication with Stripe failed
                        $this->_stripeErrorHandler($e);
                    } catch (\Stripe\Error\Base $e) {
                        $err=true;
                        // Display a very generic error to the user, and maybe send
                        $this->_stripeErrorHandler($e);
                    } catch (Exception $e) {
                        $err=true;
                        // Something else happened, completely unrelated to Stripe
                        $this->pl->save_session('error_message', 'Something went wrong, please contact us');
                    }

                    if ($err) {
                        header('location: /settings/account/plan/'.$this->urlpart[4].'/?cur='.$cur.'&mode='.$mode);
                    } else {
                        $details=array(
                            'customer_id' => $customer->id,
                            'cc_brand' => $customer->sources->data[0]->brand,
                            'cc_last_4' => $customer->sources->data[0]->last4
                        );
                        $this->lo->updateAccount(array('id'=>$this->lAccount['_id'], 'accountStatus'=>'FREE', 'stripeCustomerId'=>$details['customer_id'], 'ccBrand'=>$details['cc_brand'], 'ccLast4'=>$details['cc_last_4']));
                        header('location: /settings/subscription/change/'.$index.'/?cur='.$cur.'&mode='.$mode.'&success=y');
                    }
                    exit;
                }
            }

        }

        if (isset($_POST['infoUpdate'])) {
            $data['firstName']=$this->pl->Xssenc($_POST['firstName']);
            $data['lastName']=$this->pl->Xssenc($_POST['lastName']);
            $data['email']=$this->pl->Xssenc($_POST['email']);
            $data['phone']=$this->pl->Xssenc($_POST['phone']);
            $data['timezone']=$this->pl->Xssenc($_POST['timezone']);
            $data['dateformat']=$this->pl->Xssenc($_POST['date_format']);
            $data['timeformat']=$this->pl->Xssenc($_POST['timeformat']);

            //company details
            $account['accountId']=$this->lAccount['_id'];
            $account['companyName']=$this->pl->Xssenc(strip_tags($_POST['companyName']));
            if(!ctype_alnum($account['companyName'])) {
                $account['companyName'] = '';
            }

            $oldpassword=$this->pl->Xssenc($_POST['old_password']);
            $newpassword=$this->pl->Xssenc($_POST['password']);

            $this->pl->save_session('form_user_session', $data);
            //check if email exists and lookup password
            if ($data['email']) {
                $row=$this->lo->_getUsers(array('email' => $data['email']));
            }

            if (empty($data['firstName']) || empty($data['lastName']) || empty($data['email'])) {
                $this->pl->save_session('error_message', $this->pl->trans($m, 'First Name, Last Name, and Email fields are required!'));
            } else {
                if (count($row) && $currentEmail <> $data['email']) {
                    $this->pl->save_session('error_message', $this->pl->trans($m, 'There is already an account with this email address'));
                } else {
                    if ($newpassword && crypt($oldpassword, $row[0]['password']) != $row[0]['password'] && !$this->pl->isPreviewUser($this->lAccount)) {
                        $this->pl->save_session('error_message', $this->pl->trans($m, 'Wrong Password!'));
                    } else {
                        if (($this->pl->isPreviewUser($this->lAccount)) && (!$newpassword)) {
                            $this->pl->save_session('error_message', $this->pl->trans($m, 'You need to set a password'));
                        } else {

                            if ($newpassword) {
                                $data['password']=crypt($newpassword, '$2a$07$9edf9384756gap1b49sj9xxx0ddkjsj7521038675sxgwjn38675sgwj34kdkaqop3946c38392021naqop3948484@8$');
                                $this->lo->_updateUserPassword($data);
                                unset($data['password']);
                            }

                            if ($currentEmail <> $data['email']) {
                                $GLOBALS['sess']['loginUser']['emailVerified']=0;
                                $data['emailVerified']=0;
                            } else {
                                $data['emailVerified']=$this->lUser['emailVerified'];
                            }

                            if($this->pl->isPreviewUser($this->lAccount)) {
                                //$this->lo->updateAccount(array('id'=>$this->lAccount['_id'], 'accountStatus'=>'FREE'));
                                $this->lo->updateFormsEmailAfterRegister(array('email'=>$data['email'], 'accountId'=>$this->lAccount['_id'], 'owner'=>$this->lUser['_id']));
                                $newuser="true";
                            }
                            $updated=$this->lo->_updateUsers($data);

                            if ($this->pl->canUser($this->lAccount, 'manage_account')) {
                                $companyUpdated=$this->lo->saveCompanyDetails($account);
                            } else {
                                $companyUpdated=true;
                            }

                            if ($updated && $companyUpdated) {
                                if ($currentEmail <> $data['email']) {

                                    $newkey=$this->pl->insertId().$this->pl->insertId().$this->pl->insertId();
                                    $user['id']=$this->lUser['_id'];
                                    $user['password_token']=$newkey;
                                    $result=$this->lo->settokenUsers($user);
                                    $data['email'] = trim(preg_replace('/\s+/','', $data['email']));
                                    $data['email'] = substr($data['email'],0,254);
                                    $this->pl->sendMail(array('body' => $this->emailSignup($user, 'change_email'), 'from' => 'hello@formlets.com', 'to' => $data['email'], 'subject' => 'Please validate your Email address'));
                                }
                                $this->SetUserSession($this->lUser['_id'], 'Updated account details');
                                $this->setAccountSession($this->lAccount['_id'], 'Updated company details');

                                $this->pl->save_session(array(
                                    'form_user_session'=>'',
                                    'success_message'=>$this->pl->trans($m, 'Account has been updated!')
                                ));
                                if($newuser=="true"){
                                    header('location: /settings/account/plan/');
                                    exit;
                                }
                            }

                        }
                    }
                }
            }
        }
    }

    function processAccountdeleted() {
        if ($this->lAccount["stripeCustomerId"]) {
            require_once('../libs/stripe-php-3.20.0/init.php');
            //keys will be set on config files
            \Stripe\Stripe::setApiKey($GLOBALS["conf"]["stripe_secret_key"]);
            $subscription=$this->lAccount["stripeSubscription"];
            if ($subscription) {
                $subscription_id=json_decode($subscription)->id;
                $subscription=\Stripe\Subscription::retrieve($subscription_id);
                $subscription->cancel();
            }
        }
        $deleted=$this->lo->_deleteusers(array('id' => $this->uid, 'backup' => true));
        //delete all forms
        $this->pl->delete_form_files(array('user_id' => $this->uid), $this->lo);
        $this->lo->deleteForm(array('user_id' => $this->uid));
        $this->pl->destroy_session();
        header('location: /');
        exit;
    }

    function processTeam() {
        $m="processteam";

        if($this->lUser['blocked']) {
            exit;
        }

        if ($this->urlpart[2] == 'create') {
            // if ($this->pl->isPreviewUser($this->lAccount)) {
            //     $GLOBALS['sess']['error_message']=$this->pl->trans($m, 'You are in preview mode. Please').' <a href="/settings/account/">'.$this->pl->trans($m, 'sign up for a prefessional account').'</a> '.$this->pl->trans($m, 'to create a team.');
            // } else {
            //     if ($GLOBALS['ref']['plan_lists'][$this->account['index']]['team'] == false) {
            //         $GLOBALS['sess']['error_message']=$this->pl->trans($m, 'Please upgrade to').' <a href="/settings/subscription/change/5/">'.$this->pl->trans($m, 'Professional').'</a> '.$this->pl->trans($m, 'account to create a team.');
            //     }
            // }
            // header('location: /team/');
            // exit;
        } else {
            if ($this->urlpart[2] == 'delete-member' && $this->urlpart[3]) {
                $this->pl->validate_csrfguard();
                $row=$this->lo->_getUsers(array('id' => $this->urlpart[3]));
                if (!count($row) || $row[0]['accountId'] <> $this->lAccount['_id']) {
                    $this->errorMessage=$this->pl->trans($m, 'Member not found');
                } else {
                    $deleted=$this->lo->_deleteusers(array('id' => $this->urlpart[3], 'backup' => true));
                    header('location: /team/');
                    exit;
                }
            } else {
                if ($this->urlpart[2] == 's') { //member settings
                    if ($_POST) {
                        if (isset($_POST['save_permission'])) {
                            if (!$this->pl->canUser($this->lAccount, 'manage_account')) {
                                $this->pl->save_session('error_message', $this->pl->trans($m, 'Permission not saved. You have no right to change permissions'));
                                header('location: /team/');
                                exit;
                            }
                            $forms=$_POST['form'];
                            $new_permission=array();
                            foreach ($forms as $form) {
                                $permissions=$_POST['permission'][$form];
                                $right=0;
                                foreach ($permissions as $permission) {
                                    $right += $permission;
                                }
                                $new_permission[$form]=$right;
                            }

                            if (count($new_permission)) {
                                $save_permission=json_encode($new_permission);
                            } else {
                                $save_permission=NULL;
                            }

                            $data['userId']=$this->urlpart[3];
                            $data['accountId']=$this->lAccount['_id'];
                            $data['permissions']=$save_permission;

                            $this->lo->updateUserAccount($data);

                            $this->pl->save_session('success_message', $this->pl->trans($m, 'Rights successfully saved'));
                            header('location: /team/');
                            exit;
                        }
                    }
                }
            }
        }

        if ($_POST) {
            if ($GLOBALS['ref']['plan_lists'][$this->account['index']]['team'] == false) {
                //exit;
            }
            if (isset($_POST['invite_team'])) {

                if($this->lUser['emailVerified'] == '0' || !$this->lUser['emailVerified']) {
                    $this->pl->save_session('error_message', $this->pl->trans($m, 'Please check your mailbox (or spam folder) to validate your account before you can invite users.'));
                    header('location: /team/');
                    exit;
                }



                //var_dump($_POST);exit;
                $accountid=$this->pl->Xssenc($_POST['accountid']);
                //$maxMembers=$this->pl->Xssenc($_POST['max_members']);
                $maxMembers = 9999999;
                $email=$this->pl->Xssenc($_POST['email']);

                if(strtolower((substr($email, -6))) == 'qq.com') {
                    header('location: /team/');
                    exit;
                } else {
                    //get the team members
                    $members=$this->lo->_getUsers(array('accountId' => $this->lAccount['_id']));
                    $members_count=count($members) - 1;

                    if (empty($email)) {
                        $this->pl->save_session('error_message', $this->pl->trans($m, 'Please enter email'));
                        header('location: /team/');
                        exit;
                    } elseif ($maxMembers <> 'UNLIMITED' && $members_count >= $maxMembers) {
                        $this->pl->save_session('error_message', $this->pl->trans($m, 'Your member limit is reached, please <a href="/settings/subscription/">upgrade</a> to invite additional users'));
                        header('location: /team/');
                        exit;
                    } else {
                        //check if email already exists
                        $row=$this->lo->_getUsers(array('email' => $email));
                        if (count($row)) {
                            $account = $this->lo->getAccounts(array(
                                'accountid'=>$accountid,
                                'userid'=>$row[0]['_id']
                            ));

                            if(count($account)) {
                                $this->pl->save_session('error_message', $this->pl->trans($m, 'Email already exists.'));
                            } else {

                                if(isset($_POST['read_access'])) {
                                    $forms = $this->lo->_listForms(array('uid'=>$this->lAccountOwner['_id']));
                                    $new_permission=array();
                                    foreach ($forms as $form) {
                                        $new_permission[$form['_id']]=1;
                                    }

                                    if (count($new_permission)) {
                                        $save_permission=json_encode($new_permission);
                                    } else {
                                        $save_permission=NULL;
                                    }
                                } else {
                                    $save_permission=NULL;
                                }
                                $user['permissions'] = $save_permission;

                                $saveRights = $this->lo->saveRights(array(
                                    'id' => $this->pl->insertId(),
                                    'accountId'=> $accountid,
                                    'userId'=>$row[0]['_id'],
                                    'accountRights'=>0,
                                    'blocked'=>1,
                                    'permissions'=>$user['permissions']
                                ));

                                $this->pl->sendMail(array('body' => $this->EmailMember($row[0], $this->lUser, $this->lAccount, true), 'from' => 'hello@formlets.com', 'to' => $email, 'subject' => $this->pl->trans($m, 'You have been invited to join').' '.$this->lUser['companyName'].' '.$this->pl->trans($m, 'team on Formlets')));
                            }
                        } else {
                            $user['id']=$this->pl->insertId();
                            $user['email']=$email;
                            $user['firstName']='';
                            $user['lastName']='';
                            $user['password']=crypt($user['id'], '$2a$07$9edf9384756gap1b49sj9xxx0ddkjsj7521038675sxgwjn38675sgwj34kdkaqop3946c38392021naqop3948484@8$');
                            $user['password_token']=$this->pl->insertId().$this->pl->insertId();
                            $user['location']='';
                            $user['ip']='';
                            $user['timezone']='';
                            $user['dateformat']='';
                            $user['accountId']=$accountid;
                            $user['accountRights']=isset($_POST['read_access']) ? 1:0;


                            if(isset($_POST['read_access'])) {
                                $forms = $this->lo->_listForms(array('uid'=>$this->lAccountOwner['_id']));
                                $new_permission=array();
                                foreach ($forms as $form) {
                                    $new_permission[$form['_id']]=1;
                                }

                                if (count($new_permission)) {
                                    $save_permission=json_encode($new_permission);
                                } else {
                                    $save_permission=NULL;
                                }
                            } else {
                                $save_permission=NULL;
                            }
                            $user['permissions'] = $save_permission;

                            $result=$this->lo->_saveUsers($user);
                            $user['email'] = trim(preg_replace('/\s+/','', $user['email']));
                            $this->pl->sendMail(array('body' => $this->EmailMember($user, $this->lUser, $this->lAccount), 'from' => 'hello@formlets.com', 'to' => $user['email'], 'subject' => $this->pl->trans($m, 'You have been invited to join').' '.$this->lUser['companyName'].' '.$this->pl->trans($m, 'team on Formlets')));

                            header('location: /team/');
                            exit;
                        }
                    }
                }
            } else if($_POST['member']) {
                //save general permission
                $permissions=$_POST['general_permission'];
                $accountRights=0;
                foreach($permissions as $permission) {
                    $accountRights +=$permission;
                }

                $data['userId']=$_POST['member'];
                $data['accountId']=$this->lAccount['_id'];
                $data['accountRights']=$accountRights;

                $this->lo->updateUserAccount($data);

                return 'success';
                exit;
            }
        }

    }

    function processSettings() {
        $this->page['title']=ucwords($this->urlpart[2]);
        $this->pl->csrfguard_start();
        if ($this->urlpart[2] == 'account') {
            $this->processAccount();
        }

        if ($this->urlpart[2] == 'subscription') {
            if ($this->pl->isPreviewUser($this->lAccount)) {
                header('location: /settings/account/');
                exit;
            }

            $this->processSubscription();
        }

    }

    //
    function sendMarketingEmail() {
        return $this->sendNextdayEmail();
    }
    //

    function sendNextdayEmail() {
        $m="sendnextdayemail";
        $data=$this->lo->getYesterdayNewUsers();

        for ($d=0; $d < count($data); $d++) {
            if (($data[$d]['email']) && (!$data[$d]['emailVerified'])) {
                $emailsent .= " ".$data[$d]['email']."\r\n";
                $user=$data[$d]['email'];
                $ip=$data[$d]['ip'];
                $body=$this->OutputMailNewNovalidation();
                $this->pl->sendMail(array('ip'=>$ip,'body' => $body, 'from' => 'filip@formlets.com', 'to' => $user, 'subject' => $this->pl->trans($m, 'About the Formlets account you registered yesterday')));

            }
        }

        if ($emailsent) {
            return $this->pl->trans($m, 'Email to be sent to')." \r\n".$emailsent;
        } else {
            return $this->pl->trans($m, 'no mail sent for non validated accounts');
        }
    }

    function sendWelcomeEmail() {
        $m="sendwelcomeemail";
        $data=$this->lo->getNewUsersforwelcome();
        for ($d=0; $d < count($data); $d++) {
            if ($data[$d]['email'] && filter_var(trim($data[$d]['email']), FILTER_VALIDATE_EMAIL)) {
                $emailsent .= " ".$data[$d]['email']."\r\n";
                //$user="filip@cbel.com";
                $user=$data[$d]['email'];
                $ip=$data[$d]['ip'];
                $body=$this->OutputWelcomeEmail();
                $user = trim(preg_replace('/\s+/','', $user));
                $this->pl->sendMail(array('ip'=>$ip,'body' => $body, 'from' => 'filip@formlets.com', 'to' => $user, 'subject' => $this->pl->trans($m, 'Can we help you with your new Formlets account?')));
            }
        }
        if ($emailsent) {
            return $this->pl->trans($m, 'Email to be sent to')." \r\n".$emailsent;
        }
    }

    function isJson($string) {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    private function _isVarFoundEmailTemplates($form, $template, $data) {
        if (preg_match_all("/{(.*?)}/", $template, $m)) {
            foreach ($m[1] as $i => $varname) {
                $temp=$m[0][$i];
                if($temp == '{SUBMISSION::ID}' || $temp == '{SUBMISSION::SUBJECT}' || $temp == '{SUBMISSION::DATE}') {continue;}
                $m[0][$i]=str_replace('&amp;', '&', $m[0][$i]);
                $found=false;
                foreach ($data as $field) {
                    $fId = $field['_id'] ? $field['_id']: $field['field'];
                    $form_field=$this->pl->getElement($form['elements'], $fId);
                    //var_dump($form_field);exit;
                    if ($form_field && $form_field['type'] == 'NAME') {
                        $values=explode(', ', $field['value']);
                        $count=count($values);
                        if ($count > 3) {
                            $title=trim($values[0]);
                            $firstname=trim($values[1]);
                            $middlename=trim($values[2]);
                            $lastname=trim($values[3]);
                        } else {
                            if ($count > 2) {
                                if ($field['nameTitle']) {
                                    $title=trim($values[0]);
                                    $firstname=trim($values[1]);
                                    $lastname=trim($values[2]);
                                } else {
                                    $firstname=trim($values[0]);
                                    $middlename=trim($values[1]);
                                    $lastname=trim($values[2]);
                                }
                            } else {
                                $firstname=trim($values[0]);
                                $lastname=trim($values[1]);
                            }
                        }
                        $label=$form_field['queryName'] ?: $form_field['inputLabel'];
                        if ($label && strpos(strtolower($m[0][$i]), strtolower($label)) != false) {
                            $check_fields=array('title', 'firstname', 'middlename', 'lastname');
                            foreach ($check_fields as $cf) {
                                if (strpos(strtolower($m[0][$i]), $cf) != false) {
                                    $template=str_replace($temp, ${$cf}, $template);
                                    break;
                                }
                            }
                            $found=true;
                        }
                    } else if ($form_field && $form_field['type'] == 'US_ADDRESS') {
                        $values=explode(', ', $field['value']);
                        $count=count($values);
                        $address1=$values[0];
                        $address2=$values[1];
                        $city=$values[2];
                        $state=$values[3];
                        $zip=$values[4];
                        if ($count > 5) {
                            $country=$values[5];
                        }
                        $label=$form_field['queryName'] ?: $form_field['inputLabel'];
                        if ($label && strpos(strtolower($m[0][$i]), strtolower($label)) != false) {
                            $check_fields=array('address1', 'address2', 'city', 'state', 'zip', 'country');
                            foreach ($check_fields as $cf) {
                                if (strpos(strtolower($m[0][$i]), $cf) != false) {
                                    $template=str_replace($temp, ${$cf}, $template);
                                    break;
                                }
                            }
                            $found=true;
                        }
                    } else if($form_field && !empty($form_field['columns']) && count($form_field['columns']) > 2) {
                        $link = $this->lo->getDatasourcelink(array('formId'=>$form['_id'], 'elementId'=>$form_field['_id']));
                        if(count($link)) {
                            $datasource = $this->lo->getDatasource(array('id'=>$link[0]['datasourceId']));
                            $datasource = $datasource[0];
                            $columns = json_decode($datasource['columns'], true);
                            //var_dump($columns);exit;
                            $datasourceData = json_decode($datasource['data'], true);
                            $key=0;
                            for($x=2;$x<count($columns);$x++) {
                                if (strpos(strtolower($m[0][$i]), strtolower($columns[$x])) != false) {
                                    $key = $x+1;
                                    break;
                                }
                            }

                            if($key) {
                                foreach($datasourceData as $d) {
                                    if($form_field['lookupColumn'] && $field['value'] == $d[$form_field['lookupColumn']]) {
                                        $valReplace = $d['column_'.$key];
                                        $template=str_replace($temp, $valReplace, $template);
                                        break;
                                    } else if($d['value'] == $field['value']) {
                                        $valReplace = $d['column_'.$key];
                                        $template=str_replace($temp, $valReplace, $template);
                                        break;
                                    }
                                }

                                $found=true;
                            }
                        }
                    }

                    $ffield=$this->pl->slugify('{'.$field['label'].'}');
                    if ($form_field['queryName']) {
                        $ffield=$this->pl->slugify('{'.$form_field['queryName'].'}');
                    }
                    if ($ffield == $this->pl->slugify($m[0][$i])) {
                        if ($form_field && $form_field['type'] == 'SIGNATURE') {
                            $template=str_replace($temp, '<img src="'.$field['value'].'" />', $template);
                        } else {
                            if ($form_field && $form_field['type'] == 'FILE') {
                                $parts = explode(';;', $field['value']);
                                if(count($parts) > 1) {
                                    $org_names = explode(';;', $field['org_name']);
                                    $ctr=0;
                                    $v='<ul>';
                                    foreach($parts as $file) {
                                        $v.='<li><a href="'.$GLOBALS['protocol'].'://'.$_SERVER['HTTP_HOST'].'/file/'.$file.'/?f='.$org_names[$ctr].'" target="_blank">'.htmlentities($org_names[$ctr]).'</a></li>';
                                        $ctr++;
                                    }
                                    $v.='</ul>';
                                } else {
                                    $v='<a href="'.$GLOBALS['protocol'].'://'.$_SERVER['HTTP_HOST'].'/file/'.$field['value'].'/?f='.$field['org_name'].'" target="_blank">'.htmlentities($field['org_name']).'</a>';
                                }

                                $template=str_replace($temp, $v, $template);
                                if ($form_field['sendAsAttachment']) {
                                    $attachments[]=$GLOBALS['conf']['filepath_fileupload'].'/'.$field['value'];
                                }
                            } else {
                                $template=str_replace($temp, $field['value'], $template);
                            }
                        }

                        $found=true;
                    }
                }

                if ($found == false) {
                    $template=str_replace($temp, '', $template);
                }
            }
        }

        return $template;
    }

    private function _sendEmailTemplate($form, $templates, $data, $submission_id) {
        if (!is_array($data)) {
            $data=array();
        }

        foreach ($templates as $template) {
            $template['template']=str_replace(array(
                '&lt;table&gt;',
                '&lt;tr&gt;',
                '&lt;td&gt;',
                '&lt;/table&gt;',
                '&lt;/tr&gt;',
                '&lt;/td&gt;',
            ), array(
                '<table>',
                '<tr>',
                '<td>',
                '</table>',
                '</tr>',
                '</td>',
            ), $template['template']);

            //var_dump($template);


            //check conditions
            $continue = true;
            if($template['conditionAction'] && $template['conditionField'] && $template['conditionOperand'] && $template['conditionValue']) {
                $conditionField = '{'.$template['conditionField'].'}';
                if (preg_match_all("/{(.*?)}/", $conditionField, $m)) {
                    foreach ($m[1] as $i => $varname) {
                        $temp=$m[0][$i];
                        if($temp == '{SUBMISSION::ID}' || $temp == '{SUBMISSION::SUBJECT}' || $temp == '{SUBMISSION::DATE}') {continue;}
                        $m[0][$i]=str_replace('&amp;', '&', $m[0][$i]);
                        $found=false;
                        foreach ($data as $field) {
                            $form_field=$this->pl->getElement($form['elements'], $field['_id']);
                            if ($form_field && $form_field['type'] == 'NAME') {
                                $values=explode(', ', $field['value']);
                                $count=count($values);
                                if ($count > 3) {
                                    $title=trim($values[0]);
                                    $firstname=trim($values[1]);
                                    $middlename=trim($values[2]);
                                    $lastname=trim($values[3]);
                                } else {
                                    if ($count > 2) {
                                        if ($field['nameTitle']) {
                                            $title=trim($values[0]);
                                            $firstname=trim($values[1]);
                                            $lastname=trim($values[2]);
                                        } else {
                                            $firstname=trim($values[0]);
                                            $middlename=trim($values[1]);
                                            $lastname=trim($values[2]);
                                        }
                                    } else {
                                        $firstname=trim($values[0]);
                                        $lastname=trim($values[1]);
                                    }
                                }
                                $label=$form_field['queryName'] ?: $form_field['inputLabel'];
                                if ($label && strpos(strtolower($m[0][$i]), strtolower($label)) != false) {
                                    $check_fields=array('title', 'firstname', 'middlename', 'lastname');
                                    foreach ($check_fields as $cf) {
                                        if (strpos(strtolower($m[0][$i]), $cf) != false) {
                                            $conditionVal = ${$cf};
                                            break;
                                        }
                                    }
                                    $found=true;
                                }
                            } else if ($form_field && $form_field['type'] == 'US_ADDRESS') {
                                $values=explode(', ', $field['value']);
                                $count=count($values);
                                $address1=$values[0];
                                $address2=$values[1];
                                $city=$values[2];
                                $state=$values[3];
                                $zip=$values[4];
                                if ($count > 5) {
                                    $country=$values[5];
                                }
                                $label=$form_field['queryName'] ?: $form_field['inputLabel'];
                                if ($label && strpos(strtolower($m[0][$i]), strtolower($label)) != false) {
                                    $check_fields=array('address1', 'address2', 'city', 'state', 'zip', 'country');
                                    foreach ($check_fields as $cf) {
                                        if (strpos(strtolower($m[0][$i]), $cf) != false) {
                                            $conditionVal = ${$cf};
                                            break;
                                        }
                                    }
                                    $found=true;
                                }
                            }

                            $ffield=$this->pl->slugify('{'.$field['label'].'}');
                            if ($form_field['queryName']) {
                                $ffield=$this->pl->slugify('{'.$form_field['queryName'].'}');
                            }
                            if ($ffield == $this->pl->slugify($m[0][$i])) {
                                $conditionVal = $field['value'];
                                $found=true;
                            }
                        }

                        if($found) {
                            $conditionPassed = false;

                            switch ($template['conditionOperand']) {
                                case '=':
                                    $conditionPassed = $conditionVal == $template['conditionValue'];
                                    break;
                                case '>':
                                    $conditionPassed = $conditionVal > $template['conditionValue'];
                                    break;
                                case '<':
                                    $conditionPassed = $conditionVal < $template['conditionValue'];
                                    break;
                                case '!=':
                                    $conditionPassed = $conditionVal <> $template['conditionValue'];
                                    break;
                                case '>=':
                                    $conditionPassed = $conditionVal >= $template['conditionValue'];
                                    break;
                                case '<=':
                                    $conditionPassed = $conditionVal <= $template['conditionValue'];
                                    break;
                            }

                            if($template['conditionAction'] == 'send' && !$conditionPassed) {
                                $continue = false;
                                break;
                            } else if($template['conditionAction'] == 'dontsend' && $conditionPassed) {
                                $continue = false;
                                break;
                            }
                        }
                    }
                }
            }

            if(!$continue) {
                return false;
            }

            $attachments=array();

            $template['template'] = $this->_isVarFoundEmailTemplates($form, $template['template'], $data);
            $template['subject'] = $this->_isVarFoundEmailTemplates($form, $template['subject'], $data);
            $template['email_to'] = $this->_isVarFoundEmailTemplates($form, $template['email_to'], $data);
            $template['email_cc'] = $this->_isVarFoundEmailTemplates($form, $template['email_cc'], $data);
            $template['email_bcc'] = $this->_isVarFoundEmailTemplates($form, $template['email_bcc'], $data);
            $template['email_reply_to'] = $this->_isVarFoundEmailTemplates($form, $template['email_reply_to'], $data);

            //echo json_encode($template);exit;

            foreach ($data as $field) {
                $form_field=$this->pl->getElement($form['elements'], $field['field']);
                if ($form_field['queryName']) {
                    $field['label']=$form_field['queryName'];
                }
                $template['template']=str_replace('{SUBMISSION::ID}', $submission_id, $template['template']);
                $template['email_from']=str_replace('{'.$field['label'].'}', $field['value'], $template['email_from']);
                $template['email_reply_to']=str_replace('{'.$field['label'].'}', $field['value'], $template['email_reply_to']);
                $template['email_to']=str_replace('{'.$field['label'].'}', $field['value'], $template['email_to']);
                $template['email_cc']=str_replace('{'.$field['label'].'}', $field['value'], $template['email_cc']);
                $template['email_bcc']=str_replace('{'.$field['label'].'}', $field['value'], $template['email_bcc']);
                $template['subject']=str_replace('{'.$field['label'].'}', $field['value'], $template['subject']);
                $template['subject']=str_replace('{SUBMISSION::ID}', $submission_id, $template['subject']);
                $template['template']=str_replace('{SUBMISSION::SUBJECT}', $template['subject'], $template['template']);
                $template['template']=str_replace('{SUBMISSION::DATE}', date('m/d/Y, h:i a'), $template['template']);
            }

            //echo json_encode($template);exit;

            $email_from=json_decode($template['email_from']);
            $email_reply_to=json_decode($template['email_reply_to']);
            $recipients=json_decode($template['email_to']);
            $cc=json_decode($template['email_cc']);
            $bcc=json_decode($template['email_bcc']);

            $froms=array();
            $replyTos=array();
            $tos=array();
            $ccs=array();
            $bccs=array();

            foreach ($recipients as $recipient) {
                $recipient=trim($recipient);
                if (filter_var($recipient, FILTER_VALIDATE_EMAIL) == true) {
                    $tos[]=$recipient;
                }
            }

            if($email_from) {
                foreach ($email_from as $e) {
                    $e=trim($e);
                    if (filter_var($e, FILTER_VALIDATE_EMAIL) == true) {
                        $froms[]=$e;
                    }
                }
            }

            if($email_reply_to) {
                foreach ($email_reply_to as $e) {
                    $e=trim($e);
                    if (filter_var($e, FILTER_VALIDATE_EMAIL) == true) {
                        $replyTos[]=$e;
                    }
                }
            }

            if ($cc) {
                foreach ($cc as $c) {
                    $c=trim($c);
                    if (filter_var($c, FILTER_VALIDATE_EMAIL) == true) {
                        $ccs[]=$c;
                    }
                }
            }

            if ($bcc) {
                foreach ($bcc as $bc) {
                    $bc=trim($bc);
                    if (filter_var($bc, FILTER_VALIDATE_EMAIL) == true) {
                        $bccs[]=$bc;
                    }
                }
            }

            if(count($tos)) {
                $from=$form['emailFrom'] ?: 'hello@formlets.com';
                if(count($froms)) {
                    $from=$froms[0];
                }

                $replyTo=filter_var($form['emailReply'], FILTER_VALIDATE_EMAIL) == true ? $form['emailReply'] : 'hello@formlets.com';

                if(count($replyTos)) {
                    $replyTo=$replyTos[0];
                }

                $mailTemplate=array('body' => $template['template'], 'from' => $from, 'to' => $tos, 'replyTo' => $replyTo, 'cc' => $ccs, 'bcc' => $bccs, 'subject' => $template['subject']);
                $mailTemplate['attachments']=$attachments;

                $messageId = $this->pl->sendMail($mailTemplate);
            }
        }
    }

    private function formPaymentSuccess($sid, $pfid, $status, $redirect=true, $paypal=false) {

        if($paypal == true) {
            if($redirect) {
                header('Location: /forms/'.$this->urlpart[2].'/?payment=ok&paypal=true&stoken='.$sid);
            }
        } else {
            $submission=$this->lo->getSubmissions(array('id'=>$sid));
            $submission[0]['data']=str_replace('\\r\\n', '<br>', $submission[0]['data']);
            $submission[0]['data']=json_decode(str_replace('\\','',$submission[0]['data']), true);
            if(!$submission[0]['data']) {
                $submission[0]['data']=json_decode($submission[0]['data'], true);
            }

            $newSub=array();
            foreach($submission[0]['data'] as $key => $s) {
                $newSub[$key]=$s;
                if($s['_id'] == $pfid) {
                    $newSub[$key]['value']=$status;
                    break;
                }
            }

            if(count($newSub)) {
                $submission[0]['data']=$newSub;
                $newSubmittedData=json_encode($submission[0]['data'], JSON_UNESCAPED_UNICODE);
                $update=$this->lo->updateSubmission(array('id'=>$sid,'data'=>$newSubmittedData));

                if($redirect) {
                    header('Location: /forms/'.$this->urlpart[2].'/?payment=ok&stoken='.$sid);
                }
            }
        }
        
    }

    // these are the outside forms
    function processForms() {
        $m="processforms";
        $this->form=$this->lo->getForm(array("form_id"=>$this->urlpart[2]));

        // so we have the form structure , lets put it in vars and use it later
    /*
        $this->name     =$this->form['name'];
        $this->owner    =$this->form['owner'];
        $this->accountId=$this->form['accountId'];
        $this->elements =$this->form['elements'];
      */

        if(!$this->form['error']) {
          // put here why you need this user
            $this->user=$this->lo->_getUsers(array('id'=>$this->form['owner']));

            if(!count($this->user)) {
                $this->user = $this->lo->_getUsers(array('accountId'=>$this->form['owner']));
            }

            $form_owner = $this->user[0];

            $current_plan = $form_owner['accountStatus'];
            if($current_plan == 'PREVIEW') {
                $current_plan = 'FREE';
            }
            $account=$this->pl->planDetails($current_plan);
            $maxViews=$GLOBALS['ref']['plan_lists'][$account['index']]['maxViews'];

            $dateformat = $this->pl->getUserDateFormat($form_owner);
            $timeformat = $this->pl->getUserTimeFormat($form_owner);

            if($this->pl->isFreeAccount($form_owner) == false || $this->form['active']<>'1') {
                $this->usePassword = $this->form['usePassword'];
                $this->passwordLabel=$this->form["passwordLabel"];
                $this->passwordButtonLabel=$this->form["passwordButtonLabel"];
                $this->invalidPassword=$this->form["invalidPassword"];

                if(isset($_COOKIE['formlets_password']) && $this->pl->decrypt($_COOKIE['formlets_password']) == $this->form['password']) {
                    $this->usePassword = false;
                }
            } else {
                $this->usePassword = false;
            }
            

            if($maxViews!='UNLIMITED' && $this->form['active']=='1') {
                $usage = $this->lo->listFormUsage(array(
                    'yearMonth'=>date('Ym'),
                    'accountId'=>$form_owner['accountId'],
                    'uid'=>$form_owner['_id'],
                    'formId'=>$this->form['_id']
                ));
                if($usage[0]['pageViewCount'] >= $maxViews) {
                    $this->OutputUsageLimit();exit;
                }


                //send warning if necessary
                $percentages = $GLOBALS['ref']['views_warning_percentage'];
                foreach($percentages as $percentage) {
                    $viewsToCheck = ($percentage / 100) * $maxViews;
                    if($viewsToCheck == ($usage[0]['pageViewCount']+1)) {
                        $mailTemplate=array('body' => $this->OutputEmailTemplateViewLimit($this->form, $form_owner, $percentage), 'from' => 'hello@formlets.com', 'to' => $form_owner['email'], 'subject' => 'Warning: Form '.$this->form['name'].' reached '.$percentage.'% of its view limit');
                        $this->pl->sendMail($mailTemplate);
                        break;
                    }
                }
            }
        }
        if($this->form['type']<>"ENDPOINT"){
            if($this->form['enableCSRF']) {
                $this->pl->csrfguard_start();
            }
        }
        if(!$_POST && $this->form) {
            if($this->form['active']=='1' && !isset($_GET['preview']) && !isset($this->form['error']) && !$this->pl->isAdminOfForm($this->form, $this->lUser, $this->lAccount)) {
                $this->lo->incrementForm(array('form_id' => $this->urlpart[2],'account_id' => $this->form['accountId']));
            }
        }
        // we have a payment to process
        if($_GET['payment'] || $_GET['stripe']) {
            if($_GET['stripe']) {

                require_once('../libs/stripe-php-3.20.0/init.php');

                $element = $this->pl->getElement($this->form['elements'], $_REQUEST['el_id']);

                \Stripe\Stripe::setApiKey($element['secret_key']);

                if($_GET['stripe'] == 'custom') {
                    if(in_array($_GET['type'], ['ideal','alipay','ach_credit_transfer','bancontact', 'eps','giropay'])) {
                        $amount=$_GET['amount'];
                        $source=$_GET['source'];
                        try {

                            $currency = $this->form['currency'];
                            if(!$currency) { $currency='USD'; }

                            $charge = \Stripe\Charge::create(array(
                                 "amount" => $amount*100,
                                 "currency" => $currency,
                                 "source" => $source,
                            ));

                            $result = $charge->__toArray(true);

                            if($result['paid']) {
                                header("Location: /forms/".$this->form['_id']."/?payment=success&submission_id=".$_GET['submission_id']."&paid_field_id=".$_GET['paid_field_id']);exit;
                            } else {
                                header("Location: /forms/".$this->form['_id']."/?payment=cancel");exit;
                            }
                        } catch(\Stripe\Error\InvalidRequest $e) {
                            $this->_stripeErrorHandler($e);
                            header("Location: /forms/".$this->form['_id']."/?payment=cancel");exit;
                        }
                    }
                } else {
                    if($_POST) {
                        header("Content-Type: application/json", true);
                        try {
                            $token=$_POST['token'];

                            if($_POST['captureCard']) {
                                $customer = \Stripe\Customer::create(array(
                                    "source" => $token
                                ));
                            } else {
                                $amount=$_GET['amount'];

                                $currency = $this->form['currency'];
                                if(!$currency) { $currency='USD'; }

                                // Charge the user's card:
                                $charge=\Stripe\Charge::create(array(
                                  "amount" => $amount*100,
                                  "currency" => $currency,
                                  "source" => $token,
                                ));
                            }

                            echo json_encode(array('error'=>false));exit;
                        } catch(\Stripe\Error\Card $e) {
                            // Since it's a decline, \Stripe\Error\Card will be caught
                            echo json_encode(array('error_message'=>$this->_stripeErrorHandler($e, true), 'error'=>true));exit;
                        } catch (\Stripe\Error\RateLimit $e) {
                            // Too many requests made to the API too quickly
                            echo json_encode(array('error_message'=>$this->_stripeErrorHandler($e, true), 'error'=>true));exit;
                        } catch (\Stripe\Error\InvalidRequest $e) {
                            // Invalid parameters were supplied to Stripe's API
                            echo json_encode(array('error_message'=>$this->_stripeErrorHandler($e, true), 'error'=>true));exit;
                        } catch (\Stripe\Error\Authentication $e) {
                            // Authentication with Stripe's API failed
                            echo json_encode(array('error_message'=>$this->_stripeErrorHandler($e, true), 'error'=>true));exit;
                            // (maybe you changed API keys recently)
                        } catch (\Stripe\Error\ApiConnection $e) {
                            // Network communication with Stripe failed
                            echo json_encode(array('error_message'=>$this->_stripeErrorHandler($e, true), 'error'=>true));exit;
                        } catch (\Stripe\Error\Base $e) {
                            // Display a very generic error to the user, and maybe send
                            echo json_encode(array('error_message'=>$this->_stripeErrorHandler($e, true), 'error'=>true));exit;
                        } catch (Exception $e) {
                            // Something else happened, completely unrelated to Stripe
                            echo json_encode(array('error_message'=>'Something went wrong, please contact us', 'error'=>true));exit;
                        }
                    }
                }
            } else {
                if($_GET['payment']=='success') {
                    $paypal = isset($_GET['paypal']) ? true:false;
                    $this->formPaymentSuccess($_GET['submission_id'], $_GET['paid_field_id'], 'Completed', true, $paypal);
                } else if($_GET['payment']=='ok') {

                    if(isset($_GET['paypal']) && $_GET['paypal'] == 'true') {
                        //do nothing since it is already sending at IPN  /notify
                    } else {
                        $templates=$this->lo->getFormEmailTemplate(array('form_id'=>$this->form['_id']));
                        $submission=$this->lo->getSubmissions(array('id'=>$_GET['stoken']));
                        if(count($submission)) {
                            $submission[0]['data']=str_replace('\\r\\n', '<br>', $submission[0]['data']);
                            $submission[0]['data']=json_decode(str_replace('\\','',$submission[0]['data']), true);
                            if(!$submission[0]['data']) {
                                $submission[0]['data']=json_decode($submission[0]['data'], true);
                            }

                            if(count($templates) && $this->form['notifyUseTemplate'] && $this->pl->isFreeAccount($this->user[0]) == false) {
                                $this->_sendEmailTemplate($this->form, $templates, $submission[0]['data'], $submission[0]['_id']);
                            }
                        }
                    }
                    

                    if($this->form['redirectUrl'] && $this->form['doRedirect']=='1' && $_GET['iframe']=='true') {
                    ?>
                    <script type="text/javascript">
                        if (typeof window != "undefined") {
                            window.top.location='<?php echo $this->form['redirectUrl'];?>';
                        } else {
                            location.href='<?php echo $this->form['redirectUrl'];?>';
                        }

                    </script>
                    <?php
                    } else if($this->form['redirectUrl'] && $this->form['doRedirect']=='1') {
                          header('location:'.$this->form['redirectUrl']);
                          exit;
                    }
                } else if($_GET['payment']=='notify') {
                    // STEP 1: read POST data
                    // Reading POSTed data directly from $_POST causes serialization issues with array data in the POST.
                    // Instead, read raw POST data from the input stream.
                    $raw_post_data = file_get_contents('php://input');
                    $raw_post_array = explode('&', $raw_post_data);
                    $myPost = array();
                    foreach ($raw_post_array as $keyval) {
                        $keyval = explode ('=', $keyval);
                        if (count($keyval) == 2)
                            $myPost[$keyval[0]] = urldecode($keyval[1]);
                    }
                    // read the IPN message sent from PayPal and prepend 'cmd=_notify-validate'
                    $req = 'cmd=_notify-validate';
                    if (function_exists('get_magic_quotes_gpc')) {
                        $get_magic_quotes_exists = true;
                    }
                    foreach ($myPost as $key => $value) {
                        if ($get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1) {
                            $value = urlencode(stripslashes($value));
                        } else {
                            $value = urlencode($value);
                        }
                        $req .= "&$key=$value";
                    }

                    // Step 2: POST IPN data back to PayPal to validate
                    if(isset($GLOBALS['conf']['paypal_sandbox_mode']) && $GLOBALS['conf']['paypal_sandbox_mode'] == true) {
                        $ch = curl_init('https://ipnpb.sandbox.paypal.com/cgi-bin/webscr');
                    } else {
                        $ch = curl_init('https://ipnpb.paypal.com/cgi-bin/webscr');
                    }
                    
                    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
                    curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));
                    // In wamp-like environments that do not come bundled with root authority certificates,
                    // please download 'cacert.pem' from "https://curl.haxx.se/docs/caextract.html" and set
                    // the directory path of the certificate as shown below:
                    // curl_setopt($ch, CURLOPT_CAINFO, dirname(__FILE__) . '/cacert.pem');
                    if ( !($res = curl_exec($ch)) ) {
                        // error_log("Got " . curl_error($ch) . " when processing IPN data");
                        curl_close($ch);
                        exit;
                    }
                    curl_close($ch);

                    if (strcmp ($res, "VERIFIED") == 0) {
                      // The IPN is verified, process it
                        $payment_status = $_POST['payment_status'];
                        $this->formPaymentSuccess($_GET['submission_id'], $_GET['paid_field_id'], $payment_status, false);

                        if($payment_status == "Completed") {
                            $templates=$this->lo->getFormEmailTemplate(array('form_id'=>$this->form['_id']));
                            $submission=$this->lo->getSubmissions(array('id'=>$_GET['submission_id']));
                            if(count($submission)) {
                                $submission[0]['data']=str_replace('\\r\\n', '<br>', $submission[0]['data']);
                                $submission[0]['data']=json_decode(str_replace('\\','',$submission[0]['data']), true);
                                if(!$submission[0]['data']) {
                                    $submission[0]['data']=json_decode($submission[0]['data'], true);
                                }

                                if(count($templates) && $this->form['notifyUseTemplate'] && $this->pl->isFreeAccount($this->user[0]) == false) {
                                    $this->_sendEmailTemplate($this->form, $templates, $submission[0]['data'], $submission[0]['_id']);
                                }
                            }
                        }

                      //$this->pl->sendMail(array('body'=>$this->pl->Xssenc(print_r($_POST,1)),'from'=>'hello@formlets.com','to'=>'mamalias23@gmail.com','subject'=>'Paypal Notification 2'));
                    } else if (strcmp ($res, "INVALID") == 0) {
                      // IPN invalid, log for manual investigation
                    }

                    exit;
                }

                $this->display=$this->pl->trans($m,'success');
            }
        }
// we don't have a payment

        if(($_POST || $_FILES)&&($this->form)){
             // loop form elements
             //
            if($this->form['type']=="ENDPOINT"){
                $f=0;
                foreach ($_POST as $label => $value) {
                 $data[$f]['label']=$label;
                 $data[$f]['value']=$value;
                     $f++;
                }

            } else {


                if(isset($_POST['type']) && $_POST['type'] == 'password') {
                    if($_POST['password'] == $this->form['password']) {
                        //save to cookie
                        $cookie_value = $this->pl->encrypt($this->form['password']);
                        $key = 'formlets_password';
                        setcookie($key, $cookie_value, time() + (86400 * 30), "/"); //30 days
                        header("Location: /forms/".$this->urlpart[2]."/");exit;
                    } else {
                        header("Location: /forms/".$this->urlpart[2]."/?invalid=pw");exit;
                    }
                }


                $hasInput=false;
                $formEmpty=false;

                $captchaError=false;
                if(isset($_POST['g-recaptcha-response'])) {
                    if(empty($_POST['g-recaptcha-response'])) {
                        $captchaError=true;
                    } else {
                        $secret=$GLOBALS['conf']['google_captcha_secret_key'];
                        $verifyResponse=file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret='.$secret.'&response='.$_POST['g-recaptcha-response']);
                        $responseData=json_decode($verifyResponse);
                        if($responseData->success) {
                            //
                        } else {
                            $captchaError=true;
                        }
                    }
                }

                if($captchaError == false) {
                    foreach ($_POST as $value) {
                        if(is_array($value)) {
                            foreach($value as $val) {
                                if($val) {
                                    $hasInput=true;
                                    break;
                                }
                            }
                        } else if($value) {
                            $hasInput=true;
                            break;
                        }
                    }

                    foreach($_FILES as $file) {
                        if($file['size'] > 0) {
                            $hasInput=true;
                            break;
                        }
                    }

                    $d=0;
                    $external=array();
                    if($this->pl->isFreeAccount($form_owner) == false && $this->form['externalData']) {
                        $allowedGets=explode(',', $this->form['externalData']);
                        foreach($allowedGets as $getR) {
                            $key=trim($getR);
                            $key=str_replace(" ", "_", $key);
                            $value=$_GET[$key];
                            if(!empty($value)) {
                                $label=str_replace("_", " ", $key);
                                $external[$d][$label]=$this->pl->Xssenc($value);
                                $data[$d]['label']=$label;
                                $data[$d]['value']=$this->pl->Xssenc($value);
                                $data[$d]['_id']=$this->pl->insertId();
                                $d++;

                                $hasInput=true;
                            }
                        }
                    }

                    if(!$hasInput) {
                        $this->display=$this->pl->trans($m,'success');
                        $formEmpty=true;
                    }

                    $tobeuploaded=array();
                    usort($this->form['elements'], function($a, $b) {
                        if($a['order'] == $b['order']) {
                            return 0;
                        }

                        return $a['order'] < $b['order'] ? -1:1;
                    });

                    $payment=false;
                    $payment_element=null;
                    $amount_to_pay=0;
                    $GLOBALS['sess']['amount_to_pay']=0;
                    $captureCard=0;
                    $products=array();
                    for($e=0;$e<count($this->form['elements']);$e++){

                        if($this->form['elements'][$e]['type'] == 'PAYPAL' || $this->form['elements'][$e]['type'] == 'STRIPE' || $this->form['elements'][$e]['type'] == 'STRIPEPAYPAL') {
                            $payment=true;
                            $payment_element=$this->form['elements'][$e];
                            $GLOBALS['sess']['payment_element']=$payment_element;

                            if($payment_element['type'] == 'STRIPEPAYPAL' || ($payment_element['type'] == 'STRIPE' && isset($_POST['payment_type']))) {
                                $type=$_POST['payment_type'];
                                $payment_element['type']=$type;
                            }
                        }

                        if(($_POST["addr_2_".$this->form['elements'][$e]['_id']])||($_POST["addr_1_".$this->form['elements'][$e]['_id']])||($_POST["country_".$this->form['elements'][$e]['_id']])||($_POST["city_".$this->form['elements'][$e]['_id']])||($_POST["state_".$this->form['elements'][$e]['_id']])||($_POST["zip_".$this->form['elements'][$e]['_id']])){
                            if($_POST["country_".$this->form['elements'][$e]['_id']]) {
                                $_POST[$this->form['elements'][$e]['_id']]=array(
                                    $_POST["addr_1_".$this->form['elements'][$e]['_id']],
                                    $_POST["addr_2_".$this->form['elements'][$e]['_id']],
                                    $_POST["city_".$this->form['elements'][$e]['_id']],
                                    $_POST["state_".$this->form['elements'][$e]['_id']],
                                    $_POST["zip_".$this->form['elements'][$e]['_id']],
                                    $_POST["country_".$this->form['elements'][$e]['_id']],
                                );
                            } else {
                                $_POST[$this->form['elements'][$e]['_id']]=array(
                                    $_POST["addr_1_".$this->form['elements'][$e]['_id']],
                                    $_POST["addr_2_".$this->form['elements'][$e]['_id']],
                                    $_POST["city_".$this->form['elements'][$e]['_id']],
                                    $_POST["state_".$this->form['elements'][$e]['_id']],
                                    $_POST["zip_".$this->form['elements'][$e]['_id']]
                                );
                            }

                        }


                        if(!isset($_POST[$this->form['elements'][$e]['_id']]) && !isset($_FILES[$this->form['elements'][$e]['_id']])) {
                            continue;
                        }

                        if($_POST[$this->form['elements'][$e]['_id']] || isset($_FILES[$this->form['elements'][$e]['_id']])) {
                            $externalDataExists=$this->pl->data_exists($external, $this->form['elements'][$e]['inputLabel']);
                            if(is_integer($externalDataExists)) {
                                $data[$externalDataExists]['value']=$_POST[$this->form['elements'][$e]['_id']];
                            } else {
                                $data[$d]['field']=$this->form['elements'][$e]['name'];
                                if($_POST[$this->form['elements'][$e]['_id']] && $this->form['elements'][$e]['type'] == 'PRODUCTS') {
                                    $inputs=$_POST[$this->form['elements'][$e]['_id']];
                                    $values=array();
                                    foreach($inputs as $key => $input) {
                                        $val=explode('//', $input);
                                        $qty=$_POST['qty']["'".$this->form['elements'][$e]['_id']."'"][$val[0]];
                                        $price=$val[2] ?: $_POST['price']["'".$this->form['elements'][$e]['_id']."'"][$val[0]];
                                        if(!is_numeric($price)) { $price=0; }
                                        if($qty) {
                                            $values[]=$val[1].' x '.$_POST['qty']["'".$this->form['elements'][$e]['_id']."'"][$val[0]];
                                        } else {
                                            $values[]=$val[1];
                                        }
                                        if(!$qty) { $qty=1; }
                                        if($this->form['elements'][$e]['unit'] == 'currency' || empty($this->form['elements'][$e]['unit'])) {
                                            $amount_to_pay+=$qty*$price;
                                        }
                                    }

                                    $products[]=$values;

                                    $value=implode(', ',$values);

                                    if(trim($value) == ',') {
                                        $value='';
                                    }
                                    $data[$d]['value']=$value;
                                    //2147483647=2^31=~year 2033
                                    //The maximum value compatible with 32 bits systems
                                    setcookie($this->form['elements'][$e]['_id'], $data[$d]['value'], 2147483647);
                                    $data[$d]['order']=$d;
                                    $data[$d]['label']=$this->form['elements'][$e]['inputLabel'];
                                    $data[$d]['_id']=$this->form['elements'][$e]['_id'];
                                    $d++;
                                } else if($_POST[$this->form['elements'][$e]['_id']] && $this->form['elements'][$e]['type'] == 'SIGNATURE') {
                                    $value=$_POST[$this->form['elements'][$e]['_id']];

                                    list($type, $value) = explode(';', $value);
                                    list(, $value)      = explode(',', $value);
                                    $value = base64_decode($value);

                                    // var_dump($value);exit;
                                    // $value = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $value));
                                    // var_dump("dsdsds".$value);exit;
                                    // $imageData = base64_decode($value);

                                    $id=$this->pl->insertId(32);
                                    $filename=$id.'.png';
                                    $data[$d]['value']=$filename;
                                    $data[$d]['org_name']=$filename;
                                    $data[$d]['order']=$d;
                                    $data[$d]['label']=$this->form['elements'][$e]['inputLabel'];
                                    $data[$d]['_id']=$this->form['elements'][$e]['_id'];
                                    file_put_contents($GLOBALS['conf']['filepath_fileupload'].'/'.$filename, $value);

                                    $d++;
                                } else if($_POST[$this->form['elements'][$e]['_id']] && $this->form['elements'][$e]['type'] == 'INPUTTABLE') {

                                    $input_type = 'radio';
                                    if($this->form['elements'][$e]['inputtype']) {
                                        $input_type = $this->form['elements'][$e]['inputtype'];
                                    }

                                    $inputs=$_POST[$this->form['elements'][$e]['_id']];
                                    $values=array();
                                    foreach($inputs as $key => $input) {

                                        if($input_type == 'radio') {
                                            $questionLists=$this->form['elements'][$e]['questionList'];
                                            $qLabel=$key;
                                            if(count($questionLists)) {
                                                foreach($questionLists as $list) {
                                                    if($this->pl->slugify($list['label']) == $this->pl->slugify($key)) {
                                                        $qLabel=$list['label'];
                                                        break;
                                                    }
                                                }
                                            }
                                            $data[$d]['order']=$d;
                                            $data[$d]['value']=$_POST[$this->form['elements'][$e]['_id']][$key];
                                            $data[$d]['label']=$this->form['elements'][$e]['inputLabel'].' '.$qLabel;
                                            $data[$d]['_id']=$this->form['elements'][$e]['_id'].'_'.$this->pl->slugify($qLabel);
                                            $d++;
                                        } else if($input_type == 'text') {
                                            $questionLists=$this->form['elements'][$e]['questionList'];
                                            $qLabel=$key;
                                            if(count($questionLists)) {
                                                foreach($questionLists as $list) {
                                                    if($this->pl->slugify($list['label']) == $this->pl->slugify($key)) {
                                                        $qLabel=$list['label'];
                                                        break;
                                                    }
                                                }
                                            }

                                            $answerLists=$this->form['elements'][$e]['answerList'];
                                            foreach($answerLists as $k=>$ansList) {
                                                $data[$d]['order']=$d+$k;
                                                $data[$d]['value']=$_POST[$this->form['elements'][$e]['_id']][$key][$k];
                                                $data[$d]['label']=$this->form['elements'][$e]['inputLabel'].' '.$qLabel . ' ' . $ansList['label'];
                                                $data[$d]['_id']=$this->form['elements'][$e]['_id'].'_'.$this->pl->slugify($qLabel).'_'.$this->pl->slugify($ansList['label']);
                                                $d++;
                                            }


                                        } else if($input_type == 'checkbox') {
                                            $questionLists=$this->form['elements'][$e]['questionList'];
                                            $qLabel=$key;
                                            if(count($questionLists)) {
                                                foreach($questionLists as $list) {
                                                    if($this->pl->slugify($list['label']) == $this->pl->slugify($key)) {
                                                        $qLabel=$list['label'];
                                                        break;
                                                    }
                                                }
                                            }
                                            $data[$d]['order']=$d;
                                            $data[$d]['value']=implode(", ",$_POST[$this->form['elements'][$e]['_id']][$key]);
                                            $data[$d]['label']=$this->form['elements'][$e]['inputLabel'].' '.$qLabel;
                                            $data[$d]['_id']=$this->form['elements'][$e]['_id'].'_'.$this->pl->slugify($qLabel);
                                            $d++;
                                        }

                                    }

                                } else if($_POST[$this->form['elements'][$e]['_id']] && $this->form['elements'][$e]['type'] == 'LOOKUP') {
                                    $options = $this->form['elements'][$e]['optionsList'];
                                    $value = $_POST[$this->form['elements'][$e]['_id']];
                                    $newValue = array();
                                    foreach($options as $option) {
                                        if(isset($option[$this->form['elements'][$e]['lookupColumn']]) && $option[$this->form['elements'][$e]['lookupColumn']] == $_POST[$this->form['elements'][$e]['_id']]) {
                                            $ctr=0;
                                            foreach($option as $k=>$opt) {
                                                $label = $this->form['elements'][$e]['columns'][$ctr];
                                                $newValue[$label] = $opt;
                                                $ctr++;
                                            }
                                            $value = $newValue;
                                            break;
                                        }
                                    }
                                    $data[$d]['_id']=$this->form['elements'][$e]['_id'];
                                    $data[$d]['label']=$this->form['elements'][$e]['inputLabel'];
                                    $data[$d]['value']=$_POST[$this->form['elements'][$e]['_id']];
                                    $data[$d]['valArray']=$value;
                                    $d++;
                                } else {
                                    if($_POST[$this->form['elements'][$e]['_id']]) {
                                        if(is_array($_POST[$this->form['elements'][$e]['_id']]) && $this->form['elements'][$e]['type'] <> 'FILE') {
                                            $postArray=$_POST[$this->form['elements'][$e]['_id']];
                                            $v=array();
                                            foreach($postArray as $post) {
                                                if(trim($post)) {
                                                    $v[]=trim($post);
                                                }
                                            }
                                            $value=implode(', ', $v);
                                            if(trim($value) == ',') {
                                                $value='';
                                            }
                                            $data[$d]['value']=$value;
                                        } else if(is_array($_POST[$this->form['elements'][$e]['_id']]) && $this->form['elements'][$e]['type'] == 'FILE') {
                                            $postArray=$_POST[$this->form['elements'][$e]['_id']];
                                            $v=array();
                                            foreach($postArray as $post) {
                                                if(trim($post)) {
                                                    $v[]=trim($post);
                                                }
                                            }
                                            $data[$d]['value']=$v[0];
                                            $data[$d]['org_name']=$v[1];
                                        } else {
                                            $value=$_POST[$this->form['elements'][$e]['_id']];
                                            $data[$d]['value']=$value;
                                        }
                                    } else {
                                        if(!empty($_FILES[$this->form['elements'][$e]['_id']]["name"])) {
                                            $parts=explode('.',$_FILES[$this->form['elements'][$e]['_id']]["name"]);
                                            $id=$this->pl->insertId(32);
                                            $filename=$id.'.'.$parts[count($parts)-1];
                                            $tobeuploaded[]=array(
                                                '_id'=>$this->form['elements'][$e]['_id'],
                                                'file'=>$_FILES[$this->form['elements'][$e]['_id']],
                                                'name'=>$filename,
                                                'attachment' => $this->form['elements'][$e]['sendAsAttachment']
                                            );
                                            $data[$d]['value']=$filename;
                                            $data[$d]['org_name']=$_FILES[$this->form['elements'][$e]['_id']]["name"];
                                        } else {
                                            $data[$d]['value']="";
                                        }
                                    }

                                    //2147483647=2^31=~year 2033
                                    //The maximum value compatible with 32 bits systems
                                    setcookie($this->form['elements'][$e]['_id'], $data[$d]['value'], 2147483647);
                                    $data[$d]['order']=$d;
                                    if($this->form['elements'][$e]['type'] == 'SIGNATURE') {
                                        $data[$d]['label']=$this->form['elements'][$e]['label'];
                                    } else {
                                        $data[$d]['label']=$this->form['elements'][$e]['inputLabel'];
                                    }
                                    $data[$d]['_id']=$this->form['elements'][$e]['_id'];
                                    $data[$d]['customValidationType']=$this->form['elements'][$e]['customValidationType'];
                                    $d++;
                                }

                            }


                        } else {
                            setcookie($this->form['elements'][$e]['_id'], "", 2147483647);
                        }
                    }

                    if($_POST['total']) {
                        $amount_to_pay=$_POST['total'];
                    }

                    if($payment) {
                        $idx=count($data);
                        $data[$idx]['order']=$idx;
                        $data[$idx]['field']=$payment_element['_id'].'_total';
                        $data[$idx]['label']='Total';
                        $data[$idx]['_id']=$this->pl->insertId();
                        if($this->form['currency'] == 'USD') {
                            $symbol='$';
                        } else {
                            $symbol='';
                        }
                        $data[$idx]['value']=$symbol.$amount_to_pay.' '.$this->form['currency'];
                    }

                    $payment_element['products']=$products;
                } else {
                    $this->req_error['captcha']='Invalid Captcha';
                }
            }

            if($formEmpty) {
                if($payment) {
                    $this->display=$this->pl->trans($m,'payment');
                    $this->payment_element=$payment_element;
                }
            } else if(!$this->req_error){
                $submission_id=$this->pl->insertId();
                if($payment) {
                    $idx=count($data);
                    $data[$idx]['label']='Payment Status';
                    $data[$idx]['value']='Not confirmed';
                    $data[$idx]['_id']=$this->pl->insertId(16);
                    $this->amount_pay = $amount_to_pay;
                    $save_to_sess['amount_to_pay']=$amount_to_pay;
                    $save_to_sess['submission_id']=$submission_id;
                    $save_to_sess['paid_field_id']=$data[$idx]['_id'];
                }

                if(!$data) {
                    if($_GET['iframe'] == 'true') {
                        header("Location: /forms/".$this->urlpart[2]."/?iframe=true");exit;
                    } else {
                        header("Location: /forms/".$this->urlpart[2]."/");exit;
                    }
                }

                $form_owner=$this->user[0];

                //upload files if there is
                if(isset($tobeuploaded)) {
                    $attachments=array();
                    foreach($tobeuploaded as $file) {
                        if($file['attachment']) {
                            $attachments[]=$GLOBALS['conf']['filepath_fileupload'].'/'.$file['name'];
                        }
                        move_uploaded_file($_FILES[$file['_id']]["tmp_name"], $GLOBALS['conf']['filepath_fileupload'].'/'.$file['name']);
                    }
                }

                $this->sToSess = $save_to_sess;

                $this->submittedData = $data;
                $this->dataToSave = $data;

                $dataEncrypted = 0;
                $autoErase = $this->form['responseStorage'] == 'erase' && ($this->form['active']<>'1' || $account['plan'] == 'PRO');

                //encrypt the value if form encryption enabled
                if($this->form['responseStorage'] == 'encrypted' && ($this->form['active']<>'1' || $account['plan'] == 'PRO')) {
                    $dataEncrypted = 1;
                    foreach($this->dataToSave as &$sd) {
                        $sd['value'] = $this->pl->encrypt($sd['value']);
                    }
                } else if($autoErase) {
                    $ctrD = 0;
                    $this->dataToSave = [];
                }

                $submittedData=json_encode($this->dataToSave, JSON_UNESCAPED_UNICODE);
                if(!$this->isJson($submittedData)) {
                    $this->pl->sendMail(array('body'=>$this->pl->Xssenc(print_r($_POST,1)),'from'=>'hello@formlets.com','to'=>'dev@formlets.com', 'cc'=>'elias@oxopia.com', 'subject'=>'Submission Error Data for '.$this->pl->Xssenc($this->form['name'])));
                }

                if(count($this->dataToSave)) {
                    $this->lo->saveSubmissions(array('id'=>$submission_id,'form_id'=>$this->form['_id'],'data'=>$submittedData), $dataEncrypted);
                }

                if($this->form['active']=='1' && !$this->pl->isAdminOfForm($this->form, $this->lUser, $this->lAccount)) {
                    $this->lo->incrementFormSubmission(array('form_id' => $this->urlpart[2],'account_id' => $this->form['accountId']));
                }

                $templates=$this->lo->getFormEmailTemplate(array('form_id'=>$this->form['_id']));
                if(count($templates) && $this->form['notifyUseTemplate'] && $this->pl->isFreeAccount($form_owner) == false && !$payment) {
                    $this->_sendEmailTemplate($this->form, $templates, $data, $submission_id);
                }

                /**
                **Datasource Connector
                **/
                $connectors = $this->lo->getDatasourceConnector(array('formId'=>$this->form['_id']));
                $connectors_byDC = array();
                foreach($connectors as $connector) {
                    $connectors_byDC[$connector['datasourceId']][$connector['elementId']] = '';
                }

                foreach($data as $d) {
                    foreach($connectors_byDC as $k=>$c) {
                        if(isset($c[$d['_id']])) {
                            $connectors_byDC[$k][$d['_id']] = $d['value'];
                        }
                    }
                }

                //var_dump($connectors_byDC);exit;

                $newDatasourceList = array();
                foreach($connectors_byDC as $datasourceId => $d) {
                    $ds = $this->lo->_listDatasources(array('source_id'=>$datasourceId));
                    $ds = $ds[0];
                    $columns = array("Label", "Value");
                    $dataLists = json_decode($ds['data'], true);
                    if($ds['columns']) {
                        $columns = json_decode($ds['columns'], true);
                    }
                    //var_dump($columns);

                    $newDatasourceList[$datasourceId] = array();

                    foreach($connectors as $connector) {
                        if(isset($d[$connector['elementId']])) {
                            $c = $connector['datasourceColumn'];
                            $c = str_replace('&quot;','"', $c);
                            $key = array_search($c, $columns);
                            if($key == 0) {
                                $iKey = 'label';
                            } else if($key == 1) {
                                $iKey = 'value';
                            } else if($key > 1) {
                                $iKey = 'column_'.($key+1);
                            }

                            $newDatasourceList[$datasourceId][$iKey] = $d[$connector['elementId']];
                        }
                    }

                    //var_dump($newDatasourceList[$datasourceId]);exit;

                    $dataLists[] = $newDatasourceList[$datasourceId];

                    //var_dump(json_encode($dataLists, JSON_UNESCAPED_UNICODE));exit;

                    $this->lo->saveDatasource(array(
                        'old_id'=>$datasourceId,
                        'data'=>json_encode($dataLists, JSON_UNESCAPED_UNICODE)
                    ));
                }

                /**
                **End Datasource Connector
                **/

                 //let the zapier know there is new submittion
                 $hooks=$this->lo->getZapierHooks(array('form_id'=>$this->form['_id']));
                 if(count($hooks)) {
                     try {
                         $curl=curl_init();
                         foreach($hooks as $hook) {
                            $data['formid']=$this->form['_id'];
                            $submissions=$this->lo->getSubmissions($data);
                            $results=$this->submission_data($submissions, $this->form, $form_owner, 'post', true);
                            $nr=count($results);
                            if($nr) {
                                $jsonEncodedData=json_encode($results[0], JSON_UNESCAPED_UNICODE);
                                //echo $jsonEncodedData;exit;
                                // Configure curl options
                                $opts=array(
                                    CURLOPT_URL             => $hook['url'],
                                    CURLOPT_RETURNTRANSFER  => true,
                                    CURLOPT_CUSTOMREQUEST   => 'POST',
                                    CURLOPT_POST            => 1,
                                    CURLOPT_TIMEOUT         => 5,
                                    CURLOPT_POSTFIELDS      => $jsonEncodedData,
                                    CURLOPT_HTTPHEADER  => array('Content-Type: application/json','Content-Length: '.strlen($jsonEncodedData))
                                );

                                // Set curl options
                                curl_setopt_array($curl, $opts);
                                // Get the results
                                curl_exec($curl);

                                //$this->logEvent('Zap hook',$data['formid'],print_r($result));
                                $this->logEvent('Zap hook',$data['formid'],1);
                            }
                         }

                         if($autoErase) {
                             $executedAt = date($dateformat . ' ' . $timeformat, strtotime('now'));
                             $this->dataToSave[$ctrD]['label']='Shared with zapier';
                             $this->dataToSave[$ctrD]['value']=$executedAt;
                             $this->dataToSave[$ctrD]['_id']=$this->pl->insertId(16);
                             $ctrD++;
                         }

                         curl_close($curl);
                     } catch(Exception $e) {
                         //
                     }

                 }


                 //$this->pl->sendMail(array('body'=>$this->pl->Xssenc(print_r($_POST,1)),'from'=>'hello@formlets.com','to'=>'dev@formlets.com','subject'=>'New Submission for '.$this->pl->Xssenc($this->form['name'])));

                if($this->form['notifyNewSubmissions']) {
                    //$from=$this->form['emailFrom'] ?: 'Formlets';
                    //$replyTo=$this->form['emailReply'] ?: 'hello@formlets.com';
                    $from='hello@formlets.com';
                    $replyTo='hello@formlets.com';
                    foreach($data as $field) {
                        if (filter_var($field['value'], FILTER_VALIDATE_EMAIL) == true) {
                            $replyTo=$field['value'];
                            break;
                        }
                    }
                    $emails=explode(',', $this->form['email']);
                    $tos=array();
                    foreach($emails as $email) {
                        if (filter_var(trim($email), FILTER_VALIDATE_EMAIL) == true) {
                            $tos[]=trim($email);
                        }
                    }
                    $mail=array('body'=>$this->outputSubmissionMail($this->form, $data, $this->user),'from'=>$from,'to'=>$tos,'replyTo'=>$replyTo,'subject'=>'['.$this->pl->Xssenc(stripslashes($this->form['name'])).'] New Submission');
                    $mail['attachments']=$attachments;
                    $this->pl->sendMail($mail);

                    if($autoErase) {
                        $executedAt = date($dateformat . ' ' . $timeformat, strtotime('now'));
                        $this->dataToSave[$ctrD]['label']='Sent to email';
                        $this->dataToSave[$ctrD]['value']=$this->form['email'] . ' Date: ' . $executedAt;
                        $this->dataToSave[$ctrD]['_id']=$this->pl->insertId(16);
                        $ctrD++;
                    }
                }

                if($this->form['notifySubmitter'] && $this->form['notifyUseTemplate']<>'1') {

                    if($this->pl->isFreeAccount($form_owner) == false || $this->form['active']<>'1') {
                        $notifyEmail='';
                        foreach($data as $d) {
                            if($d['customValidationType'] == 'EMAIL') {
                                $notifyEmail=$d['value'];
                                break;
                            }
                        }

                        if($this->form['type']=="ENDPOINT") {
                            foreach($data as $d) {
                                if(filter_var($d['value'], FILTER_VALIDATE_EMAIL)) {
                                    $notifyEmail=$d['value'];
                                    break;
                                }
                            }
                        }

                        if($notifyEmail) {
                            $from=$this->form['emailFrom'] ?: 'hello@formlets.com';
                            $replyTo=$this->form['emailReply'] ?: 'hello@formlets.com';
                            $to=$notifyEmail;
                            $mail=array('body'=>$this->outputSubmissionMail($this->form, $data),'from'=>$from,'to'=>$to,'replyTo'=>$replyTo,'subject'=>'['.$this->pl->Xssenc($this->form['name']).'] New Submission');
                            //var_dump($mail);
                            $this->pl->sendMail($mail);
                        }
                    }
                }

                if($autoErase) {
                    $executedAt = date($dateformat . ' ' . $timeformat, strtotime('now'));
                    $this->dataToSave[$ctrD]['label']='Closed transaction and removed data';
                    $this->dataToSave[$ctrD]['value']=$executedAt;
                    $this->dataToSave[$ctrD]['_id']=$this->pl->insertId(16);

                    $submittedData=json_encode($this->dataToSave, JSON_UNESCAPED_UNICODE);
                    $this->lo->saveSubmissions(array('id'=>$submission_id,'form_id'=>$this->form['_id'],'data'=>$submittedData), $dataEncrypted);
                }

                if($payment && ($amount_to_pay || $payment_element['captureCard'])) {
                    $this->display=$this->pl->trans($m,'payment');
                    $this->payment_element=$payment_element;
                } else if($payment && $amount_to_pay<=0) {
                    header('Location: /forms/'.$this->urlpart[2].'/?payment=ok&stoken='.$submission_id);
                } else {
                    $this->display=$this->pl->trans($m,'success');
                }

                if(!$payment) {
                    if($this->form['redirectUrl'] && $this->form['doRedirect']=='1' && $_GET['iframe']=='true') {
                    ?>
                    <script type="text/javascript">
                        if (typeof window != "undefined") {
                            window.top.location='<?php echo $this->form['redirectUrl'];?>';
                        } else {
                            location.href='<?php echo $this->form['redirectUrl'];?>';
                        }

                    </script>
                    <?php
                    } else if($this->form['redirectUrl'] && $this->form['doRedirect']=='1') {
                          header('location:'.$this->form['redirectUrl']);
                          exit;
                    }
                }
            }
        }

    }

    function checkSESEmailVerified($email) {
        $client = SesClient::factory(array(
            'version'=> 'latest',
            'region' => $GLOBALS['conf']['ses']['region'],
            'credentials'=> [
                'key'    => $GLOBALS['conf']['ses']['key'],
                'secret' => $GLOBALS['conf']['ses']['secret'],
            ]
        ));

        $verifiedEmails = $client->GetIdentityVerificationAttributes([
            'Identities' => [$email]
        ]);

        $statuses = $verifiedEmails->get('VerificationAttributes');
        if(count($statuses)) {
            if($statuses[$email]['VerificationStatus'] == 'Success') {
                $email_verified = 1;
            } else {
                $email_verified = 0;
            }
        } else {
            $email_verified = 0;
        }

        return $email_verified;
    }

    function processEmail() {
        $m="processemail";
        $this->canAccess=$GLOBALS['ref']['plan_lists'][$this->account['index']]['emailTemplates'];

        if ($_POST) {

            if($this->urlpart[4] == 'template') {

                $data = array(
                    'id'=>$this->urlpart[3],
                    'html'=>$_POST['templateHTML']
                );

                $this->lo->saveEmailTemplateHTML($data);
                $save_to_sess['success_message']=$this->pl->trans($m, 'Template has been saved.');
                $this->pl->save_session($save_to_sess);
                header('Location: /email/'.$this->urlpart[2].'/'.$this->urlpart[3].'/template/');
                exit;
            }

            $error = false;
            if (empty($_POST['form']) || empty($_POST['name']) || empty($_POST['subject']) || empty($_POST['recipient']) || empty($_POST['template'])) {
                $save_to_sess['error_message']=$this->pl->trans($m, 'All * fields are required');
                $error = true;
            }

            if (filter_var($_POST['emailfrom'], FILTER_VALIDATE_EMAIL) == false) {
                $save_to_sess['error_message']=$this->pl->trans($m, 'From field is not valid email address');
                $error = true;
            }

            if($error) {
                $save_to_sess['old_input']=$_POST;
                $this->pl->save_session($save_to_sess);
                if ($this->urlpart[4] == 'edit') {
                    header('Location: /email/'.$this->urlpart[2].'/'.$this->urlpart[3].'/edit/');
                    exit;
                }
                header('Location: /email/'.$this->urlpart[2].'/');
                exit;
            }


            $_POST['emailfrom']=trim(strip_tags(str_replace("&nbsp;", "", $_POST['emailfrom'])));
            $_POST['emailfrom']=trim(strip_tags(str_replace("&amp;", "&", $_POST['emailfrom'])));

            $emailVerified = $this->checkSESEmailVerified($_POST['emailfrom']);
            if(!$emailVerified) {
                $client = SesClient::factory(array(
                    'version'=> 'latest',
                    'region' => $GLOBALS['conf']['ses']['region'],
                    'credentials'=> [
                        'key'    => $GLOBALS['conf']['ses']['key'],
                        'secret' => $GLOBALS['conf']['ses']['secret'],
                    ]
                ));

                $result = $client->verifyEmailAddress([
                    'EmailAddress' => $_POST['emailfrom'],
                ]);
            }

            $_POST['emailReplyTo']=trim(strip_tags(str_replace("&nbsp;", "", $_POST['emailReplyTo'])));
            $_POST['emailReplyTo']=trim(strip_tags(str_replace("&amp;", "&", $_POST['emailReplyTo'])));
            $_POST['recipient']=trim(strip_tags(str_replace("&nbsp;", "", $_POST['recipient'])));
            $_POST['recipient']=trim(strip_tags(str_replace("&amp;", "&", $_POST['recipient'])));
            $_POST['cc']=trim(strip_tags(str_replace("&nbsp;", "", $_POST['cc'])));
            $_POST['cc']=trim(strip_tags(str_replace("&amp;", "&", $_POST['cc'])));
            $_POST['bcc']=trim(strip_tags(str_replace("&nbsp;", "", $_POST['bcc'])));
            $_POST['bcc']=trim(strip_tags(str_replace("&amp;", "&", $_POST['bcc'])));
            $_POST['subject']=trim(strip_tags(str_replace("&nbsp;", "", $_POST['subject'])));
            $_POST['subject']=trim(strip_tags(str_replace("&amp;", "&", $_POST['subject'])));
            $_POST['template']=str_replace("&amp;", "&", $_POST['templateHTML']);

            $data=$_POST;

            if ($this->urlpart[3] && $this->urlpart[4] == 'edit') {
                $data['id']=$this->urlpart[3];
            }

            $tid=$this->lo->saveEmailTemplate($data);
            if($emailVerified) {
                $save_to_sess['success_message']=$this->pl->trans($m, 'Template has been saved.');
            }

            $save_to_sess['old_input']='';
            $this->pl->save_session($save_to_sess);
            header('Location: /email/');
            exit;
        }

        if (($this->urlpart[2] && $this->urlpart[3]) && $this->urlpart[4] == 'delete') {
            $this->pl->validate_csrfguard();
            $data=array(
                'id' => $this->urlpart[3],
                'user_id' => $this->uid
            );

            $this->lo->deleteTemplate($data);
            $this->pl->save_session('success_message', $this->pl->trans($m, 'Template has been deleted.'));
            header('Location: /email/'); // TODO change
            exit;
        } else {
            if (($this->urlpart[2] && $this->urlpart[3]) && ($this->urlpart[4] == 'enable' || $this->urlpart[4] == 'disable')) {
                $this->pl->validate_csrfguard();

                if (!$this->canAccess) {
                    $this->pl->save_session('error_message', $this->pl->trans($m, 'Please').' <a href="/settings/subscription/">'.$this->pl->trans($m, 'upgrade your account').'</a> '.$this->pl->trans($m, 'to use the Autoresponders'));
                    header('Location: /email/');
                    exit;
                }

                $data=array(
                    'form_id' => $this->urlpart[2],
                    'notifySubmitter' => $this->urlpart[4] == 'enable' ? 1 : null,
                    'notifyUseTemplate' => $this->urlpart[4] == 'enable' ? 1 : 0
                );

                $this->lo->editFormAutoResponder($data);

                $this->pl->save_session('success_message', $this->pl->trans($m, 'Template has been updated'));
                header('Location: /email/');
                exit;
            }
        }
    }

    function processAdvancethemes() {
        $m="processadvancethemes";
        //if($this->urlpart[2]) {
        $canAccess=$GLOBALS['ref']['plan_lists'][$this->account['index']]['theme'];

        if (!$canAccess) {
            $this->pl->save_session('error_message', $this->pl->trans($m, 'Please').' <a href="/settings/subscription/">'.$this->pl->trans($m, 'upgrade your account').'</a> '.$this->pl->trans($m, 'to use advanced custom CSS'));
            header('Location: /advancethemes/');
            exit;
        }
        //}

        if ($_POST) {
            if (empty($_POST['name'])) {
                $this->pl->save_session('error_message', $this->pl->trans($m, 'Please enter theme name'));
                if ($this->urlpart[3] == 'edit') {
                    header('Location: /advancethemes/'.$this->urlpart[2].'/edit/');
                    exit;
                }
                header('Location: /advancethemes/new/');
                exit;
            }

            $data=$_POST;
            $data['type']='Advanced';

            if ($this->urlpart[2] && $this->urlpart[3] == 'edit') {
                $data['id']=$this->urlpart[2];
            }

            $save=$this->lo->saveThemes($data);
            $this->pl->save_session('success_message', $this->pl->trans($m, 'Theme has been saved.'));
            header('Location: /advancethemes/');
            exit;
        }

        if (($this->urlpart[2]) && ($this->urlpart[3] == 'delete')) {
            $this->pl->validate_csrfguard();
            $data=array(
                'id' => $this->urlpart[2],
                'user_id' => $this->uid
            );

            $this->lo->deleteTheme($data);
            $this->pl->save_session('success_message', $this->pl->trans($m, 'Theme has been deleted.'));
            header('Location: /advancethemes/');
            exit;

        }
    }

    function processThemes() {
        $m="processthemes";
        if ($_POST) {
            if (empty($_POST['name'])) {
                $this->pl->save_session('error_message', $this->pl->trans($m, 'Please enter theme name'));
                if ($this->urlpart[3] == 'edit') {
                    header('Location: /themes/'.$this->urlpart[2].'/edit/');
                    exit;
                }
                header('Location: /themes/new/');
                exit;
            }

            $data=$_POST;
            $data['type']='Basic';

            if ($this->urlpart[2] && ($this->urlpart[3] == 'edit')) {
                $data['id']=$this->urlpart[2];
            }

            $save=$this->lo->saveThemes($data);
            $this->pl->save_session('success_message', $this->pl->trans($m, 'Theme has been saved.'));
            header('Location: /themes/');
            exit;
        }

        if ($this->urlpart[2] && ($this->urlpart[3] == 'delete')) {
            $data=array(
                'id' => $this->urlpart[2],
                'user_id' => $this->uid
            );

            $this->lo->deleteTheme($data);
            $this->pl->save_session('success_message', $this->pl->trans($m, 'Theme has been deleted.'));
            header('Location: /themes/');
            exit;

        }
    }

    function processeditor() {
        $m="processeditor";
        $allowed=false;
        $this->form=$this->lo->getForm(array("form_id" => $this->urlpart[2]));
        if ($this->uaccountstatus == 'MEMBER') {
            $form_id=$this->urlpart[2];
            $permissions=array();
            $me=$this->lo->_getUsers(array('id' => $this->lUser['_id']))[0];
            if ($me['permissions']) {
                $permissions=json_decode(str_replace('\"', '"', $me['permissions']), true);
            }

            if (isset($permissions[$form_id]) && $permissions[$form_id] == 'edit') {
                $allowed=true;
            }
        } else {
            $allowed=true;
        }

        if (!$allowed || $this->lAccount['blocked']) {
            echo $this->pl->trans($m, 'Permission denied');
            exit;
        }

        if($this->lUser['blocked']) {
            header('location: '.$GLOBALS['level'].'form/');
            exit;
        }
    }


    function processform() {
        $m="processform";
        if (array_key_exists(3, $this->urlpart)) {
            $id=substr($this->urlpart[3], 0, 24);
            if (($this->urlpart[2] == 'delete') && ($id)) {
                $this->pl->validate_csrfguard();
                if ($this->uaccountstatus == 'MEMBER') {
                    echo $this->pl->trans($m, 'permission denied');
                    exit;
                }

                $this->pl->delete_form_files(array('form_id' => $id, 'user_id' => $this->uid), $this->lo);
                $this->lo->deleteForm(array('form_id' => $id, 'user_id' => $this->lAccountOwner['_id'], 'accountId'=>$this->lAccount['_id']));
                $this->pl->save_session('success_message', 'Form has been deleted');
                header('location: '.$GLOBALS['level'].'form/');
                exit;
            } else {
                if (($this->urlpart[2] == 'duplicate') && ($id)) {
                    $this->pl->validate_csrfguard();
                    if ($this->uaccountstatus == 'MEMBER') {
                        echo $this->pl->trans($m, 'permission denied');
                        exit;
                    }

                    $this->lo->duplicateForm(array('form_id' => $id, 'user_id' => $this->lAccountOwner['_id'], 'accountId'=>$this->lAccount['_id']));
                    $this->pl->save_session('success_message', 'Form has been duplicated');
                    header('location: '.$GLOBALS['level'].'form/');
                    exit;
                } else {
                    if (($this->urlpart[2] == 'activate' || $this->urlpart[2] == 'deactivate') && $id) {
                        $this->pl->validate_csrfguard();
                        if ($this->uaccountstatus == 'MEMBER') {
                            echo $this->pl->trans($m, 'permission denied');
                            exit;
                        }
                        if ($this->urlpart[2] == 'activate') {
                            $active=1;
                        } else {
                            $active=0;
                        }

                        $update=$this->lo->saveFormState(array('form_id' => $id, 'user_id' => $this->lAccountOwner['_id'], 'accountId'=>$this->lAccount['_id'], 'active' => $active, 'user_account' => $this->account));
                        if ($update['updated']) {
                            $this->pl->save_session('success_message', $update['message']);
                        } else {
                            $this->pl->save_session('error_message', $update['message']);
                        }

                        header('Location: /form/');
                        exit;
                    }
                }
            }
        }
    }


    // endoint functions

    function processf() {
        $this->processForms();
    }


    // platform functions

    function processManage() {

    }


    private function _getUserByToken($data) {
        return $this->lo->checktokenUsers($data);
    }

    //
    function processcheckemail() {
        $m="processcheckemail";
        if ($this->urlpart[3]) {
            $data=array('id' => $this->urlpart[2], 'password_token' => $this->urlpart[3]);
            $row=$this->_getUserByToken($data);
            if (!$row[0]['_id']) {
                $this->okMessage=$this->pl->trans($m, 'The link(token) you are using has expired.').' <a href="'.$GLOBALS['level'].'resendemailvalidation/'.$this->urlpart[2].'/">'.$this->pl->trans($m, 'Please request a new token').'</a>';
            } else {
                $this->lo->_removetokenUsers(array('id' => $row[0]['_id'], 'password_token' => $this->urlpart[3]));
                if ($this->lUser) {
                    $this->SetUserSession($row[0]['_id'], 'Email verification check ok with login');
                    header('Location: /form/?emailok=y');
                } else {
                    header('location: '.$GLOBALS['level'].'emailok');
                }
                exit;
            }
        }

    }
    //


    // when a user has lost his password
    function processnewpassword() {
        $m="processnewpassword";
        //if (strlen($this->urlpart[3]) == 32) {
            $row=$this->lo->checktokenUsers(array('password_token' => $this->urlpart[3], 'id' => $this->urlpart[2]));
            if (!$row[0]['_id']) {
                $this->okMessage=$this->pl->trans($m, 'The password set token you are using has expired. please request a new token at').' <a href="'.$GLOBALS['level'].'password/">'.$this->pl->trans($m, 'Request a new password').'</a>';
            } else {
                if ($_POST) {
                    if (($this->pl->Xssenc($_POST['password_confirmation']) == $this->pl->Xssenc($_POST['password'])) && (strlen($this->pl->Xssenc($_POST['password'])) > 7)) {
                        $this->lo->_removetokenUsers(array(
                            'id' => $this->urlpart[2],
                            'password_token' => $this->urlpart[3],
                            'reset_password' => true
                        ));
                        $row=$this->lo->setPasswordUsers(array('password_token' => $this->urlpart[3], 'id' => $this->urlpart[2], 'password' => crypt($this->pl->Xssenc($_POST['password']), '$2a$07$9edf9384756gap1b49sj9xxx0ddkjsj7521038675sxgwjn38675sgwj34kdkaqop3946c38392021naqop3948484@8$')));
                        $this->lo->_removetokenUsers(array('id' => $this->urlpart[2], 'password_token' => $this->urlpart[3]));
                        unset($GLOBALS['sess']['loginUser']);
                        $this->okMessage=$this->pl->trans($m, 'Your new password is set, please').' <a href="'.$GLOBALS['level'].'login/">'.$this->pl->trans($m, 'Login').'</a>';
                    } else {
                        if (strlen($this->pl->Xssenc($_POST['password']) < 8)) {
                            $this->errorMessage=$this->pl->trans($m, 'The password needs to be at least 8 characters long');
                        } else {
                            $this->errorMessage=$this->pl->trans($m, 'The password and its confirmation need to be the same');
                        }
                    }
                }
            }
            unset($row); /// just to be sure not to leak data
        //}
    }
    //

    function processAdmin_support() {

        if (isset($_POST['submit_support_form'])) {
            $_id=$this->urlpart[5];
            $lang=$this->urlpart[3];
            $state=$_id;
            if ((!$_id) || ($_id == "new")) {
                $_id=$this->pl->insertId();
            }
            if (!$_POST['remove_img1']) {
                $data['img1']=$this->pl->Xssenc($_POST['hidden_img1']);
            }
            if (!$_POST['remove_img2']) {
                $data['img2']=$this->pl->Xssenc($_POST['hidden_img2']);
            }
            if ($_FILES) {
                $imgf=array('img1', 'img2');
                for ($i=0; $i < count($imgf); $i++) {
                    if (($_FILES[$imgf[$i]]["type"] == 'image/png') ||
                        ($_FILES[$imgf[$i]]["type"] == 'image/gif') ||
                        ($_FILES[$imgf[$i]]["type"] == 'image/jpg') ||
                        ($_FILES[$imgf[$i]]["type"] == 'image/jpeg')) {
                        $parts=explode('.', $_FILES[$imgf[$i]]["name"]);
                        $size=$_FILES[$imgf[$i]]["size"];
                        $id=$this->pl->insertId();
                        $filename=$id.'.'.$parts[count($parts) - 1];
                        move_uploaded_file($_FILES[$imgf[$i]]["tmp_name"], $GLOBALS['conf']['filepath'].'/'.$filename);
                        $data[$imgf[$i]]=$filename;
                        $data[$imgf[$i].'_size']=$size;
                    }
                }
            }

            $request=array(
                '_id' => $_id,
                'state' => $state,
                'title' => $this->pl->Xssenc($_POST['title']),
                'intro' => $this->pl->Xssenc($_POST['intro']),
                'img1' => $this->pl->Xssenc($data['img1']),
                'img2' => $this->pl->Xssenc($data['img2']),
                'lang' => $this->pl->Xssenc($lang),
                'body1' => $this->pl->Xssenc($_POST['body1']),
                'body2' => $this->pl->Xssenc($_POST['body2']),
                'url' => str_replace(' ', '-', strtolower($this->pl->Xssenc($_POST['url']))),
                'category' => $this->pl->Xssenc($_POST['category'])
            );
    //print_r($request);
            if (empty($_POST['title']) || empty($_POST['category']) || empty($_POST['url'])) {
                $this->errorMessage="Please supply required fields!";
            } else {

                if ($this->lo->saveFaq($request)) {
                    if ($state == 'new') {
                        //    header('location: '.$GLOBALS['level'].'admin/support/'.$lang.'/edit/'. $request['_id'] .'?saved=ok');
                    } else {
                        //    header('location: '.$GLOBALS['level'].'admin/support/'.$lang.'/edit/'. $request['_id'] .'/?updated=ok');
                    }
                }
            }
        }
    }

    function processAdmin_features() {
        if($_POST) {

            $_id=$this->urlpart[5];
            $state=$_id;
            if ((!$_id) || ($_id == "new")) {
                $_id=$this->pl->insertId();
            }

            if (!$_POST['remove_img1']) {
                $data['img1']=$this->pl->Xssenc($_POST['hidden_img1']);
            }
            if (!$_POST['remove_img2']) {
                $data['img2']=$this->pl->Xssenc($_POST['hidden_img2']);
            }
            if ($_FILES) {
                $imgf=array('img1', 'img2');
                for ($i=0; $i < count($imgf); $i++) {
                    if (($_FILES[$imgf[$i]]["type"] == 'image/png') ||
                        ($_FILES[$imgf[$i]]["type"] == 'image/gif') ||
                        ($_FILES[$imgf[$i]]["type"] == 'image/jpg') ||
                        ($_FILES[$imgf[$i]]["type"] == 'image/jpeg')) {
                        $parts=explode('.', $_FILES[$imgf[$i]]["name"]);
                        $size=$_FILES[$imgf[$i]]["size"];
                        $id=$this->pl->insertId();
                        $filename=$id.'.'.$parts[count($parts) - 1];
                        move_uploaded_file($_FILES[$imgf[$i]]["tmp_name"], $GLOBALS['conf']['filepath'].'/'.$filename);
                        $data[$imgf[$i]]=$filename;
                        $data[$imgf[$i].'_size']=$size;
                    }
                }
            }

            $data = array(
                'id'=>$_id,
                'state'=>$state,
                'title'=>$this->pl->Xssenc($_POST['title']),
                'url'=>$this->pl->Xssenc($_POST['url']),
                'body'=>$_POST['body'],
                'img1' => $this->pl->Xssenc($data['img1']),
                'img2' => $this->pl->Xssenc($data['img2']),
                'body2' => $_POST['body2'],
            );

            //var_dump($data);exit;

            $this->lo->saveFeatures($data);

            header('Location: /admin/features/');
        }
    }

    function processAdmin_templates() {
        if (isset($_POST['submit_templates_form'])) {
            $id=$this->urlpart[3];
            if (!$_POST['remove_img1']) {
                $data['img1']=$this->pl->Xssenc($_POST['hidden_img1']);
            }
            if (!$_POST['remove_img2']) {
                $data['img2']=$this->pl->Xssenc($_POST['hidden_img2']);
            }
            if (!$_POST['remove_img3']) {
                $data['img3']=$this->pl->Xssenc($_POST['hidden_img3']);
            }

            if ($_FILES) {
                $imgf=array('img1', 'img2', 'img3');
                for ($i=0; $i < count($imgf); $i++) {
                    if (($_FILES[$imgf[$i]]["type"] == 'image/png') ||
                        ($_FILES[$imgf[$i]]["type"] == 'image/gif') ||
                        ($_FILES[$imgf[$i]]["type"] == 'image/jpg') ||
                        ($_FILES[$imgf[$i]]["type"] == 'image/jpeg')) {
                        $parts=explode('.', $_FILES[$imgf[$i]]["name"]);
                        $size=$_FILES[$imgf[$i]]["size"];
                        $filename=$id.'_'.($i + 1).'.'.$parts[count($parts) - 1];
                        move_uploaded_file($_FILES[$imgf[$i]]["tmp_name"], $GLOBALS['conf']['filepath_support'].'/'.$filename);
                        $data[$imgf[$i]]=$filename;
                    }
                }
            }

            $request=array(
                'name' => $this->pl->Xssenc($_POST['name']),
                'description' => $this->pl->Xssenc($_POST['description']),
                'published' => $_POST['publish'] ?: 0,
                'img1' => $this->pl->Xssenc($data['img1']),
                'img2' => $this->pl->Xssenc($data['img2']),
                'img3' => $this->pl->Xssenc($data['img3']),
            );

            if (empty($_POST['name']) || empty($_POST['description'])) {
                $this->pl->save_session('error_message', 'Please supply required fields!');
            } else {
                if ($this->lo->saveFormTemplate($request, $id)) {
                    $this->pl->save_session('success_message', 'Template has been saved.');
                    header('Location: /admin/templates/');
                    exit;
                }
            }
        }
    }

    function processAdmin() {
        $id='';
        if ($this->urlpart[2] === 'support') {

            if ($this->urlpart[3] === 'delete') {
                $id=substr($this->urlpart[4], 0, 24);
                $this->lo->deleteFaq($id);
            } else {
                $this->processAdmin_support();
            }

            if ($id) {
                header('location: '.$GLOBALS['level'].'admin/support/?deleted=ok');
                exit;
            }
        } else if ($this->urlpart[2] === 'templates') {
            if ($this->urlpart[4] === 'delete') {
                $id=$this->urlpart[3];
                $this->lo->deleteFormTemplate($id);
            } else {
                $this->processAdmin_templates();
            }

            if ($id) {
                $this->pl->save_session('success_message', 'Template successfully deleted!');
                header('location: '.$GLOBALS['level'].'admin/templates/');
                exit;
            }
        } else if($this->urlpart[2] === 'features') {
            if ($this->urlpart[3] === 'delete') {
                $id=substr($this->urlpart[4], 0, 24);
                $this->lo->deleteFeatures($id);
            } else {
                $this->processAdmin_features();
            }

            if ($id) {
                header('location: '.$GLOBALS['level'].'admin/features/?deleted=ok');
                exit;
            }
        } else {
            if ($this->urlpart[2] === 'users') {
                $this->processUser();
            }
        }
    }

    function getAccount($accountId=null) {
        if ($accountId) {
            $accounts=$this->lo->getAccounts(array('accountid' => $accountId, 'userid' => $this->lUser['_id']));
        } else {
            $accounts=$this->lo->getAccounts(array('userid' => $this->lUser['_id']));
        }

        //$GLOBALS['sess']['loginAccount']=$accounts[0];
        //$GLOBALS['sess']['loginAccountId']=$accounts[0]['_id'];
        $this->lo->setUserSession(session_id(), $this->lUser['_id'], $accounts[0]['_id']);
        $accountOwner = $this->lo->getAccountOwner(array('accountId' => $accounts[0]['_id']));
        $saveSessAccountOwner = array('_id'=>$accountOwner['_id']);
        $this->pl->save_session('loginAccountOwner', $saveSessAccountOwner);
    }

    function logEvent($type, $userid, $tag) {

    // needs to log IP and timestamp too
        $array['type']=$type;
        $array['userid']=$userid;
        $array['tag']=$tag;
        $array['_id']=$this->pl->insertId();

        $this->lo->createEvent($array);
    }

    //this is a standardised function to login a user
    function SetUserSession($id, $eventtag) {
        if ($this->lUser['admin']) {
            $setadmin=$this->lUser['admin'];
        }
        $accounts = $this->lo->getAccounts(array('userid' => $id));
        $this->lo->setSession(session_id(), time());
        $this->lo->setUserSession(session_id(), $id, $accounts[0]['_id']);
        $row=$this->lo->_getUsers(array('id' => $id));
        if ($row[0]) {
            $set=$row[0];
            if ($set['admin'] == '1') {
                $rights=$this->lo->getAdmin(array('id' => $set['_id']));
                $this->pl->save_session('admin', $rights[0]);
            }
            if ($setadmin) {
                $this->pl->save_session('admin', $setadmin);
            }
            unset($set['password']);
            unset($set['password_token']);
            //$GLOBALS['sess']['loginUser']=$set;
            $this->lUser=$set;

            $this->logEvent('Session start', $id, $eventtag);
            if (!$setadmin) {
                $this->lo->loginUsers(array('id' => $id, 'ip' => $this->pl->getIP()));
            }
            return true;
        } else {
            return false;
        }
    }
    //

    function setAccountSession($id, $eventtag) {
        $row=$this->lo->getAccounts(array('accountid' => $id, 'userid' => $this->lUser['_id']));
        if ($row[0]) {
            $set=$row[0];
            //$GLOBALS['sess']['loginAccount']=$set;
            //$this->pl->save_session($GLOBALS['sess']);
            $this->lAccount=$set;
            $this->logEvent('Session Account start', $id, $eventtag);
            return true;
        } else {
            return false;
        }
    }

    function hasOwnAccount($accounts) {
        if(count($accounts)) {
            foreach($accounts as $account) {
                if($account['accountRights'] == $GLOBALS['ref']['user_all_rights']) {
                    return true;
                }
            }
        }

        return false;
    }


    // misc functionality with actions on the user table , by admin
    function processUser() {


        if (isset($this->lUser['admin']['rights'])) {
            if ($this->lUser['admin']['rights'] == 15) {
                if ($this->urlpart[3] != 'list') {
                    $id=substr($this->urlpart[4], 0, 24);

                }
                if (($this->urlpart[3] == 'delete') && ($id)) {
                    $this->pl->validate_csrfguard();
                    //var_dump($id);exit;
                    // $user['id']=$id;
                    // $user['backup']=true;
                    // $result=$this->lo->_deleteusers($user);
                    // $result=$this->lo->deleteallForms($user);
                } else if(($this->urlpart[3] == 'block') && ($id)) {
                    $user['id'] = $id;
                    $user['account'] = $this->urlpart[5];
                    $user['blocked'] = 1;
                    $result = $this->lo->blockUnblockUser($user);

                    $this->logEvent('User Blocked', $id, 'user blocked by admin');
                } else if(($this->urlpart[3] == 'unblock') && ($id)) {
                    $user['id'] = $id;
                    $user['account'] = $this->urlpart[5];
                    $user['blocked'] = 0;
                    $result = $this->lo->blockUnblockUser($user);

                    $this->logEvent('User Unblocked', $id, 'user Unblocked by admin');
                } else if(($this->urlpart[3] == 'events') && ($id)) {
                    $user['id'] = $id;
                    $this->events = $this->lo->getUserEvents($user);
                } else {
                    if (($this->urlpart[3] == 'take') && ($id)) {
                        $this->pl->validate_csrfguard();
                        //$this->lo->setUserSession(session_id(), $id);
                        $this->SetUserSession($id, 'Admin with Id '.substr($this->lUser['admin']['userId'], 0, 4).' takes user session');
                        $this->getAccount();
                    } else {
                        if (($this->urlpart[3] == 'release') && ($id)) {
                            $this->pl->validate_csrfguard();
                            $this->SetUserSession($this->lUser['admin']['userId'], 'Admin exits user session from Id '.$id);
                            $this->getAccount();
                        }
                    }
                }
            }
        }
        if ($id && $this->urlpart[3]<>'events') {
            header('location: '.$GLOBALS['level'].'admin/users/');
            exit;
        }
    }
    //

    function processTry() {
        if ($this->lUser) {
            header('location: /form/');
            exit;
        }

        //save account
        $account['id']=$this->pl->insertId();
        $result=$this->lo->saveAccount($account, 'PREVIEW');

        $newUserId=$this->pl->insertId();
        $tempEmail=$this->pl->insertId(16);
        $tempPassword=crypt('6hdd*ù£&jsç87%qZE', '$2a$07$9edf9384756gap1b49sj9xxx0ddkjsj7521038675sxgwjn38675sgwj34kdkaqop3946c38392021naqop3948484@8$');
        $token=$this->pl->insertId().$this->pl->insertId();

        //save user to sys_users
        $newUser['id']=$newUserId;
        $newUser['accountId']=$account['id'];
        $newUser['accountRights']=63;
        $newUser['email']=$tempEmail;
        $newUser['firstName']="";
        $newUser['lastName']="";
        $newUser['password']=$tempPassword;
        $newUser['password_token']=$token;
        $newUser['ip'] = $this->pl->getIp();

        $location = $this->pl->getUserLocation();

        if($location['city'] && $location['country_name']) {
            $newUser['location'] = $location['city'].' ,'.$location['country_name'];
        } else {
            $newUser['location']="unknown";
        }

        $newUser['timezone'] = $location['time_zone'];

        $newUser['dateformat']='dd/mm/yyyy';
        $newUser['referer']=$GLOBALS['sess']['referer'];
        $result=$this->lo->_saveUsers($newUser);
        $this->SetUserSession($newUser['id'], 'Try without loggin in');
        $this->lAccount=$this->getAccount();

        $newFormId = $this->pl->insertId();

        header('location: '.$GLOBALS['level'].'editor/'.$newFormId.'/#enew');
        exit;
    }

    // Person requests to signup (standalone without creating a survey)
    function processSignup() {
        $this->pl->csrfguard_start();
        $m="processsignup";
        if ($this->lUser) {
            header('location: /form/');
            exit;
        }
        if (isset($_GET['red'])) {
            $parts=explode('/', $_GET['red']);
        }
        if ($_POST) {
            if (!$this->uid) {
                if ($_POST['email']) {
                    $row=$this->lo->_getUsers(array('email' => $this->pl->Xssenc($_POST['email'])));

                }
                if (!$row[0]['_id']) {
                    if (($_POST['email']) && (strlen($this->pl->Xssenc($_POST['password'])) > 7) && (strlen($this->pl->Xssenc($_POST['firstname'])) > 1) && (strlen($this->pl->Xssenc($_POST['lastname'])) > 1)) {

                        $firstname = $this->pl->Xssenc($_POST['firstname']);
                        $lastname = $this->pl->Xssenc($_POST['lastname']);

                        //save account
                        $account['id']=$this->pl->insertId();
                        $account['name']=$firstname.' account';
                        $result=$this->lo->saveAccount($account);

                        $newUserId=$this->pl->insertId();
                        $token=$this->pl->insertId().$this->pl->insertId();

                        //save user to sys_users
                        $newUser['id']=$newUserId;
                        $newUser['accountId']=$account['id'];
                        $newUser['accountRights']=63;
                        $newUser['email']=$this->pl->Xssenc($_POST['email']);
                        $newUser['firstName']=$firstname;
                        $newUser['lastName']=$lastname;
                        $newUser['password']=crypt($this->pl->Xssenc($_POST['password']), '$2a$07$9edf9384756gap1b49sj9xxx0ddkjsj7521038675sxgwjn38675sgwj34kdkaqop3946c38392021naqop3948484@8$');
                        $newUser['password_token']=$token;
                        $newUser['ip'] = $this->pl->getIp();
                        $location = $this->pl->getUserLocation();
                        // $ipfound = json_decode(file_get_contents("http://freegeoip.net/json/{$newUser['ip']}"));
                        // if ($ipfound) {
                        //     if($ipfound->city && $ipfound->country_name) {
                        //         $newUser['location']=$ipfound->city.", ".$ipfound->country_name;
                        //     } else {
                        //         $newUser['location']="unknown";
                        //     }
                        //     $newUser['timezone']=$ipfound->time_zone;
                        // } else {
                        //     $newUser['location']="unknown";
                        //     $newUser['timezone']='';
                        // }

                        if($location['city'] && $location['country_name']) {
                            $newUser['location'] = $location['city'].' ,'.$location['country_name'];
                        } else {
                            $newUser['location']="unknown";
                        }

                        $newUser['timezone'] = $location['time_zone'];

                        $newUser['dateformat']='dd/mm/yyyy';
                        $newUser['referer']=$GLOBALS['sess']['referer'];
                        $result=$this->lo->_saveUsers($newUser);

                        $this->SetUserSession($newUser['id'], 'Signup session');
                        $this->lAccount=$this->getAccount();
                        $newUser['email']= trim(preg_replace('/\s+/','', $newUser['email']));
                        $newUser['email'] = substr($newUser['email'],0,254);
                        $this->pl->sendMail(array('ip'=>$newUser['ip']."-".$newUser['location'],'body' => $this->EmailSignup($newUser), 'from' => 'hello@formlets.com', 'to' => $newUser['email'], 'subject' => $this->pl->trans($m, 'Please validate your email so you can publish forms')));

                        $index=$GLOBALS['ref']['plan_lookup_name'][$_GET['ref']];
                        if ($index) {
                            header('location: /settings/subscription/change/'.$index.'/');
                        } else {
                            if (in_array($parts[1], $this->interfaces)) {
                                // TODO clean this $_GET['red']
                                header('location: '.$GLOBALS['level'].substr($_GET['red'], 1));
                            } else {
                                header('location: '.$GLOBALS['level'].'form/?newuser=true');
                            }
                        }

                        exit;
                    } else {
                        $this->errorMessage=$this->pl->trans($m, 'Please fill out FirstName, Lastname ,Email, and a password with minimal 8 characters');
                    }
                } else {
                    $this->errorMessage=$this->pl->trans($m, 'There is already an account on this email address,  please login or recover the password');
                }
            } else {
                $this->errorMessage=$this->pl->trans($m, 'No signup when you are already logged in ');

            }
        }
    }
    //

    // process password resend request
    function processPassword() {
        $m="processpassword";
        if ($_POST) {
            $email=filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
            if (!filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
                $username=$this->pl->Xssenc($email);
                $row=$this->lo->_getUsers(array('email' => $username));
                if ($row[0]['_id']) {
                    $newkey=$this->pl->insertId().$this->pl->insertId().$this->pl->insertId();
                    $user['id']=$row[0]['_id'];
                    $user['password_token']=$newkey;
                    $result=$this->lo->settokenUsers($user);
                    $this->pl->sendMail(array('body' => $this->EmailPassword($user), 'from' => 'hello@formlets.com', 'to' => $username, 'subject' => 'Reset password request'));
                }
                unset($row); /// just to be sure not to leak data

            }
            $this->okMessage=$this->pl->trans($m, 'Please check your email for your password reset');
        }
    }

    function processResendemailvalidation() {
        $m="processresendemailvalidation";
        if ($this->lUser['email']) {
            // we have a logged in user
            $email=$this->lUser['email'];
            $user['id']=$this->lUser['_id'];
        } else {
            if ($this->urlpart[2]) {
                // lookup user based on previous token id
                $user['id']=$this->urlpart[2];
                $row=$this->lo->_getUsers(array('id' => $user['id']));
                if ($row[0]) {
                    $email=$row[0]['email'];
                }
            }
        }
        if ($email) {
            $user['password_token']=$this->pl->insertId().$this->pl->insertId();
            $result=$this->lo->settokenUsers($user);
            $email = trim(preg_replace('/\s+/','', $email));
            $email = substr($email,0,254);
            $this->pl->sendMail(array('body' => $this->emailSignup($user, 'resend_validation'), 'from' => 'hello@formlets.com', 'to' => $email, 'subject' => 'Please validate your Email address'));
        }
        if ($this->lUser['_id']) {
            $this->pl->save_session('success_message', $this->pl->trans($m, 'The new email validation has been sent.'));
            header('Location: /form/');
        }

    }


    // a user wants to login
    function processLogin() {
        $this->pl->csrfguard_start();
        $m="processlogin";
        if (isset($_GET['red'])) {
            $parts=explode('/', $_GET['red']);
        }
        if (isset($this->lUser)) {
            if (in_array($parts[1], $this->interfaces)) {
                header('location: '.$GLOBALS['level'].substr($_GET['red'], 1));
            } else {
                header('location: '.$GLOBALS['level']);
            }
            exit;
        }
        if ($_POST) {
            $username=$this->pl->Xssenc($_POST['username']);
            $password=$this->pl->Xssenc($_POST['password']);
            if ($username != NULL && $password != NULL) {
                $row=$this->lo->_getUsers(array('email' => $username));
                if (count($row)) {
                    if (crypt($password, $row[0]['password']) == $row[0]['password']) {
                        $this->SetUserSession($row[0]['_id'], 'Login to new session');
                        $this->lAccount=$this->getAccount();
                        unset($row);
                        if (in_array($parts[1], $this->interfaces)) {
                            header('location: '.$GLOBALS['level'].substr($_GET['red'], 1));
                        } else {
                            header('location: '.$GLOBALS['level']);
                        }
                        exit;
                    } else {
                        $this->errorMessage=$this->pl->trans($m, 'Invalid email or password.');
                    }
                } else {
                    $this->errorMessage=$this->pl->trans($m, 'Invalid email or password.');
                }
            } else {
                $this->errorMessage=$this->pl->trans($m, 'Invalid email or password.');
            }
        }
    }
    //

    // a user requests to be logged out
    function processLogout() {
        $this->pl->validate_csrfguard();
        $session_id = session_id();
        $this->lo->deleteSession(array('session'=>$session_id));
        if (($this->lUser) && (in_array($_GET['red'], $this->interfaces))) {
            $this->pl->destroy_session();
            // you need a logged in user to access these pages
            header('location:'.$GLOBALS['level'].$this->pl->Xssenc($_GET['red']));
            exit;
        } else {
            $this->pl->destroy_session();
            header('location:'.$GLOBALS['level']);
            exit;
        }
    }
    //

    private function _zapierBasicAuth() {
        $m="zapierbasicauth";
        $username=null;
        $password=null;

        if($GLOBALS['conf']['env'] == 'local') {
            return $this->lUser;
        }

        // mod_php
        if (isset($_SERVER['PHP_AUTH_USER'])) {
            $username=$this->pl->Xssenc($_SERVER['PHP_AUTH_USER']);
            $password=$this->pl->Xssenc($_SERVER['PHP_AUTH_PW']);

            // most other servers
        } elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            if (strpos(strtolower($_SERVER['HTTP_AUTHORIZATION']), 'basic') === 0) {
                list($username, $password)=explode(':', base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'], 6)));
            }
        } elseif (isset($_GET['auth'])) { //for testing porpuses
            if (strpos(strtolower($_GET['auth']), 'basic') === 0) {
                list($username, $password)=explode(':', base64_decode(substr($_GET['auth'], 6)));
            }
        }

        if (empty($username) || empty($password)) {
            http_response_code(422);
            echo json_encode(array('response_code' => 422, 'message' => $this->pl->trans($m, 'Username or Password field is required')));
            exit;
        }

        $row=$this->lo->_getUsers(array('email' => $username));
        if (isset($row[0])) {
            if (crypt($password, $row[0]['password']) == $row[0]['password']) {
                return $row[0];
            } else {
                http_response_code(401);
                echo json_encode(array('response_code' => 401, 'message' => $this->pl->trans($m, 'Invalid password')));
                exit;
            }
        } else {
            http_response_code(401);
            echo json_encode(array('response_code' => 401, 'message' => $this->pl->trans($m, 'Email not found')));
            exit;
        }
    }

    function submission_data($submissions, $form, $user=null, $type='get', $single=false) {
        if (count($submissions) == 0) {
            $elements = $form['elements'];
            foreach ($elements as $element) {
                if($element['type'] != 'LABEL') {
                    $label=$element['inputLabel'];
                    if (!$label) {
                        $label=$element['label'];
                    }
                    if ($element['queryName']) {
                        $label=$element['queryName'];
                    }

                    $results[$ctr][$label]=null;
                }
            }

            $results[$ctr]['dateCreated']=(new DateTime())->format('c');

        } else {

            $results=[];
            $ctr=0;
            foreach ($submissions as $key => $submission) {
                $encrypted = $submission['encrypted'];
                $sdata=str_replace('\r\n', '<br>', $submission['data']);
                $sdata=str_replace('\\', '', $sdata);
                $submissions[$ctr]['data']=json_decode($sdata, true);
                if (!$submissions[$ctr]['data']) {
                    $submissions[$ctr]['data']=json_decode($submission['data'], true);
                }
                //var_dump($submissions[$ctr]['data']);exit;
                for ($i=0; $i < count($submissions[$key]['data']); $i++) {
                    $field=$submissions[$key]['data'][$i];
                    if($encrypted) {
                        $field['value'] = $this->pl->decrypt($field['value']);
                    }
                    $label=$field['label'];
                    $element = $this->pl->getElement($form['elements'], $field['_id']);

                    if(!$label && $element['label']) {
                        $label = $element['label'];
                    }

                    if ($element['queryName']) {
                        $label=$element['queryName'];
                    }

                    $label=trim($label);

                    if ($element['type'] == 'NAME') {
                        $values=explode(', ', $field['value']);
                        $count=count($values);
                        if ($count > 3) {
                            $results[$ctr][$label]=trim($values[0])." ".trim($values[1])." ".trim($values[2])." ".trim($values[3]);
                            $results[$ctr][$label.'_title']=trim($values[0]);
                            $results[$ctr][$label.'_firstname']=trim($values[1]);
                            $results[$ctr][$label.'_middlename']=trim($values[2]);
                            $results[$ctr][$label.'_lastname']=trim($values[3]);
                        } else {
                            if ($count > 2) {
                                if ($element['nameTitle']) {
                                    $results[$ctr][$label]=trim($values[0])." ".trim($values[1])." ".trim($values[2]);
                                    $results[$ctr][$label.'_title']=trim($values[0]);
                                    $results[$ctr][$label.'_firstname']=trim($values[1]);
                                    $results[$ctr][$label.'_lastname']=trim($values[2]);
                                } else {
                                    $results[$ctr][$label]=trim($values[0])." ".trim($values[1])." ".trim($values[2]);
                                    $results[$ctr][$label.'_firstname']=trim($values[0]);
                                    $results[$ctr][$label.'_middlename']=trim($values[1]);
                                    $results[$ctr][$label.'_lastname']=trim($values[2]);
                                }
                            } else {
                                $results[$ctr][$label]=$values[0]." ".$values[1];
                                $results[$ctr][$label.'_firstname']=$values[0];
                                $results[$ctr][$label.'_lastname']=$values[1];
                            }
                        }
                    } else {
                        if ($element['type'] == 'US_ADDRESS') {
                            $values=explode(', ', $field['value']);
                            $count=count($values);
                            if ($count > 5) {
                                if ($values[1] == '') {
                                    $results[$ctr][$label]=$values[0].", ".$values[2].", ".$values[3].", ".$values[4].", ".$values[5];
                                } else {
                                    $results[$ctr][$label]=$values[0].", ".$values[1].", ".$values[2].", ".$values[3].", ".$values[4].", ".$values[5];
                                }
                            } else {
                                if ($values[1] == '') {
                                    $results[$ctr][$label]=$values[0].", ".$values[2].", ".$values[3].", ".$values[4];
                                } else {
                                    $results[$ctr][$label]=$values[0].", ".$values[1].", ".$values[2].", ".$values[3].", ".$values[4];
                                }
                            }

                            $results[$ctr][$label.'_address1']=$values[0];
                            $results[$ctr][$label.'_address2']=$values[1];
                            $results[$ctr][$label.'_city']=$values[2];
                            $results[$ctr][$label.'_state']=$values[3];
                            $results[$ctr][$label.'_zip']=$values[4];
                            if ($count > 5) {
                                $results[$ctr][$label.'_country']=$values[5];
                            }
                        } else {
                            if ($element['type'] == 'DATE') {
                                $results[$ctr][$label]=$field['value'];
                                $results[$ctr][$label.'_ISO']=(new DateTime($field['value']))->format('c');
                                if ($user) {
                                    $dateformat = $this->pl->getUserDateFormat($user);
                                    $date=DateTime::createFromFormat($dateformat, $field['value']);
                                    if (!$date) {
                                        $date=new DateTime($field['value']);
                                    }
                                    if ($date) {
                                        $results[$ctr][$label.'_ISO']=$date->format('c');
                                    } else {
                                        $results[$ctr][$label.'_ISO']='';
                                    }
                                    //$results[$ctr][$label]=$date->format('c');
                                }
                            } else {
                                if ($element['type'] == 'DATETIME') {
                                    $results[$ctr][$label]=$field['value'];
                                    $results[$ctr][$label.'_ISO']=(new DateTime($field['value']))->format('c');
                                    if ($user) {
                                        $timeFormat='H:i';
                                        if ($element['use12Notation']) {
                                            $timeFormat='h:i A';
                                        }
                                        $dateformat = $this->pl->getUserDateFormat($user) . ' ' . $timeFormat;
                                        $date=DateTime::createFromFormat($dateformat, $field['value']);
                                        if (!$date) {
                                            $date=new DateTime($field['value']);
                                        }
                                        if ($date) {
                                            $results[$ctr][$label.'_ISO']=$date->format('c');
                                        } else {
                                            $results[$ctr][$label.'_ISO']='';
                                        }
                                        //$results[$ctr][$label]=$date->format('c');
                                    }
                                } else {
                                    if ($element['type'] == 'SIGNATURE') {
                                        //$results[$ctr][$label]=$this->pl->base64_to_jpeg($field['value'], 'test.jpg');
                                        //$results[$ctr][$label]=$GLOBALS['protocol'].'://'.$_SERVER['HTTP_HOST'].'/base64/'.$submission['_id'].'/'.$field['_id'].'/';
                                        $results[$ctr][$label]=$GLOBALS['protocol'].'://'.$_SERVER['HTTP_HOST'].'/file/'.$field['value'].'/';
                                    } else {
                                        if ($element['type'] == 'FILE') {
                                            $parts = explode(';;', $field['value']);
                                            if(count($parts) > 1 && isset($field['org_name'])) {
                                                $org_names = explode(';;', $field['org_name']);
                                                $files_str = '';
                                                foreach($parts as $file) {
                                                    $files_str.=$GLOBALS['protocol'].'://'.$_SERVER['HTTP_HOST'].'/file/'.$file.'/ \n ';
                                                }

                                                $results[$ctr][$label]=$files_str;
                                            } else {
                                                if($field['value']) {
                                                    $results[$ctr][$label]=$GLOBALS['protocol'].'://'.$_SERVER['HTTP_HOST'].'/file/'.$field['value'].'/';
                                                } else {
                                                    $results[$ctr][$label]='';
                                                }
                                            }
                                        } else {
                                            if ($field['label'] == 'dateCreated') {
                                                $date_array=date_parse($field['value']);
                                                $val=mktime($date_array['hour'], $date_array['minute'], $date_array['second'], $date_array['month'], $date_array['day'], $date_array['year']);
                                                $objDateTime=new DateTime($val);
                                                $results[$ctr][$label]=$objDateTime->format('c');
                                            } elseif (count($field['value']) > 1) {
                                                //TODO
                                                $results[$ctr][$label]=$field['value'];
                                            } else {
                                                $breaks=array("<br />", "<br>", "<br/>");
                                                $text=str_ireplace($breaks, "\n", $field['value']);
                                                $results[$ctr][$label]=$text;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }

                    $results[$ctr]['dateCreated']=(new DateTime($submissions['dateCreated']))->format('c');

                }

                $ctr++;

                if($single) {
                    break;
                }
            }

        }

        return $results;
    }

    function processSaveorremovetemplate() {
        $m="processsaveorremovetemplate";
        if (!$this->isAdmin) {
            echo $this->pl->trans($m, 'Permission denied');
            exit;
        }

        $data=array(
            'formid' => $this->urlpart[2]
        );

        if ($this->lo->isFormTemplate($data)) {
            $this->pl->save_session('success_message', $this->pl->trans($m, 'This form has been removed as a template'));
        } else {
            $this->pl->save_session('success_message', $this->pl->trans($m, 'This form has been set as a template'));
        }

        $this->lo->processFormTemplate($data);
        header('Location: /editor/'.$this->urlpart[2].'/#eelements');
        exit;
    }

    function processapi() {
        header('Content-Type: application/json');
        header('Access-Control-Allow-Methods: POST, PUT, DELETE, GET, OPTIONS');
        header('Access-Control-Allow-Headers: Authorization');

        if ($this->urlpart[2] == 'forms') {
            $user=$this->_zapierBasicAuth();
            $this->forms=$this->lo->_listForms(array('uid' => $user['_id']));
            http_response_code(200);
            //echo json_encode(array('response_code'=>200, 'message'=>'OK')); exit;
        } else {
            if ($this->urlpart[2] == 'zapier') {
                if ($this->urlpart[3] == 'hooks' && empty($this->urlpart[4])) {

                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                        $this->_zapierBasicAuth();

                        if (!empty($_POST['name']) && !empty($_POST['form']) && !empty($_POST['url'])) { //save hook
                            $data['id']=$this->pl->insertId();
                            $data['name']=$_POST['name'];
                            $data['form']=$_POST['form'];
                            $data['url']=$_POST['url'];
                            $data['append_data']=$_POST['append_data'];

                            $hook=$this->lo->saveZapierHook($data);
                        }

                        http_response_code(200);
                        echo json_encode(array('response_code' => 200, 'message' => 'OK'));
                        exit;
                    } else {
                        http_response_code(405);
                        echo json_encode(array('response_code' => 405, 'message' => 'Method not Allowed'));
                        exit;
                    }
                }

                //unsubscribe
                if ($this->urlpart[3] == 'hooks' && $this->urlpart[4] == 'unsubscribe') {
                    echo json_encode(array('response_code' => 200, 'message' => 'OK'));
                    exit;
                }

                if ($this->urlpart[3] == 'forms' && $this->urlpart[4] && $this->urlpart[5] == 'submissions') {
                    $user=$this->_zapierBasicAuth();
                    $data['formid']=$this->urlpart[4];

                    $this->form=$this->lo->getForm(array("form_id"=>$data['formid']));

                    $submissions=$this->lo->getSubmissions($data);
                    $results=$this->submission_data($submissions, $this->form, $user);

                    echo json_encode($results, JSON_UNESCAPED_UNICODE);
                    exit;
                }
            }
        }
    }

    function processNewaccount() {
        if($this->hasOwnAccount) {
            header('Location: /form/');exit;
        }

        $m='newaccount';
        if($_POST) {
            $accountname=$this->pl->Xssenc($_POST['accountname']);
            if(empty($accountname)) {
                $this->pl->save_session('error_message', $this->pl->trans($m, 'Account name is required'));
                header('Location: /newaccount/');exit;
            }

            $account = array(
                'id'=>$this->pl->insertId(),
                'name'=>$accountname
            );

            $saveAccount = $this->lo->saveAccount($account);

            $saveRights = $this->lo->saveRights(array(
                'id' => $this->pl->insertId(),
                'accountId'=> $account['id'],
                'userId'=>$this->lUser['_id'],
                'accountRights'=>63,
                'blocked'=>0
            ));

            $this->pl->save_session('success_message', $this->pl->trans($m, 'Account Created'));
            if(isset($_POST['switch'])) {
                header('Location: /switch/'.$account['id'].'/');exit;
            } else {
                header('Location: /newaccount/');exit;
            }
        }
    }

    function processAcceptinvite() {
        if(!$this->urlpart[2] || !$this->urlpart[3]) {
            $this->output404();
            exit;
        }

        header("Location: /switch/".$this->urlpart[2]."/".$this->urlpart[3]."/");
    }

    function processSwitch() {
        if(!$this->urlpart[2]) {
            $this->output404();
            exit;
        }

        $accountId = $this->urlpart[2];
        $user = $this->lUser['_id'];
        if($this->urlpart[3]) {
            $user = $this->urlpart[3];
            $row = $this->lo->_getUsers(array('id'=>$user));
            $this->SetUserSession($row[0]['_id'], 'Login via accept invitation');
        }
        $check = $this->lo->getAccounts(array('accountid'=>$accountId, 'userid'=>$user));
        if(count($check)) {
            //echo "yes";exit;
            if($this->urlpart[3]) {
                $this->lo->blockUnblockUser(array(
                    'id'=>$user,
                    'account'=>$accountId,
                    'blocked'=>0
                ));
            }
            $this->lAccount = $this->getAccount($accountId);
            header('location: /form/');
            exit;
        } else {
            $this->output404();
            exit;
        }
    }

    function processDeletefile() {
        if($this->urlpart[2]) {
            $file = $GLOBALS['conf']['filepath_fileupload'].'/'.$this->urlpart[2];
            if(file_exists($file)) {
                unlink($file);
            }
        }
    }

    function like_match($pattern, $subject) {
        $pattern = str_replace('%', '.*', preg_quote($pattern, '/'));
        return (bool) preg_match("/^{$pattern}$/i", $subject);
    }

    function processGetdatasource() {
        $lookupColumn = $_GET['c'];
        $val = $_GET['q'];
        if(!isset($lookupColumn) || !isset($val)) {
            $this->Output404();
        }
        if($this->urlpart[2]) {
            $datasource = $this->lo->getDatasource(array('id'=>$this->urlpart[2]));
            if(count($datasource)) {
                $ds = $datasource[0];

                $suggestions = array();
                $dd = array();
                $datas = json_decode($ds['data'], true);
                foreach($datas as $opt) {
                    $s = $opt[$lookupColumn];
                    if($this->like_match($val.'%',$s)) {
                        $suggestions[] = $s;
                        $dd[] = $opt;
                    }
                }

                $suggestions_string = implode(',', $suggestions);

                $response = array(
                    'columns'=>array("Label", "Value"),
                    'data' => $dd,
                    'dataList'=>$suggestions_string
                );

                //$ds['dataList'] = $suggestions_string;
                echo json_encode($response);exit;
            }
        }

        $default = array(
            'data'=>'[{"label":"Option 1"},{"label":"Option 2"}]',
            'dataList'=>"Option 1, Option 2",
            'columns'=>'["Label","Value"]'
        );
        echo json_encode($default);exit;
    }

    function processSecretfileuploadtest() {
        if($_FILES) {
            var_dump($_FILES);exit;
        }
    }

    function processUpdateautoresponder() {
        $this->lo->updateAutoresponders();
        echo 'ok';exit;
    }

}



?>
