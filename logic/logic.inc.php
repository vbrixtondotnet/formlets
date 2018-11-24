<?php

class Logic {

    // Construct
    public function __construct() {
        $this->defdb=$GLOBALS['defdb'];
        $this->methodlist=array('getAccount','getForm','saveForm','saveFormPage','getFormElement','createForm','listForm','deleteform','getTemplates','getFormPages','editFormElement','editElements','editAccount','getStripeConnect','saveTags','updatePages'); // methods available over API
        $this->alpha=array('','A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
        $this->pl=new Platform;

    }

    private function _getAccountId($array) {
        $aid=null;
        $uid = isset($array['uid']) ? $array['uid']:$array['user_id'];
        if(!$uid) {
            return null;
        } else {
            if ($GLOBALS['sess']['loginAccount']) {
                $aid=$GLOBALS['sess']['loginAccount']['_id'];
            } else {
                $accounts=$this->getAccounts(array('userid'=>$array['uid']));
                $aid=$accounts[0]['_id'];
            }
        }

        return $aid;
    }

    public function isValidTimeStamp($timestamp) {
        return ((string)(int)$timestamp===$timestamp)
            && ($timestamp<=PHP_INT_MAX)
            && ($timestamp>=~PHP_INT_MAX);
    }

    public function createEvent($data) {
        $sql="INSERT INTO `events` (`_id`,`userid`, `type`, `tag`)
                     VALUES (?,?,?,?)";

        $sqp=array("ssss",addslashes($data['_id']),addslashes($data['userid']),addslashes($data['type']),addslashes($data['tag'])); // s or i or b string or integer or blob
        $result=$this->opsDbQuery($sql,$sqp);
    }

    public function getUserEvents($array) {
        $sql="SELECT * FROM `events` WHERE userid=? ORDER BY inserttime DESC";
        $sqp=array("s",addslashes($array['id']));

        $rows = $this->getDbQuery($sql, $sqp);
        return $rows;
    }
//

//

    /**
     *
     * @param  String $sql
     * @param  Array $sqlp
     * @return Object
     */
    public function opsDbQuery($sql,$sqlp) {
        //echo $sql."<br>";
        for ($p=0;$p<count($sqlp);$p++) {
            $list[]=&$sqlp[$p];
        }
        $res=$this->defdb->prepare($sql);
        $ref=new ReflectionClass('mysqli_stmt');
        $method=$ref->getMethod("bind_param");
        if ($list) {
            $method->invokeArgs($res,$list);
        }
        $GLOBALS['bench_sql_list'][]=$sql;
        if ($_GET["bench"]=="y") {
            $GLOBALS['bench_sql_starttime'][]=round(benchit() - $GLOBALS['bench_start'],4);
            $res->execute();
            $GLOBALS['bench_sql_stoptime'][]=round(benchit() - $GLOBALS['bench_start'],4);
        } else {
            $res->execute();
        }

        return $res;
        $res->close();
    }
//


//

    public function getYesterdayNewUsers() {
        $sql="select email,emailVerified,ip from sys_users WHERE UNIX_TIMESTAMP(dateCreated) >= UNIX_TIMESTAMP(CAST(NOW() - INTERVAL 1 DAY AS DATE)) AND UNIX_TIMESTAMP(dateCreated) <= UNIX_TIMESTAMP(CAST(NOW() AS DATE))";
        $result=$this->getDbQuery($sql);
        return $result;
    }

//

    /**
     *
     * @param  String $sql
     * @param  Array $sqlp
     * @return Array
     */
    public function getDbQuery($sql,$sqlp=null) {
        $results=[];
        //echo $sql."<br>";
        if ($sqlp) {
            for ($p=0;$p<count($sqlp);$p++) {
                $list[]=&$sqlp[$p];
            }
            $res=$this->defdb->prepare($sql);
            $ref=new ReflectionClass('mysqli_stmt');
            $method=$ref->getMethod("bind_param");
            $method->invokeArgs($res,$list);
            $GLOBALS['bench_sql_list'][]=$sql;
            if ($_GET["bench"]=="y") {
                $GLOBALS['bench_sql_starttime'][]=round(benchit() - $GLOBALS['bench_start'],4);
                $res->execute();
                $GLOBALS['bench_sql_stoptime'][]=round(benchit() - $GLOBALS['bench_start'],4);
            } else {
                $res->execute();
            }

            $data=$res->result_metadata();
            $fields=array();
            $out=array();
            $count=1;

            while ($field=$data->fetch_field()) {
                $fields[$count]= &$out[$field->name];
                $count++;
            }
            call_user_func_array(array($res,'bind_result'),$fields);
            while ($res->fetch()) {
                $results[]=array_map(array($this,"copy_value"),$out);
            }
        } else {
            $res=$this->defdb->prepare($sql);
            $GLOBALS['bench_sql_list'][]=$sql;
            if ($_GET["bench"]=="y") {
                $GLOBALS['bench_sql_starttime'][]=round(benchit() - $GLOBALS['bench_start'],4);
                $res->execute();
                $GLOBALS['bench_sql_stoptime'][]=round(benchit() - $GLOBALS['bench_start'],4);
            } else {
                $res->execute();
            }
            $resu=$res->get_result();

            while ($row=$resu->fetch_assoc()) {
                $results[]=$row;
            }
        }
        return $results;
        /* close resultset */
        $res->close();
    }

    public function emailverified($uid) {
        $sql="select `emailVerified` from sys_users WHERE `_id`=?";
        $sqp=array("s",addslashes($uid)); // s or i or b string or integer or blob
        $result=$this->getDbQuery($sql,$sqp);
        if ($result[0]['emailVerified']==1) {
            return true;
        } else {
            return false;
        }
    }

    public function getNewUsersforwelcome() {
        $sql="SELECT * FROM sys_users where DATE_SUB(NOW(),INTERVAL 20 MINUTE) < `dateCreated` AND DATE_SUB(NOW(),INTERVAL 5 MINUTE) >= `dateCreated`";
        $result=$this->getDbQuery($sql);
        return $result;
    }

    /**
     *
     * @param  Array $data
     * @return Void
     */
    public function saveZapierHook($data) {
        $sql="INSERT INTO `hooks` (`_id`, `name`, `form`, `url`, `dateCreated`, `__v`)
                     VALUES (?,?,?,?,now(),?)";
        $sqp=array("sssss",addslashes($data['id']),addslashes($data['name']),addslashes($data['form']),addslashes($data['url']),addslashes($data['append_data'])); // s or i or b string or integer or blob

        //var_dump($sqp);exit;
        $result=$this->opsDbQuery($sql,$sqp);
    }

    /**
     * Used to get all hooks registered from the database
     * @param  Array $array
     * @return Array
     */
    public function getZapierHooks($array) {
        $result=[];
        if (isset($array['form_id'])) {
            $sql="SELECT * FROM `hooks` WHERE `form` = ?";
            $sqp=array("s",addslashes($array['form_id']));
            $result=$this->getDbQuery($sql,$sqp);
        }
        return $result;
    }

    /**
     * this function will be called when stripe passes a hook
     * @param Array $request an array from stripe API
     * @return  Void
     */
    public function _StripeWebHook($request) {
        if ($type!='invoice.payment_failed') {
            $object=$request['data']['object'];
            $card=$request['data']['object']['sources']['data'];
            $time=microtime();
            $type=$request['type'];

            if ($type==='customer.subscription.created' || $type==='customer.subscription.updated') {
                $sql="UPDATE `sys_accounts` set `accountStatus` = ?, `stripeSubscription` = ?, `planExpiration` = ?, `subscriptionWillRenew` = true where stripeCustomerId = ?";
                $sqp=array("ssss",addslashes($request['data']['object']['plan']['id']),json_encode($object),date('Y-m-d H:i:s',$request['data']['object']['current_period_end']),addslashes($request['data']['object']['customer']));
                $result=$this->opsDbQuery($sql,$sqp);
            }

            if ($type==='customer.subscription.deleted') {
                $sql="UPDATE `sys_accounts` set `accountStatus` = 'FREE', `stripeCustomerId` = '', `ccLast4`='', `ccBrand`='', `planExpiration`='' where stripeCustomerId = ?";
                $sqp=array("s",addslashes($request['data']['object']['customer']));
                $result=$this->opsDbQuery($sql,$sqp);
            }

        } else {
            $sql="UPDATE `sys_accounts` set `accountStatus` = 'FREE', `stripeCustomerId` = '', `ccLast4`='', `ccBrand`='' where stripeCustomerId = ?";
            $sqp=array("s",addslashes($request['data']['object']['customer']));
            $result=$this->opsDbQuery($sql,$sqp);
        }
    }

    /**
     * set forms active = 0 when possible based on their new subscription
     * @param  Array $user
     * @param  String $current_plan eg: BASIC, PRO, or PREMIUM
     * @return Void
     */
    public function updateFormsAfterDowngraded($user,$current_plan) {
        //get active forms
        $array['uid']=$user['_id'];
        if(!isset($array['accountId'])) {
            $array['accountId'] = $this->_getAccountId($array);
        }
        $array['active']=1;
        $forms=$this->_listForms($array);

        $account=$this->pl->planDetails($current_plan);
        $maxActive=$GLOBALS['ref']['plan_lists'][$account['index']]['maxActiveForms'];

        if (count($forms)>$maxActive) {
            $n=count($forms) - $maxActive;
            for ($x=$n - 1;$x>=0;$x--) {
                $sql="UPDATE `forms` SET `active`=0 WHERE `owner`=? and `accountId`=? and `_id`=?";
                $sqp=array("sss",addslashes($array['uid']),addslashes($array['accountId']),addslashes($forms[$x]['_id']));
                $result=$this->opsDbQuery($sql,$sqp);
            }
        }

        if($maxActive == 1 && $this->pl->formHasElement($forms[0], $GLOBALS['ref']['RESTRICTED_ELEMENTS_FOR_FREE_USERS'])) {
            $sql="UPDATE `forms` SET `active`=0 WHERE `owner`=? and `accountId`=? and `_id`=?";
            $sqp=array("sss",addslashes($array['uid']),addslashes($array['accountId']),addslashes($forms[0]['_id']));
            $result=$this->opsDbQuery($sql,$sqp);
        }

        if($maxActive == 1) {
            $sql="UPDATE `forms` SET `notifyUseTemplate`=0, `notifySubmitter`=0 WHERE `owner`=? and `accountId`=?";
            $sqp=array("ss",addslashes($array['uid']),addslashes($array['accountId']));
            $result=$this->opsDbQuery($sql,$sqp);
        }
    }

    public function updateFormStats($array) {
        if(!isset($array['accountId'])) {
            $array['accountId'] = $this->_getAccountId($array);
        }

        $sql="update `forms` f set f.stats=? where f.owner=? and f.accountId=? and f._id=?";
        $sqp=array("ssss",addslashes($array['stats']),addslashes($array['uid']),addslashes($array['accountId']),addslashes($array['form_id'])); // s or i or b string or integer or blob
        return $this->opsDbQuery($sql,$sqp);
    }

    public function listFormUsage($array) {
        if(!isset($array['accountId'])) {
            $array['accountId'] = $this->_getAccountId($array);
        }

        if($array['formId']) {
            if($array['yearMonth']) {
                $sql="select f._id, f.name, v.yearMonth, COALESCE(v.pageViewCount,0) as pageViewCount, COALESCE(v.responseCount,0) as responseCount from `forms` f left join `form_views` v on v.formId=f._id and v.yearMonth=? where f.owner=? and f.accountId=? and f._id=? order by f.dateCreated DESC";
                $sqp=array("ssss",addslashes($array['yearMonth']),addslashes($array['uid']),addslashes($array['accountId']),addslashes($array['formId'])); // s or i or b string or integer or blob
            } else {
                $sql="select f._id, f.name, v.yearMonth, COALESCE(v.pageViewCount,0) as pageViewCount, COALESCE(v.responseCount,0) as responseCount from `forms` f right join `form_views` v on v.formId=f._id where f.owner=? and f.accountId=? and f._id=? order by f.dateCreated DESC";
                $sqp=array("sss",addslashes($array['uid']),addslashes($array['accountId']),addslashes($array['formId'])); // s or i or b string or integer or blob
            }
        } else {
            $sql="select f._id, f.name, v.yearMonth, COALESCE(v.pageViewCount,0) as pageViewCount, COALESCE(v.responseCount,0) as responseCount from `forms` f left join `form_views` v on v.formId=f._id and v.yearMonth=? where f.owner=? and f.accountId=? order by f.dateCreated DESC";
            $sqp=array("sss",addslashes($array['yearMonth']),addslashes($array['uid']),addslashes($array['accountId'])); // s or i or b string or integer or blob
        }

        $rows=$this->getDbQuery($sql,$sqp);
        return $rows;
    }

    public function _listForms($array) {
        if(!isset($array['accountId'])) {
            $array['accountId'] = $this->_getAccountId($array);
        }
        if (array_key_exists('form_id',$array)) {
            if(empty($array['uid'])) {
                $sql="select f._id,f.responseStorage,f.responseStatusLists,f.stats,f.elements,f.name,f.views,f.type,f.active,f.dateCreated,s.submissionCount as submissions,s.seen as seen, round((f.views-s.submissionCount)/f.views*100,2) as bounce from `forms` f  left join (select count(*) as submissionCount,sum(seen) as seen,form from submissions group by form) s ON f._id = s.form where f._id=? and f.stats='public'";
                $sqp=array("s",addslashes($array['form_id'])); // s or i or b string or integer or blob
            } else {
                $sql="select f._id,f.responseStorage,f.responseStatusLists,f.stats,f.elements,f.name,f.views,f.type,f.active,f.dateCreated,s.submissionCount as submissions,s.seen as seen, round((f.views-s.submissionCount)/f.views*100,2) as bounce from `forms` f  left join (select count(*) as submissionCount,sum(seen) as seen,form from submissions group by form) s ON f._id = s.form where f.owner=? and f.accountId=? and f._id=?";
                $sqp=array("sss",addslashes($array['uid']),addslashes($array['accountId']),addslashes($array['form_id'])); // s or i or b string or integer or blob
            }

        } elseif (array_key_exists('active',$array)) {
            $sql="select f._id,f.responseStorage,f.responseStatusLists,f.name,f.views,f.type,f.active,f.dateCreated,s.submissionCount as submissions,s.seen as seen, round((f.views-s.submissionCount)/f.views*100,2) as bounce from `forms` f  left join (select count(*) as submissionCount,sum(seen) as seen,form from submissions group by form) s ON f._id = s.form where f.owner=? and f.accountId=? and f.active=1 order by dateCreated DESC";
            $sqp=array("ss",addslashes($array['uid']),addslashes($array['accountId'])); // s or i or b string or integer or blob
        } else {
            if (!empty($array['tag'])) {
                $addWhere="and f.tags LIKE ?";
                $params=array("sss",addslashes($array['uid']),addslashes($array['accountId']),'%'.addslashes($array['tag']).'%'); // s or i or b string or integer or blob
            } else {
                $addWhere="";
                $params=array("ss",addslashes($array['uid']),addslashes($array['accountId'])); // s or i or b string or integer or blob
            }
            if (!empty($array['sort'])) {
                $sort=explode('-',$array['sort']);
                $sql="select f._id,f.responseStorage,f.responseStatusLists,f.notifyUseTemplate,f.notifySubmitter,f.elements,f.tags,f.name,f.views,f.type,f.active,f.dateCreated,s.submissionCount as submissions,s.seen as seen, round((f.views-s.submissionCount)/f.views*100,2) as bounce, h.hooksCount from `forms` f  left join (select count(*) as submissionCount,sum(seen) as seen,form from submissions group by form) s ON f._id = s.form left join (select count(*) as hooksCount,form from hooks group by form) h ON h.form=f._id where f.owner=? and f.accountId=? ".$addWhere." order by f.".$sort[0]." ".$sort[1] . " , f.active DESC, f.dateCreated ASC";
                $sqp=$params;
            } else {
                $sql="select f._id,f.responseStorage,f.responseStatusLists,f.notifyUseTemplate,f.notifySubmitter,f.elements,f.tags,f.name,(IFNULL(f.views, 0) + v.pageViewCount) as views,f.type,f.active,f.dateCreated,s.submissionCount as submissions,s.seen as seen, round(((IFNULL(f.views, 0) + v.pageViewCount)-s.submissionCount)/(IFNULL(f.views, 0) + v.pageViewCount)*100,2) as bounce, h.hooksCount from `forms` f  left join (select count(*) as submissionCount,sum(seen) as seen,form from submissions group by form) s ON f._id = s.form left join (select count(*) as hooksCount,form from hooks group by form) h ON h.form=f._id left join(select sum(pageViewCount) as pageViewCount,formId from form_views group by formId) v ON f._id=v.formId where f.owner=? and f.accountId=? ".$addWhere. " order by f.active DESC, f.dateCreated ASC";
                $sqp=$params;
            }
        }
        $rows=$this->getDbQuery($sql,$sqp);
        return $rows;
    }

    public function _listIntegrations($array) {
        if(!isset($array['accountId'])) {
            $array['accountId'] = $this->_getAccountId($array);
        }

        if ($array['integration_id'] && $array['accountId'] && $array['uid']) {
            $sql="select * from form_configs d where d.owner=? and d.accountId=? and d._id=?";
            $sqp=array("sss",addslashes($array['uid']),addslashes($array['accountId']),addslashes($array['integration_id'])); // s or i or b string or integer or blob
        } else if($array['integration_id']) {
            $sql="select * from form_configs d where d._id=?";
            $sqp=array("s",addslashes($array['integration_id'])); // s or i or b string or integer or blob
        } else {
            $sql="select * from form_configs d where d.owner=? and d.accountId=?";
            $sqp=array("ss",addslashes($array['uid']),addslashes($array['accountId'])); // s or i or b string or integer or blob
        }

        $rows=$this->getDbQuery($sql,$sqp);
        return $rows;
    }

    public function saveIntegration($array) {
        if(!isset($array['accountId'])) {
            $array['accountId'] = $this->_getAccountId($array);
        }
        if($array['old_id']) {
            if($array['title']) {
                $sql="UPDATE form_configs SET `title`=?, `configs`=? WHERE _id=?";
                $sqp=array("sss",$array['title'],$array['configs'],addslashes($array['old_id']));
            } else {
                $sql="UPDATE form_configs SET `configs`=? WHERE _id=?";
                $sqp=array("ss",$array['configs'],addslashes($array['old_id']));
            }

            $result=$this->opsDbQuery($sql,$sqp);
        } else {
            $sql="INSERT INTO form_configs (`_id`,`owner`,`accountId`,`title`,`type`,`configs`) VALUES(?,?,?,?,?,?)";
            $sqp=array("ssssss",addslashes($array['id']),addslashes($array['uid']),addslashes($array['accountId']),addslashes($array['title']),addslashes($array['type']),$array['configs']);
            $result=$this->opsDbQuery($sql,$sqp);
        }
    }

    public function getIntegrationlink($array) {
        if(!isset($array['accountId'])) {
            $array['accountId'] = $this->_getAccountId($array);
        }

        if($array['formId'] && $array['elementId']) {
            $sql="select * from form_configs_link d where d.formId=? and d.elementId=?";
            $sqp=array("ss",addslashes($array['formId']),addslashes($array['elementId'])); // s or i or b string or integer or blob
        } else if($array['elementId']) {
            $sql="select * from form_configs_link d where d.elementId=?";
            $sqp=array("s",addslashes($array['elementId'])); // s or i or b string or integer or blob
        } else if($array['formId']) {
            $sql="select * from form_configs_link d where d.formId=?";
            $sqp=array("s",addslashes($array['formId'])); // s or i or b string or integer or blob
        } else if($array['formConfigsId']) {
            $sql="select d.*, f.name as form_name from form_configs_link d LEFT JOIN forms f ON f._id=d.formId where d.formConfigsId=?";
            $sqp=array("s",addslashes($array['formConfigsId'])); // s or i or b string or integer or blob
        } else {
            $sql="select * from form_configs_link d where d._id=?";
            $sqp=array("s",addslashes($array['id'])); // s or i or b string or integer or blob
        }

        $rows=$this->getDbQuery($sql,$sqp);
        return $rows;
    }

    public function saveIntegrationlink($array) {
        $sql="INSERT INTO form_configs_link (`_id`,`formId`,`elementId`,`formConfigsId`) VALUES(?,?,?,?)";
        $sqp=array("ssss",addslashes($array['id']),addslashes($array['formId']),addslashes($array['elementId']),addslashes($array['formConfigsId']));
        $result=$this->opsDbQuery($sql,$sqp);
    }

    public function removeIntegrationlink($array) {
        $sql="DELETE FROM form_configs_link WHERE _id=?";
        $sqp=array("s",$array['id']);
        $result=$this->opsDbQuery($sql,$sqp);
    }

    public function _listDatasources($array) {
        if(!isset($array['accountId'])) {
            $array['accountId'] = $this->_getAccountId($array);
        }

        if ($array['source_id'] && $array['accountId'] && $array['uid']) {
            $sql="select d.*, l.count from datasources d left join(select count(*) as count, datasourceId from datasources_link group by datasourceId) l on d._id=l.datasourceId where d.owner=? and d.accountId=? and d._id=?";
            $sqp=array("sss",addslashes($array['uid']),addslashes($array['accountId']),addslashes($array['source_id'])); // s or i or b string or integer or blob
        } else if($array['source_id']) {
            $sql="select d.*, l.count from datasources d left join(select count(*) as count, datasourceId from datasources_link group by datasourceId) l on d._id=l.datasourceId where d._id=?";
            $sqp=array("s",addslashes($array['source_id'])); // s or i or b string or integer or blob
        } else {
            $sql="select d.*, l.count from datasources d left join(select count(*) as count, datasourceId from datasources_link group by datasourceId) l on d._id=l.datasourceId where d.owner=? and d.accountId=?";
            $sqp=array("ss",addslashes($array['uid']),addslashes($array['accountId'])); // s or i or b string or integer or blob
        }

        $rows=$this->getDbQuery($sql,$sqp);
        return $rows;
    }

    public function removeDatasource($array) {
        if(!isset($array['accountId'])) {
            $array['accountId'] = $this->_getAccountId($array);
        }

        $sql="DELETE FROM datasources WHERE _id=? AND accountId=? AND owner=?";
        $sqp=array("sss", addslashes($array['id']), addslashes($array['accountId']), addslashes($array['uid']));
        $result = $this->opsDbQuery($sql, $sqp);
        return $result;
    }

    public function getDatasourcelink($array) {
        if(!isset($array['accountId'])) {
            $array['accountId'] = $this->_getAccountId($array);
        }

        if($array['formId'] && $array['elementId']) {
            $sql="select dl.*, d.data, d.columns, d.title from datasources d join datasources_link dl on d._id=dl.datasourceId WHERE dl.formId=? and dl.elementId=?";
            $sqp=array("ss",addslashes($array['formId']),addslashes($array['elementId'])); // s or i or b string or integer or blob
        } else if($array['elementId']) {
            $sql="select dl.*, d.data, d.columns, d.title from datasources d join datasources_link dl on d._id=dl.datasourceId WHERE dl.elementId=?";
            $sqp=array("s",addslashes($array['elementId'])); // s or i or b string or integer or blob
        } else if($array['formId']) {
            //$sql="select * from datasources_link d where d.formId=?";
            $sql="select dl.*, d.data, d.columns, d.title from datasources d join datasources_link dl on d._id=dl.datasourceId WHERE dl.formId=?";
            $sqp=array("s",addslashes($array['formId'])); // s or i or b string or integer or blob
        } else if($array['datasourceId']) {
            $sql="select d.*, f.name as form_name from datasources_link d LEFT JOIN forms f ON f._id=d.formId where d.datasourceId=?";
            $sqp=array("s",addslashes($array['datasourceId'])); // s or i or b string or integer or blob
        } else {
            $sql="select dl.*, d.data, d.columns, d.title from datasources d join datasources_link dl on d._id=dl.datasourceId WHERE dl._id=?";
            $sqp=array("s",addslashes($array['id'])); // s or i or b string or integer or blob
        }

        $rows=$this->getDbQuery($sql,$sqp);
        return $rows;
    }

    public function getDatasource($array) {
        if($array['id']) {
            $sql = "select d.data, d.columns from datasources d where d._id=?";
            $sqp=array("s", addslashes($array['id']));

            $rows=$this->getDbQuery($sql,$sqp);
            return $rows;
        }
    }

    public function saveDatasource($array) {
        if(!isset($array['accountId'])) {
            $array['accountId'] = $this->_getAccountId($array);
        }
        if($array['old_id']) {
            if($array['title']) {
                $sql="UPDATE datasources SET `title`=?, `data`=?, `columns`=? WHERE _id=?";
                $sqp=array("ssss",$array['title'],$array['data'],$array['columns'],addslashes($array['old_id']));
            } else {
                $sql="UPDATE datasources SET `data`=? WHERE _id=?";
                $sqp=array("ss",$array['data'],addslashes($array['old_id']));
            }

            $result=$this->opsDbQuery($sql,$sqp);
        } else {
            $sql="INSERT INTO datasources (`_id`,`owner`,`accountId`,`title`,`data`,`columns`) VALUES(?,?,?,?,?,?)";
            $sqp=array("ssssss",addslashes($array['id']),addslashes($array['uid']),addslashes($array['accountId']),addslashes($array['title']),$array['data'],$array['columns']);
            $result=$this->opsDbQuery($sql,$sqp);
        }
    }

    public function getDatasourceConnector($array) {
        if($array['formId'] && $array['accountId']) {
            $sql="select * from datasource_response_connector c where c.formId=? and c.accountId=? ORDER BY dateCreated";
            $sqp=array("ss",addslashes($array['formId']),addslashes($array['accountId'])); // s or i or b string or integer or blob
        } else if($array['formId']) {
            $sql="select * from datasource_response_connector c where c.formId=? ORDER BY dateCreated";
            $sqp=array("s",addslashes($array['formId'])); // s or i or b string or integer or blob
        } else if($array['elementId'] && $array['datasourceId']) {
            $sql="select * from datasource_response_connector c where c.elementId=? and c.datasourceId=?  ORDER BY dateCreated";
            $sqp=array("ss",addslashes($array['elementId']),addslashes($array['datasourceId'])); // s or i or b string or integer or blob
        }

        $rows=$this->getDbQuery($sql,$sqp);
        return $rows;
    }

    public function saveDatasourceConnector($array) {
        if(!isset($array['accountId'])) {
            $array['accountId'] = $this->_getAccountId($array);
        }
        if($array['old_formId']) {
            $sql="UPDATE datasource_response_connector SET import=? WHERE formId=?";
            $sqp=array("is", addslashes($array['import']), addslashes($array['old_formId']));
            $result = $this->opsDbQuery($sql, $sqp);
        } else {
            $sql="INSERT INTO datasource_response_connector (`_id`, `formId`, `accountId`, `elementId`, `datasourceId`, `datasourceColumn`) VALUES(?,?,?,?,?,?)";
            $sqp=array("ssssss", addslashes($array['id']), addslashes($array['formId']), addslashes($array['accountId']), addslashes($array['elementId']), addslashes($array['datasourceId']), addslashes($array['datasourceColumn']));
            $result = $this->opsDbQuery($sql, $sqp);
        }

    }

    public function removeDatasourceConnector($array) {
        if($array['id']) {
            $sql="DELETE FROM datasource_response_connector WHERE _id=?";
            $sqp=array("s",$array['id']);
            $result=$this->opsDbQuery($sql,$sqp);
        } else if($array['formId']) {
            $sql="DELETE FROM datasource_response_connector WHERE formId=?";
            $sqp=array("s",$array['formId']);
            $result=$this->opsDbQuery($sql,$sqp);
        }
    }

    public function saveDatasourcelink($array) {
        if($array['update']) {
            $sql="UPDATE datasources_link SET datasourceId=? WHERE elementId=? AND formId=?";
            $sqp=array("sss",addslashes($array['datasourceId']),addslashes($array['elementId']),addslashes($array['formId']));
            $result=$this->opsDbQuery($sql,$sqp);
        } else {
            $sql="INSERT INTO datasources_link (`_id`,`formId`,`elementId`,`datasourceId`) VALUES(?,?,?,?)";
            $sqp=array("ssss",addslashes($array['id']),addslashes($array['formId']),addslashes($array['elementId']),addslashes($array['datasourceId']));
            $result=$this->opsDbQuery($sql,$sqp);
        }
    }

    public function removeDatasourcelink($array) {
        $sql="DELETE FROM datasources_link WHERE _id=?";
        $sqp=array("s",$array['id']);
        $result=$this->opsDbQuery($sql,$sqp);
    }

    public function getAccounts($array) {
        if ($array['accountid'] && $array['userid']) {
            $sql="select a.*, r.accountRights, r.permissions, r.blocked from `sys_accounts` a, `sys_rights` r where r.accountId=a._id and r.accountId=? and r.userId=?";
            $sqp=array("ss",addslashes($array['accountid']),addslashes($array['userid'])); // s or i or b string or integer or blob
        } elseif ($array['accountid']) {
            $sql="select a.*, r.accountRights, r.permissions, r.blocked from `sys_accounts` a, `sys_rights` r where r.accountId=a._id and a._id=?";
            $sqp=array("s",addslashes($array['accountid'])); // s or i or b string or integer or blob
        } elseif ($array['userid']) {
            $sql="select a.*, r.accountRights, r.permissions, r.blocked from `sys_accounts` a, `sys_rights` r where r.accountId=a._id and r.userId=? ORDER BY r.dateCreated";
            $sqp=array("s",addslashes($array['userid'])); // s or i or b string or integer or blob
        }
        $rows=$this->getDbQuery($sql,$sqp);
        return $rows;
    }
//

//

    /**
     * @param  String $uid
     * @param  Array $details
     * @return void
     */
    public function _saveStripeId($uid,$details) {
        $sql="UPDATE `sys_accounts` set `stripeCustomerId` = ?, `ccBrand`= ?, `ccLast4` = ? where _id = ?";
        $sqp=array("ssss",addslashes($details['customer_id']),addslashes($details['cc_brand']),addslashes($details['cc_last_4']),addslashes($uid));
        $result=$this->opsDbQuery($sql,$sqp);
    }

    /**
     *
     * @return Array
     */
    public function getCategory() {
        $sql="SELECT name,_id FROM faq_categories order by name asc";
        $result=$this->getDbQuery($sql);
        return $result;
    }

    /**
     *
     * @param  Array $request
     * @param  String $url
     * @return Mixed
     */
    public function saveFaq($request) {
        if ($request['state']=="new") {
            $sql="INSERT INTO `faq_article` (`_id`, `category`, `url`, `intro`, `title`, `img1`, `body1`,`img2`, `body2`, `lang`,`dateCreated`)
                     VALUES (?,?,?,?,?,?,?,?,?,?,now())";
            $sqp=array("ssssssssss",addslashes($request['_id']),addslashes($request['category']),addslashes($request['url']),addslashes($request['intro']),addslashes($request['title']),addslashes($request['img1']),addslashes($request['body1']),addslashes($request['img2']),addslashes($request['body2']),addslashes($request['lang'])); // s or i or b string or integer or blob
        } else {
            $sql="UPDATE `faq_article` set `category` = ?, `url` = ?, `title` = ?, `intro` = ?, `img1` = ?, `body1` = ?, `img2` = ?, `body2` = ?, `lang` = ? where _id = ?";
            $sqp=array("ssssssssss",addslashes($request['category']),addslashes($request['url']),addslashes($request['title']),addslashes($request['intro']),addslashes($request['img1']),addslashes($request['body1']),addslashes($request['img2']),addslashes($request['body2']),addslashes($request['lang']),addslashes($request['_id'])); // s or i or b string or integer or blob
        }

        return $this->opsDbQuery($sql,$sqp);
    }

    /**
     *
     * @param  Array $request
     * @param  String $url
     * @return Mixed
     */
    public function saveFeatures($request) {
        if ($request['state']=="new") {
            $sql="INSERT INTO `features` (`_id`, `title`, `url`, `body`, `body2`, `img1`, `img2`)
                     VALUES (?,?,?,?,?,?,?)";
            $sqp=array("sssssss",addslashes($request['id']),addslashes($request['title']),addslashes($request['url']),$request['body'],$request['body2'],$request['img1'],$request['img2']); // s or i or b string or integer or blob
        } else {
            $sql="UPDATE `features` set `url` = ?, `title` = ?, `body` = ? , `body2` = ?, `img1` = ?, `img2` = ? where _id = ?";
            $sqp=array("sssssss",addslashes($request['url']),addslashes($request['title']),$request['body'],$request['body2'],$request['img1'],$request['img2'],addslashes($request['id'])); // s or i or b string or integer or blob
        }

        $result = $this->opsDbQuery($sql,$sqp);
        //var_dump($result);exit;
        return $result;
    }


    /**
     *
     * @param  Array $array
     * @return Array
     */
    public function getFeatures($array=null) {
        if (isset($array['url'])) {
            $sql="SELECT * FROM `features` WHERE `url` = ?";
            $sqp=array("s",addslashes($array['url']));
            $result=$this->getDbQuery($sql,$sqp);
            $result=$result[0];
        } else if (isset($array['id'])) {
            $sql="SELECT * FROM `features` WHERE `_id` = ?";
            $sqp=array("s",addslashes($array['id']));
            $result=$this->getDbQuery($sql,$sqp);
            $result=$result[0];
        } else {
            $sql="SELECT * FROM `features` order by title";
            $sqp=array();
            $result=$this->getDbQuery($sql,$sqp);
        }
        return $result;
    }

    /**
     *
     * @param  Array $array
     * @return Array
     */
    public function getFaqDetail($array=null) {
        if (isset($array['url'])) {
            $sql="SELECT * FROM `faq_article` WHERE `url` = ? AND `lang` = ?";
            $sqp=array("ss",addslashes($array['url']),addslashes($array['lang']));
            $result=$this->getDbQuery($sql,$sqp);
            $result=$result[0];
        } elseif (isset($array['id'])) {
            $sql="SELECT * FROM `faq_article` WHERE `_id` = ?";
            $sqp=array("s",addslashes($array['id']));
            $result=$this->getDbQuery($sql,$sqp);
            $result=$result[0];
        }
        return $result;
    }

    public function getFaqList($array=null) {
        $sql="SELECT fa.*,fc.name as catname FROM faq_article fa  join faq_categories fc ON fc._id = fa.category  where fa.lang=? order by catname,title";
        $sqp=array("s",addslashes($array['lang']));
        $result=$this->getDbQuery($sql,$sqp);
        return $result;
    }

    /**
     *
     * @param  String $id
     * @return Mixed
     */
    public function deleteFaq($id) {
        $sql="DELETE from `faq_article` WHERE `_id`=?";
        $sqp=array("s",addslashes($id)); // s or i or b string or integer or blob
        $result=$this->opsDbQuery($sql,$sqp);
    }

    /**
     *
     * @param  String $id
     * @return Mixed
     */
    public function deleteFeatures($id) {
        $sql="DELETE from `features` WHERE `_id`=?";
        $sqp=array("s",addslashes($id)); // s or i or b string or integer or blob
        $result=$this->opsDbQuery($sql,$sqp);
    }

    /**
     *
     * @param  Array $sqp
     * @return Array
     */
    public function getStats($sqp=null) {
        $sql="SELECT YEAR(`dateCreated`) AS YEAR, MONTH(  `dateCreated` ) AS MONTH , COUNT( * ) AS count FROM  `sys_users` GROUP BY YEAR(  `dateCreated` ) , MONTH(  `dateCreated` ) ORDER BY YEAR DESC ,MONTH DESC LIMIT 0 , 36";
        $rows['users']=$this->getDbQuery($sql,$sqp);
        $sql="SELECT YEAR( dateCreated ) AS YEAR, MONTH( dateCreated ) AS MONTH , COUNT( * ) AS count , COUNT( DISTINCT (owner)) AS  `unique owner` FROM  `forms` GROUP BY YEAR( dateCreated ) , MONTH( dateCreated ) ORDER BY YEAR DESC , MONTH DESC LIMIT 0 , 36";
        $rows['forms']=$this->getDbQuery($sql,$sqp);
        $sql="SELECT YEAR( s.dateCreated ) AS YEAR, MONTH( s.dateCreated ) AS MONTH , COUNT( * ) AS count, COUNT( DISTINCT (s.form)) AS  `unique forms` , COUNT( DISTINCT (f.owner)) AS  `unique owner` FROM  `submissions` s JOIN `forms` f on f._id=s.form GROUP BY YEAR( s.dateCreated ) , MONTH( s.dateCreated ) ORDER BY YEAR DESC , MONTH DESC LIMIT 0 , 36";
        //$sql="SELECT YEAR( s.dateCreated ) AS YEAR, MONTH( s.dateCreated ) AS MONTH , COUNT( * ) AS count FROM  `submissions` s GROUP BY YEAR( s.dateCreated ) , MONTH( s.dateCreated ) ORDER BY YEAR DESC , MONTH DESC LIMIT 0 , 36";
        $rows['submissions']=$this->getDbQuery($sql,$sqp);
        return $rows;
    }

    public function getPhishingForms() {
        $blacklisted=join('|',$GLOBALS['ref']['blacklisted_elements']);
        $blacklisted=strtolower($blacklisted);
        $sql="SELECT f._id as ID, f.name, r.blocked, f.dateCreated FROM  `forms` f left join `sys_rights` r ON r.userId=f.owner WHERE LOWER(elements) REGEXP '$blacklisted' order by dateCreated desc limit 0,50";
        $rows['forms']=$this->getDbQuery($sql,$sqp);
        return $rows;
    }

    public function getCCForms() {
        $blacklisted=join('|',$GLOBALS['ref']['credit_card_detector']);
        $blacklisted=strtolower($blacklisted);
        $sql="SELECT f._id as ID, f.name, r.blocked, f.dateCreated FROM  `forms` f left join `sys_rights` r ON r.userId=f.owner WHERE LOWER(elements) REGEXP '$blacklisted' order by dateCreated desc";
        $rows['forms']=$this->getDbQuery($sql,$sqp);
        return $rows;
    }

    public function getFormsWithImages($array) {
        $page=1;
        if (!empty($array['page'])) {
            $bool=(!is_int($array['page']) ? (ctype_digit($array['page'])) : true);
            if (false===$page) {
                $page=1;
            } else {
                $page=$array['page'];
            }
        }
        // set the number of items to display per page
        $items_per_page=25;

        $regex = '%"picture":"%.jpg"%';
        $sql="SELECT f._id as ID, f.name, f.dateCreated, f.elements, f.logo, r.blocked FROM  `forms` f left join `sys_rights` r ON f.owner=r.userId AND f.accountId=r.accountId WHERE (f.elements LIKE '$regex' OR (f.logo is not null AND f.logo<>''))  order by f.dateCreated desc";
        //$rows['forms']=$this->getDbQuery($sql,$sqp);
        //return $rows;

        $sqp=array(); // s or i or b string or integer or blob
        $rows=$this->getDbQuery($sql,$sqp);
        $row_count=count($rows);
        $page_count=0;
        if ($row_count) {
            $page_count=(int)ceil($row_count / $items_per_page);
            // double check that request page is in range
            if ($page>$page_count) {
                // error to user, maybe set page to 1
                $page=1;
            }
        }

        // build query
        $offset=($page - 1) * $items_per_page;

        $sql="SELECT f._id as ID, f.name, f.dateCreated, f.elements, f.logo, r.blocked FROM  `forms` f left join `sys_rights` r ON f.owner=r.userId AND f.accountId=r.accountId WHERE (f.elements LIKE '$regex' OR (f.logo is not null AND f.logo<>''))  ORDER BY f.dateCreated DESC limit ".$offset.",".$items_per_page;

        $sqp=array(); // s or i or b string or integer or blob
        $rows=$this->getDbQuery($sql,$sqp);

        return array("data"=>$rows,"page_count"=>$page_count,"page"=>$page,"rows_count"=>$row_count);
    }

    /**
     * Add and Edit users
     * @param  Array $array
     * @param  string $accountStatus
     * @return Mixed
     */

    public function _updateUsers($array) {
        //var_dump($array);exit;
        $sql="UPDATE `sys_users` SET `firstName`=?, `lastName`=?, `email`=?, `emailVerified`=?, `phone`=?, `timezone`=?, `dateformat`=?, `use12hr`=? WHERE `_id`=?";
        $sqp=array("sssssssss",addslashes($array['firstName']),addslashes($array['lastName']),addslashes($array['email']),addslashes($array['emailVerified']),addslashes($array['phone']),addslashes($array['timezone']),addslashes($array['dateformat']),addslashes($array['timeformat']),addslashes($array['_id']));
        $result=$this->opsDbQuery($sql,$sqp);
        return $result;
    }

    public function _updateUserPassword($array) {
        $sql="UPDATE `sys_users` SET `password`=? WHERE `_id`=?";
        $sqp=array("ss",addslashes($array['password']),addslashes($array['_id']));
        $result=$this->opsDbQuery($sql,$sqp);
        return $result;
    }

    public function saveAccount($array,$accountStatus='FREE') {
        $sql="INSERT INTO `sys_accounts` (`_id`, `accountStatus`, `companyName`)
             VALUES (?,?,?)";
        $sqp=array("sss",
            addslashes($array['id']),
            addslashes($accountStatus),
            addslashes($array['name']));

        $result=$this->opsDbQuery($sql,$sqp);
        return $result;
    }

    public function updateAccount($array) {
        $sql="UPDATE `sys_accounts` SET accountStatus=?, stripeSubscription=?, planExpiration=?, stripeCustomerId=?, ccBrand=?, ccLast4=? WHERE `_id`=?";
        $array['planExpiration'] = $array['planExpiration'] ?: date('Y-m-d H:i:s');
        $sqp=array("sssssss",
            addslashes($array['accountStatus']),
            addslashes($array['stripeSubscription']),
            addslashes($array['planExpiration']),
            addslashes($array['stripeCustomerId']),
            addslashes($array['ccBrand']),
            addslashes($array['ccLast4']),
            addslashes($array['id'])
        );
        $result=$this->opsDbQuery($sql,$sqp);

        return $result;
    }

    public function updateFormsEmailAfterRegister($array) {
        $sql="UPDATE `forms` SET email=? WHERE accountId=? AND owner=?";
        $sqp=array("sss",addslashes($array['email']),addslashes($array['accountId']),addslashes($array['owner']));
        $result=$this->opsDbQuery($sql,$sqp);

        return $result;
    }

    public function saveRights($array) {
        $sql_a="INSERT INTO `sys_rights` (`_id`, `accountId`, `userId`, `accountRights`, `permissions`,`blocked`)
         VALUES (?,?,?,?,?,?)";

        $sqp_a=array("ssssss",
            addslashes($array['id']),
            addslashes($array['accountId']),
            addslashes($array['userId']),
            addslashes($array['accountRights']),
            $array['permissions'],
            addslashes($array['blocked'])
        );

        $result_a=$this->opsDbQuery($sql_a,$sqp_a);
    }

    public function saveCompanyDetails($array) {
        $sql="UPDATE `sys_accounts` SET `companyName`=? WHERE `_id`=?";
        $sqp=array("ss",addslashes($array['companyName']),addslashes($array['accountId']));
        $result=$this->opsDbQuery($sql,$sqp);
        return $result;
    }

    public function _saveUsers($array) {
        $array['emailVerified']=isset($array['emailVerified']) ? $array['emailVerified'] : 0;

        $sql="INSERT INTO `sys_users` (`_id`, `email`, `password`, `ip`, `firstName`, `lastName`, `hybridauth_provider_name`, `hybridauth_provider_uid`, `emailVerified`, `emailValidationToken`, `location`, `timezone`, `dateformat`, `referer`)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $sqp=array("ssssssssssssss",
            addslashes($array['id']),
            addslashes($array['email']),
            addslashes($array['password']),
            addslashes($array['ip']),
            addslashes($array['firstName']),
            addslashes($array['lastName']),
            addslashes($array['provider']),
            addslashes($array['identifier']),
            addslashes($array['emailVerified']),
            addslashes($array['password_token']),
            addslashes($array['location']),
            addslashes($array['timezone']),
            addslashes($array['dateformat']),
            addslashes($array['referer']));

        $result=$this->opsDbQuery($sql,$sqp);


        if (isset($array['accountId']) && isset($array['accountRights'])) {
            $sql_a="INSERT INTO `sys_rights` (`_id`, `accountId`, `userId`, `accountRights`, `permissions`)
             VALUES (?,?,?,?,?)";

            $sqp_a=array("sssss",
                $this->pl->insertId(),
                addslashes($array['accountId']),
                addslashes($array['id']),
                addslashes($array['accountRights']),
                $array['permissions']
            );

            $result_a=$this->opsDbQuery($sql_a,$sqp_a);
        }

        return $result;
    }
//


//

    public function saveTags($array) {
        $sql="UPDATE forms SET `tags`=? where `_id`=?";
        $sqp=array("ss",addslashes($array['tags']),addslashes($array['formid']));
        $rows=$this->opsDbQuery($sql,$sqp);
        return $rows;
    }

    public function listTemplates($array) {
        if(!isset($array['accountId'])) {
            $array['accountId'] = $this->_getAccountId($array);
        }
        if ($array['id']) {
            $sql="select e.*, f.name as form_name, f.notifySubmitter, f.notifyUseTemplate from `email_templates` e  left join forms f ON f._id = e.form_id where e.owner=? and e.accountId=? and e._id=?";
            $sqp=array("sss",addslashes($array['uid']),addslashes($array['accountId']),addslashes($array['id'])); // s or i or b string or integer or blob
        } else {
            $sql="select e.*, f.name as form_name, f.notifySubmitter, f.notifyUseTemplate from `email_templates` e  left join forms f ON f._id = e.form_id where e.owner=? and e.accountId=?";
            $sqp=array("ss",addslashes($array['uid']),addslashes($array['accountId'])); // s or i or b string or integer or blob
        }

        $rows=$this->getDbQuery($sql,$sqp);

        return $rows;
    }

    /**
     * [deleteTemplate description]
     * @param  [type] $array [description]
     * @return [type]        [description]
     */
    public function deleteTemplate($array) {
        if(!isset($array['accountId'])) {
            $array['accountId'] = $this->_getAccountId($array);
        }
        $sql="delete from `email_templates` where `_id`=? and `owner`=? and `accountId`=?";
        $sqp=array("sss",addslashes($array['id']),addslashes($array['user_id']),addslashes($array['accountId'])); // s or i or b string or integer or blob
        $result=$this->opsDbQuery($sql,$sqp);
        return $result;
    }

    public function saveEmailTemplateHTML($array) {
        $array['uid']=$GLOBALS['sess']['loginUser']['_id'];

        $sql="UPDATE `email_templates` SET `templateHTML`=? WHERE `_id`=? AND `owner`=?";
        $sqp=array("sss", $array['html'], $array['id'],$array['uid']); // s or i or b string or integer or blob
        $result=$this->opsDbQuery($sql,$sqp);
        return $result;
    }

    /**
     * [saveEmailTemplate description]
     * @param  [type] $array [description]
     * @return [type]        [description]
     */
    public function saveEmailTemplate($array) {
        $array['uid']=$GLOBALS['sess']['loginUser']['_id'];
        if(!isset($array['accountId'])) {
            $array['accountId'] = $this->_getAccountId($array);
        }
        $emailfrom=explode(",",$array['emailfrom']);
        $emailfrom=json_encode($emailfrom);
        $emailReplyTo=explode(",",$array['emailReplyTo']);
        $emailReplyTo=json_encode($emailReplyTo);
        $recipients=explode(",",$array['recipient']);
        $recipients=json_encode($recipients);
        $cc=explode(",",$array['cc']);
        $cc=json_encode($cc);
        $bcc=explode(",",$array['bcc']);
        $bcc=json_encode($bcc);
        if ($array['id']) {
            $sql="UPDATE `email_templates` SET `form_id`=?,`name` = ?, `subject` = ?, `email_from`=?, `email_reply_to`=?, `email_to` = ?, `email_cc` = ?, `email_bcc`=?, `template` = ?, `conditionAction`=?, `conditionField`=?, `conditionOperand`=?, `conditionValue`=? WHERE `_id`=? AND `owner`=?";
            $sqp=array("sssssssssssssss", addslashes($array['form']), addslashes($array['name']),addslashes($array['subject']),$emailfrom,$emailReplyTo,$recipients,$cc,$bcc,$array['template'],$array['conditionAction'],$array['conditionField'],$array['conditionOperand'],$array['conditionValue'],$array['id'],$array['uid']); // s or i or b string or integer or blob
            $result=$this->opsDbQuery($sql,$sqp);
            return $array['id'];
        } else {
            $id=$this->pl->insertId(16);
            $sql="INSERT INTO `email_templates` (`_id`, `owner`, `accountId`, `form_id`, `name`, `subject`, `email_from`, `email_reply_to`, `email_to`, `email_cc`, `email_bcc`, `template`,`conditionAction`, `conditionField`, `conditionOperand`, `conditionValue`)
                     VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
            $sqp=array("ssssssssssssssss",$id,$array['uid'],$array['accountId'],$array['form'],addslashes($array['name']),addslashes($array['subject']),$emailfrom,$emailReplyTo,$recipients,$cc,$bcc,$array['template'],$array['conditionAction'],$array['conditionField'],$array['conditionOperand'],$array['conditionValue']); // s or i or b string or integer or blob
            $result=$this->opsDbQuery($sql,$sqp);

            return $id;
        }
    }

    public function listThemes($array) {
        if(!isset($array['accountId'])) {
            $array['accountId'] = $this->_getAccountId($array);
        }
        if ($array['id']) {
            if ($array['type']) {
                $sql="select * from `themes` t where t.owner=? and t.accountId=? and t._id=? and t.type=?";
                $sqp=array("ssss",addslashes($array['uid']),addslashes($array['accountId']),addslashes($array['id']),addslashes($array['type'])); // s or i or b string or integer or blob
            } else {
                $sql="select * from `themes` t where t.owner=? and t.accountId=? and t._id=?";
                $sqp=array("sss",addslashes($array['uid']),addslashes($array['accountId']),addslashes($array['id'])); // s or i or b string or integer or blob
            }
        } else {
            if ($array['type']) {
                $sql="select * from `themes` t where t.owner=? and t.accountId=? and t.type=?";
                $sqp=array("sss",addslashes($array['uid']),addslashes($array['accountId']),addslashes($array['type'])); // s or i or b string or integer or blob
            } else {
                $sql="select * from `themes` t where t.owner=? and t.accountId=?";
                $sqp=array("ss",addslashes($array['uid']),addslashes($array['accountId'])); // s or i or b string or integer or blob
            }
        }

        $rows=$this->getDbQuery($sql,$sqp);

        return $rows;
    }

    public function getTheme($array) {
        $sql="select * from `themes` t where t._id=?";
        $sqp=array("s",addslashes($array['id'])); // s or i or b string or integer or blob
        $rows=$this->getDbQuery($sql,$sqp);
        return $rows;
    }

    /**
     * [saveThemes description]
     * @param  [type] $array [description]
     * @return [type]        [description]
     */
    public function saveThemes($array) {
        $array['uid']=$GLOBALS['sess']['loginUser']['_id'];
        if(!isset($array['accountId'])) {
            $array['accountId'] = $this->_getAccountId($array);
        }
        if ($array['id']) {
            $sql="UPDATE `themes` SET
                                `name` = ?,
                                `themeFormBackground` = ?,
                                `themeFormBorder` = ?,
                                `themeBrowserBackground` = ?,
                                `themeFieldHover` = ?,
                                `themeFieldActive` = ?,
                                `themeFieldBackground` = ?,
                                `themeFieldBorder` = ?,
                                `themeFieldSelected` = ?,
                                `themeFieldError` = ?,
                                `themeSubmitButton` = ?,
                                `themeSubmitButtonText` = ?,
                                `themeText` = ?,
                                `themeFieldText` = ?,
                                `themeFont` = ?,
                                `themeCSS` = ?
                            WHERE `_id` = ?";

            $sqp=array(
                "sssssssssssssssss",
                $array['name'],
                addslashes($array['themeFormBackground']),
                addslashes($array['themeFormBorder']),
                addslashes($array['themeBrowserBackground']),
                addslashes($array['themeFieldHover']),
                addslashes($array['themeFieldActive']),
                addslashes($array['themeFieldBackground']),
                addslashes($array['themeFieldBorder']),
                addslashes($array['themeFieldSelected']),
                addslashes($array['themeFieldError']),
                addslashes($array['themeSubmitButton']),
                addslashes($array['themeSubmitButtonText']),
                addslashes($array['themeText']),
                addslashes($array['themeFieldText']),
                addslashes($array['themeFont']),
                $array['themeCSS'],
                $array['id']
            ); // s or i or b string or integer or blob
            $result=$this->opsDbQuery($sql,$sqp);
        } else {
            $id=$this->pl->insertId(16);
            $sql="INSERT INTO `themes` (
                                `_id`,
                                `type`,
                                `name`,
                                `owner`,
                                `accountId`,
                                `themeFormBackground`,
                                `themeFormBorder`,
                                `themeBrowserBackground`,
                                `themeFieldHover`,
                                `themeFieldActive`,
                                `themeFieldBackground`,
                                `themeFieldBorder`,
                                `themeFieldSelected`,
                                `themeFieldError`,
                                `themeSubmitButton`,
                                `themeSubmitButtonText`,
                                `themeText`,
                                `themeFieldText`,
                                `themeFont`,
                                `themeCSS`
                            )
                     VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
            $sqp=array(
                "ssssssssssssssssssss",
                $id,
                $array['type'],
                $array['name'],
                $array['uid'],
                $array['accountId'],
                addslashes($array['themeFormBackground']),
                addslashes($array['themeFormBorder']),
                addslashes($array['themeBrowserBackground']),
                addslashes($array['themeFieldHover']),
                addslashes($array['themeFieldActive']),
                addslashes($array['themeFieldBackground']),
                addslashes($array['themeFieldBorder']),
                addslashes($array['themeFieldSelected']),
                addslashes($array['themeFieldError']),
                addslashes($array['themeSubmitButton']),
                addslashes($array['themeSubmitButtonText']),
                addslashes($array['themeText']),
                addslashes($array['themeFieldText']),
                addslashes($array['themeFont']),
                $array['themeCSS'],
            ); // s or i or b string or integer or blob
            $result=$this->opsDbQuery($sql,$sqp);
        }
    }

    /**
     * [deleteTheme description]
     * @param  [type] $array [description]
     * @return [type]        [description]
     */
    public function deleteTheme($array) {
        if(!isset($array['accountId'])) {
            $array['accountId'] = $this->_getAccountId($array);
        }
        $sql="delete from `themes` where `_id`=? and `owner`=?";
        $sqp=array("ss",addslashes($array['id']),addslashes($array['user_id'])); // s or i or b string or integer or blob
        $result=$this->opsDbQuery($sql,$sqp);
        return $result;
    }

    /**
     *
     * @param  Array $array
     * @return Mixed
     */
    public function createForm($array) {
        if(!isset($array['accountId'])) {
            $array['accountId'] = $this->_getAccountId($array);
        }
        $sql="select f._id from forms f WHERE f.owner=? and f.accountId=? limit 0,1";
        $sqp=array("ss",addslashes($array['user_id']),addslashes($array['accountId'])); // s or i or b string or integer or blob
        $rows=$this->getDbQuery($sql,$sqp);
        $email=$GLOBALS['sess']['loginUser']['email'];
        if ($array['sourceform'] && $array['sourceform']<>'blank') {
            $sql_get="select f.* from `forms` f  where f._id=? limit 0,1";
            $sqp_get=array("s",addslashes($array['sourceform']));
            $rows=$this->getDbQuery($sql_get,$sqp_get);
            $newElements=array();
            if (($rows[0]['elements']<>'[]') && ($rows)) {
                $elements=json_decode(stripslashes($rows[0]['elements']),true);
                if (!$elements) {
                    $elements=json_decode($rows[0]['elements'],true);
                }

                foreach ($elements as &$element) {
                    $newElementId=$this->pl->insertId(16);
                    $element['name']=$newElementId;
                    $element['_id']=$newElementId;
                }

                $newElements=json_encode($elements,JSON_UNESCAPED_UNICODE);
            }

            $newid=$array['form_id'];
            $sql1="CREATE TEMPORARY TABLE `tmp` SELECT * FROM `forms` WHERE `_id`=?";
            $sql2='UPDATE `tmp` SET `_id`=?, `name`=?, `owner`=?, `accountId`=?, `email`=?, `elements`=?, `views`=0, `dateCreated`=now(), `active`=0, `responseStatusLists`=\'[{"_id":"all", "label":"All"},{"_id":"new", "label":"New"},{"_id":"viewed", "label":"Viewed"}]\' WHERE `_id`=?';
            $sql3="INSERT INTO `forms` SELECT * FROM `tmp` WHERE `_id`=?";
            $sql4="DROP TABLE `tmp`";

            $sqp1=array("s",addslashes($array['sourceform']));
            $sqp2=array("sssssss",$newid,addslashes($array['name']),addslashes($array['user_id']),addslashes($array['accountId']),$email,$newElements,addslashes($array['sourceform']));
            $sqp3=array("s",$newid);
            $sqp4=array();

            $result1=$this->opsDbQuery($sql1,$sqp1);
            $result2=$this->opsDbQuery($sql2,$sqp2);
            $result3=$this->opsDbQuery($sql3,$sqp3);
            $result4=$this->opsDbQuery($sql4,$sqp4);

            return $result1;
        } else {
            $elements=array();
            $els=json_encode($elements);

            $sql="INSERT INTO `forms` (`_id`, `elements`, `owner`, `accountId`, `type`, `name`, `displayHeader`, `notifyNewSubmissions`, `email`, `dateCreated`, `responseStatusLists`)
                     VALUES (?,?,?,?,?,?,'1','1',?,now(),?)";
            $sqp=array("ssssssss",addslashes($array['form_id']),$els,addslashes($array['user_id']),addslashes($array['accountId']),addslashes($array['type']),addslashes($array['name']),addslashes($email), '[{"_id":"all", "label":"All"},{"_id":"new", "label":"New"},{"_id":"viewed", "label":"Viewed"}]'); // s or i or b string or integer or blob
            $result=$this->opsDbQuery($sql,$sqp);
        }

        $members=$this->_getUsers(array('accountId'=>$array['accountId']));
        $owner=$members[0];
        if (count($members)>1) {
            foreach ($members as $member) {
                if ($member['_id']<>$owner['_id']) {
                    $permissions=array();
                    if ($member['permissions']) {
                        $permissions=json_decode(str_replace('\"','"',$member['permissions']),true);
                    }

                    $permissions[$array['form_id']]=$member['accountRights'];

                    $save_permission=json_encode($permissions);

                    $data['userId']=$member['_id'];
                    $data['accountId']=$array['accountId'];
                    $data['permissions']=$save_permission;
                    $GLOBALS['sess']['loginAccount']['permissions']=$save_permission;

                    $this->updateUserAccount($data);
                }
            }
        }
    }

    /**
     *
     * @param  Array $array
     * @return Array
     */
    public function _getUsers($array=null) {
        $row='';
        $page=0;
        if (is_numeric($array['page'])) {
            $page=$array['page'] * 50;
        }
        if ($array['email']) {
            $sql="select u.* from `sys_users` u where LOWER(u.email) = LOWER(?) limit 0,1";
            $sqp=array("s",addslashes($array['email'])); // s or i or b string or integer or blob
        } elseif ($array['id']) {
            $sql="select u.*, a._id as accountId, a.accountStatus, a.planExpiration, a.stripeCustomerId, a.stripeCustomer, a.stripeSubscription, a.ccBrand, a.ccLast4, a.companyName, r.accountRights, r.permissions, r.blocked from `sys_users` u LEFT JOIN `sys_rights` r ON u._id=r.userId LEFT JOIN `sys_accounts` a ON r.accountId=a._id WHERE u._id=? ORDER BY r.dateCreated limit 0,1";
            $sqp=array("s",addslashes($array['id'])); // s or i or b string or integer or blob
        } elseif ($array['stripeCustomerId']) {
            $sql="select u.*, a._id as accountId, a.accountStatus, a.planExpiration, a.stripeCustomerId, a.stripeCustomer, a.stripeSubscription, a.ccBrand, a.ccLast4, a.companyName, r.accountRights, r.permissions, r.blocked from `sys_users` u LEFT JOIN `sys_rights` r ON u._id=r.userId LEFT JOIN `sys_accounts` a ON r.accountId=a._id WHERE a.stripeCustomerId=? ORDER BY u.dateCreated ASC limit 0,1";
            $sqp=array("s",addslashes($array['stripeCustomerId'])); // s or i or b string or integer or blob
        } elseif ($array['accountId']) {
            $sql="select u.*, a._id as accountId, a.accountStatus, a.planExpiration, a.stripeCustomerId, a.stripeCustomer, a.stripeSubscription, a.ccBrand, a.ccLast4, a.companyName, r.accountRights, r.permissions, r.blocked from `sys_users` u LEFT JOIN `sys_rights` r ON u._id=r.userId LEFT JOIN `sys_accounts` a ON r.accountId=a._id WHERE r.accountId=? ORDER BY r.accountRights DESC";
            $sqp=array("s",addslashes($array['accountId'])); // s or i or b string or integer or blob
        } elseif ($array['all_users']) {
            $sql="select u.* from `sys_users` u";
            $sqp=null;
        }
        $rows=$this->getDbQuery($sql,$sqp);
        //var_dump($rows);
        return $rows;
    }

    public function updateUserAccount($array) {
        if ($array['accountId'] && $array['userId']) {
            if (isset($array['accountRights']) && isset($array['permissions'])) {
                $sql="UPDATE `sys_rights` SET `accountRights`=?, `permissions`=? WHERE `accountId`=? AND `userId`=?";
                $sqp=array('ssss',addslashes($array['accountRights']),$array['permissions'],addslashes($array['accountId']),addslashes($array['userId']));
            } else {
                if (isset($array['accountRights'])) {
                    $sql="UPDATE `sys_rights` SET `accountRights`=? WHERE `accountId`=? AND `userId`=?";
                    $sqp=array('sss',addslashes($array['accountRights']),addslashes($array['accountId']),addslashes($array['userId']));
                } else {
                    if (isset($array['permissions'])) {
                        $sql="UPDATE `sys_rights` SET `permissions`=? WHERE `accountId`=? AND `userId`=?";
                        $sqp=array('sss',$array['permissions'],addslashes($array['accountId']),addslashes($array['userId']));
                    }
                }
            }
        } elseif ($array['id']) {
            $sql="UPDATE `sys_rights` SET `accountRights`=?, `permissions`=? WHERE `_id`=?";
            $sqp=array('sss',addslashes($array['accountRights']),addslashes($array['permissions']),addslashes($array['id']));
        }

        $result=$this->opsDbQuery($sql,$sqp);
        return $result;
    }

    public function saveFormState($array) {

        $data = array(
            'update_type'=>'state',
            'active'=>$array['active'],
            'form_id'=>$array['form_id'],
            'user_account'=>$array['user_account']
        );

        if(!empty($array['accountId'])) {
            $data['accountId'] = $array['accountId'];
        }

        if(!empty($array['user_id'])) {
            $data['user_id'] = $array['user_id'];
        }

        $update=$this->saveForm($data);

        return $update;
    }

    /**
     *
     * @param  Array $array
     * @param  string $type
     * @return Mixed
     */
    public function saveForm($array) {
        //var_dump($array);exit;
        $m="saveform";
        if (array_key_exists('update_type',$array) && $array['update_type']=='state') {
            $res=array();

            $owner=$GLOBALS['sess']['loginUser']['_id'];

            if(!empty($array['user_id'])) {
                $owner = $array['user_id'];
            }

            $checkUser=$this->_getUsers(array('id'=>$owner));
            if (count($checkUser)==0) { //it might be the user has been deleted for phishing so we logout them
                session_destroy();
                $res['updated']=false;
                $res['active']=0;
                $res['message']='Invalid User';
                return $res;
            }

            if(!isset($array['accountId'])) {
                $array['user_id'] = $owner;
                $array['accountId'] = $this->_getAccountId($array);
            }

            $accountStatus=$GLOBALS['sess']['loginAccount']['accountStatus'];

            $sql="select f.elements, f.type from forms f  where f._id=? and f.owner=? and f.accountId=? limit 0,1";
            $sqp=array("sss",addslashes($array['form_id']),addslashes($owner), $array['accountId']); // s or i or b string or integer or blob
            $rows=$this->getDbQuery($sql,$sqp);

            if ($array['active']==1) {
                $elements=json_decode(str_replace('\\','',$rows[0]['elements']),true);
                if (!$elements) {
                    $elements=json_decode($rows[0]['elements'],true);
                }
                if ((!$elements || !count($elements)) && $rows[0]['type']<>'ENDPOINT') {
                    $res['updated']=false;
                    $res['active']=!$array['active']; //return to the previous state
                    $res['message']=$this->pl->trans($m,'Cannot activate empty form');
                    return $res;
                }

                if ($this->pl->isPreviewUser($GLOBALS['sess']['loginAccount'])) {
                    $res['updated']=false;
                    $res['active']=!$array['active']; //return to the previous state
                    $res['message']=$this->pl->trans($m,'You are in preview mode. Please').' <a href="/settings/account/">'.$this->pl->trans($m,'register your account').'</a> '.$this->pl->trans($m,'to publish this form');
                    return $res;
                } elseif ($GLOBALS['sess']['loginUser']['emailVerified']==0) {
                    $res['updated']=false;
                    $res['active']=!$array['active']; //return to the previous state
                    $res['message']=$this->pl->trans($m,'Please check your mailbox (or spam folder) to validate your account before publishing this form');
                    return $res;
                }

                $index=$array['user_account']['index'];
                $maxActive=$GLOBALS['ref']['plan_lists'][$index]['maxActiveForms'];

                $data['uid']=$GLOBALS['sess']['loginAccountOwner']['_id'];
                $data['active']=1;

                $activeForms=$this->_listForms($data);

                if (count($activeForms)>=$maxActive) {
                    $res['updated']=false;
                    $res['active']=!$array['active']; //return to the previous state
                    $forms="forms";
                    if ($maxActive==1) {
                        $forms="form";
                    }
                    $res['message']=$this->pl->trans($m,'Your account allows you to publish').' '.$maxActive.' '.$forms.', '.$this->pl->trans($m,'you attained that limit, please unpublish other form or').' <a href="/settings/subscription/">'.$this->pl->trans($m,'upgrade your account').'</a>.';
                    return $res;
                }

                if ($accountStatus=='FREE' || $accountStatus=='PREVIEW') {
                    //check if the elements has FILE type
                    $sql="select f.elements from `forms` f  where f._id=? and f.owner=? and f.accountId=? limit 0,1";
                    $sqp=array("sss",addslashes($array['form_id']),addslashes($owner),$array['accountId']);
                    $rows=$this->getDbQuery($sql,$sqp);

                    $hasRestrictedElement=false;
                    $hasRestrictedAttribute=false;
                    $rElement='';

                    $elements=json_decode(str_replace('\\','',$rows[0]['elements']),true);
                    if (!$elements) {
                        $elements=json_decode($rows[0]['elements'],true);
                    }
                    foreach ($elements as $element) {
                        if (in_array($element['type'],$GLOBALS['ref']['RESTRICTED_ELEMENTS_FOR_FREE_USERS'])) {
                            $hasRestrictedElement=true;
                            $rElement=$element['inputLabel'];
                            break;
                        }

                        //
                        if (!empty($element['enableLogic']) && $element['enableLogic']) {
                            $hasRestrictedAttribute=true;
                            $rElement=$element['inputLabel'];
                            break;
                        }
                    }

                    if ($hasRestrictedElement || $hasRestrictedAttribute) {
                        if ($hasRestrictedElement) {
                            $res['message']=$this->pl->trans($m,'You can test without publishing but').' <a href="/settings/subscription/">'.$this->pl->trans($m,'upgrade your account').'</a> '.$this->pl->trans($m,'to publish with the').' '.$rElement . ' element';
                        } else {
                            $res['message']=$this->pl->trans($m,'You can test without publishing but').' <a href="/settings/subscription/">'.$this->pl->trans($m,'upgrade your account').'</a> '.$this->pl->trans($m,'to publish with the logic display for ').' '.$rElement;
                        }

                        $res['updated']=false;
                        $res['active']=!$array['active']; //return to the previous state
                        return $res;
                    }

                    $form = $this->getForm(array('form_id'=>$array['form_id']));
                    if($form['usePassword'] == '1') {
                        $res['message']=$this->pl->trans($m,'You can test without publishing but').' <a href="/settings/subscription/">'.$this->pl->trans($m,'upgrade your account').'</a> '.$this->pl->trans($m,'to publish with the').' form with password';

                        $res['updated']=false;
                        $res['active']=!$array['active']; //return to the previous state
                        return $res;
                    }
                }
            }

            $sql="UPDATE `forms` SET `active`=? WHERE `owner`=? and `accountId`=? and `_id`=?";
            $sqp=array("ssss",addslashes($array['active']),addslashes($owner),$array['accountId'],addslashes($array['form_id']));
            $result=$this->opsDbQuery($sql,$sqp);
            $res['updated']=true;
            $res['active']=$array['active'];
            if ($array['active']==1) {
                $res['message']='The form is published';
            } else {
                $res['message']='The form is unpublished';
            }
            return $res;
        }
    }

    public function incrementForm($array) {

        $yearMonth = date('Ym');

        $sql="UPDATE `form_views` SET pageViewCount = pageViewCount + 1 WHERE `formId`=? AND yearMonth=?";
        $sqp=array("ss",addslashes($array['form_id']),addslashes($yearMonth));
        $result=$this->opsDbQuery($sql,$sqp);

        if($result->affected_rows == 0) {
            $newid = $this->pl->insertId();
            $sql="INSERT INTO `form_views` (`_id`,`formId`,`accountId`,`yearMonth`,`pageViewCount`) VALUES(?,?,?,?,?)";
            $sqp=array("sssss", $newid, $array['form_id'], $array['account_id'], $yearMonth, 1);
            $result=$this->opsDbQuery($sql,$sqp);
        }
    }

    public function incrementFormSubmission($array) {

        $yearMonth = date('Ym');

        $sql="UPDATE `form_views` SET responseCount = responseCount + 1 WHERE `formId`=? AND yearMonth=?";
        $sqp=array("ss",addslashes($array['form_id']),addslashes($yearMonth));
        $result=$this->opsDbQuery($sql,$sqp);
    }

    public function getTopViewForms($array) {
        $sql="SELECT v.formId as id, v.pageViewCount, a.accountStatus FROM `form_views` v LEFT JOIN `sys_accounts` a ON a._id=v.accountId WHERE v.yearMonth=? ORDER BY v.pageViewCount DESC LIMIT 50";
        $sqp=array("s", $array['yearMonth']);
        $rows['forms']=$this->getDbQuery($sql,$sqp);
        return $rows;
    }

    public function getTopResponseForms($array) {
        $sql="SELECT v.formId as id, v.responseCount, a.accountStatus FROM `form_views` v LEFT JOIN `sys_accounts` a ON a._id=v.accountId WHERE v.yearMonth=? ORDER BY v.responseCount DESC LIMIT 50";
        $sqp=array("s", $array['yearMonth']);
        $rows['forms']=$this->getDbQuery($sql,$sqp);
        return $rows;
    }

    /**
     * Save Page
     * @param  array $array
     * @return mixed
     */
    public function saveFormPage($array) {
        if($array['page'] == 'success') {
            return null;
        } else {
            //return $array;
            if ($array['user_id']) {
                if(!isset($array['accountId'])) {
                    $array['accountId'] = $this->_getAccountId($array);
                }
                $elements=array();
                $pages=array();

                $sql="select f.elements, f.pages from `forms` f  where f._id=? and f.owner=? and f.accountId=? limit 0,1";
                $sqp=array("sss",addslashes($array['form_id']),addslashes($array['user_id']), $array['accountId']); // s or i or b string or integer or blob
                $rows=$this->getDbQuery($sql,$sqp);

                $oldVal=$rows[0]['elements'];
                if ($rows[0]['elements']<>'[]' && $rows[0]['elements']<>null) {
                    $elements=json_decode(str_replace('\\"','"',$rows[0]['elements']),true);
                    if (!$elements) {
                        $elements=json_decode($rows[0]['elements'],true);
                    }
                }

                if ($array['action'] && $array['action']=='delete') {
                    $oldPage=$rows[0]['pages'];
                    $pages=json_decode(str_replace('\\"','"',$rows[0]['pages']),true);
                    if (!$pages) {
                        $pages=json_decode($rows[0]['pages'],true);
                    }
                    $newpages=array();

                    $ctr=0;
                    foreach ($pages as $page) {
                        if ($page['_id']!=$array['page']) {
                            $newpages[$ctr]['_id']=$page['_id'];
                            $newpages[$ctr]['nextButtonText']=strip_tags($page['nextButtonText']);
                            $newpages[$ctr]['previousButtonText']=strip_tags($page['previousButtonText']);
                            $ctr++;
                        }
                    }

                    $newElements=array();
                    foreach ($elements as $element) {
                        if ($element['page']!=$array['page']) {
                            $newElements[]=$element;
                        }
                    }

                    $sql="update `forms` set pages=?, elements=? where _id=? and owner=? and accountId=? ";
                    $sqp=array("sssss",json_encode($newpages,JSON_UNESCAPED_UNICODE),json_encode($newElements,JSON_UNESCAPED_UNICODE),addslashes($array['form_id']),addslashes($array['user_id']), $array['accountId']); // s or i or b string or integer or blob
                    $result=$this->opsDbQuery($sql,$sqp);

                    if ($GLOBALS['conf']['enable_formupdatelog']) {
                        //insert to formupdatelog
                        $logid=$this->pl->insertId();
                        $sql="insert into `formupdatelog` (`_id`,`form`,`field`,`old_value`,`new_value`,`request`) values(?,?,?,?,?,?)";
                        $sqp=array("ssssss",$logid,addslashes($array['form_id']),'elements',$oldVal,json_encode($newElements,JSON_UNESCAPED_UNICODE),json_encode($_REQUEST,JSON_UNESCAPED_UNICODE));
                        $result=$this->opsDbQuery($sql,$sqp);

                        $logid2=$this->pl->insertId();
                        $sql="insert into `formupdatelog` (`_id`,`form`,`field`,`old_value`,`new_value`,`request`) values(?,?,?,?,?,?)";
                        $sqp=array("ssssss",$logid2,addslashes($array['form_id']),'pages',$oldPage,json_encode($newpages,JSON_UNESCAPED_UNICODE),json_encode($_REQUEST,JSON_UNESCAPED_UNICODE));
                        $result=$this->opsDbQuery($sql,$sqp);
                    }

                    return $newpages;
                } else {
                    if ($rows[0]['pages']<>'[]' && $rows[0]['pages']<>null && $rows[0]['pages']<>'[{"_id":{}}]') {
                        $oldPage=$rows[0]['pages'];
                        $pages=json_decode(str_replace('\\"','"',$rows[0]['pages']),true);
                        if (!$pages) {
                            $pages=json_decode($rows[0]['pages'],true);
                        }
                    } else {
                        $hasPage = 0;
                        if (count($elements)) {
                            $element=array();
                            foreach ($elements as $key=>$row) {
                                $element[$key]=$row['order'];
                            }
                            array_multisort($element,SORT_ASC,$elements);

                            foreach ($elements as $element) {
                                if (!$this->_find_key_value($pages,'_id',$element['page']) && $element['page']!='success') {
                                    $pages[]['_id']=$element['page'];
                                    $hasPage = 1;
                                }
                            }
                        }
                        if($hasPage == 0) {
                            $pages[]=array('_id'=>'page1');
                        }
                    }

                    $pages[]['_id']=$array['page'];

                    $sql="update `forms` set pages=? where _id=? and owner=? and accountId=?";
                    $sqp=array("ssss",json_encode($pages,JSON_UNESCAPED_UNICODE),addslashes($array['form_id']),addslashes($array['user_id']), $array['accountId']); // s or i or b string or integer or blob
                    $result=$this->opsDbQuery($sql,$sqp);

                    if ($GLOBALS['conf']['enable_formupdatelog']) {
                        //insert to formupdatelog
                        $logid2=$this->pl->insertId();
                        $sql="insert into `formupdatelog` (`_id`,`form`,`field`,`old_value`,`new_value`,`request`) values(?,?,?,?,?,?)";
                        $sqp=array("ssssss",$logid2,addslashes($array['form_id']),'pages',$oldPage,json_encode($pages,JSON_UNESCAPED_UNICODE),json_encode($_REQUEST,JSON_UNESCAPED_UNICODE));
                        $result=$this->opsDbQuery($sql,$sqp);
                    }
                }

                return $pages;
            }
        }


        return null;
    }

    /**
     * find a value by key in array
     * @param  array $array
     * @param  string $key
     * @param  string $val
     * @return boolean
     */
    public function _find_key_value($array,$key,$val) {
        foreach ($array as $item) {
            if (is_array($item) && $this->_find_key_value($item,$key,$val)) {
                return true;
            }
            if (isset($item[$key]) && $item[$key]==$val) {
                return true;
            }
        }
        return false;
    }

    public function updatePages($array) {
        if ($array['user_id']) {
            if(!isset($array['accountId'])) {
                $array['accountId'] = $this->_getAccountId($array);
            }
            $sql="update `forms` set pages=? where _id=? and owner=? and accountId=? ";
            $sqp=array("ssss",json_encode($array['pages'],JSON_UNESCAPED_UNICODE),addslashes($array['form_id']),addslashes($array['user_id']), $array['accountId']); // s or i or b string or integer or blob
            $result=$this->opsDbQuery($sql,$sqp);

            //return $result;

            return array('success'=>true);
        }
    }

    public function getElementLabel($array, $skip_datasource = false) {
        $element=$this->getFormElement(array(
            'user_id'=>$array['owner'],
            'form_id'=>$array['form_id'],
            'element_id'=>$array['el_id'],
            'skip_datasource'=>$skip_datasource
        ));

        if ($element) {
            $label = isset($element['queryName']) ? $element['queryName'] : $element['label'];
            if(!$label) { $label = $element['inputLabel']; }
            return $label;
        }

        return null;
    }

    private function _finalElements($array, $elements) {
        if($array['skip_datasource']) { return $elements; }

        $links = $this->getDatasourcelink(array('formId'=>$array['form_id'], 'accountId'=>$array['accountId']));
        $link_elements = array();
        foreach($links as $link) {
            $link_elements[$link['elementId']] = $link;
        }

        if (count($elements)) {
            foreach ($elements as $key=>$row) {
                $ord[$key]=$row['order'];
                if (!$row['_id']) {
                    $elements[$key]['_id']=$row['name'];
                }

                if(isset($link_elements[$elements[$key]['_id']])) {
                    $link = $link_elements[$elements[$key]['_id']];

                    //$datasource = $this->_listDatasources(array('source_id'=>$link['datasourceId'], 'accountId'=>$array['accountId']));
                    //$ds = $datasource[0];

                    $list = 'optionsList';
                    if(strtolower($elements[$key]['type']) == 'products') {
                        $list = 'productsList';
                    }

                    $elements[$key][$list] = json_decode($link['data'], true);
                    if(!$link['columns']) { $link['columns'] = '["Label","Value"]'; }
                    $elements[$key]['columns'] = json_decode($link['columns'], true);
                    $elements[$key]['datasource_name'] = $link['title'];
                    $elements[$key]['datasource_id'] = $link['datasourceId'];
                }
            }
            array_multisort($ord,SORT_ASC,$elements);
        }

        return $elements;
    }

    /**
     * get the single element in elements field
     * @param  array $array
     * @return array
     */
    public function getFormElement($array) {
        if ($array['user_id']) {
            if(!isset($array['accountId'])) {
                $array['accountId'] = $this->_getAccountId($array);
            }

            $elements=array();
            $sql="select f.elements from `forms` f  where f._id=? and f.owner=? and f.accountId=? limit 0,1";
            $sqp=array("sss",addslashes($array['form_id']),addslashes($array['user_id']), $array['accountId']); // s or i or b string or integer or blob
            $rows=$this->getDbQuery($sql,$sqp);

            if ($rows[0]['elements']<>'[]' && $rows[0]['elements']<>null) {
                $elements=json_decode(str_replace('\\"','"',$rows[0]['elements']),true);
                if (!$elements) {
                    $elements=json_decode($rows[0]['elements'],true);
                }
            }

            $element=array();
            if (count($elements)) {
                foreach ($elements as $key=>$row) {
                    if ($row['_id']==$array['element_id']) {
                        $element=$row;
                        break;
                    }
                }
            }

            $els = [$element];
            $els = $this->_finalElements($array, $els);
            return $els[0];
        } else {
            $elements=array();
            $sql="select f.elements from `forms` f  where f._id=? limit 0,1";
            $sqp=array("s",addslashes($array['form_id'])); // s or i or b string or integer or blob
            $rows=$this->getDbQuery($sql,$sqp);
            if ($rows[0]['elements']<>'[]' && $rows[0]['elements']<>null) {
                $elements=json_decode(str_replace('\\"','"',$rows[0]['elements']),true);
                if (!$elements) {
                    $elements=json_decode($rows[0]['elements'],true);
                }
                $elements = $this->_finalElements($array, $elements);
            }

            if (!$array['element_id']) {
                return $elements;
            } else {
                $element=array();
                if (count($elements)) {
                    foreach ($elements as $key=>$row) {
                        if ($row['_id']==$array['element_id']) {
                            $element=$row;
                            break;
                        }
                    }
                }

                return $element;
            }
        }

        return null;
    }

    public function getElementType($array) {
        $element=$this->getFormElement(array(
            'user_id'=>$array['owner'],
            'form_id'=>$array['form_id'],
            'element_id'=>$array['el_id']
        ));

        if ($element) {
            return $element['type'];
        }

        return null;
    }

    /**
     *
     * @param  Array $array
     * @return Mixed
     */
    public function deleteForm($array) {
        if(!isset($array['accountId'])) {
            $array['accountId'] = $this->_getAccountId($array);
        }
        if (empty($array['form_id'])) {
            $sql="delete from `forms` where `owner`=? and `accountId`=?";
            $sqp=array("ss",addslashes($array['user_id']), $array['accountId']); // s or i or b string or integer or blob
        } else {
            $sql="delete from `forms` where `_id`=? and `owner`=? and `accountId`=?";
            $sqp=array("sss",addslashes($array['form_id']),addslashes($array['user_id']), $array['accountId']); // s or i or b string or integer or blob
        }
        $result=$this->opsDbQuery($sql,$sqp);
        return $result;
    }

    public function duplicateForm($array) {
        if(!isset($array['accountId'])) {
            $array['accountId'] = $this->_getAccountId($array);
        }

        $newid=$this->pl->insertId(16);

        $sql_get="select f.* from `forms` f  where f._id=? and f.owner=? and f.accountId=? limit 0,1";
        $sqp_get=array("sss",addslashes($array['form_id']),addslashes($array['user_id']), $array['accountId']);
        $rows=$this->getDbQuery($sql_get,$sqp_get);

        //var_dump($rows);exit;

        $newElements=array();
        if (($rows[0]['elements']<>'[]') && ($rows)) {
            $elements=json_decode(stripslashes($rows[0]['elements']),true);
            if (!$elements) {
                $elements=json_decode($rows[0]['elements'],true);
            }
            $newids=[];

            foreach($elements as $element) {
                $newElementId=$this->pl->insertId(16);
                $newids[$element['_id']]=$newElementId;
            }

            foreach ($elements as &$element) {

                $sourceLinksCheck = $this->getDatasourcelink(array('formId'=>$array['form_id'],'elementId'=>$element['_id']));
                if(count($sourceLinksCheck)) {
                    $newsourceLink = $this->saveDatasourcelink(array(
                        'id'=>$this->pl->insertId(),
                        'formId'=>$newid,
                        'elementId'=>$newids[$element['_id']],
                        'datasourceId'=>$sourceLinksCheck[0]['datasourceId']
                    ));
                }

                if ($element['logicField']) {
                    $element['logicField']=$newids[$element['logicField']];
                }

                if($element['fieldLists']) {
                    if(is_array($element['fieldLists']) && count($element['fieldLists'])) {
                        foreach($element['fieldLists'] as &$fl) {
                            $fl['field'] = $newids[$fl['field']];
                        }
                    }
                }

                if($element['conditions']) {
                    $conditions = json_decode($element['conditions'],true);
                    if(count($conditions)) {
                        foreach($conditions as &$cd) {
                            $cd['if'] = $newids[$cd['if']];
                        }

                        $element['conditions'] = json_encode($conditions,JSON_UNESCAPED_UNICODE);
                    }
                }

                $element['name']=$newids[$element['_id']];
                $element['_id']=$newids[$element['_id']];
            }

            $newElements=json_encode($elements,JSON_UNESCAPED_UNICODE);
        }

        $sql1="CREATE TEMPORARY TABLE `tmp` SELECT * FROM `forms` WHERE `_id`=? AND `owner`=? and `accountId`=?";
        $sql2="UPDATE `tmp` SET `_id`=?, `name` = CONCAT('Copy of ', name), `elements`=?, `views`=0, `active`=0, `dateCreated`=now() WHERE `_id`=? AND `owner`=? and `accountId`=?";
        $sql3="INSERT INTO `forms` SELECT * FROM `tmp` WHERE `_id`=?";
        $sql4="DROP TABLE `tmp`";

        $sqp1=array("sss",addslashes($array['form_id']),addslashes($array['user_id']), $array['accountId']);
        $sqp2=array("sssss",$newid,$newElements,addslashes($array['form_id']),addslashes($array['user_id']), $array['accountId']);
        $sqp3=array("s",$newid);
        $sqp4=array();

        $result1=$this->opsDbQuery($sql1,$sqp1);
        $result2=$this->opsDbQuery($sql2,$sqp2);
        $result3=$this->opsDbQuery($sql3,$sqp3);
        $result4=$this->opsDbQuery($sql4,$sqp4);


        $members=$this->_getUsers(array('accountId'=>$array['accountId']));
        $owner=$members[0];
        if (count($members)>1) {
            foreach ($members as $member) {
                if ($member['_id']<>$owner['_id']) {
                    $permissions=array();
                    if ($member['permissions']) {
                        $permissions=json_decode(str_replace('\"','"',$member['permissions']),true);
                    }

                    $permissions[$newid]=$member['accountRights'];

                    $save_permission=json_encode($permissions);

                    $data['userId']=$member['_id'];
                    $data['accountId']=$array['accountId'];
                    $data['permissions']=$save_permission;
                    $GLOBALS['sess']['loginAccount']['permissions']=$save_permission;

                    $this->updateUserAccount($data);
                }
            }
        }
    }

    /**
     *
     * @param  Array $a
     * @param  Array $b
     * @return Number
     */
    public function sortByOrder($a,$b) {
        return $a['order'] - $b['order'];
    }

    /**
     *
     * @param  Array $array
     * @return Mixed
     */
    public function getForm($array) {
        $m="getform";
        $themeFields='t.themeFormBackground, t.themeFormBorder, t.themeBrowserBackground, t.themeFieldHover, t.themeFieldActive, t.themeFieldBackground, t.themeFieldBorder, t.themeFieldSelected, t.themeFieldError, t.themeSubmitButton, t.themeSubmitButtonText, t.themeText, t.themeDescriptionText, t.themeFieldText, t.themeFont, t.themeCSS';
        if(!isset($array['accountId'])) {
            $array['accountId'] = $this->_getAccountId($array);
        }
        if ($array['form_id']) {
            if ($array['user_id']) {
                $sql="select f.*, ".$themeFields." from `forms` f left join `themes` t on t._id=f.themeID  where f._id=? and f.owner=? and f.accountId=? limit 0,1";
                $sqp=array("sss",addslashes($array['form_id']),addslashes($array['user_id']),addslashes($array['accountId'])); // s or i or b string or integer or blob
            } else {
                $sql="select f.* from `forms` f  where f._id=? limit 0,1";
                $sqp=array("s",addslashes($array['form_id'])); // s or i or b string or integer or blob
            }
            $rows=$this->getDbQuery($sql,$sqp);

            if (($rows[0]['elements']<>'[]') && ($rows)) {
                $rows[0]['elements'] = str_replace('\n','<br>',$rows[0]['elements']);
                $elements=json_decode(stripslashes($rows[0]['elements']),true);
                if (!$elements) {
                    $elements=json_decode($rows[0]['elements'],true);
                }

                $elements = $this->_finalElements($array, $elements);

                $rows[0]['elements']=$elements;
            } else {
                unset($rows[0]['elements']);
            }

            if ($rows[0]['pages']) {
                $temp_pages = $rows[0]['pages'];
                $json_pages = json_decode(str_replace('\\"','"',$rows[0]['pages']));
                $rows[0]['pages']=$json_pages;
                if (!$json_pages) {
                    $rows[0]['pages']=json_decode($temp_pages,true);
                }
            }
            if ($rows[0]['timeSeriesViews']) {
                $rows[0]['timeSeriesViews']=json_decode(str_replace('\\"','"',$rows[0]['timeSeriesViews']));
            }
            if ($rows[0]['logo']) {
                if (json_decode(str_replace('\\"','"',$rows[0]['logo']))) {
                    $rows[0]['logo']=json_decode(str_replace('\\"','"',$rows[0]['logo']));
                }
            }
            if ((!$rows[0]) && ($array['create']) && ($array['order']) && ($array['type']) && ($array['name'])) {
                $sql="INSERT INTO `forms` (`_id`, `order`, `name`, `owner`,`accountId`,`dateCreated`,`type`) VALUES (?,?,?,?,?,now(),?)";
                $sqp=array("ssssss",addslashes($array['form_id']),addslashes($array['order']),addslashes($array['name']),addslashes($array['user_id']),addslashes($array['accountId']),addslashes($array['type'])); // s or i or b string or integer or blob
                $result=$this->opsDbQuery($sql,$sqp);
                unset($array['create']); // to prevent we end up in a loop when the insert fails
                return $this->getForm($array);
            } else {
                if ($rows[0]) {
                    return $rows[0];
                } else {
                    $error['error']=$this->pl->trans($m,'Form does not exist or no rights to this form');
                    return $error;
                }
            }
        } elseif ($array['all_forms']) {
            $sql="select * from `forms`";
            $sqp=null;

            $rows=$this->getDbQuery($sql,$sqp);
            return $rows;
        } else {
            $error['error']="error code: logic 105";
            return $error;
        }
    }

    public function editElements($array) {
        if ($array['updates']) {

            $arr = $array['updates'][0];

            if(!isset($array['accountId'])) {
                $array['accountId'] = $this->_getAccountId($array);
            }

            $sql="select f.elements from `forms` f  where f._id=? and f.owner=? and f.accountId=? limit 0,1";
            $sqp=array("sss",addslashes($array['form_id']),addslashes($array['user_id']), $array['accountId']); // s or i or b string or integer or blob
            $rows=$this->getDbQuery($sql,$sqp);

            $elements=json_decode(str_replace('\\"','"',$rows[0]['elements']),true);
            if (!$elements) {
                $elements=json_decode($rows[0]['elements'],true);
            }

            foreach ($array['updates'] as $update) {
                $new=array(
                    'form_id'=>$array['form_id'],
                    'action'=>'update',
                    'el_id'=>$update['el_id'],
                    'order'=>$update['order'],
                    'page'=>$update['page'],
                    'size'=>$update['size'],
                    'user_id'=>$array['user_id'],
                );

                $elcount=count($elements);
                $found=array();
                for ($e=0;$e<$elcount;$e++) {
                    if ($elements[$e]['name']==$new['el_id'] || $elements[$e]['_id']==$new['el_id']) {
                        $elements[$e]['order']=$new['order'];
                        $elements[$e]['page']=$new['page'];
                        $elements[$e]['size']=$new['size'];
                    }
                }
            }

            $sql="update `forms` set elements=? where _id=? and owner=? and accountId=? ";
            $sqp=array("ssss",json_encode($elements,JSON_UNESCAPED_UNICODE),addslashes($array['form_id']),addslashes($array['user_id']), $array['accountId']); // s or i or b string or integer or blob
            $result=$this->opsDbQuery($sql,$sqp);

            return 'ok';
        }
    }

    private function _editFormElement2($array){
        return $this->editFormElement($array);
    }

    /**
     *
     * @param  Array $array
     * @return Mixed
     */
    public function editFormElement($array) {
        $editable=array('');
        //do you have the rights to edit ?
        if ($array['user_id']) {
            if(!isset($array['accountId'])) {
                $array['accountId'] = $this->_getAccountId($array);
            }

            if (($array['form_id']) && ($array['el_id']) && ($array['el_id']<>'settings') && ($array['el_id']<>'confirmation') && ($array['el_id']<>'endpoint') && ($array['el_id']<>'theme') && (($array['prop']) || ($array['type']) || ($array['action']))) {
                $sql="select f.elements from `forms` f  where f._id=? and f.owner=? and f.accountId=? limit 0,1";
                $sqp=array("sss",addslashes($array['form_id']),addslashes($array['user_id']), $array['accountId']); // s or i or b string or integer or blob
                $rows=$this->getDbQuery($sql,$sqp);

                $oldVal=$rows[0]['elements'];
                if ($rows[0]['elements']<>'[]') {
                    $elements=json_decode(str_replace('\\"','"',$rows[0]['elements']),true);
                    if (!$elements) {
                        $elements=json_decode($rows[0]['elements'],true);
                    }
                }

                $elcount=count($elements);
                $found=array();
                for ($e=0;$e<$elcount;$e++) {
                    if ($array['value']=="true") {
                        $array['value']=true;
                    }

                    if ($elements[$e]['name']==$array['el_id'] || $elements[$e]['_id']==$array['el_id']) {
                        $found[]=$array['el_id'];
                        if ($array['prop']) {
                            if (substr($array['prop'],0,7)=="option_") {
                                $listName="optionsList";
                                if (strtolower($array['el_type'])=='products') {
                                    $listName="productsList";
                                }
                                if (isset($array['list_type'])) {
                                    $listName=$array['list_type'];
                                }

                                if (strtolower($array['el_type'])=='qty' && $listName == 'optionsList') {
                                    //
                                } else {
                                    $sourceLinksCheck = $this->getDatasourcelink(array('formId'=>$array['form_id'],'elementId'=>$array['el_id']));
                                    if(count($sourceLinksCheck)) {
                                        $sId = $sourceLinksCheck[0]['datasourceId'];
                                        $source = $this->_listDatasources(array('source_id'=>$sId, 'accountId'=>$array['accountId']));
                                        if(count($source)) {
                                            $ds = $source[0];
                                            $elements[$e][$listName] = json_decode($ds['data'], true);
                                        }
                                    }
                                }

                                $parts=explode("_",$array['prop']);
                                if ($array['action']=="delete") {
                                    unset($elements[$e][$listName][substr($parts[2],1)]);
                                    $elements[$e][$listName]=array_values($elements[$e][$listName]);
                                } elseif ($array['action']=="moveup") {
                                    $up=$elements[$e][$listName][substr($parts[2],1) - 1];
                                    if ($up) {
                                        $elements[$e][$listName][substr($parts[2],1) - 1]=$elements[$e][$listName][substr($parts[2],1)];
                                        $elements[$e][$listName][substr($parts[2],1)]=$up;
                                    }
                                } elseif ($array['action']=="movedown") {
                                    $down=$elements[$e][$listName][substr($parts[2],1) + 1];
                                    if ($down) {
                                        $elements[$e][$listName][substr($parts[2],1) + 1]=$elements[$e][$listName][substr($parts[2],1)];
                                        $elements[$e][$listName][substr($parts[2],1)]=$down;
                                    }
                                } else {
                                    if(($elements[$e]['type'] == 'RADIO' || $elements[$e]['type'] == 'SELECT') && $parts[1] == 'default') {
                                        $opts = $elements[$e][$listName];
                                        foreach($opts as $k=>$opt) {
                                            $elements[$e][$listName][$k][$parts[1]] = 0;
                                        }
                                        $elements[$e][$listName][substr($parts[2],1)][$parts[1]]=$array['value'];
                                    } else {
                                        $elements[$e][$listName][substr($parts[2],1)][$parts[1]]=$array['value'];
                                    }
                                }

                                //return $elements[$e][$listName];

                                if ((strtolower($array['el_type'])=='products' && $listName=='productsList') || (strtolower($array['el_type'])<>'inputtable' && strtolower($array['el_type'])<>'qty' && $listName=='optionsList')) {
                                    $sourceLinks = $this->getDatasourcelink(array('formId'=>$array['form_id'],'elementId'=>$array['el_id']));
                                    if(count($sourceLinks) == 0) {

                                        $ds = $this->_listDatasources(array('uid'=>$array['user_id'], 'accountId'=>$array['accountId']));
                                        $ds_title = 'List ' . (count($ds)+1);
                                        //add new data source

                                        if($array['user_id'] && $array['accountId'] && $array['form_id'] && $array['el_id']) {
                                            $sourceId = $this->pl->insertId();
                                            $newsource = $this->saveDatasource(array(
                                                'id'=>$sourceId,
                                                'uid'=>$array['user_id'],
                                                'accountId'=>$array['accountId'],
                                                'title'=>$ds_title,
                                                'data'=>json_encode($elements[$e][$listName],JSON_UNESCAPED_UNICODE)
                                            ));

                                            $newsourceLink = $this->saveDatasourcelink(array(
                                                'id'=>$this->pl->insertId(),
                                                'formId'=>$array['form_id'],
                                                'elementId'=>$array['el_id'],
                                                'datasourceId'=>$sourceId
                                            ));
                                        }
                                    } else {
                                        //update data source
                                        $old_id = $sourceLinks[0]['datasourceId'];
                                        $updatedSource = $this->saveDatasource(array(
                                            'old_id'=>$old_id,
                                            'data'=>json_encode($elements[$e][$listName],JSON_UNESCAPED_UNICODE)
                                        ));
                                    }
                                }
                            } else {
                                if ($array['prop']=='picture') {
                                    if ($array['value']) {
                                        $parts=explode('.',$array['value']['name']);
                                        $filename=$array['el_id'].'.jpg';
                                        move_uploaded_file($array['value']['tmp_name'],$GLOBALS['conf']['filepath_img'].'/'.$filename);
                                        $array['value']=$filename;
                                    }
                                }

                                $res = array(
                                    'prop'=>$array['prop'],
                                    'oldvalue'=>$elements[$e][$array['prop']],
                                    'newvalue'=>$array['value']
                                );

                                $elements[$e][$array['prop']]=$array['value'];
                                if($array['prop'] == 'conditions' && is_array($array['value'])) {
                                    $elements[$e][$array['prop']]=json_encode($array['value']);
                                    $elements[$e]['enableLogic']=1;
                                }

                                echo json_encode($res);
                            }
                        } elseif ($array['action']=='update') { //this is specific for re ordering the elements
                            $elements[$e]['order']=$array['order'];
                            $elements[$e]['page']=$array['page'];
                            $elements[$e]['size']=$array['size'];
                        } elseif ($array['action']=="delete") {
                            //remove image
                            $image = $GLOBALS['conf']['filepath_img'].'/'.$array['el_id'].'.jpg';
                            if(file_exists($image)) {
                                unlink($image);
                            }
                            unset($elements[$e]);
                        }
                    }
                }

                if ($array['action']=="delete") {
                    $elements=array_values($elements);
                }

                if ((!$found[$array['el_id']]) && ($array['type']) && ($array['page']) && ($array['order'])) {
                    $elements[$elcount]=array(
                        "name"=>$array['el_id'],
                        "order"=>$array['order'],
                        "type"=>$array['type'],
                        "page"=>$array['page'],
                        "_id"=>$array['el_id']
                    );

                    $els=array('size','inputLabel','placeholderText','instructionText','required','disabled','helpText','optionsList','iconEnabled','iconName','customValidationType','queryName','defaultValue','labelText','placeholderTitleText','placeholderFirstText','placeholderLastText','placeholderMiddleText','placeholderAddress1Text','placeholderAddress2Text','placeholderCityText','placeholderStateText','placeholderZipText','placeholderCountryText','country','format','validationMessage','use12Notation','interval','beginDate','endDate','otherOptionLabel','label','labelStripe','labelPaypal','buttonLabel','paymentsPageLabel','totalLabel','cardNameLabel','cardNumberLabel','expiryDateLabel','securityCodeLabel','postCodeLabel','postCode','enableAmountLabel','sendAsAttachment','productsList','questionList','answerList','optionsList','captchaError','fieldLists','calculationTotal','maxLengthErrorMessage','textAreaHeight','clearLabel','width','height','unfinishUpload','finishedUpload','uploading','fileSizeError','fileDimensionError','dM','dT','dW','dTH','dF','dSat','dSun','lookupColumn','autoSuggest','notExistsErrorMessage', 'logicAction', 'enableLogic', 'conditionAndOr', 'conditions', 'logicField', 'logicCondition', 'logicValue', 'hidden', 'securityCode','paymentProcessButtonLabel','largeFile','multipleFile', 'captureCard', 'captureLabel', "card", "ideal", "alipay", "ach_credit_transfer", "bancontact", "eps", "giropay", "multibanco", "p24", "sepa_debit", "sofort", "idealLabel", "alipayLabel", "ach_credit_transferLabel", "bancontactLabel", "epsLabel", "giropayLabel", "multibancoLabel", "p24Label", "sepa_debitLabel", "sofortLabel", "dateFormat", "textSize", "pickerLang");

                    foreach ($els as $el) {
                        if ($array[$el]) {
                            $elements[$elcount][$el]=$array[$el];
                        }
                    }

                    echo "set done";
                }

                $response='';
                $logid='';
                if ($this->pl->json_validate(json_encode($elements,JSON_UNESCAPED_UNICODE))) {
                    $sql="update `forms` set elements=? where _id=? and owner=? and accountId=? ";

                    //return json_encode($elements);
                    $sqp=array("ssss",json_encode($elements,JSON_UNESCAPED_UNICODE),addslashes($array['form_id']),addslashes($array['user_id']), $array['accountId']); // s or i or b string or integer or blob
                    $result=$this->opsDbQuery($sql,$sqp);

                    if($array['prop'] == 'datasource_id') {
                        $link = $this->getDatasourcelink(array('formId'=>$array['form_id'], 'elementId'=>$array['el_id']));
                        if(count($link)) {
                            $this->saveDatasourcelink(array(
                                'update'=>true,
                                'datasourceId'=>$array['value'],
                                'elementId'=>$array['el_id'],
                                'formId'=>$array['form_id']
                            ));
                        }
                    }
                    $response='ok';
                } else {
                    $response='error';
                }
                if ($GLOBALS['conf']['enable_formupdatelog']) {
                    //insert to formupdatelog
                    $logid=$this->pl->insertId();
                    $sql="insert into `formupdatelog` (`_id`,`form`,`field`,`old_value`,`new_value`,`request`) values(?,?,?,?,?,?)";
                    $sqp=array("ssssss",$logid,addslashes($array['form_id']),'elements',$oldVal,json_encode($elements,JSON_UNESCAPED_UNICODE),json_encode($_REQUEST,JSON_UNESCAPED_UNICODE));
                    $result=$this->opsDbQuery($sql,$sqp);
                }
                if ($response=='error' && $oldVal<>'[]') {
                    //$this->pl->sendMail(array('body' => 'Form update error with transaction ID: ' . $logid . '<br>SQL DATA: <br><br><br> ' . print_r($sqp, true) . '<br><br><br>SQL RESULT: ' . print_r($result, true), 'from' => 'hello@formlets.com', 'to' => 'elias@oxopia.com', 'subject' => 'Form update Error log'));
                }

                return $response;
            } elseif (($array['form_id']) && ($array['prop']) && ($array['prop']<>'elements')) {
                $proplist=array(
                    //settings
                    "name",
                    "logo",
                    "description",
                    "displayHeader",
                    "doRedirect",
                    "submitSuccessMessage",
                    "redirectUrl",
                    "notifyNewSubmissions",
                    "notifySubmitter",
                    "notifyUseTemplate",
                    "email",
                    "emailReply",
                    "emailFrom",
                    "submitButtonText",
                    "previousButtonText",
                    "nextButtonText",
                    "inactiveMessage",
                    "requiredMessage",
                    "autoComplete",
                    "footerPaginationPageText",
                    "footerPaginationOfText",
                    "currency",
                    "isExternalData",
                    "externalData",
                    "enableCSRF",
                    "autoFill",

                    //themes
                    "themeEnabled",
                    "themeID",
                    "customCSS",

                    "rtl",
                    "trackGeoAndTimezone",
                    "responseStorage",
                    "usePassword",
                    "password",
                    "passwordLabel",
                    "passwordButtonLabel",
                    "invalidPassword",
                    "leavePrompt",
                );

                $pageFields=array('previousButtonText','nextButtonText');

                $themeFields=array('themeFormBackground','themeBrowserBackground','themeFont','themeFormBorder','themeFieldBackground','themeFieldBorder','themeFieldActive','themeFieldHover','themeFieldError','themeFieldSelected','themeSubmitButton','themeSubmitButtonText','themeText','themeDescriptionText','themeFieldText','themeCSS');

                $sql_form="select * from `forms` where _id=? and owner=? and accountId=? limit 0,1";
                $sqp_form=array("sss",addslashes($array['form_id']),addslashes($array['user_id']), $array['accountId']);
                $form=$this->getDbQuery($sql_form,$sqp_form);

                if (in_array($array['prop'],$themeFields)) {
                    if ($form[0]['themeID']) {
                        $themeID=$form[0]['themeID'];
                    } else {
                        $themeID=$this->insertNewTheme($form,$array);
                    }

                    $sql="update `themes` set `".$array['prop']."`=? where _id=? and owner=? and accountId=?";
                    $sqp=array("ssss",addslashes($array['value']),addslashes($themeID),addslashes($array['user_id']), $array['accountId']); // s or i or b string or integer or blob
                    $result=$this->opsDbQuery($sql,$sqp);
                }

                if (in_array($array['prop'],$pageFields) && $array['page']) {
                    $pages=json_decode(str_replace('\\"','"',$form[0]['pages']),true);
                    if (!$pages) {
                        $pages=json_decode($form[0]['pages'],true);
                    }
                    $newpages=array();
                    foreach ($pages as $page) {
                        $newpage=$page;
                        if ($page['_id']==$array['page']) {
                            $newpage[$array['prop']]=strip_tags($array['value']);
                        }
                        $newpages[]=$newpage;
                    }

                    $sql="update `forms` set pages=? where _id=? and owner=? and accountId=? ";
                    $sqp=array("ssss",json_encode($newpages),addslashes($array['form_id']),addslashes($array['user_id']), $array['accountId']); // s or i or b string or integer or blob
                    $result=$this->opsDbQuery($sql,$sqp);

                    return 'button text set';
                } elseif (in_array($array['prop'],$proplist)) {
                    if ($array['prop']=='logo') {
                        if ($array['value']) {
                            $parts=explode('.',$array['value']['name']);
                            // $id=$this->pl->insertId(16);
                            // $filename=$id.'.'.$parts[count($parts)-1];
                            $filename=$array['form_id'].'_logo.jpg';
                            move_uploaded_file($array['value']['tmp_name'],$GLOBALS['conf']['filepath_img'].'/'.$filename);

                            $array['value']=$filename;
                        } else {
                            //remove logo
                            $logo = $GLOBALS['conf']['filepath_img'].'/'.$array['form_id'].'_logo.jpg';
                            if(file_exists($logo)) {
                                unlink($logo);
                            }
                        }
                    }

                    if ($array['prop']=='themeEnabled' && $array['value']=='1') {
                        if (empty($form[0]['themeID'])) {
                            $this->insertNewTheme($form,$array);
                        }
                    }

                    //get old value
                    $sql="select `".$array['prop']."` from `forms` WHERE _id=? and owner=? and accountId=? ";
                    $sqp=$sqp=array("sss",addslashes($array['form_id']),addslashes($array['user_id']), $array['accountId']); // s or i or b string or integer or blob
                    $row=$this->getDbQuery($sql,$sqp);
                    $oldVal='';
                    if (count($row)) {
                        $oldR=$row[0];
                        $oldVal=$oldR[$array['prop']];
                    }

                    $sql="update `forms` set `".$array['prop']."`=? where _id=? and owner=? and accountId=? ";
                    $sqp=array("ssss",$array['value'],addslashes($array['form_id']),addslashes($array['user_id']), $array['accountId']); // s or i or b string or integer or blob
                    $result=$this->opsDbQuery($sql,$sqp);

                    if ($GLOBALS['conf']['enable_formupdatelog']) {
                        $sql="insert into `formupdatelog` (`_id`,`form`,`field`,`old_value`,`new_value`,`request`) values(?,?,?,?,?,?)";
                        $sqp=array("ssssss",$this->pl->insertId(),addslashes($array['form_id']),$array['prop'],$oldVal,addslashes($array['value']),json_encode($_REQUEST,JSON_UNESCAPED_UNICODE));
                        $result=$this->opsDbQuery($sql,$sqp);
                    }
                    return 'ok set';
                } else {
                    return $array['prop'].' Nok';
                }
            }
        }
    }

    /**
     * [insertNewTheme description]
     * @param  [type] $array [description]
     * @return [type]        [description]
     */
    public function insertNewTheme($form,$array) {
        $themeID=$this->pl->insertId(16);
        if(!isset($array['accountId'])) {
            $array['accountId'] = $this->_getAccountId($array);
        }
        //update
        $sql="UPDATE forms SET themeID=? WHERE _id=? and owner=? and accountId=?";
        $sqp=array("ssss",$themeID,addslashes($array['form_id']),addslashes($array['user_id']), $array['accountId']);
        $result=$this->opsDbQuery($sql,$sqp);

        $sql="INSERT INTO `themes` (
                            `_id`,
                            `type`,
                            `name`,
                            `owner`,
                            `accountId`,
                            `themeFormBackground`,
                            `themeFormBorder`,
                            `themeBrowserBackground`,
                            `themeFieldHover`,
                            `themeFieldActive`,
                            `themeFieldBackground`,
                            `themeFieldBorder`,
                            `themeFieldSelected`,
                            `themeFieldError`,
                            `themeSubmitButton`,
                            `themeSubmitButtonText`,
                            `themeText`,
                            `themeDescriptionText`,
                            `themeFieldText`,
                            `themeFont`,
                            `themeCSS`
                        )
                 VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $sqp=array(
            "sssssssssssssssssssss",
            $themeID,
            'Basic',
            $form[0]['name'].' Theme',
            $array['user_id'],
            $array['accountId'],
            '#FFFFFF',
            '#D6D7D6',
            '#F5F5F5',
            '#3E4943',
            '#4BAEC2',
            '#FFFFFF',
            '#D6D7D6',
            '#4BAEC2',
            '#D1603D',
            '#4BAEC2',
            '#FFFFFF',
            '#3E4943',
            '#9DA3A0',
            '#3E4943',
            '',
            '',
        ); // s or i or b string or integer or blob
        $result=$this->opsDbQuery($sql,$sqp);

        return $themeID;
    }

    public function editFormAutoResponder($array) {
        $sql="UPDATE `forms` SET notifySubmitter=?, notifyUseTemplate=? WHERE _id=?";
        $sqp=array("sss",addslashes($array['notifySubmitter']),addslashes($array['notifyUseTemplate']),addslashes($array['form_id'])); // s or i or b string or integer or blob
        $result=$this->opsDbQuery($sql,$sqp);
        return $result;
    }

    /**
     *
     * @param  Array $array
     * @return Void
     */
    public function deleteallForms($array) {
        $sql="delete from `forms` WHERE `owner`=?";
        $sqp=array("s",addslashes($array['id'])); // s or i or b string or integer or blob
        $result=$this->opsDbQuery($sql,$sqp);
    }

    /**
     *
     * @param  Array $array
     * @return Void
     */
    public function loginUsers($array) {
        $sql="update `sys_users` set loginCount=loginCount+1,dateLastlogin=now() where _id=?";
        $sqp=array("s",addslashes($array['id'])); // s or i or b string or integer or blob
        $result=$this->opsDbQuery($sql,$sqp);
    }

    public function deleteSession($array) {
        if ($array['session']) {
            $sql="DELETE FROM `sys_sessions` WHERE session=?";
            $sqp=array("s",addslashes($array['session']));
            $result=$this->opsDbQuery($sql,$sqp);
        } else {
            if ($array['userId']) {
                $sql="DELETE FROM `sys_sessions` WHERE userId=?";
                $sqp=array("s",addslashes($array['userId']));
                $result=$this->opsDbQuery($sql,$sqp);
            } else {
                if ($array['ip']) {
                    $sql="DELETE FROM `sys_sessions` WHERE ip=?";
                    $sqp=array("s",addslashes($array['ip']));
                    $result=$this->opsDbQuery($sql,$sqp);
                }
            }
        }
    }

    public function getSessionData($id) {
        $sql="select u.*, s.session, s.data, s.dateTouched, a._id as accountId, a.accountStatus, a.planExpiration, a.stripeCustomerId, a.stripeCustomer, a.stripeSubscription, a.ccBrand, a.ccLast4, a.companyName, r._id as rightsId, r.accountRights, r.permissions, r.blocked, ad.userId as adminUserId, ad.rights as adminRights from `sys_sessions` s right join `sys_users` u on u._id=s.userId right join `sys_rights` r ON s.userId=r.userId and s.accountId=r.accountId right join `sys_accounts` a ON s.accountId=a._id left join `sys_admin` ad on s.userId=ad.userId WHERE s.session=?";
        $sqp=array("s",$id);
        $result=$this->getDbQuery($sql,$sqp);
        return $result;
    }

    public function setSession($id,$time) {
        $sql="INSERT INTO `sys_sessions` (`_id`, session, dateTouched) VALUES(?,?,?)";
        $sqp=array("sss",$this->pl->insertId(),$id,$time);
        $result=$this->opsDbQuery($sql,$sqp);
    }

    public function writeSession($id,$data) {
        $currentTime=time();
        $sql="UPDATE `sys_sessions` SET data=?, dateTouched=? WHERE session=?";
        $sqp=array("sss",$data,$currentTime,$id);
        $result=$this->opsDbQuery($sql,$sqp);

        return $result;
    }

    public function setUserSession($id, $user_id, $account_id) {
        $sql="UPDATE `sys_sessions` SET userId=?, accountId=? WHERE session=?";
        $sqp=array("sss",$user_id, $account_id, $id);
        $result=$this->opsDbQuery($sql,$sqp);

        return $result;
    }

    public function removeSession($id) {
        $sql="DELETE FROM `sys_sessions` WHERE session=?";
        $sqp=array("s",$id);
        $this->opsDbQuery($sql,$sqp);
    }

    public function removeSessionGC($maxlifetime) {
        // $currentTime=time();
        // $sql="DELETE FROM `sys_sessions` WHERE dateTouched + $maxlifetime < $currentTime";
        // $sqp=array();
        // $this->opsDbQuery($sql,$sqp);
    }

    public function saveMailQueue($array) {
        $id = $this->pl->insertId();
        $sql="INSERT INTO message_queue (`_id`,`email`,`data`,`responseStatus`) VALUES(?,?,?,?)";
        $sqp=array("ssss", $id, json_encode($array['to']), json_encode($array['data']), $array['responseStatus']);
        $result = $this->opsDbQuery($sql,$sqp);
    }

    public function getMessageQueue($array) {
        $sql="SELECT * FROM message_queue WHERE email LIKE ? ORDER BY dateCreated ASC";
        $sqp=array("s", addslashes('%'.$array['email'].'%'));
        $row = $this->getDbQuery($sql, $sqp);
        return $row;
    }

    public function deleteMessageQueue($array) {
        $sql="DELETE FROM message_queue WHERE _id=?";
        $sqp=array("s", addslashes($array['id']));
        $result = $this->opsDbQuery($sql, $sqp);
    }

    /**
     *
     * @param  Array $array
     * @return Void
     */
    public function _deleteusers($array) {
        if ($array['id']) {
            if ($array['backup']) {

                $sql="INSERT INTO deleted_sys_users SELECT u.*, CURRENT_TIMESTAMP() FROM sys_users u WHERE u._id=?";
                $sqp=array("s",addslashes($array['id'])); // s or i or b string or integer or blob
                $result=$this->opsDbQuery($sql,$sqp);

                $sql="INSERT INTO deleted_sys_rights SELECT r.*, CURRENT_TIMESTAMP() FROM sys_rights r WHERE r.userId=?";
                $sqp=array("s",addslashes($array['id'])); // s or i or b string or integer or blob
                $result=$this->opsDbQuery($sql,$sqp);

            }

            $sql="delete from `sys_users` WHERE `_id`=? ";
            $sqp=array("s",addslashes($array['id'])); // s or i or b string or integer or blob
            $result=$this->opsDbQuery($sql,$sqp);

            $sql="delete from `sys_rights` WHERE `userId`=? ";
            $sqp=array("s",addslashes($array['id'])); // s or i or b string or integer or blob
            $result=$this->opsDbQuery($sql,$sqp);
        }
    }

    public function blockUnblockUser($array) {
        if($array['id']) {
            $sql="UPDATE `sys_rights` SET blocked=? WHERE accountId=? AND userId=?";
            $sqp=array("sss",$array['blocked'],$array['account'], $array['id']);
            $result=$this->opsDbQuery($sql,$sqp);
        }
    }

    /**
     *
     * @param  Array $array
     * @return Void
     */
    public function deletesubmission($array) {
        if ($array['id']) {
            $sql="delete from `submissions` WHERE `_id`=?";
            $sqp=array("s",addslashes($array['id'])); // s or i or b string or integer or blob
            $result=$this->opsDbQuery($sql,$sqp);
        }
    }

    public function getAccountOwner($array) {
        $users=$this->_getUsers(array('accountId'=>$array['accountId']));
        return $users[0];
    }

    public function _getUsersAdmin($array=null) {
        $rows=[];
        $page=0;
        if (is_numeric($array['page'])) {
            $page=$array['page'] * 50;
        }
        if (isset($array['keyword'])) {

            if(trim($array['keyword']) == '') {
                $sql="SELECT u.* , a.accountStatus, a._id as accountId, r.blocked,
                (SELECT count(*) FROM forms I1 WHERE I1.owner = u._id OR I1.owner=r.accountId) as forms_count,
                (SELECT count(*) FROM submissions I2 WHERE I2.form IN(SELECT _id from forms WHERE (owner=u._id or owner=r.accountId))) as submissions_count
                FROM sys_users u LEFT JOIN sys_rights r ON r.userId=u._id LEFT JOIN sys_accounts a ON a._id=r.accountId ORDER BY dateCreated desc LIMIT ".$page.",50";
                $sqp=array();
                $rows=$this->getDbQuery($sql,$sqp);

                return $rows;
            }

            $keyword = addslashes($array['keyword']);
            if($array['type'] == 'email') {
                $where = 'u.email LIKE ?';
                $keyword = addslashes('%'.$array['keyword'].'%');
                $sqp=array("s",$keyword);
            } else if($array['type'] == 'formId') {
                $where = 'f._id=?';
            } else if($array['type'] == 'stripeCustomerId') {
                $where = 'a.stripeCustomerId=?';
            } else if($array['type'] == 'accountId') {
                $where = 'a._id=?';
            } else if($array['type'] == 'verified') {
                $where = 'u.emailVerified=?';
            } else if($array['type'] == 'accountStatus') {
                $keyword = addslashes('%'.$array['keyword'].'%');
                $where = 'a.accountStatus LIKE ?';
            }

            $sqp=array("s",$keyword);

            if($array['type'] == 'formId') {
                $sql="SELECT u.* , a.accountStatus, a._id as accountId, r.blocked,
                (SELECT count(*) FROM forms I1 WHERE I1.owner = u._id OR I1.owner=r.accountId) as forms_count,
                (SELECT count(*) FROM submissions I2 WHERE I2.form IN(SELECT _id from forms WHERE (owner=u._id or owner=r.accountId))) as submissions_count
                FROM sys_users u LEFT JOIN sys_rights r ON r.userId=u._id LEFT JOIN sys_accounts a ON a._id=r.accountId LEFT JOIN `forms` f ON f.owner=u._id WHERE ".$where." ORDER BY dateCreated desc LIMIT ".$page.",50";
            } else {
                $sql="SELECT u.* , a.accountStatus, a._id as accountId, r.blocked,
                (SELECT count(*) FROM forms I1 WHERE I1.owner = u._id OR I1.owner=r.accountId) as forms_count,
                (SELECT count(*) FROM submissions I2 WHERE I2.form IN(SELECT _id from forms WHERE (owner=u._id or owner=r.accountId))) as submissions_count
                FROM sys_users u LEFT JOIN sys_rights r ON r.userId=u._id LEFT JOIN sys_accounts a ON a._id=r.accountId WHERE ".$where." ORDER BY dateCreated desc LIMIT ".$page.",50";
            }


            $rows=$this->getDbQuery($sql,$sqp);

        }

        return $rows;
    }

    /**
     *
     * @param  Array $array
     * @return Array
     */
    public function getFormswithSubmissions($array) {
        if ($array['id']) {
            $sql="SELECT DISTINCT (s.form) AS formid, f.* FROM  `submissions` s LEFT JOIN forms f ON s.form = f._id order by dateCreated desc LIMIT 0 , 25";
            $sqp=array("s",addslashes($array['id'])); // s or i or b string or integer or blob
        }
        $rows=$this->getDbQuery($sql,$sqp);
        return $rows;
    }

    public function getFormEmailTemplate($array) {
        if ($array['form_id']) {
            $sql="select * from `email_templates` where form_id=?";
            $sqp=array("s",addslashes($array['form_id'])); // s or i or b string or integer or blob

            $rows=$this->getDbQuery($sql,$sqp);
            return $rows;
        }
    }

    public function updateResponseStatus($array) {
        $seen = 1;
        if($array['status'] == 'new') {
            $seen=0;
        }
        $sql="UPDATE `submissions` SET status=?, seen=? WHERE _id=?";
        $sqp=array("sss", addslashes($array['status']), addslashes($seen), addslashes($array['responseId']));
        $result = $this->opsDbQuery($sql, $sqp);

        return $result;
    }

    /**
     *
     * @param  Array $array
     * @return Array
     */
    public function getSubmissions($array) {
        if ($array['formid']) {
            if ($array['id']) {
                $sql="select * from `submissions` where form=? and _id=?";
                $sqp=array("ss",addslashes($array['formid']),addslashes($array['id'])); // s or i or b string or integer or blob

                if($array['viewdetail']) {
                    $sql2="update `submissions` set status='viewed', seen=1 where form=? and _id=? and status='new'";
                    $sqp2=array("ss",addslashes($array['formid']),addslashes($array['id'])); // s or i or b string or integer or blob
                    $this->opsDbQuery($sql2, $sqp2);
                }
            } else {
                if ($array['all']) {
                    $where='';
                    if ($array['response']) {
                        if ($array['response']=='all') {
                            $where='';
                        } elseif ($array['response']=='last') {
                            $where='MONTH(dateCreated)=MONTH(NOW())-1 and YEAR(dateCreated)=YEAR(NOW())';
                        } elseif ($array['response']=='this') {
                            $where='MONTH(dateCreated)=MONTH(NOW()) and YEAR(dateCreated)=YEAR(NOW())';
                        }
                    }

                    if ($where) {
                        $sql="select * from `submissions` where ".$where." and form=? order by dateCreated desc";
                    } else {
                        $sql="select * from `submissions` where form=? order by dateCreated desc";
                    }

                    $sqp=array("s",addslashes($array['formid'])); // s or i or b string or integer or blob
                } else {
                    if (is_numeric($array['_start'])) {
                        $start=$array['_start'];
                    } else {
                        $start=0;
                    }
                    $nr=25;
                    $sql="select * from `submissions` where form=? order by dateCreated desc limit ".$start.",".$nr;
                    $sqp=array("s",addslashes($array['formid'])); // s or i or b string or integer or blob
                }
            }
        } else if (isset($array['all_submission'])) {
            $sql="select * from `submissions`";
            $sqp=null;
        } else if ($array['id'] && $array['related_form_id']) {
            $sql="select * from `submissions` where _id=?";
            $sqp=array("s",addslashes($array['id'])); // s or i or b string
            $rows=$this->getDbQuery($sql,$sqp);
            if (count($rows)) {
                $formid=$rows[0]['form'];

                $sql1="select owner from `forms` where _id=? or _id=?";
                $sqp1=array("ss",addslashes($formid),addslashes($array['related_form_id'])); // s or i or b string
                $rows1=$this->getDbQuery($sql1,$sqp1);

                if ($rows1[0]['owner']==$rows1[1]['owner'] || (count($rows1) == 1 && $rows1[0]['owner'] == $array['owner']['_id'])) {
                    return $rows;
                } else {
                    return [];
                }
            }
        } else if ($array['id']) {
            $sql="select * from `submissions` where _id=?";
            $sqp=array("s",addslashes($array['id'])); // s or i or b string
        }
        $rows=$this->getDbQuery($sql,$sqp);
        return $rows;
    }

    function updateFormStatusLists($array) {
        $sql="UPDATE `forms` SET responseStatusLists=? WHERE _id=?";
        $sqp=array("ss",$array['lists'], addslashes($array['formId']));
        $this->opsDbQuery($sql, $sqp);
    }

    /**
     * paginated for submission
     * @param  array $array
     * @return array
     */
    public function paginatedSubmissions($array) {
        $page=1;
        if (!empty($array['page'])) {
            $bool=(!is_int($array['page']) ? (ctype_digit($array['page'])) : true);
            if (false===$page) {
                $page=1;
            } else {
                $page=$array['page'];
            }
        }
        // set the number of items to display per page
        $items_per_page=10;

        $where='';
        // if ($array['response']=='all') {
        //     $where='';
        // } elseif ($array['response']=='last') {
        //     $where='MONTH(dateCreated)=MONTH(NOW())-1 and YEAR(dateCreated)=YEAR(NOW())';
        // } elseif ($array['response']=='this') {
        //     $where='MONTH(dateCreated)=MONTH(NOW()) and YEAR(dateCreated)=YEAR(NOW())';
        // }

        if($array['status'] <> 'all') {
            $where='status="'.addslashes($array['status']).'"';
        }

        $row_count=$array['totalRows'];
        $page_count=0;
        if ($row_count) {
            $page_count=(int)ceil($row_count / $items_per_page);
            // double check that request page is in range
            if ($page>$page_count) {
                // error to user, maybe set page to 1
                $page=1;
            }
        }

        // build query
        $offset=($page - 1) * $items_per_page;
        if ($where) {
            $sql="select * from `submissions` where ".$where." and form=? order by dateCreated desc limit ".$offset.",".$items_per_page;
        } else {
            $sql="select * from `submissions` where form=? order by dateCreated desc limit ".$offset.",".$items_per_page;
        }

        $sqp=array("s",addslashes($array['formid'])); // s or i or b string or integer or blob
        $rows=$this->getDbQuery($sql,$sqp);

        return array("data"=>$rows,"page_count"=>$page_count,"page"=>$page,"rows_count"=>$row_count);
    }

    /**
     *
     * @param  Array $array
     * @return Void
     */
    public function saveSubmissions($array, $encrypted) {
        $sql="INSERT INTO `submissions` (`_id`, `form`, `data`, `seen`, `dateCreated`, `status`, `encrypted`) VALUES
                     (?, ?, ?, '0', now(), 'new', ?)";
        $sqp=array("ssss",addslashes($array['id']),addslashes($array['form_id']),$array['data'], $encrypted); // s or i or b string or integer or blob
        $result=$this->opsDbQuery($sql,$sqp);
    }

    public function emailLog($array) {

        $sql="INSERT INTO `email_logs` (`_id`, `emailTo`, `from`, `subject`, `body`, `status`,`clientip`) VALUES(?,?,?,?,?,?,?)";
        $sqp=array("sssssss", addslashes($array['id']), addslashes($array['to']), addslashes($array['from']), addslashes($array['subject']), addslashes($array['body']), addslashes($array['status']), addslashes($array['ip']));
        $result = $this->opsDbQuery($sql, $sqp);
        return $result;
    }

    public function getFormTotalResponses($array) {
        $sql = "SELECT status, count(*) as total FROM `submissions` WHERE form=? GROUP BY status";
        $sqp = array("s", addslashes($array['formid']));
        $rows=$this->getDbQuery($sql,$sqp);

        $totals = array('all'=>0,'new'=>0,'viewed'=>0);

        $total = 0;
        foreach($rows as $row) {
            $totals[$row['status']] = $row['total'];
            $total+=$row['total'];
        }

        if($total) {
            $totals['all'] = $total;
        }

        return $totals;
    }

    /**
     * [updateSubmission description]
     * @param  [type] $array [description]
     * @return [type]        [description]
     */
    public function updateSubmission($array) {
        $sql="UPDATE `submissions` SET `data`=? WHERE `_id`=?";
        $sqp=array("ss",$array['data'],addslashes($array['id'])); // s or i or b string or integer or blob
        $result=$this->opsDbQuery($sql,$sqp);
        return $result;
    }

    /**
     *
     * @param  Array $array
     * @return Array
     */
    public function getAdmin($array) {
        if ($array['id']) {
            $sql="select a._id as accountId, ad.* from `sys_admin` ad LEFT JOIN `sys_rights` r ON r.userId=ad.userId LEFT JOIN `sys_accounts` a ON a._id=r.accountId where ad.userId=? ORDER BY a.dateCreated ASC limit 0,1";
            $sqp=array("s",addslashes($array['id'])); // s or i or b string or integer or blob
        }
        $rows=$this->getDbQuery($sql,$sqp);
        return $rows;
    }

    /**
     *
     * @param Array $array
     */
    public function setTokenUsers($array) {
        $sql="update `sys_users` set emailValidationToken=?,dateEmailvalidation=now() where _id=?";
        $sqp=array("ss",addslashes($array['password_token']),addslashes($array['id'])); // s or i or b string or integer or blob
        $result=$this->opsDbQuery($sql,$sqp);
    }

    /**
     *
     * @param Array $array
     * @return Void
     */
    public function setPasswordUsers($array) {
        $sql="update `sys_users` set password=?, emailValidationToken='' where _id=? and emailValidationToken=? and emailValidationToken<>'' and dateEmailvalidation>now()- interval 4 hour ";
        $sqp=array("sss",addslashes($array['password']),addslashes($array['id']),addslashes($array['password_token'])); // s or i or b string or integer or blob
        $result=$this->opsDbQuery($sql,$sqp);
    }

    /**
     *
     * @param  Array $array
     * @return Void
     */
    public function _removetokenUsers($array) {
        $user=$this->_getUsers(array('id'=>$array['id']))[0];
        if ($user['email']) {
            $sql="update `sys_users` set dateEmailvalidation=now(), emailVerified=1 where _id=? and emailValidationToken=? and emailValidationToken<>'' and dateEmailvalidation>now()- interval 4 hour ";
            $sqp=array("ss",addslashes($array['id']),addslashes($array['password_token'])); // s or i or b string or integer or blob
        } elseif (isset($array['reset_password']) && $array['reset_password']) {
            $sql="update `sys_users` set dateEmailvalidation=now(), emailVerified=1 where _id=? and emailValidationToken=? and emailValidationToken<>'' and dateEmailvalidation>now()- interval 4 hour ";
            $sqp=array("ss",addslashes($array['id']),addslashes($array['password_token'])); // s or i or b string or integer or blob
        } else {
            $sql="update `sys_users` set emailValidationToken='',dateEmailvalidation=now() emailVerified=1 where _id=? and emailValidationToken=? and emailValidationToken<>'' and dateEmailvalidation>now()- interval 4 hour ";
            $sqp=array("ss",addslashes($array['id']),addslashes($array['password_token'])); // s or i or b string or integer or blob
        }

        $result=$this->opsDbQuery($sql,$sqp);
    }

    /**
     *
     * @param  Array $array
     * @return Array
     */
    public function checktokenUsers($array) {
        $sql="select * from `sys_users` where _id=? and emailValidationToken=? and dateEmailvalidation>now()- interval 4 hour limit 0,1";
        $sqp=array("ss",addslashes($array['id']),addslashes($array['password_token'])); // s or i or b string or integer or blob
        $rows=$this->getDbQuery($sql,$sqp);
        return $rows;
    }

    /**
     *
     * @param  array $array
     * @return void
     */
    public function processFormTemplate($array) {
        if ($this->isFormTemplate($array)) {
            $sql="DELETE FROM form_templates WHERE sourceform=?";
            $sqp=array("s",addslashes($array['formid'])); // s or i or b string or integer or blob
        } else {
            $id=$this->pl->insertId(16);
            $sql="INSERT INTO `form_templates` (`_id`, `name`, `description`, `usecount`, `img1`, `img2`, `img3`, `sourceform`, `published`) VALUES
                         (?, '', '', '0', '', '', '', ?, '0')";
            $sqp=array("ss",addslashes($id),addslashes($array['formid'])); // s or i or b string or integer or blob
        }
        $result=$this->opsDbQuery($sql,$sqp);
    }

    /**
     *
     * @param  array $array
     * @return boolean
     */
    public function isFormTemplate($array) {
        $sql="select 1 from `form_templates` where sourceform=?";
        $sqp=array("s",addslashes($array['formid'])); // s or i or b string or integer or blob
        $rows=$this->getDbQuery($sql,$sqp);
        return count($rows)>0;
    }

    /**
     *
     * @return array
     */
    public function getFormTemplates($array=array()) {
        if ($array['id']) {
            $sql="select * from `form_templates` WHERE _id=?";
            $sqp=array("s",addslashes($array['id'])); // s or i or b string or integer or blob
        } elseif ($array['published']) {
            $sql="select * from `form_templates` WHERE published=?";
            $sqp=array("s",addslashes($array['published'])); // s or i or b string or integer or blob
        } else {
            $sql="select * from `form_templates`";
            $sqp=array(); // s or i or b string or integer or blob
        }

        $rows=$this->getDbQuery($sql,$sqp);
        return $rows;
    }

    /**
     *
     * @param  array $data
     * @param  string $id
     * @return void
     */
    public function saveFormTemplate($data,$id) {
        $sql="UPDATE form_templates SET name=?, description=?, img1=?, img2=?, img3=?, published=? WHERE _id=?";
        $sqp=array("sssssss",addslashes($data['name']),addslashes($data['description']),addslashes($data['img1']),addslashes($data['img2']),addslashes($data['img3']),addslashes($data['published']),addslashes($id)); // s or i or b string or integer or blob
        return $this->opsDbQuery($sql,$sqp);
    }

    ///// Stored Procedures /////////

    /**
     *
     * @param  string $id
     * @return void
     */
    public function deleteFormTemplate($id) {
        $sql="DELETE FROM form_templates WHERE _id=?";
        $sqp=array("s",addslashes($id)); // s or i or b string or integer or blob
        $result=$this->opsDbQuery($sql,$sqp);
    }

    /**
     * delete old formupdatelog
     * @param  int $days number of days
     * @return void
     */
    public function cleanFormUpdateLogs($days) {
        $sql="DELETE FROM formupdatelog WHERE date_created < NOW() - INTERVAL ? DAY";
        $sqp=array("i",addslashes($days)); // s or i or b string or integer or blob
        $result=$this->opsDbQuery($sql,$sqp);
    }

    public function cleanEmailLogs($days) {
        $sql="DELETE FROM email_logs WHERE dateCreated < NOW() - INTERVAL ? DAY";
        $sqp=array("i",addslashes($days)); // s or i or b string or integer or blob
        $result=$this->opsDbQuery($sql,$sqp);
    }

    /**
     * delete old formupdatelog
     * @param  int $days number of days
     * @return void
     */
    public function cleanMessageQueue($days) {
        $sql="DELETE FROM message_queue WHERE dateCreated < NOW() - INTERVAL ? DAY";
        $sqp=array("i",addslashes($days)); // s or i or b string or integer or blob
        $result=$this->opsDbQuery($sql,$sqp);
    }

    public function cleanSysSession() {
        $sql="DELETE FROM sys_sessions WHERE data IS NULL or data='' or data='amount_to_pay|i:0;template_form|N;template_templates|a:0:{}template_data|a:0:{}template_form_owner|N;'";
        $sqp=array(); // s or i or b string or integer or blob
        $result=$this->opsDbQuery($sql,$sqp);

        $sql="DELETE FROM sys_sessions WHERE data NOT LIKE '%loginUser%' and dateUpdated < (CURDATE() - INTERVAL 14 DAY);";
        $sqp=array(); // s or i or b string or integer or blob
        $result=$this->opsDbQuery($sql,$sqp);
    }

    /**
     *
     * @param  Mixed $v
     * @return Mixed
     */
    public function copy_value($v) {
        return $v;
    }

    public function updateAutoresponders() {
        $sql="SELECT _id, template FROM email_templates";
        $sqp=null;
        $rows=$this->getDbQuery($sql,$sqp);

        $finalTemplateHead = <<<EOD
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <style>
            @import url(https://fonts.googleapis.com/css?family=Source+Sans+Pro);

            .white-background {
                background-color: #FFF;
            }
            .lightGray {
                color: #C5CAC5;
            }

            table.body-wrap .container {
                border: 2px solid #D6D7D6;
                border-radius: 3px;
                font-family: "Source Sans Pro", "Helvetica Neue", "Helvetica", Helvetica, Arial, sans-serif;
            }

            .padding {
                padding: 10px 0;
            }

            table {
                border-collapse: collapse;
            }
            table.body-wrap {
                width: 100%;
                padding: 20px;
            }

            h1, h2, h3 {
                line-height: 1.1;
                margin-bottom: 15px;
                margin: 40px 0 10px;
                line-height: 1.2;
                font-weight: 200;
            }
            h1 {
                font-weight: 500;
                margin: 18px;
                font-size: 36px;
            }

            .container {
                margin-top: 10px;
                display: block!important;
                max-width: 600px;
                margin: 0 auto!important; /* makes it centered */
                clear: both!important;
            }

            .body-wrap .container {
                padding: 20px;
            }

            .content {
                max-width: 600px;
                margin: 0 auto;
                display: block;
            }

            .content table {
                width: 100%;
            }
        </style>
    </head>
    <body>
    <table class="body-wrap">
        <tr>
            <td class="container white-background">
                <div class="content">
                    <table>
                        <tr>
                            <td align="center">
                                <h1>{SUBMISSION::SUBJECT}</h1>
                                <p class="lightGray">{SUBMISSION::DATE}</p>
                            </td>
                        </tr>
                        <tr class="padding"><td class="padding"></td></tr>
                        <tr>
                            <td>
EOD;
    $finalTemplateFoot = <<<EOD
</td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>
    </body>
    </html>
EOD;

        foreach($rows as $row) {

            if (preg_match('~<body[^>]*>(.*?)</body>~si', $row['template'], $body)) {
                //dont update
            } else {
                $finalTemplate=$finalTemplateHead.$row['template'].$finalTemplateFoot;

                $sql="UPDATE email_templates SET template=? WHERE _id=?";
                $sqp=array("ss", $finalTemplate, $row['_id']);
                $result=$this->opsDbQuery($sql,$sqp);
            }
        }
    }
}
