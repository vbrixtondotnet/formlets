<?php
// form elements
abstract class output_elements {

    function _overwriteDefaultValueFromExternalData($data) {
        $value = $data['defaultValue'];
        if(($this->pl->isFreeAccount($this->fowner) == false && $this->form['isExternalData']) || ($this->form['active']<>'1' && $this->form['isExternalData'])) {
            $externals = explode(',', $this->form['externalData']);
            foreach($externals as $external) {
                $ext = trim($external);
                $ext = str_replace(' ', '_', $ext);

                $label = $data['queryName'];
                if(!$label) {
                    $label = $data['inputLabel'];
                }

                if($ext == $label && !empty($_GET[$ext])) {
                    $value = $_GET[$ext];
                    break;
                }
            }
        }

        return $value;
    }

    function correct_label($label) {
        //$label = htmlentities($label);
        $label = str_replace('&lt;','<',$label);
        $label = str_replace('&gt;','>',$label);
        $label = stripslashes($label);
        $allowed_tags = array('b', 'i', 'strong');
        $escaped_str = htmlentities($label);
        $replace_what = array_map(function($v){ return "~&lt;(/?)$v(.*?)&gt;~"; }, $allowed_tags);
        $replace_with = array_map(function($v){ return "<$1$v$2>"; }, $allowed_tags);
        $label = preg_replace($replace_what, $replace_with, $escaped_str);
        $label = str_replace('&quot;','"', $label);
        $label = preg_replace("/<([a-z][a-z0-9]*)(?:[^>]*(\sstyle=['\"][^'\"]*['\"]))?[^>]*?(\/?)>/i",'<$1$2$3>', $label);
        return $label;
    }

  function _getAdditionalAttr($data) {
      $addAttr = '';

      preg_match_all("/\{([^\}]*)\}/", $data['defaultValue'], $matches1);

      if(count($matches1)) {
          if(count($matches1[1])) {
              $old_val = $data['defaultValue'];
              $old_val = str_replace('{', '<', $old_val);
              $old_val = str_replace('}', '>', $old_val);
              foreach($matches1[1] as $match) {
                  $data['defaultValue'] = preg_replace('{'.$match.'}', '', $data['defaultValue']);
              }

              $data['defaultValue'] = preg_replace('/\{\}/', '', $data['defaultValue']);

              $addAttr.=' input-vars data-label="'.implode(',',$matches1[1]).'" input-vars-type="value" input-vars-old="'.$old_val.'"';
          }
      }

      if($data['defaultValue']) {
          $addAttr.=' value="'.$data['defaultValue'].'"';
      }

      preg_match_all("/\{([^\}]*)\}/", $data['placeholderText'], $matches);

      if(count($matches)) {
          if(count($matches[1])) {
              $old_val = $data['placeholderText'];
              $old_val = str_replace('{', '<', $old_val);
              $old_val = str_replace('}', '>', $old_val);
              foreach($matches[1] as $match) {
                  $data['placeholderText'] = str_replace('{'.$match.'}', '', $data['placeholderText']);
              }

              $data['placeholderText'] = preg_replace('/\{\}/', '', $data['placeholderText']);

              $addAttr.=' input-vars data-label="'.implode(',',$matches[1]).'" input-vars-type="placeholder" input-vars-old="'.$old_val.'"';
          }

      }

      $addAttr.='placeholder="'.htmlentities($data['placeholderText']).'"';

      return $addAttr;
  }

  function _getDefaultValueFromSubmission($submission, $default_value, $input_label) {
      $encrypted=$submission[0]['encrypted'];
      if(count($submission)) {
    		$submission = json_decode(str_replace('\\','',$submission[0]["data"]),true);
    		if(!$submission) {
    	    	$submission = json_decode($submission[0]["data"], true);
    	    }

    		foreach($submission as $s) {
    			if($s['label'] == $input_label) {
                    if($encrypted) {
                        $default_value = $this->pl->decrypt($s['value']);
                    } else {
                        $default_value = $s['value'];
                    }

    				break;
    			}
    		}
      }

      return $default_value;
  }

  function elFormText($data, $submission, $form_owner){
    $data['defaultValue'] = $this->_overwriteDefaultValueFromExternalData($data);
  	$type="text";
  	$readonly="";
  	$class="";

    if($_COOKIE[$data['_id']] && $this->form['autoFill']=='1' && $this->pl->isFreeAccount($this->user) == false) {
        $data['defaultValue'] = $_COOKIE[$data['_id']];
    }

    $data['defaultValue'] = $this->_getDefaultValueFromSubmission($submission, $data['defaultValue'], $data['inputLabel']);

    $addAttr='';
    $addAttr.=$this->_getAdditionalAttr($data);

  	$icon_positon = 'left';
    if($this->form['rtl']) {
        $icon_positon = 'right';
    }
    if($data['disabled']==true || $_GET['action']=='print') {$readonly = 'readonly="true"';}
  	if($data['required']=='true'){$as='<i class="icon-asterisk"></i>'; $validate="validate-required";}
  	if($data['instructionText']){$instruct='<p class="help inline">'.$this->correct_label($data['instructionText']).'</p>';}
  	if($data['customValidationType']){
  		$validate.=" validate-".$data['customValidationType'];
  		if ($data['customValidationType'] == "DATE"){
  			$type = "dates";
  			$class="DATE";
  		} else if($data['customValidationType'] == "DATETIME") {
  			$type = "dates";
  			$class="DATETIME";
  		} else if($data['customValidationType'] == "TIME") {
  			$type = "dates";
  			$class="TIME";
  		} else if ($data['customValidationType'] == "PHONE"){
  			$type = "phones";
  		} else if($data['customValidationType'] == "REGEX") {
            $addAttr.=' REGEX="'.htmlspecialchars($data['regex']).'"';
        }

  		if (isset($data['validationMessage']) && $data['validationMessage']) {
  			$validate.=" error-message=\"".addslashes($data['validationMessage']) . "\"";
  		}
  	}

  	if($data['helpText']){$help='<i class="icon-info tooltip-container tooltip-position-top tooltip-position-left">
  				<div class="tooltip-wrapper">
  					<div class="tooltip">
  						<p>'.$data['helpText'].'</p>
  					</div></div></i>';}

    $label = $as.$this->correct_label($data['inputLabel']).$help;
    if($data['size']<12 && !$label) {
        $label='&nbsp;';
    }

  			$return='<label>'.$label.'</label><div class="controls-container">';
  	            if($data['iconEnabled']==1 && $data['iconName']){
  	            	if ($data['iconPositionRight']){ $icon_positon = 'right';}
  	            		$return.='<div class="icon icon-'.$icon_positon.'">
  							<div class="controls">
  								<i validate-clear="" class="ionicons ion-android-close"></i>
  							</div>
  							<input '.$readonly.' class="text '.$class.'" type="'.$type.'" data-inputmask="'.$input_mask.'" name="'.$data['_id'].'" '.$validate.' '.$addAttr.'>
  							<i class="fa '.$data['iconName'].'"></i>
  						</div>';
  	            } else {
  	            		$return.='<input '.$readonly.' class="text '.$class.'" type="'.$type.'" data-inputmask="'.$input_mask.'" name="'.$data['_id'].'" '.$validate.' '.$addAttr.'>';
  	            }
  		$return.='<div class="gc input-group-help error"></div>
  		<div class="gc input-group-help req-error"></div>
  	</div>'.$instruct;

  	return $return;
  }


  function elFormLookup($data, $submission, $form_owner){
    $data['defaultValue'] = $this->_overwriteDefaultValueFromExternalData($data);
  	$type="text";
  	$readonly="";
  	$class="";

    $suggestions = array();
    if(!$data['columns']) {
        $data['columns'] = array('Label','Value');
    }
    $columns = json_encode($data['columns']);
    $datasource_data = json_encode($data['optionsList']);

    foreach($data['optionsList'] as $opt) {
        $s = $opt[$data['lookupColumn']];
        if($s) {
            $suggestions[] = $s;
        }
    }

    $suggestions_string = implode(',', $suggestions);

    if($_COOKIE[$data['_id']] && $this->form['autoFill']=='1' && $this->pl->isFreeAccount($this->user) == false) {
        $data['defaultValue'] = $_COOKIE[$data['_id']];
    }

  	$data['defaultValue'] = $this->_getDefaultValueFromSubmission($submission, $data['defaultValue'], $data['inputLabel']);

    $addAttr='';

    $addAttr.=$this->_getAdditionalAttr($data);

    if(!$data['datasource_id']) {
        $data['datasource_id'] = 'noid';
    }
    $addAttr.=' data-datasource-id="'.$data['datasource_id'].'"';

  	$icon_positon = 'left';
    if($this->form['rtl']) {
        $icon_positon = 'right';
    }
    if($data['disabled']==true || $_GET['action']=='print') {$readonly = 'readonly="true"';}
  	if($data['required']=='true'){$as='<i class="icon-asterisk"></i>'; $validate="validate-required";}
  	if($data['instructionText']){$instruct='<p class="help inline">'.$this->correct_label($data['instructionText']).'</p>';}

  	if($data['helpText']){$help='<i class="icon-info tooltip-container tooltip-position-top tooltip-position-left">
  				<div class="tooltip-wrapper">
  					<div class="tooltip">
  						<p>'.$data['helpText'].'</p>
  					</div></div></i>';}

    $label = $as.$this->correct_label($data['inputLabel']).$help;
    if($data['size']<12 && !$label) {
        $label='&nbsp;';
    }

    if($data['autoSuggest']) {
        $class.="autosuggest";
    } else {
        $class.="autosuggest";
    }

    $error='Invalid Value';
    if($data['notExistsErrorMessage']) {
        $error=$data['notExistsErrorMessage'];
    }

    $addAttr.=' invalid-value="'.$error.'"';

  			$return='<label>'.$label.'</label><div class="controls-container">';
  	            if($data['iconEnabled']==1 && $data['iconName']){
  	            	if ($data['iconPositionRight']){ $icon_positon = 'right';}
  	            		$return.='<div class="icon icon-'.$icon_positon.'">
  							<div class="controls">
  								<i validate-clear="" class="ionicons ion-android-close"></i>
  							</div>
  							<input lookup '.$readonly.' class="text '.$class.'" type="'.$type.'" data-inputmask="'.$input_mask.'" name="'.$data['_id'].'" '.$validate.' '.$addAttr.'
        data-columns="'.htmlentities($columns).'" data-lookup-column="'.$data['lookupColumn'].'" data-list="">
  							<i class="fa '.$data['iconName'].'"></i>
  						</div>';
  	            } else {
  	            		$return.='<input lookup '.$readonly.' class="text '.$class.'" type="'.$type.'" data-inputmask="'.$input_mask.'" name="'.$data['_id'].'" '.$validate.' '.$addAttr.' data-columns="'.htmlentities($columns).'" data-lookup-column="'.$data['lookupColumn'].'" data-list="">';
  	            }
  		$return.='<div class="gc input-group-help error"></div>
  		<div class="gc input-group-help req-error"></div>
  	</div>'.$instruct;

  	return $return;
  }

  function elFormCalculation($data, $submission, $form_owner){
    $data['defaultValue'] = $this->_overwriteDefaultValueFromExternalData($data);
  	$type="text";
  	$readonly="";
  	$class="totalHidden";

    if($_COOKIE[$data['_id']] && $this->form['autoFill']=='1' && $this->pl->isFreeAccount($this->user) == false) {
        $data['defaultValue'] = $_COOKIE[$data['_id']];
    }

  	$data['defaultValue'] = $this->_getDefaultValueFromSubmission($submission, $data['defaultValue'], $data['inputLabel']);

    $addAttr='';
    $addAttr.=$this->_getAdditionalAttr($data);

  	$icon_positon = 'left';
    if($this->form['rtl']) {
        $icon_positon = 'right';
    }
    if($data['disabled']==true || $_GET['action']=='print') {$readonly = 'readonly="true"';}
  	if($data['required']=='true'){$as='<i class="icon-asterisk"></i>'; $validate="validate-required";}
  	if($data['instructionText']){$instruct='<p class="help inline">'.$this->correct_label($data['instructionText']).'</p>';}

  	if($data['helpText']){$help='<i class="icon-info tooltip-container tooltip-position-top tooltip-position-left">
  				<div class="tooltip-wrapper">
  					<div class="tooltip">
  						<p>'.$data['helpText'].'</p>
  					</div></div></i>';}

    $label = $as.$this->correct_label($data['inputLabel']).$help;
    if($data['size']<12 && !$label) {
        $label='&nbsp;';
    }

    $error='Invalid Value';
    if($data['notExistsErrorMessage']) {
        $error=$data['notExistsErrorMessage'];
    }

    $addAttr.=' invalid-value="'.$error.'"';

  			$return='<label>'.$label.'</label><div class="controls-container">';
  	            if($data['iconEnabled']==1 && $data['iconName']){
  	            	if ($data['iconPositionRight']){ $icon_positon = 'right';}
  	            		$return.='<div class="icon icon-'.$icon_positon.'">
  							<div class="controls">
  								<i validate-clear="" class="ionicons ion-android-close"></i>
  							</div>
  							<input '.$readonly.' class="text '.$class.'" type="'.$type.'" data-inputmask="'.$input_mask.'" name="'.$data['_id'].'" '.$validate.' '.$addAttr.'>
  							<i class="fa '.$data['iconName'].'"></i>
  						</div>';
  	            } else {
  	            		$return.='<input '.$readonly.' class="text '.$class.'" type="'.$type.'" data-inputmask="'.$input_mask.'" name="'.$data['_id'].'" '.$validate.' '.$addAttr.'>';
  	            }
  		$return.='<div class="gc input-group-help error"></div>
  		<div class="gc input-group-help req-error"></div>
  	</div>'.$instruct;

  	return $return;
  }

  private function _elFormDateElement($data, $submission, $date_type='date', $form_owner) {
    $data['defaultValue'] = $this->_overwriteDefaultValueFromExternalData($data);
  	$type="text";
  	$readonly="";
  	$class=$date_type."Picker";
  	$customAttr = "";
  	if($date_type=='date') {
  		$iclass="fa-calendar";
        $defaultDate = $data['defaultValue'] ?:'';
  		$beginDate = $data['beginDate'] ?:'';
  		$endDate = $data['endDate'] ?:'';
  		$dateFormat = $data['dateFormat'] ?:$form_owner['dateformat'];
        $dateFormat = $this->pl->getValidDateFormat($dateFormat);
        $dateLang = $data['pickerLang'];
  		$customAttr = 'beginDate="'.$beginDate.'" endDate="'.$endDate.'" defaultDate="'.$defaultDate.'" dateFormat="'.$dateFormat.'" dateLang="'.$dateLang.'"';
  	} else if($date_type=='datetime') {
  		$iclass="fa-calendar";
        $defaultDate = $data['defaultValue'] ?: '';
  		$beginDate = $data['beginDate'] ?:'';
  		$endDate = $data['endDate'] ?:'';
        $minTime = $data['minTime'] ?:'';
  		$maxTime = $data['maxTime'] ?:'';
        $dateFormat = $data['dateFormat'] ?:$form_owner['dateformat'];
        $dateFormat = $this->pl->getValidDateFormat($dateFormat);
        $dateLang = $data['pickerLang'];
  		$customAttr = 'beginDate="'.$beginDate.'" endDate="'.$endDate.'" interval="'.$data['interval'].'" use12Notation="'.$data['use12Notation'].'" defaultDate="'.$defaultDate.'" minTime="'.$minTime.'" maxTime="'.$maxTime.'" dateFormat="'.$dateFormat.'" dateLang="'.$dateLang.'"';
  	} else if($date_type=='time') {
  		$iclass="fa-clock-o";
        $minTime = $data['minTime'] ?:'';
  		$maxTime = $data['maxTime'] ?:'';
  		$customAttr = 'interval="'.$data['interval'].'" use12Notation="'.$data['use12Notation'].'" minTime="'.$minTime.'" maxTime="'.$maxTime.'"';
  	}

    if($date_type=='datetime' || $date_type=='date') {
        $disabledDays = array();
        if($data['dM']) {$disabledDays[]=1;}
        if($data['dT']) {$disabledDays[]=2;}
        if($data['dW']) {$disabledDays[]=3;}
        if($data['dTH']) {$disabledDays[]=4;}
        if($data['dF']) {$disabledDays[]=5;}
        if($data['dSat']) {$disabledDays[]=6;}
        if($data['dSun']) {$disabledDays[]=0;}

        $disabledDays = implode(',',$disabledDays);

        $customAttr.=' disabledDays="'.$disabledDays.'"';
    }

    if($_COOKIE[$data['_id']] && $this->form['autoFill']=='1' && $this->pl->isFreeAccount($this->user) == false) {
        $data['defaultValue'] = $_COOKIE[$data['_id']];
    }

  	$data['defaultValue'] = $this->_getDefaultValueFromSubmission($submission, $data['defaultValue'], $data['inputLabel']);

    $addAttr.=$this->_getAdditionalAttr($data);

  	$icon_positon = 'left';
    if($this->form['rtl']) {
        $icon_positon = 'right';
    }
    	if($data['disabled']==true) {$readonly = 'readonly="true"';}
    	if($_GET['action']=='print') {$readonly = 'disabled="true"';}
  	if($data['required']=='true'){$as='<i class="icon-asterisk"></i>'; $validate="validate-required";}
  	if($data['instructionText']){$instruct='<p class="help inline">'.$this->correct_label($data['instructionText']).'</p>';}
  	if($data['helpText']){$help='<i class="icon-info tooltip-container tooltip-position-top tooltip-position-left">
  				<div class="tooltip-wrapper">
  					<div class="tooltip">
  						<p>'.$data['helpText'].'</p>
  					</div></div></i>';}

    $label = $as.$this->correct_label($data['inputLabel']).$help;
    if($data['size']<12 && !$label) {
        $label='&nbsp;';
    }

  			$return='<label>'.$label.'</label><div class="controls-container">';

      		$return.='<div class="icon icon-'.$icon_positon.'">
  				<div class="controls">
  					<i validate-clear="" class="ionicons ion-android-close"></i>
  				</div>
  				<input '.$customAttr.' '.$readonly.' class="text '.$class.'" type="'.$type.'" data-inputmask="'.$input_mask.'" name="'.$data['_id'].'" id="field'.$data['_id'].'" '.$validate.' '.$addAttr.'>
  				<i class="fa '.$iclass.'"></i>
  			</div>';

  		$return.=$instruct.'<div class="gc input-group-help error"></div>
  		<div class="gc input-group-help req-error"></div>
  	</div>';

  	return $return;
  }

  function elFormDate($data, $submission, $form_owner) {
  	return $this->_elFormDateElement($data, $submission, 'date', $form_owner);
  }

  function elFormTime($data, $submission, $form_owner) {
  	return $this->_elFormDateElement($data, $submission, 'time', $form_owner);
  }

  function elFormDatetime($data, $submission, $form_owner) {
  	return $this->_elFormDateElement($data, $submission, 'datetime', $form_owner);
  }

  function elFormName($data, $submission, $form_owner){
  	$readonly="";
    $title = "";
  	$fname = "";
  	$mname = "";
  	$lname = "";

    if($_COOKIE[$data['_id']] && $this->form['autoFill']=='1' && $this->pl->isFreeAccount($this->user) == false) {
        $values = explode(',', $_COOKIE[$data['_id']]);
        if(count($values) > 3) {
            $title = trim($values[0]);
            $fname = trim($values[1]);
            $mname = trim($values[2]);
            $lname = trim($values[3]);
        } else if(count($values) > 2) {
            if($data['nameTitle']) {
                $title = trim($values[0]);
                $fname = trim($values[1]);
                $lname = trim($values[2]);
            } else {
                $fname = trim($values[0]);
                $mname = trim($values[1]);
                $lname = trim($values[2]);
            }
        } else {
            $fname = trim($values[0]);
            $lname = trim($values[1]);
        }
    }

    $data['defaultValue'] = $this->_getDefaultValueFromSubmission($submission, $data['defaultValue'], $data['inputLabel']);
    $values = explode(',', $data['defaultValue']);
    if(count($values) > 3) {
        $title = trim($values[0]);
        $fname = trim($values[1]);
        $mname = trim($values[2]);
        $lname = trim($values[3]);
    } else if(count($values) > 2) {
        if($data['nameTitle']) {
            $title = trim($values[0]);
            $fname = trim($values[1]);
            $lname = trim($values[2]);
        } else {
            $fname = trim($values[0]);
            $mname = trim($values[1]);
            $lname = trim($values[2]);
        }
    } else {
        $fname = $values[0];
        $lname = $values[1];
    }

    if($fname) {
        $attrFname='value="'.$fname.'"';
    }
    if($lname) {
        $attrLname='value="'.$lname.'"';
    }
    if($mname) {
        $attrMname='value="'.$mname.'"';
    }

  	  if($data['disabled']==true || $_GET['action']=='print') {$readonly = 'readonly="true"';}
      if($data['required']=='true'){$as='<i class="icon-asterisk"></i>'; $validate="validate-required";}
      if($data['instructionText']){$instruct='<p class="help inline">'.$this->correct_label($data['instructionText']).'</p>';}
        if($data['middleName'] && !$data['nameTitle']) {
            $nr=5;
        } else if(!$data['middleName'] && $data['nameTitle']) {
            $nr=5;
        } else if($data['middleName'] && $data['nameTitle']) {
            $nr=4;
        } else {
            $nr=6;
        }

        if($data['middleName']) {
            $middle='<div class="gc pad-bottom medium-2">
                <fieldset>
                    <input '.$readonly.' '.$attrMname.' type="text" name="'.$data['_id'].'[]" class="text" placeholder="'.$data['placeholderMiddleText'].'">
                </fieldset>
            </div>';
        }
        if($data['nameTitle']) {
            $titleField='<div class="gc pad-bottom medium-2">
                <fieldset>
                    <div class="select">
                        <select class="text" name="'.$data['_id'].'[]">
                            <option value="">'.$data['placeholderTitleText'].'</option>
                            <option value="Mr.">Mr</option>
                            <option value="Mrs.">Mrs</option>
                            <option value="Ms.">Ms</option>
                            <option value="Dr.">Dr</option>
                            <option value="Prof.">Prof</option>
                            <option value="Sir">Sir</option>
                            <option value="Pr.">Pr</option>
                            <option value="Br.">Br</option>
                            <option value="Sr.">Sr</option>
                        </select>
                    </div>
                </fieldset>
            </div>';
        }

        if($this->form['rtl']) {
            $return='<div class="gc pad-top medium-12">
      			<label>'.$as.$this->correct_label($data['inputLabel']).'</label>
      		</div>
                '.$titleField.'
                <div class="gc pad-bottom medium-'.$nr.'">
                    <fieldset>
      				     <input '.$readonly.' '.$attrLname.' type="text" name="'.$data['_id'].'[]" '.$validate.' placeholder="'.$data['placeholderLastText'].'" class="text">
                    </fieldset>
                </div>
                '.$middle.'
              <div class="gc pad-bottom medium-'.$nr.'">
                  <fieldset>
    				      <input '.$readonly.' '.$attrFname.' type="text" name="'.$data['_id'].'[]" '.$validate.' placeholder="'.$data['placeholderFirstText'].'" class="text">
                  </fieldset>
              </div>
      		<div class="gc" style="margin-top: -5px; margin-bottom: 3px">
                        '.$instruct.'
      		</div>
      		<div class="gc input-group-help error"></div>
      		<div class="gc input-group-help req-error"></div>';
        } else {
            $return='<div class="gc pad-top medium-12">
      			<label>'.$as.$this->correct_label($data['inputLabel']).'</label>
      		</div>
                '.$titleField.'
      			<div class="gc pad-bottom medium-'.$nr.'">
                    <fieldset>
      				      <input '.$readonly.' '.$attrFname.' type="text" name="'.$data['_id'].'[]" '.$validate.' placeholder="'.$data['placeholderFirstText'].'" class="text">
                    </fieldset>
                </div>
                              '.$middle.'
      			<div class="gc pad-bottom medium-'.$nr.'">
                    <fieldset>
      				     <input '.$readonly.' '.$attrLname.' type="text" name="'.$data['_id'].'[]" '.$validate.' placeholder="'.$data['placeholderLastText'].'" class="text">
                    </fieldset>
                </div>
      		<div class="gc" style="margin-top: -5px; margin-bottom: 3px">
                        '.$instruct.'
      		</div>
      		<div class="gc input-group-help error"></div>
      		<div class="gc input-group-help req-error"></div>';
        }

  		return $return;
  }

  function elFormTextarea($data, $submission, $form_owner){
    $data['defaultValue'] = $this->_overwriteDefaultValueFromExternalData($data);
  	$readonly="";

    if($_COOKIE[$data['_id']] && $this->form['autoFill']=='1' && $this->pl->isFreeAccount($this->user) == false) {
        $data['defaultValue'] = $_COOKIE[$data['_id']];
    }

  	$data['defaultValue'] = $this->_getDefaultValueFromSubmission($submission, $data['defaultValue'], $data['inputLabel']);

    $validate='';
        if($data['disabled']==true || $_GET['action']=='print') {$readonly = 'readonly="true"';}
        if($data['required']=='true'){$as='<i class="icon-asterisk"></i>'; $validate.="validate-required";}
        if($data['instructionText']){$instruct='<p class="help inline">'.$this->correct_label($data['instructionText']).'</p>';}
        if($data['helpText']){$help='<i class="icon-info tooltip-container tooltip-position-top tooltip-position-left">
  				<div class="tooltip-wrapper">
  					<div class="tooltip">
  						<p>'.$data['helpText'].'</p>
  					</div></div></i>';}

    if($data['textMaxLength'] && is_numeric($data['textMaxLength'])) {
        $error = 'This field exceeded the maximum characters allowed.';
        if($data['maxLengthErrorMessage']) {
            $error = $data['maxLengthErrorMessage'];
        }
        $remaining = '<div class="rc"><span class="remainingChar">0</span> / <span class="maxChar">'.$data['textMaxLength'].'</span></div>';
        $addAttr = 'max-char="'.$data['textMaxLength'].'" error-message="'.htmlspecialchars($error).'"';
        $validate.= " validate-maxlength";
    } else {
        $remaining = '';
        $addAttr='';
    }

    if(!$data['textAreaHeight']) {
      $height = (4 * 22.987) + 15.2;
    } else {
      $height= $data['textAreaHeight'];
    }

    $label = $as.$this->correct_label($data['inputLabel']).$help;
    if($data['size']<12 && !$label) {
        $label='&nbsp;';
    }

    		$return='<label>'.$label.'</label>';
    		$return.='<textarea '.$readonly.' name="'.$data['_id'].'" id="textarea_'.$data['_id'].'" class="text" '.$validate.' style="height:'.$height.'px;" placeholder="'.$data['placeholderText'].'" '.$addAttr.'>'.$data['defaultValue'].'</textarea>';


  		$return.=$instruct.'<div class="gc input-group-help error">'.$remaining.'</div><div class="gc input-group-help req-error"></div>';

    return $return;
  }
  //

  function elFormInputtable($data, $submission, $form_owner) {
    $readonly="";

    $submission = array();
    if(count($submission)) {
        $submission = json_decode(str_replace('\\','',$submission[0]["data"]),true);
        if(!$submission) {
            $submission = json_decode($submission[0]["data"], true);
        }
    }

    if($data['disabled']==true || $_GET['action']=='print') {$readonly = 'readonly="true"';}
    if($data['required']=='true'){$as='<i class="icon-asterisk"></i>'; $validate="validate-required";}
    if($data['instructionText']){$instruct='<p class="help inline">'.$this->correct_label($data['instructionText']).'</p>';}
    if($data['helpText']){$help='<i class="icon-info tooltip-container tooltip-position-top tooltip-position-left">
            <div class="tooltip-wrapper">
                <div class="tooltip">
                    <p>'.$data['helpText'].'</p>
                </div></div></i>';}

    $label = $as.$this->correct_label($data['inputLabel']).$help;
    if($data['size']<12 && !$label) {
        $label='&nbsp;';
    }

    $input_type = 'radio';
    if($data['inputtype']) {
        $input_type = $data['inputtype'];
    }

    $return='<label>'.$label.'</label>';

    $return.='<table class="input_table"><thead><tr><th scope="col"></th>';
    for($c=0;$c<count($data['answerList']);$c++){
        $return.='<th class="gray" scope="col">'.$data['answerList'][$c]['label'].'</th>';
    }
    $return.='</tr></thead><tbody>';
    for($c=0;$c<count($data['questionList']);$c++){

        $labelwithQ = $data['inputLabel'] . ' ' . $data['questionList'][$c]['label'];
        foreach($submission as $s) {
            if($s['label'] == $data['inputLabel'] . ' ' . $data['questionList'][$c]['label']) {
                $defaultValue = $s['value'];
                $label = $s['label'];
                break;
            }
        }

        $return.='<tr class="question">';
        $return.='<td class="gray" data-label="">'.$data['questionList'][$c]['label'].'</td>';
        $disabled = '';
        for($x=0;$x<count($data['answerList']);$x++) {
            if($defaultValue == addslashes($data['answerList'][$x]['label']) && $label == $labelwithQ) {
                $disabled = 'disabled checked';
            }

            $value = addslashes($data['answerList'][$x]['label']);
            if($data['answerList'][$x]['value']) {
                $value = addslashes($data['answerList'][$x]['value']);
            }

            if($input_type == 'radio') {
                $return.='<td class="ans" data-label="'.$data['answerList'][$x]['label'].'">
                    <label class="option">
                        <input '.$disabled.' type="radio" name="'.$data['_id'].'[\''.$this->pl->slugify($data['questionList'][$c]['label']).'\']" value="'.$value.'" '.$validate.'>&nbsp;<i></i>
                    </label>
                </td>';
            } else if($input_type == 'text') {
                $return.='<td class="ans" data-label="'.$data['answerList'][$x]['label'].'">

                        <input '.$disabled.' class="text" type="text" name="'.$data['_id'].'[\''.$this->pl->slugify($data['questionList'][$c]['label']).'\'][]" '.$validate.'>&nbsp;<i></i>

                </td>';
            } else if($input_type == 'checkbox') {
                $return.='<td class="ans" data-label="'.$data['answerList'][$x]['label'].'">
                    <label class="option">
                        <input '.$disabled.' type="checkbox" name="'.$data['_id'].'[\''.$this->pl->slugify($data['questionList'][$c]['label']).'\'][]" value="'.$value.'" '.$validate.'>&nbsp;<i></i>
                    </label>
                </td>';
            }


        }
        $return.='</tr>';
    }
    $return.='</tbody></table>';

        $return.=$instruct.'<div class="gc input-group-help error"></div>
        <div class="gc input-group-help req-error"></div>';
    return $return;
  }

  //
  function elFormCheckbox($data, $submission, $form_owner){
  	$readonly='';
  	$validate='';

    $data['defaultValue'] = $this->_getDefaultValueFromSubmission($submission, $data['defaultValue'], $data['inputLabel']);

    $defaultData = explode(', ', $data['defaultValue']);

  	if($data['disabled']==true || $_GET['action']=='print') {$readonly = 'disabled="true"';}
        if($data['required']=='true'){$as='<i class="icon-asterisk"></i>'; $validate="validate-required";}
        if($data['instructionText']){$instruct='<p class="help inline">'.$this->correct_label($data['instructionText']).'</p>';}
        if($data['helpText']){$help='<i class="icon-info tooltip-container tooltip-position-top tooltip-position-left">
  			<div class="tooltip-wrapper">
  			<div class="tooltip">
  				<p>'.$data['helpText'].'</p>
  			</div></div></i>';}

        $label = $as.$this->correct_label($data['inputLabel']).$help;
        if($data['size']<12 && !$label) {
            $label='&nbsp;';
        }

    		$return='<label>'.$label.'</label>';
          for($c=0;$c<count($data['optionsList']);$c++){
                $value = $data['optionsList'][$c]['value'];
                if($this->pl->notempty($value) == false) {
                    $value = $data['optionsList'][$c]['label'];
                }

                if(!$value && $value<>'0') { continue; }

            	if($data['optionsList'][$c]['selected']){$selected='selected';}
            	if($data['optionsList'][$c]['disabled']){$selected='disabled';}

                if(in_array($value, $defaultData)) {
                    $selected = 'checked';
                } else {
                    $selected = '';
                }

                if($_GET['action']!='print' && $data['optionsList'][$c]['default']) {
                    $selected = 'checked';
                }
  			$return.='<div>
                  <label class="option" '.$disabled.'>
  					<input id="'.$data['optionsList'][$c]['_id'].'" '.$selected.' '.$readonly.' '.$validate.' type="checkbox" name="'.$data['_id'].'[]" value="'.htmlentities($value).'">
  					'.$data['optionsList'][$c]['label'].'<i></i>
  				</label>
  			</div>';
          }

          if($data['otherOption']) {
          	$return.='<div class="product_container">
                  <label class="option other_label" '.$disabled.'>
  					<input class="other_input" '.$selected.' '.$readonly.' '.$validate.' type="checkbox" name="'.$data['_id'].'[]" value="'.$data['otherOptionLabel'].'">
  					'.$data['otherOptionLabel'].':<i></i>
  					<input type="text" class="text other_option" />
  				</label>
  			</div>';
          }

  		$return.=$instruct.'<div class="gc input-group-help error"></div>
  		<div class="gc input-group-help req-error"></div>';
    return $return;
  }
  //

  function elFormProducts($data, $submission, $form_owner) {
  	$currency = $this->form['currency'];
  	$currencySymbol='$';
    if($currency!='USD') {$currencySymbol='';}
    if($data['unit'] != 'currency' && !empty($data['unit'])) {
        $currency = $data['unit'];
        $currencySymbol='';
    }
  	$readonly='';
  	$validate='';

    $data['defaultValue'] = $this->_getDefaultValueFromSubmission($submission, $data['defaultValue'], $data['inputLabel']);

    $defaultData = explode(', ', $data['defaultValue']);

  	if($data['disabled']==true) {$readonly = 'readonly="true"';}
  	if($_GET['action']=='print') {$readonly = 'disabled="true"';}
        if($data['required']=='true'){$as='<i class="icon-asterisk"></i>'; $validate="validate-required";}
        if($data['instructionText']){$instruct='<p class="help inline">'.$this->correct_label($data['instructionText']).'</p>';}
        if($data['helpText']){$help='<i class="icon-info tooltip-container tooltip-position-top tooltip-position-left">
  			<div class="tooltip-wrapper">
  			<div class="tooltip">
  				<p>'.$data['helpText'].'</p>
  			</div></div></i>';}

            $label = $as.$this->correct_label($data['inputLabel']).$help;
            if($data['size']<12 && !$label) {
                $label='&nbsp;';
            }

    		$return='<label>'.$label.'</label>';
    		if($data['enableAmount']) {
    			$return.='<table class="productListDisplay">';
    		}

    		if($data['useSelect']) {
    			if($data['enableAmount']) {
    				$return.='<tr class="product_container"><td class="other_label"><div class="select">';
    			} else {
    				$return.='<div class="select product_container">';
    			}

    			$return.='<select name="'.$data['_id'].'[]" class="text product_input" '.$validate.' '.$readonly.'><option></option>';

        		for($c=0;$c<count($data['productsList']);$c++){
                    if($data['productsList'][$c]) {
                       $value = $data['productsList'][$c]['value'] ?: 0;
                        $input_value = $data['productsList'][$c]['label'].' ('.$currencySymbol.$value.' '.$currency.')';
                        $label = $data['productsList'][$c]['label'].' ('.$currencySymbol.$value.' '.$currency.')';
                        if($data['productsList'][$c]['selected']){$selected='selected';}
                        if($data['productsList'][$c]['disabled']){$selected='disabled';}

                        $return.='<option value="'.htmlentities('0//'.$input_value.'//'.$value).'">'.$label.'</option>';
                    }
      	        }
      	        if($data['enableAmount']) {
      	        	$options = '';
                		if($data['enableAmountLabel']) {
                			$options = '<option value="">'.$data['enableAmountLabel'].'</option>';
                		}
                		for($o=0;$o<count($data['optionsList']);$o++){
                			$options .= '<option value="'.$data['optionsList'][$o]['value'].'">'.$data['optionsList'][$o]['label'].'</option>';
                		}

      	        	$return.='</select></div>
      				<span class="currency" style="display:none">'.$currency.'</span>
      				<span class="currencySymbol" style="display:none">'.$currencySymbol.'</span>
      	        	</td>
      	        	<td class="priceColumn">
      	        		<select class="text other_option product_qty no-validate select_qty" name="qty[\''.$data['_id'].'\'][]">'.$options.'</select>
      	        	</td>
      	        	<td class="totalColumn select_total"><span class="total"></span></td>
      	        	</tr>';
      	        } else {
      	        	$return.='</select></div>';
      	        }

    		} else {
    			for($c=0;$c<count($data['productsList']);$c++) {
                    if($data['productsList'][$c]) {
          	        	$value = $data['productsList'][$c]['value'] ?: 0;
          	        	$input_value = $data['productsList'][$c]['label'].' ('.$currencySymbol.$value.' '.$currency.')';
          	        	$label = $data['productsList'][$c]['label'].' ('.$currencySymbol.$value.' '.$currency.')';
          	          	if($data['productsList'][$c]['selected']){$selected='selected';}
          	          	if($data['productsList'][$c]['disabled']){$selected='disabled';}

                        if(in_array($input_value, $defaultData)) {
                            $selected = 'checked';
                        } else {
                            $selected = '';
                        }

          	          	if($data['enableAmount']) {
          	          		$options = '';
          	          		if($data['enableAmountLabel']) {
          	          			$options = '<option value="">'.$data['enableAmountLabel'].'</option>';
          	          		}
          	          		for($o=0;$o<count($data['optionsList']);$o++){
          	          			$options .= '<option value="'.$data['optionsList'][$o]['value'].'">'.$data['optionsList'][$o]['label'].'</option>';
          	          		}
          	          		$return.='<tr class="product_container">
          		                <td class="other_label" '.$disabled.'>
                                    <input class="no-validate" type="hidden" name="price[\''.$data['_id'].'\']['.$c.']" value="'.htmlentities($value).'">
          		                	<label class="option">
          							<input class="other_input product_input" id="'.$data['productsList'][$c]['_id'].'" '.$selected.' '.$readonly.' '.$validate.' type="checkbox" name="'.$data['_id'].'[]" value="'.htmlentities($c.'//'.$input_value).'">
          							'.$input_value.'<i></i>
          							</label>
          							<span class="productPrice" style="display:none">'.$value.'</span>
          							<span class="currency" style="display:none">'.$currency.'</span>
          							<span class="currencySymbol" style="display:none">'.$currencySymbol.'</span>
          						</td>
          						<td class="priceColumn">
          							<select class="text other_option product_qty no-validate" name="qty[\''.$data['_id'].'\'][]">'.$options.'</select>
          						</td>
          						<td class="totalColumn"><span class="total"></span></td>
          					</tr>';

          	          	} else {
          	          		$return.='<div class="product_container">
                                <input class="no-validate" type="hidden" name="price[\''.$data['_id'].'\']['.$c.']" value="'.htmlentities($value).'">
          		                <label class="option other_label" '.$disabled.'>
          							<input class="product_input" id="'.$data['productsList'][$c]['_id'].'" '.$selected.' '.$readonly.' '.$validate.' type="checkbox" name="'.$data['_id'].'[]" value="'.htmlentities($c.'//'.$input_value).'">
          							'.$label.'<i></i>
          							<span class="productPrice" style="display:none">'.$value.'</span>
          							<span class="currency" style="display:none">'.$currency.'</span>
          							<span class="currencySymbol" style="display:none">'.$currencySymbol.'</span>
          						</label>
          					</div>';
          	          	}
                    }
                }
    		}

          if($data['enableAmount']) {
          	$return.='</table>';
      	}
  		$return.=$instruct.'<div class="gc input-group-help error"></div>
  		<div class="gc input-group-help req-error"></div>';
    return $return;
  }

  //
  function elFormUs_address($data, $submission, $form_owner){
  	$readonly='';
  	if($data['disabled']==true || $_GET['action']=='print') {$readonly = 'readonly="true"';}
      if($data['required']=='true'){$as='<i class="icon-asterisk"></i>'; $validate="validate-required";}
      if($data['instructionText']){$instruct='<p class="help inline">'.$this->correct_label($data['instructionText']).'</p>';}
      if($data['helpText']){$help='<i class="icon-info tooltip-container tooltip-position-top tooltip-position-left">
  				<div class="tooltip-wrapper">
  					<div class="tooltip">
  						<p>'.$data['helpText'].'</p>
  					</div></div></i>';}

    $address1 = "";
    $address2 = "";
    $city = "";
    $state = "";
    $zip = "";
    $country = "";
    if($_COOKIE[$data['_id']] && $this->form['autoFill']=='1' && $this->pl->isFreeAccount($this->user) == false) {
        $values = explode(',', $_COOKIE[$data['_id']]);
        if(count($values) > 5) {
            $address1 = trim($values[0]);
            $address2 = trim($values[1]);
            $city = trim($values[2]);
            $state = trim($values[3]);
            $zip = trim($values[4]);
            $country = trim($values[5]);
        } else {
            $address1 = trim($values[0]);
            $address2 = trim($values[1]);
            $city = trim($values[2]);
            $state = trim($values[3]);
            $zip = trim($values[4]);
        }
    }

    if($data['defaultCountry']) {
        $country = $data['defaultCountry'];
    }

    $data['defaultValue'] = $this->_getDefaultValueFromSubmission($submission, $data['defaultValue'], $data['inputLabel']);
    $values = explode(',', $data['defaultValue']);
    if(count($values) > 5) {
        $address1 = trim($values[0]);
        $address2 = trim($values[1]);
        $city = trim($values[2]);
        $state = trim($values[3]);
        $zip = trim($values[4]);
        $country = trim($values[5]);
    } else {
        $address1 = trim($values[0]);
        $address2 = trim($values[1]);
        $city = trim($values[2]);
        $state = trim($values[3]);
        $zip = trim($values[4]);
    }

  	if(isset($data['format'])) {
  		$states = $GLOBALS['ref']['element_address'][$data['format']]['states'];
  		if($GLOBALS['ref']['element_address'][$data['format']]['state_type'] == 'select') {
  			foreach($states as $value => $state1) {
                if($state == $state1) {
                    $stateoptions.='<option value="'.$value.'" selected>'.$state1.'</option>';
                } else {
                    $stateoptions.='<option value="'.$value.'">'.$state1.'</option>';
                }
  			}

  			$stateField = '<fieldset class="select"><select class="text" name="state_'.$data['_id'].'" '.$readonly.' ' .$validate.' autocomplete="state">
          		<option disabled="" selected="" style="display:none;">'.$data['placeholderStateText'].'</option>
          	'.$stateoptions.'
        		</select></fieldset>';
  		} else {
  			$stateField = '<fieldset><input value="'.$state.'" class="text static" placeholder="'.$data['placeholderStateText'].'" type="text" name="state_'.$data['_id'].'" '.$readonly.' ' .$validate.' autocomplete="state"></fieldset>';
  		}
  	} else {
  		$states=$GLOBALS['ref']['element_address']['US']['states'];

  		foreach($states as $value => $state1) {
            if($state == $state1) {
                $stateoptions.='<option value="'.$value.'" selected>'.$state1.'</option>';
            } else {
                $stateoptions.='<option value="'.$value.'">'.$state1.'</option>';
            }
  		}

  		$stateField = '<fieldset class="select"><select class="text" name="state_'.$data['_id'].'" '.$readonly.' ' .$validate.' autocomplete="state">
      		<option value="" disabled="" selected="" style="display:none;">'.$data['placeholderStateText'].'</option>
      	'.$stateoptions.'
    		</select></fieldset>';
  	}

  	if(isset($data['country']) && $data['country']) {
  		$countryField = '<div class="gc pad-half-compact g12">
            <fieldset>
                <input value="'.$country.'" type="text" placeholder="'.$data['placeholderCountryText'].'" class="text static" name="country_'.$data['_id'].'" '.$readonly.' ' .$validate.' autocomplete="country">
            </fieldset>
        </div>';
  	} else {
  		$countryField = '';
  	}

    $label = $as.$this->correct_label($data['inputLabel']).$help;
    if($data['size']<12 && !$label) {
        $label='&nbsp;';
    }

  					$return.='
  					  	<div class="gc" style="padding-top: 7.5px;">
  					  		<label>'.$label.'</label>
  					  	</div>
  					  	<div class="gc pad-half-all" style="padding-top: 0; padding-bottom: 0;">
  					    <div class="gc pad-half-compact g12">
                            <fieldset>
                                <input value="'.$address1.'" type="text" name="addr_1_'.$data['_id'].'" placeholder="'.$data['placeholderAddress1Text'].'" autocomplete="address 1" class="text static" '.$readonly.' ' .$validate.'>
                            </fieldset>
                        </div>
  					    <div class="gc pad-half-compact g12">
                            <fieldset>
                                <input value="'.$address2.'" type="text" name="addr_2_'.$data['_id'].'" placeholder="'.$data['placeholderAddress2Text'].'" autocomplete="address 2" class="text static" '.$readonly.'>
                            </fieldset>
                        </div>
  					    <div class="gc pad-half-compact medium-12 large-4">
                            <fieldset>
                                <input value="'.$city.'" type="text" name="city_'.$data['_id'].'" placeholder="'.$data['placeholderCityText'].'" autocomplete="city" class="text static" '.$readonly.' ' .$validate.'>
                            </fieldset>
                        </div>
  					    <div class="gc pad-half-compact medium-0 large-1"></div>
  					    <div class="gc pad-half-compact medium-6 large-4">
  					      	'.$stateField.'
  					    </div>
  					    <div class="gc pad-half-compact medium-6 large-3">
                            <fieldset>
                                <input value="'.$zip.'" type="text" name="zip_'.$data['_id'].'" placeholder="'.$data['placeholderZipText'].'" autocomplete="zip code" class="text static" '.$readonly.' ' .$validate.'>
                            </fieldset>
                        </div>
  					    '.$countryField.'
  					  </div>
  				<div class="gc" style="margin-top: -4px;">'.$instruct.'</div>';
  		$return.='<div class="gc input-group-help error"></div>
  		<div class="gc input-group-help req-error"></div>';

    		return $return;
  }
  //

  //
  function elFormSection($data, $submission, $post=null, $form_owner=null){

      if($post && !$form_owner) {
          $form_owner = $post;
          $post = null;
      }

      $class="";
      if($data['textSize']) {
          $class = "h".$data['textSize'];
      }

      if($post) {
          foreach ($post as $field) {
              $form_field=$this->lo->getFormElement(array('form_id' => $form['_id'], 'element_id' => $field['field']));
              if ($form_field['queryName']) {
                  $field['label']=$form_field['queryName'];
              }
              $data['labelText']=str_ireplace('{'.$field['label'].'}', $field['value'], $data['labelText']);
          }
      }

  	return '<h2 style="white-space: pre-wrap" class="'.$class.'">'.$data['labelText'].'</h2>
          <hr>';
  }
  //

  //
  function elFormRange($data, $submission, $form_owner){
  	$readonly='';

    $data['defaultValue'] = $this->_getDefaultValueFromSubmission($submission, $data['defaultValue'], $data['inputLabel']);

  	if($data['disabled']==true || $_GET['action']=='print') {$readonly = 'disabled="true"';}
      if($data['required']=='true'){$as='<i class="icon-asterisk"></i>'; $validate="validate-required";}
      if($data['instructionText']){$instruct='<p class="help inline">'.$this->correct_label($data['instructionText']).'</p>';}
      if($data['helpText']){$help='<i class="icon-info tooltip-container tooltip-position-top tooltip-position-left">
  				<div class="tooltip-wrapper">
  					<div class="tooltip">
  						<p>'.$data['helpText'].'</p>
  					</div></div></i>';}

    $label = $as.$this->correct_label($data['inputLabel']).$help;
    if($data['size']<12 && !$label) {
        $label='&nbsp;';
    }

    		$return='<label>'.$label.'</label>
  		<div fm-layout="table">
  			<div fm-input-group="range" class="tooltip-container tooltip-position-top" '.$readonly.'>
  				<input id="'.$data['_id'].'" type="range" name="'.$data['_id'].'" min="'.$data['rangeMin'].'" max="'.$data['rangeMax'].'" step="1" '.$validate.' value="'.$data['defaultValue'].'" '.$readonly.'>
  				<div class="tooltip-wrapper tooltip-range">
  					<div class="tooltip">
  						<p></p>
  					</div>
  				</div>
  			</div>
  			<div fm-input-group="output-container">
  				<output>41</output>
  			</div>
  		</div>
  		'.$instruct;

  		$return.='<div class="gc input-group-help error"></div>
  		<div class="gc input-group-help req-error"></div>';

  	return $return;

  }//

  //
  function elFormSwitch($data, $submission, $form_owner){
  	$readonly='';
  	$validate='';

    $data['defaultValue'] = $this->_getDefaultValueFromSubmission($submission, $data['defaultValue'], $data['inputLabel']);

    $defaultData = explode(', ', $data['defaultValue']);

  	$on = $data['onLabel'] ?:'ON';
  	$off = $data['offLabel'] ?: 'OFF';
  	if($data['disabled']==true || $_GET['action']=='print') {$readonly = 'disabled="true"';}
      if($data['required']=='true'){$as='<i class="icon-asterisk"></i>'; $validate="validate-required";}
      if($data['instructionText']){$instruct='<p class="help inline">'.$this->correct_label($data['instructionText']).'</p>';}
      if($data['helpText']){$help='<i class="icon-info tooltip-container tooltip-position-top tooltip-position-left">
  				<div class="tooltip-wrapper">
  					<div class="tooltip">
  						<p>'.$data['helpText'].'</p>
  					</div></div></i>';}

    $label = $as.$this->correct_label($data['inputLabel']).$help;
    if($data['size']<12 && !$label) {
        $label='&nbsp;';
    }

    		$return='<label>'.$label.'</label>';

    		for($c=0;$c<count($data['optionsList']);$c++){
                    $value = $data['optionsList'][$c]['value'];
                    if($this->pl->notempty($value) == false) {
                        $value = $data['optionsList'][$c]['label'];
                    }

                    if(!$value && $value<>'0') { continue; }

                    if($data['optionsList'][$c]['selected']){$selected='selected';}
                    if($data['optionsList'][$c]['disabled']){$selected='disabled';}

                    if(in_array($value, $defaultData)) {
                        $selected = 'checked';
                    } else {
                        $selected = '';
                    }

                    if($_GET['action']!='print' && $data['optionsList'][$c]['default']) {
                        $selected = 'checked';
                    }

  			$return.='<div>
                          <label class="option switch" '.$disabled.'>
  							<input id="'.$data['optionsList'][$c]['_id'].'" '.$selected.' '.$readonly.' '.$validate.' type="checkbox" name="'.$data['_id'].'[]" value="'.htmlentities($value).'">
  							'.$data['optionsList'][$c]['label'].'
  							<span class="switch-container">
  								<span class="switch-status on">'.$on.'</span>
  								<span class="switch-status off">'.$off.'</span>
  								<i></i>
  							</span>
  						</label>
  					</div>';
          }

          if($data['otherOption']) {
          	$return.='<div class="product_container">
                  <label class="option switch other_label" '.$disabled.'>
  					<input class="other_input" '.$selected.' '.$readonly.' '.$validate.' type="checkbox" name="'.$data['_id'].'[]" value="'.$data['otherOptionLabel'].'">
  					'.$data['otherOptionLabel'].':
  					<span class="switch-container">
  						<span class="switch-status on">'.$on.'</span>
  						<span class="switch-status off">'.$off.'</span>
  						<i></i>
  					</span>
  					<input type="text" class="text other_option" />
  				</label>
  			</div>';
          }

  		$return.=$instruct.'
                  <div class="gc input-group-help error"></div>
  	<div class="gc input-group-help req-error"></div>';

  	return $return;
  }
  //

  //
  function elFormSelect($data, $submission, $form_owner){
  	$readonly='';
  	if($data['disabled']==true || $_GET['action']=='print') {$readonly = 'readonly="true"';}
      if(!$data['placeholderText']){$data['placeholderText']="Select";}
      if($data['required']=='true'){$as='<i class="icon-asterisk"></i>'; $validate="validate-required";}
      if($data['instructionText']){$instruct='<p class="help inline">'.$this->correct_label($data['instructionText']).'</p>';}

    if($_COOKIE[$data['_id']] && $this->form['autoFill']=='1' && $this->pl->isFreeAccount($this->user) == false) {
        $data['defaultValue'] = $_COOKIE[$data['_id']];
    }

    $data['defaultValue'] = $this->_getDefaultValueFromSubmission($submission, $data['defaultValue'], $data['inputLabel']);

      if($data['helpText']){$help='<i class="icon-info tooltip-container tooltip-position-top tooltip-position-left">
  				<div class="tooltip-wrapper">
  					<div class="tooltip">
  						<p>'.$data['helpText'].'</p>
  					</div></div></i>';}

        $label = $as.$this->correct_label($data['inputLabel']).$help;
        if($data['size']<12 && !$label) {
            $label='&nbsp;';
        }

    		$return='<label>'.$label.'</label>
  		<div class="select">
  			<select name="'.$data['_id'].'" class="text" '.$validate.' '.$readonly.'>
  				<option selected disabled value="">'.$data['placeholderText'].'</option>';
  		    for($c=0;$c<count($data['optionsList']);$c++){
  			   $value = trim($data['optionsList'][$c]['value'])!='' ? htmlentities($data['optionsList'][$c]['value']):htmlentities($data['optionsList'][$c]['label']);

               if(!$value && $value<>'0') { continue; }

                   // if($data['optionsList'][$c]['selected']){$selected='selected';}
                   // if($data['optionsList'][$c]['disabled']){$selected='disabled';}
                if($data['defaultValue'] == $value || $data['optionsList'][$c]['default']) {
                    $return.='<option value="'.$value.'" selected>'.$data['optionsList'][$c]['label'].'</option>';
                } else {
                    $return.='<option value="'.$value.'">'.$data['optionsList'][$c]['label'].'</option>';
                }
            }
  		$return.='</select>
  		</div>'.$instruct.'<div class="gc input-group-help error"></div>
  		<div class="gc input-group-help req-error"></div>';

    	return $return;

  }//

  //
  function elFormRadio($data, $submission, $form_owner){
  	$readonly='';
  	$validate='';

    $data['defaultValue'] = $this->_getDefaultValueFromSubmission($submission, $data['defaultValue'], $data['inputLabel']);

  	if($data['disabled']==true || $_GET['action']=='print') {$readonly = 'disabled="true"';}
      if($data['required']=='true'){$as='<i class="icon-asterisk"></i>'; $validate="validate-required";}
      if($data['instructionText']){$instruct='<p class="help inline">'.$this->correct_label($data['instructionText']).'</p>';}
      if($data['helpText']){$help='<i class="icon-info tooltip-container tooltip-position-top tooltip-position-left">
  		<div class="tooltip-wrapper">
  			<div class="tooltip">
  				<p>'.$data['helpText'].'</p>
  			</div></div></i>';}

    $label = $as.$this->correct_label($data['inputLabel']).$help;
    if($data['size']<12 && !$label) {
        $label='&nbsp;';
    }

  			$return='<label>'.$label.'</label>';
            for($c=0;$c<count($data['optionsList']);$c++){
          	    $value = $data['optionsList'][$c]['value'];
                if($this->pl->notempty($value) == false) {
                    $value = $data['optionsList'][$c]['label'];
                }

                if(!$value && $value<>'0') { continue; }

            	if($data['optionsList'][$c]['selected']){$selected='selected';}
            	if($data['optionsList'][$c]['disabled']){$selected='disabled';}

                if($value == $data['defaultValue']) {
                    $selected='checked';
                } else {
                    $selected='';
                }

                if($_GET['action']!='print' && $data['optionsList'][$c]['default']) {
                    $selected = 'checked';
                }

      			$return.='<div>
                      <label class="option" '.$disabled.'>
      					<input id="'.$data['optionsList'][$c]['_id'].'" '.$selected.' '.$readonly.' '.$validate.' type="radio" name="'.$data['_id'].'" value="'.htmlentities($value).'">
      			        <i></i>
                        '.$data['optionsList'][$c]['label'].'
      				</label>
      			</div>';
            }

          if($data['otherOption']) {
          	$return.='<div class="product_container">
                  <label class="option other_label" '.$disabled.' for="'.$data['_id'].'">
  					<input class="other_input" '.$selected.' '.$readonly.' '.$validate.' type="radio" name="'.$data['_id'].'" value="'.$data['otherOptionLabel'].'">
  					'.$data['otherOptionLabel'].':<i></i>
  					<input type="text" class="text other_option" />
  				</label>
  			</div>';
          }

  		$return.=$instruct.'<div class="gc input-group-help error"></div>
  		<div class="gc input-group-help req-error"></div>';

    	return $return;
  }
  //

  function starrating_selected($defaultValue, $value) {
      return $defaultValue == $value ? 'checked':'';
  }

  function starrating_selected_class($defaultValue, $value) {
      return $defaultValue == $value ? 'selected':'';
  }

  function starrating_selected_readonly($defaultValue, $value) {
      if(!$defaultValue) { return ''; }
      return $defaultValue == $value ? 'checked':'disabled';
  }

  function elFormStarrating($data, $submission, $form_owner){
    $data['defaultValue'] = $this->_overwriteDefaultValueFromExternalData($data);
  	$readonly="";
  	$class="";

    if($_COOKIE[$data['_id']] && $this->form['autoFill']=='1' && $this->pl->isFreeAccount($this->user) == false) {
        $data['defaultValue'] = $_COOKIE[$data['_id']];
    }

  	$data['defaultValue'] = $this->_getDefaultValueFromSubmission($submission, $data['defaultValue'], $data['inputLabel']);

    $addAttr='';
    if($data['defaultValue']) {
        $addAttr='value="'.$data['defaultValue'].'"';
    }

  	$icon_positon = 'left';
    if($this->form['rtl']) {
        $icon_positon = 'right';
    }
    if($data['disabled']==true || $_GET['action']=='print') {$readonly = 'readonly="true"';}
  	if($data['required']=='true'){$as='<i class="icon-asterisk"></i>'; $validate="validate-required";}
  	if($data['instructionText']){$instruct='<p class="help inline">'.$this->correct_label($data['instructionText']).'</p>';}

  	if($data['helpText']){$help='<i class="icon-info tooltip-container tooltip-position-top tooltip-position-left">
  				<div class="tooltip-wrapper">
  					<div class="tooltip">
  						<p>'.$data['helpText'].'</p>
  					</div></div></i>';}

    $label = $as.$this->correct_label($data['inputLabel']).$help;
    if($data['size']<12 && !$label) {
        $label='&nbsp;';
    }

    $return='<label>'.$label.'</label><div class="controls-container">';
  	$return.='<div class="rating">
       <label class="'.$this->starrating_selected_class($data['defaultValue'], 5).'">
           <input type="radio" name="'.$data['_id'].'" '.$this->starrating_selected($data['defaultValue'], 5).' '.$this->starrating_selected_readonly($data['defaultValue'], 5).' '.$validate.' value="5" title="5 stars" data-rule="required"> 5
       </label>
       <label class="'.$this->starrating_selected_class($data['defaultValue'], 4).'">
           <input type="radio" name="'.$data['_id'].'" '.$this->starrating_selected($data['defaultValue'], 4).' '.$this->starrating_selected_readonly($data['defaultValue'], 4).' '.$validate.' value="4" title="4 stars" data-rule="required"> 4
       </label>
       <label class="'.$this->starrating_selected_class($data['defaultValue'], 3).'">
           <input type="radio" name="'.$data['_id'].'" '.$this->starrating_selected($data['defaultValue'], 3).' '.$this->starrating_selected_readonly($data['defaultValue'], 3).' '.$validate.' value="3" title="3 stars" data-rule="required"> 3
       </label>
       <label class="'.$this->starrating_selected_class($data['defaultValue'], 2).'">
           <input type="radio" name="'.$data['_id'].'" '.$this->starrating_selected($data['defaultValue'], 2).' '.$this->starrating_selected_readonly($data['defaultValue'], 2).' '.$validate.' value="2" title="2 stars" data-rule="required"> 2
       </label>
       <label class="'.$this->starrating_selected_class($data['defaultValue'], 1).'">
           <input type="radio" name="'.$data['_id'].'" '.$this->starrating_selected($data['defaultValue'], 1).' '.$this->starrating_selected_readonly($data['defaultValue'], 1).' '.$validate.' value="1" title="1 star" data-rule="required"> 1
       </label>
   </div>';
    $return.='<div class="gc input-group-help error"></div>
  		<div class="gc input-group-help req-error"></div>
  	</div>'.$instruct;

  	return $return;
  }

  //
  function elFormLabel($data, $submission, $post=null, $form_owner=null){

      if($post && !$form_owner) {
          $form_owner = $post;
          $post = null;
      }

      //echo json_encode($data);
  	$data['labelText'] = preg_replace("/<a(.*?)>/", "<a$1 target=\"_top\">", $data['labelText']);
    $data['labelText'] = str_replace('\n\n','<br>',$data['labelText']);

    if($post) {
        foreach ($post as $field) {
            if(stripos($data['labelText'], '{'.$field['label'].'}')!==false) {
                $form_field=$this->lo->getFormElement(array('form_id' => $form['_id'], 'element_id' => $field['field']));
                if ($form_field['queryName']) {
                    $field['label']=$form_field['queryName'];
                }
                $data['labelText']=str_ireplace('{'.$field['label'].'}', $field['value'], $data['labelText']);
            }
        }
    }

  	return '<p>'.nl2br($data['labelText']).'</p>';
  }
  //

  function elFormPicture($data, $submission, $form_owner) {
  	$width = '';
  	$height = '';
  	if($data['width'] && $data['width']!='') {
  		$width = 'width:'.$data['width'].';';
  	}
  	if($data['height'] && $data['height']!='') {
  		$height = 'height:'.$data['height'].';';
  	}
    if($data['picture']) {
        return '<img src="/images/' . $data['picture'] . '" style="max-width:100%;'.$width.$height.'" />';
    }
  }

  function elFormCaptcha($data, $submission, $form_owner) {
    $error = $data['captchaError'] ?: 'Please validate the captcha';
    return '<div class="g-recaptcha" data-error="'.$error.'" data-sitekey="'.$GLOBALS['conf']['google_captcha_site_key'].'" data-callback="check_captcha"></div>';
  }

  function elFormSignature($data, $submission, $form_owner) {
    $data['defaultValue'] = $this->_getDefaultValueFromSubmission($submission, $data['defaultValue'], $data['inputLabel']);

    if($data['required']=='true'){$as='<i class="icon-asterisk"></i>'; $validate="validate-required";}
  	if($data['instructionText']){$instruct='<p class="help inline">'.$this->correct_label($data['instructionText']).'</p>';}

  	if($data['helpText']){$help='<i class="icon-info tooltip-container tooltip-position-top tooltip-position-left">
  				<div class="tooltip-wrapper">
  					<div class="tooltip">
  						<p>'.$data['helpText'].'</p>
  					</div></div></i>';}

    $label = $as.$this->correct_label($data['label']).$help;
    if($data['size']<12 && !$label) {
        $label='&nbsp;';
    }

    if($data['defaultValue']) {
        return '<label>'.$label.'</label><img src="/file/'.$data['defaultValue'].'" />';
    } else {
        return '<input type="hidden" name="'.$data['_id'].'" '.$validate.'><label>'.$label.'</label><div class="canvasC" style="width:'.$data['width'].'px;height:'.$data['height'].'px;"><canvas class="signature-pad" width="'.$data['width'].'" height="'.$data['height'].'" default="'.$data['defaultValue'].'"></canvas><div class="actions"><a class="clear" href="javascript:;">'.$data['clearLabel'].'</a></div></div>';
    }

  }

  function elFormPaypal($data, $submission, $form_owner) {
  	$amount = $data['amount'] ?: 0;
  	$symbol = '$';
    if($this->form['currency']!='USD') {$symbol='';}
  	return '<div class="gc medium-12 total_container"><span class="totalLabel">'.$data['totalLabel'].'</span>: <span class="symbol">'.$symbol.'</span><span class="total">'.$amount.'</span> <span class="currency">'.$this->form['currency'].'</span></div><div class="gc medium-6 payment_label">'.$data['label'].'</div><div class="gc medium-6 payment_logo"><img src="'.$GLOBALS['protocol'].'://'.$_SERVER['HTTP_HOST'].'/static/img/paypal.png" style="max-height:50px" /></div>';
  }

  function elFormStripe($data, $submission, $form_owner) {
  	$amount = $data['amount'] ?: 0;
  	$symbol = '$';
    if($this->form['currency']!='USD') {$symbol='';}

    $methods = '';

    $paymentMethods = $GLOBALS['ref']['STRIPE_ADDITIONAL_METHODS'];

    $x=0;
    foreach($paymentMethods as $pm=>$pmlabel) {
        $checked = '';
        if($data[$pm]) {
            $dlabel = $data[$pm.'Label'];
            if(!$dlabel) {
                $dlabel = $pmlabel;
            }
            if($x==0) {
                $checked = 'checked="checked"';
            }
            $methods.='
                <div class="gc medium-7">
                    <div style="margin-top:25px">
                        <label class="option">
                            <input type="radio" id="stripe_payment_'.$pm.'" name="payment_type" value="'.$pm.'" '.$checked.'>
                            <i></i>
                            <span class="payment_label"><label for="stripe_payment_'.$pm.'" style="font-weight:normal">'.$dlabel.'</label></span>
                        </label>
                    </div>
                </div>
                <div class="gc medium-5 payment_logo">
                    <label for="stripe_payment" style="margin-top:15px;"><img src="'.$GLOBALS['protocol'].'://'.$_SERVER['HTTP_HOST'].'/static/img/'.$pm.'_logo.png" style="max-height:50px" /></label>
                </div>
            ';
            $x++;
        }
    }

    if($methods) {
        $card = '
        <div class="gc medium-7">
            <div style="margin-top:25px">
                <label class="option">
                    <input type="radio" id="stripe_payment" name="payment_type" value="STRIPE">
                    <i></i>
                    <span class="payment_label"><label for="stripe_payment" style="font-weight:normal">'.$data['label'].'</label></span>
                </label>
            </div>
        </div>
        <div class="gc medium-5 payment_logo">
            <label for="stripe_payment" style="margin-top:15px;"><img src="'.$GLOBALS['protocol'].'://'.$_SERVER['HTTP_HOST'].'/static/img/credit-cards.png" style="max-height:50px" /></label>
        </div>
        ';

        if(isset($data['card']) && !$data['card']) {
            $card = '';
        }

      	return '<div class="gc medium-12 total_container">
      				<span class="totalLabel">'.$data['totalLabel'].'</span>
      				: <span class="symbol">'.$symbol.'</span><span class="total">'.$amount.'</span>
      				<span class="currency">'.$this->form['currency'].'</span>
      			</div>
      			<div class="gc medium-12">'.$data['label'].'</div>
      			'.$methods.$card;
    } else {
        if($data['captureCard']) {
            $label = 'Capturing credit card data for later processing';
            if($data['captureLabel']) {
                $label = $data['captureLabel'];
            }
            return '<div class="gc medium-12 total_container">'.$label.'</div><div class="gc medium-6 payment_label">'.$data['label'].'</div><div class="gc medium-6 payment_logo"><img src="'.$GLOBALS['protocol'].'://'.$_SERVER['HTTP_HOST'].'/static/img/credit-cards.png" style="max-height:50px" /></div>';
        } else {
            return '<div class="gc medium-12 total_container"><span class="totalLabel">'.$data['totalLabel'].'</span>: <span class="symbol">'.$symbol.'</span><span class="total">'.$amount.'</span> <span class="currency">'.$this->form['currency'].'</span></div><div class="gc medium-6 payment_label">'.$data['label'].'</div><div class="gc medium-6 payment_logo"><img src="'.$GLOBALS['protocol'].'://'.$_SERVER['HTTP_HOST'].'/static/img/credit-cards.png" style="max-height:50px" /></div>';
        }
    }



  }

  function elFormStripepaypal($data, $submission, $form_owner) {
  	$amount = $data['amount'] ?: 0;
  	$symbol = '$';
    if($this->form['currency']!='USD') {$symbol='';}

    $methods = '';
    $paymentMethods = $GLOBALS['ref']['STRIPE_ADDITIONAL_METHODS'];

    $x=0;
    foreach($paymentMethods as $pm=>$pmlabel) {
        $checked = '';
        if($data[$pm]) {
            $dlabel = $data[$pm.'Label'];
            if(!$dlabel) {
                $dlabel = $pmlabel;
            }
            if($x==0) {
                $checked = 'checked="checked"';
            }
            $methods.='
                <div class="gc medium-7">
                    <div style="margin-top:25px">
                        <label class="option">
                            <input type="radio" id="stripe_payment_'.$pm.'" name="payment_type" value="'.$pm.'" '.$checked.'>
                            <i></i>
                            <span class="payment_label"><label for="stripe_payment_'.$pm.'" style="font-weight:normal">'.$dlabel.'</label></span>
                        </label>
                    </div>
                </div>
                <div class="gc medium-5 payment_logo">
                    <label for="stripe_payment" style="margin-top:15px;"><img src="'.$GLOBALS['protocol'].'://'.$_SERVER['HTTP_HOST'].'/static/img/'.$pm.'_logo.png" style="max-height:50px" /></label>
                </div>
            ';
            $x++;
        }
    }

    $checkCard = 'checked="checked"';
    if($methods) {
        $checkCard='';
    }

    $card = '
    <div class="gc medium-7">
        <div style="margin-top:25px">
            <label class="option">
                <input type="radio" id="stripe_payment" name="payment_type" value="STRIPE" '.$checkCard.'>
                <i></i>
                <span class="payment_label"><label for="stripe_payment" style="font-weight:normal">'.$data['labelStripe'].'</label></span>
            </label>
        </div>
    </div>
    <div class="gc medium-5 payment_logo">
        <label for="stripe_payment" style="margin-top:15px;"><img src="'.$GLOBALS['protocol'].'://'.$_SERVER['HTTP_HOST'].'/static/img/credit-cards.png" style="max-height:50px" /></label>
    </div>
    ';

    if(isset($data['card']) && !$data['card']) {
        $card = '';
    }

    $invalidEmailPaypal = true;
    if (filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $invalidEmailPaypal = false;
    }

    $paypalStyle = "";
    if($invalidEmailPaypal){
        $paypalStyle="display:none;";
    }

  	return '<div class="gc medium-12 total_container">
  				<span class="totalLabel">'.$data['totalLabel'].'</span>
  				: <span class="symbol">'.$symbol.'</span><span class="total">'.$amount.'</span>
  				<span class="currency">'.$this->form['currency'].'</span>
  			</div>
  			<div class="gc medium-12">'.$data['label'].'</div>
  			'.$methods.$card.'
  			<div style="'.$paypalStyle.'"><div class="gc medium-7">
  				<div style="margin-top:25px">
                  	<label class="option">
  						<input type="radio" id="paypal_payment" name="payment_type" value="PAYPAL">
  						<i></i>
                        <span class="payment_label"><label for="paypal_payment" style="font-weight:normal">'.$data['labelPaypal'].'</label></span>
  					</label>
  				</div>
  			</div>
  			<div class="gc medium-5 payment_logo">
  				<label for="paypal_payment" style="margin-top:15px;"><img src="'.$GLOBALS['protocol'].'://'.$_SERVER['HTTP_HOST'].'/static/img/paypal.png" style="max-height:50px" /></label>
  			</div></div>';
  }

  //
  function elFormPagebreak(){
  	$form_pagebreak='<p class="help" align="center">PDF Page Break</p>';
  	return $form_pagebreak;
  }
  //


  //
  function elFormFile($data, $submission, $form_owner){
    $readonly="";

    $data['defaultValue'] = $this->_getDefaultValueFromSubmission($submission, $data['defaultValue'], $data['inputLabel']);

  	$button = 'Choose file...';
  	if($data['fileButtonLabel']) {$button = $data['fileButtonLabel'];}
  	if($data['disabled']==true || $_GET['action']=='print') {$readonly = 'readonly="true"';}
      if($data['required']=='true'){$as='<i class="icon-asterisk"></i>'; $validate="validate-required";}
      if($data['instructionText']){$instruct='<p class="help inline">'.$this->correct_label($data['instructionText']).'</p>';}
      if($data['helpText']){$help='<i class="icon-info tooltip-container tooltip-position-top tooltip-position-left">
  				<div class="tooltip-wrapper">
  					<div class="tooltip">
  						<p>'.$data['helpText'].'</p>
  					</div></div></i>';}

    $label = $as.$this->correct_label($data['inputLabel']).$help;
    if($data['size']<12 && !$label) {
        $label='&nbsp;';
    }

    if($data['largeFile']) {
        $unfinishupload = 'File is not ready yet.';
        $finishupload = 'File Uploaded';
        $uploading = 'Uploading';
        $fileSizeError = 'File size did not meet the requirement.';
        $fileDimensionError = 'Image dimension did not meet the requirement.';
        $fileType='';
        $minSize='';
        $maxSize='';
        $minHeight='';
        $maxHeight='';
        $minWidth='';
        $addAttr='';
        $multipleFile="false";
        if($data['unfinishUpload']) { $unfinishupload = $data['unfinishUpload']; }
        if($data['finishedUpload']) { $finishupload = $data['finishedUpload']; }
        if($data['uploading']) { $uploading = $data['uploading']; }
        if($data['fileSizeError']) { $fileSizeError = $data['fileSizeError']; }
        if($data['fileDimensionError']) { $fileDimensionError = $data['fileDimensionError']; }
        if($data['fileType']) { $fileType = $data['fileType']; }
        if($data['minSize']) { $minSize = $data['minSize']; }
        if($data['maxSize']) { $maxSize = $data['maxSize']; }
        if($data['minHeight']) { $minHeight = $data['minHeight']; }
        if($data['maxHeight']) { $maxHeight = $data['maxHeight']; }
        if($data['minWidth']) { $minWidth = $data['minWidth']; }
        if($data['maxWidth']) { $maxWidth = $data['maxWidth']; }
        if($data['multipleFile']) { $addAttr="multiple"; }

        if($readonly) {
            $str='';
            $parts=explode('.',$data['defaultValue']['value']);
            if(count($parts) > 1 && strlen($parts[0]) == 32 && strlen($parts[1]) < 5){
                if(isset($data['defaultValue']['org_name'])) {
                    $str.='<a href="/file/'.$data['defaultValue']['value'].'/?f='.urlencode($data['defaultValue']['org_name']).'" target="_blank">'.htmlentities($data['defaultValue']['org_name']).'</a>';
                } else {
                    $str.='<a href="/file/'.$data['defaultValue']['value'].'/" target="_blank">'.htmlentities($data['defaultValue']['value']).'</a>';
                }
            } else {
                $parts = explode(';;', $data['defaultValue']['value']);
                if(count($parts) > 1 && isset($data['defaultValue']['org_name'])) {
                    $org_names = explode(';;', $data['defaultValue']['org_name']);
                    $ctr=0;
                    $str.='<ul>';
                    foreach($parts as $file) {
                        $str.='<li><a href="/file/'.$file.'/?f='.urlencode($org_names[$ctr]).'" target="_blank">'.htmlentities($org_names[$ctr]).'</a></li>';
                        $ctr++;
                    }
                    $str.='</ul>';
                }
            }

            $return='<label>'.$label.'</label><div>'.$str.'</div>';
        } else {
            $return='<label>'.$label.'</label>
            <div class="file">
                <input type="hidden" class="hidden_file" name="'.$data['_id'].'[\'file\']" id="file_'.$data['_id'].'_file" value="" />
                <input type="hidden" class="hidden_filename" name="'.$data['_id'].'[\'filename\']" id="file_'.$data['_id'].'_filename" value="" />
                <input large-file id="file_'.$data['_id'].'" accept="'.$fileType.'" type="file" name="'.$data['_id'].'" class="file" '.$validate.' minSize="'.$minSize.'" maxSize="'.$maxSize.'" minHeight="'.$minHeight.'" maxHeight="'.$maxHeight.'" minWidth="'.$minWidth.'" maxWidth="'.$maxWidth.'" fileSizeError="'.$fileSizeError.'" fileDimensionError="'.$fileDimensionError.'" unfinishupload="'.$unfinishupload.'" finishupload="'.$finishupload.'" uploading="'.$uploading.'" style="display: none;" '.$readonly.' '.$addAttr.'>
                <button type="button" class="" '.$readonly.'>'.$button.'</button>
                <span class="loading_indicator" id="file_'.$data['_id'].'_indicator"></span>
                <p class="filename" id="file_'.$data['_id'].'_filename_container"></p>
                <div class="files_container" id="file_'.$data['_id'].'_files_container">
                </div>
            </div>
            '.$instruct.'<div class="gc input-group-help error"></div>
            <div class="gc input-group-help req-error"></div>';
        }

    } else {
        if($readonly) {
            $return='<label>'.$label.'</label>';
        } else {
            $return='<label>'.$label.'</label>
            <div class="file">
                <input id="file_'.$data['_id'].'" type="file" name="'.$data['_id'].'" class="file" '.$validate.' style="display: none;" '.$readonly.'>
                <button type="button" class="" '.$readonly.'>'.$button.'</button>
                <p class="filename"></p>
            </div>
            '.$instruct.'<div class="gc input-group-help error"></div>
            <div class="gc input-group-help req-error"></div>';
        }
    }


    	return $return;

  }
  //

  //
  function side_text($type='text'){
  	$m = "side_text";
  	ob_start();
  ?>
  	<div class="pad">
  	<fieldset>
  	  	<label class="type"><?php echo $this->pl->trans($m,'Field Type'); ?>:<span><?php echo $this->pl->trans($m,'Text input short'); ?></span></label>
  	</fieldset>
  	<fieldset class="reqdis"></fieldset>
  	<hr>
  	<fieldset class="inputLabel"></fieldset>
  	<fieldset class="instructionText"></fieldset>
  	<fieldset class="helpText"></fieldset>
  	<fieldset class="placeholderText"></fieldset>
  	<fieldset class="defaultValue"></fieldset>
  	<hr>
  	<div>
  		<label><?php echo $this->pl->trans($m,'Field Icon'); ?>
  			<i class="icon-info">
  				<div class="tooltip-wrapper">
  					<div class="tooltip">
  						<p><?php echo $this->pl->trans($m,'Place a descriptive icon inside the text element (e.g. a phone or envelope)'); ?>.</p>
  					</div>
  				</div>
  			</i>
  		</label>
  		<fieldset>
  			<label class="option">
  				<input id="s_iconEnabled" type="checkbox" prop="iconEnabled" <?php echo $type<>'text' ? 'checked':'' ?>><?php echo $this->pl->trans($m,'Enable Icon'); ?>
  				<i></i>
  			</label>
  		</fieldset>
  	    <div class="show_iconEnabled_1">
  			<select class="text fontawesome-select" prop="iconName" style="font-family: FontAwesome, sans-serif;">
  				<option value=""><?php echo $this->pl->trans($m,'Select'); ?></option>
  			</select>
  		</div>
  		<hr>
  		<label><?php echo $this->pl->trans($m,'Validation'); ?>
  			<i class="icon-info">
  				<div class="tooltip-wrapper">
  					<div class="tooltip">
  						<p><?php echo $this->pl->trans($m,'Make sure the user enters text as a specific format, like email addresses or phone numbers'); ?>.</p>
  					</div>
  				</div>
  			</i>
  		</label>
  		<fieldset class="select">
  			<select class="text" prop="customValidationType">
  				<option value="NONE"><?php echo $this->pl->trans($m,'No Validation'); ?></option>
  				<option value="PHONE" <?php echo $type=='phone' ? 'selected':'' ?>><?php echo $this->pl->trans($m,'Phone Number'); ?></option>
  				<option value="PHONE8"><?php echo $this->pl->trans($m,'8 Digits Phone Number'); ?></option>
  				<option value="PHONE10"><?php echo $this->pl->trans($m,'10 Digits Phone Number'); ?></option>
  				<option value="PHONE13"><?php echo $this->pl->trans($m,'13 Digits Phone Number'); ?></option>
  				<option value="EMAIL" <?php echo $type=='email' ? 'selected':'' ?>><?php echo $this->pl->trans($m,'Email Address'); ?></option>
  				<option value="NUMBER" <?php echo $type=='number' ? 'selected':'' ?>><?php echo $this->pl->trans($m,'Number'); ?></option>
                <option value="DATE" <?php echo $type=='date' ? 'selected':'' ?>><?php echo $this->pl->trans($m,'Date'); ?></option>
  				<option value="REGEX" <?php echo $type=='regex' ? 'selected':'' ?>><?php echo $this->pl->trans($m,'Custom using regular expression'); ?></option>
  				<option value="ALPHANUM" <?php echo $type=='regex' ? 'selected':'' ?>><?php echo $this->pl->trans($m,'Alpha-Numeric'); ?></option>
  			</select>
  		</fieldset>
        <fieldset class="customValidationType_REGEX" style="display:none;">
            <br />
            <label><?php echo $this->pl->trans($m,'Regular Expression'); ?>
                <i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
                    <div class="tooltip-wrapper">
                        <div class="tooltip">
                            <p>an example to accept only numbers: /^[0-9+]*$/</p>
                        </div>
                    </div>
                </i>
            </label>
            <input type="text" prop="regex" placeholder="<?php echo $this->pl->trans($m,'Regular Expression'); ?>" class="text small dark">
        </fieldset>
  		<fieldset class="validationMessage" style="margin-top: 15px;display:none"></fieldset>
  	</div>
  	<hr>
  		<fieldset class="queryName"></fieldset>
    <hr>
        <fieldset class="logic"></fieldset>
  	</div>

  <?php
  	return ob_get_clean();
  }


  /// end of form components

  function currency_options($type='text'){
  	$m = "side_text";
  	ob_start();
  ?>
  	<select class="text fontawesome-select" id="s_currency" prop="currency">
  	<?php
  	$currencies = $GLOBALS['ref']['payment_currencies'];
  	foreach($currencies as $value => $currency){
  	?>
  		<?php if($currency=='USD'){ ?>
  			<option value="<?php echo $currency; ?>" selected><?php echo $currency; ?></option>
  		<?php } else { ?>
  			<option value="<?php echo $currency; ?>"><?php echo $currency; ?></option>
  		<?php } ?>
  	<?php } ?>
  	</select>
  <?php
  	return ob_get_clean();
  }


  function tt($m,$text,$eg=null){
  ob_start();
  ?>
  <i class="icon-info">
    <div class="tooltip-wrapper">
      <div class="tooltip">
        <p><?php echo $this->pl->trans($m,$text); if($eg){?> (e.g. "<?php echo $eg;?>").<?php } ?></p>
      </div>
    </div>
  </i>
  <?php
  return ob_get_clean();
  }

  //
  function OutputScriptComponents(){
  	$m = "scriptcomponents";

  $side_text=$this->side_text();

  $side_lookup='
  <div class="pad">
  <fieldset>
      <label class="type">'.$this->pl->trans($m,'Field Type').':<span>'.$this->pl->trans($m,'Lookup').'</span></label>
  </fieldset>
  <fieldset class="reqdis"></fieldset>
  <hr>
  <fieldset class="inputLabel"></fieldset>
  <fieldset class="instructionText"></fieldset>
  <fieldset class="helpText"></fieldset>
  <fieldset class="placeholderText"></fieldset>
  <fieldset class="defaultValue"></fieldset>
  <hr>
  <fieldset class="optionsList"></fieldset>
  <hr>
  <fieldset>
      <label>'.$this->pl->trans($m,'Lookup Column').'
          <i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
              <div class="tooltip-wrapper">
                  <div class="tooltip">
                      <p>'.$this->pl->trans($m,'Lookup column').'</p>
                  </div>
              </div>
          </i>
      </label>
      <fieldset class="select">
          <select class="text" prop="lookupColumn">
              <option></option>
              <option value="label" selected>Label</option>
              <option value="value">Value</option>
          </select>
      </fieldset>
  </fieldset>
  <fieldset>
  	<label>'.$this->pl->trans($m,'Not Exists Error Message').'
  		<i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
  			<div class="tooltip-wrapper">
  				<div class="tooltip">
  					<p>'.$this->pl->trans($m,'Error message when the inputted data does not exists in the list.').'</p>
  				</div>
  			</div>
  		</i>
  	</label>
  	<input type="text" prop="notExistsErrorMessage" placeholder="'.$this->pl->trans($m,'Error message').'" class="text small dark">
  </fieldset>
  <hr>
  <fieldset>
    <label class="option switch">
        Autosuggest
        <span>
        <i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
            <div class="tooltip-wrapper">
                <div class="tooltip">
                    <p>'.$this->pl->trans($m,'will autosuggest list as you type keywords').'</p>
                </div>
            </div>
        </i>
        <span>
        <input type="checkbox" class="autoSuggest" prop="autoSuggest">
        <span class="switch-container">
            <span class="switch-status on">'.$this->pl->trans($m,'ON').'</span>
            <span class="switch-status off">'.$this->pl->trans($m,'OFF').'</span>
            <i></i>
        </span>
    </label>
  </fieldset>
  <hr>
  <fieldset class="queryName"></fieldset>
  <hr>
    <fieldset class="logic"></fieldset>
  </div>
  ';

  $side_calculation='
  <div class="pad">
  <fieldset>
      <label class="type">'.$this->pl->trans($m,'Field Type').':<span>'.$this->pl->trans($m,'Calculation').'</span></label>
  </fieldset>
  <fieldset class="reqdis"></fieldset>
  <hr>
  <fieldset class="inputLabel"></fieldset>
  <fieldset class="instructionText"></fieldset>
  <fieldset class="helpText"></fieldset>
  <fieldset class="placeholderText"></fieldset>
  <fieldset class="defaultValue"></fieldset>
  <hr>
  <fieldset>
    <label class="option switch">
        Hide this field
        <span>
        <i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
            <div class="tooltip-wrapper">
                <div class="tooltip">
                    <p>'.$this->pl->trans($m,'this field will be hidden').'</p>
                </div>
            </div>
        </i>
        <span>
        <input type="checkbox" class="hidden" prop="hidden">
        <span class="switch-container">
            <span class="switch-status on">'.$this->pl->trans($m,'ON').'</span>
            <span class="switch-status off">'.$this->pl->trans($m,'OFF').'</span>
            <i></i>
        </span>
    </label>
  </fieldset>
  <hr>
  <fieldset class="calculation"></fieldset>
  <hr>
  <fieldset class="queryName"></fieldset>
  <hr>
    <fieldset class="logic"></fieldset>
  </div>
  ';

  $dateLang = '';
  foreach($GLOBALS['ref']['flatpickr_langs'] as $k=>$lang) {
      $dateLang.='<option value="'.$k.'">'.$lang.'</option>';
  }

  $side_date = '
  <div class="pad">
  <fieldset>
  	<label class="type">'.$this->pl->trans($m,'Field Type').':<span>'.$this->pl->trans($m,'Date').'</span></label>
  </fieldset>
  <fieldset class="reqdis"></fieldset>
  <hr>
  <fieldset class="inputLabel"></fieldset>
  <fieldset class="instructionText"></fieldset>
  <fieldset class="helpText"></fieldset>
  <fieldset class="placeholderText"></fieldset>
  <fieldset class="defaultValue"></fieldset>
  <hr>
  <fieldset>
      <label>Format
          <i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
              <div class="tooltip-wrapper">
                  <div class="tooltip">
                      <p>'.$this->pl->trans($m,'date format').'</p>
                  </div>
              </div>
          </i>
      </label>
      <select class="text" prop="dateFormat">
          <option></option>
          <option>MM/DD/YYYY</option>
          <option>DD/MM/YYYY</option>
          <option>DD-MM-YYYY</option>
          <option>YYYY-MM-DD</option>
      </select>
  </fieldset>
  <fieldset>
      <label>Localization
          <i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
              <div class="tooltip-wrapper">
                  <div class="tooltip">
                      <p>'.$this->pl->trans($m,'Date widget language').'</p>
                  </div>
              </div>
          </i>
      </label>
      <select class="text" prop="pickerLang">
          <option>English</option>
          '.$dateLang.'
      </select>
  </fieldset>
  <fieldset>
  	<label>'.$this->pl->trans($m,'Begin Date').'
  		<i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
  			<div class="tooltip-wrapper">
  				<div class="tooltip">
  					<p>'.$this->pl->trans($m,'This is the beginning date of the date picker. you can write').' "now" or "now +/- &lt;number&gt; days/weeks/months/years" or "YYYY-MM-DD" date format.</p>
  				</div>
  			</div>
  		</i>
  	</label>
  	<input type="text" prop="beginDate" placeholder="'.$this->pl->trans($m,'Begin Date').'" class="text small dark">
  </fieldset>
  <fieldset>
  	<label>'.$this->pl->trans($m,'End Date').'
  		<i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
  			<div class="tooltip-wrapper">
  				<div class="tooltip">
  					<p>'.$this->pl->trans($m,'This is the end date of the date picker. you can write').' "now" or "now +/- &lt;number&gt; days/weeks/months/years" or "YYYY-MM-DD" date format.</p>
  				</div>
  			</div>
  		</i>
  	</label>
  	<input type="text" prop="endDate" placeholder="'.$this->pl->trans($m,'End Date').'" class="text small dark">
  </fieldset>
  <br>
  <fieldset class="disabledDays"></fieldset>
  <hr>
  <fieldset class="queryName"></fieldset>
  <hr>
    <fieldset class="logic"></fieldset>
  ';

  $side_time = '
  <div class="pad">
  <fieldset>
  	<label class="type">'.$this->pl->trans($m,'Field Type').':<span>'.$this->pl->trans($m,'Time').'</span></label>
  </fieldset>
  <fieldset class="reqdis"></fieldset>
  <hr>
  <fieldset class="inputLabel"></fieldset>
  <fieldset class="instructionText"></fieldset>
  <fieldset class="helpText"></fieldset>
  <fieldset class="placeholderText"></fieldset>
  <fieldset class="defaultValue"></fieldset>
  <hr>
  <fieldset>
  	<label class="option">
  		<input type="checkbox" prop="use12Notation">'.$this->pl->trans($m,'Use 12 hour notation').'
  		<i></i>
  	</label>
  </fieldset>
  <fieldset>
  	<label>'.$this->pl->trans($m,'Minute Interval').'
  		<i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
  			<div class="tooltip-wrapper">
  				<div class="tooltip">
  					<p>'.$this->pl->trans($m,'This is the minute interval').'</p>
  				</div>
  			</div>
  		</i>
  	</label>
  	<input type="number" prop="interval" placeholder="1" value="1" class="text small dark">
  </fieldset>
  <fieldset>
  	<label>'.$this->pl->trans($m,'From Time').'
  		<i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
  			<div class="tooltip-wrapper">
  				<div class="tooltip">
  					<p>'.$this->pl->trans($m,'Set minimum time').'</p>
  				</div>
  			</div>
  		</i>
  	</label>
  	<input type="text" prop="minTime" class="text small dark">
  </fieldset>
  <fieldset>
  	<label>'.$this->pl->trans($m,'To Time').'
  		<i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
  			<div class="tooltip-wrapper">
  				<div class="tooltip">
  					<p>'.$this->pl->trans($m,'Set maximum time').'</p>
  				</div>
  			</div>
  		</i>
  	</label>
  	<input type="text" prop="maxTime" class="text small dark">
  </fieldset>
  <hr>
  <fieldset class="queryName"></fieldset>
  <hr>
    <fieldset class="logic"></fieldset>
  ';

  $side_datetime = '
  <div class="pad">
  <fieldset>
  	<label class="type">'.$this->pl->trans($m,'Field Type').':<span>'.$this->pl->trans($m,'Date and Time').'</span></label>
  </fieldset>
  <fieldset class="reqdis"></fieldset>
  <hr>
  <fieldset class="inputLabel"></fieldset>
  <fieldset class="instructionText"></fieldset>
  <fieldset class="helpText"></fieldset>
  <fieldset class="placeholderText"></fieldset>
  <fieldset class="defaultValue"></fieldset>
  <hr>
  <fieldset>
      <label>Format
          <i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
              <div class="tooltip-wrapper">
                  <div class="tooltip">
                      <p>'.$this->pl->trans($m,'date format').'</p>
                  </div>
              </div>
          </i>
      </label>
      <select class="text" prop="dateFormat">
          <option></option>
          <option>MM/DD/YYYY</option>
          <option>DD/MM/YYYY</option>
          <option>DD-MM-YYYY</option>
          <option>YYYY-MM-DD</option>
      </select>
  </fieldset>
  <fieldset>
      <label>Localization
          <i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
              <div class="tooltip-wrapper">
                  <div class="tooltip">
                      <p>'.$this->pl->trans($m,'Date widget language').'</p>
                  </div>
              </div>
          </i>
      </label>
      <select class="text" prop="pickerLang">
          <option>English</option>
          '.$dateLang.'
      </select>
  </fieldset>
  <fieldset>
  	<label>'.$this->pl->trans($m,'Begin Date').'
  		<i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
  			<div class="tooltip-wrapper">
  				<div class="tooltip">
  					<p>'.$this->pl->trans($m,'This is the beginning date of the date picker. you can write').' "now" or "now +/- &lt;number&gt; days/weeks/months/years" or "YYYY-MM-DD" date format.</p>
  				</div>
  			</div>
  		</i>
  	</label>
  	<input type="text" prop="beginDate" placeholder="'.$this->pl->trans($m,'Begin Date').'" class="text small dark">
  </fieldset>
  <fieldset>
  	<label>'.$this->pl->trans($m,'End Date').'
  		<i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
  			<div class="tooltip-wrapper">
  				<div class="tooltip">
  					<p>'.$this->pl->trans($m,'This is the end date of the date picker. you can write').' "now" or "now +/- &lt;number&gt; days/weeks/months/years" or "YYYY-MM-DD" date format.</p>
  				</div>
  			</div>
  		</i>
  	</label>
  	<input type="text" prop="endDate" placeholder="'.$this->pl->trans($m,'End Date').'" class="text small dark">
  </fieldset>
  <br>
  <fieldset class="disabledDays"></fieldset>
  <hr>
  <fieldset>
  	<label class="option">
  		<input type="checkbox" prop="use12Notation">'.$this->pl->trans($m,'Use 12 hour notation').'
  		<i></i>
  	</label>
  </fieldset>
  <fieldset>
  	<label>'.$this->pl->trans($m,'Minute Interval').'
  		<i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
  			<div class="tooltip-wrapper">
  				<div class="tooltip">
  					<p>'.$this->pl->trans($m,'This is the minute interval').'</p>
  				</div>
  			</div>
  		</i>
  	</label>
  	<input type="number" prop="interval" placeholder="1" value="1" class="text small dark">
  </fieldset>
  <hr>
  <fieldset class="queryName"></fieldset>
  <hr>
    <fieldset class="logic"></fieldset>
  ';

  $side_file = '
  <div class="pad">
  <fieldset>
  	<label class="type">'.$this->pl->trans($m,'Field Type').':<span>'.$this->pl->trans($m,'File upload').'</span></label>
  	<label class="type">Max Size:<span>'. $GLOBALS['ref']['MAX_UPLOAD_SIZE']. 'MB</span></label>
  </fieldset>
  <fieldset class="reqdis"></fieldset>
  <hr>
  <fieldset class="inputLabel"></fieldset>
  <fieldset>
  	<label>'.$this->pl->trans($m,'Button Label').'
  		<i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
  			<div class="tooltip-wrapper">
  				<div class="tooltip">
  					<p>'.$this->pl->trans($m,'This is the text of the button').'</p>
  				</div>
  			</div>
  		</i>
  	</label>
  	<input id="s_fileButtonLabel" type="text" prop="fileButtonLabel" placeholder="'.$this->pl->trans($m,'Choose File').'..." value="Choose File..." class="text small dark">
  </fieldset>
  <fieldset class="instructionText"></fieldset>
  <fieldset class="helpText"></fieldset>
  <fieldset>
    <label class="option switch">
        '.$this->pl->trans($m,'Async file upload (+5mb)').'
        <span>
        <i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
            <div class="tooltip-wrapper">
                <div class="tooltip">
                    <p>'.$this->pl->trans($m,'Upload file up to 2gb size').'</p>
                </div>
            </div>
        </i>
        <span>
        <input type="checkbox" class="largeFile" prop="largeFile">
        <span class="switch-container">
            <span class="switch-status on">'.$this->pl->trans($m,'ON').'</span>
            <span class="switch-status off">'.$this->pl->trans($m,'OFF').'</span>
            <i></i>
        </span>
    </label>
  </fieldset>
  <div class="show_largeFile_1" style="display:none">
      <fieldset>
        <label class="option switch">
            '.$this->pl->trans($m,'allow multiple files').'
            <span>
            <i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
                <div class="tooltip-wrapper">
                    <div class="tooltip">
                        <p>'.$this->pl->trans($m,'Allow more than 1 file').'</p>
                    </div>
                </div>
            </i>
            <span>
            <input type="checkbox" class="multipleFile" prop="multipleFile">
            <span class="switch-container">
                <span class="switch-status on">'.$this->pl->trans($m,'ON').'</span>
                <span class="switch-status off">'.$this->pl->trans($m,'OFF').'</span>
                <i></i>
            </span>
        </label>
      </fieldset>
      <fieldset>
          <label>'.$this->pl->trans($m,'Min Size(mb)').'
              <i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
                  <div class="tooltip-wrapper">
                      <div class="tooltip">
                          <p>'.$this->pl->trans($m,'minimum size(mb)').'.</p>
                      </div>
                  </div>
              </i>
          </label>
          <input type="number" prop="minSize" placeholder="" data-default-value="" class="text small dark">
      </fieldset>
      <fieldset>
          <label>'.$this->pl->trans($m,'Max Size(mb)').'
              <i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
                  <div class="tooltip-wrapper">
                      <div class="tooltip">
                          <p>'.$this->pl->trans($m,'maximum size(mb)').'.</p>
                      </div>
                  </div>
              </i>
          </label>
          <input type="number" prop="maxSize" placeholder="" data-default-value="" class="text small dark">
      </fieldset>
      <fieldset>
          <label>'.$this->pl->trans($m,'File Size Upload Error Message').'
              <i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
                  <div class="tooltip-wrapper">
                      <div class="tooltip" style="margin-left:-90px">
                          <p>'.$this->pl->trans($m,'Error message when upload file size is not correct.').'.</p>
                      </div>
                  </div>
              </i>
          </label>
          <input type="text" value="File size did not meet the requirement." prop="fileSizeError" placeholder="" data-default-value="" class="text small dark">
      </fieldset>
      <hr>
      <fieldset>
          <label>File Type
              <i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
                  <div class="tooltip-wrapper">
                      <div class="tooltip">
                          <p>'.$this->pl->trans($m,'Select which file type to accept').'</p>
                      </div>
                  </div>
              </i>
          </label>
          <select class="text" prop="fileType">
              <option value="">any</option>
              <option value="image/*">any image</option>
              <option value="video/*">any video</option>
              <option value="audio/*">any audio</option>
              <option value="image/jpeg">jpeg</option>
              <option value="image/png">png</option>
              <option value="image/gif">gif</option>
              <option value="application/pdf">pdf</option>
              <option value="text/plain">plain text</option>
              <option value=".ppt, .pptx">.ppt or .pptx</option>
              <option value=".doc, .docx">.doc or .docx</option>
              <option value=".csv">.csv</option>
              <option value=".xls, .xlsx">.xls or .xlsx</option>
          </select>
      </fieldset>
    <div class="image_dimension" style="display:none;">
        <fieldset style="float: left;width: 47%;margin-right: 5px;">
            <label>'.$this->pl->trans($m,'Min Height(px)').'
                <i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
                    <div class="tooltip-wrapper">
                        <div class="tooltip">
                            <p>'.$this->pl->trans($m,'Minimum height of the image').'.</p>
                        </div>
                    </div>
                </i>
            </label>
            <input type="number" prop="minHeight" placeholder="" data-default-value="" class="text small dark">
        </fieldset>
        <fieldset style="float: left;width: 47%;margin-left: 5px;">
            <label>'.$this->pl->trans($m,'Max Height(px)').'
                <i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
                    <div class="tooltip-wrapper">
                        <div class="tooltip" style="margin-left:-90px">
                            <p>'.$this->pl->trans($m,'Maximum height of the image').'.</p>
                        </div>
                    </div>
                </i>
            </label>
            <input type="number" prop="maxHeight" placeholder="" data-default-value="" class="text small dark">
        </fieldset>

        <fieldset style="float: left;width: 47%;margin-right: 5px;">
            <label>'.$this->pl->trans($m,'Min Width(px)').'
                <i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
                    <div class="tooltip-wrapper">
                        <div class="tooltip">
                            <p>'.$this->pl->trans($m,'Minimum width of the image').'.</p>
                        </div>
                    </div>
                </i>
            </label>
            <input type="number" prop="minWidth" placeholder="" data-default-value="" class="text small dark">
        </fieldset>
        <fieldset style="float: left;width: 47%;margin-left: 5px;">
            <label>'.$this->pl->trans($m,'Max Width(px)').'
                <i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
                    <div class="tooltip-wrapper">
                        <div class="tooltip" style="margin-left:-90px">
                            <p>'.$this->pl->trans($m,'Maximum width of the image.').'.</p>
                        </div>
                    </div>
                </i>
            </label>
            <input type="number" prop="maxWidth" placeholder="" data-default-value="" class="text small dark">
        </fieldset>

        <fieldset>
            <label>'.$this->pl->trans($m,'File Dimension Error Message').'
                <i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
                    <div class="tooltip-wrapper">
                        <div class="tooltip" style="margin-left:-90px">
                            <p>'.$this->pl->trans($m,'Error message when image dimension is not correct.').'.</p>
                        </div>
                    </div>
                </i>
            </label>
            <input type="text" value="Image dimension did not meet the requirement." prop="fileDimensionError" placeholder="" data-default-value="" class="text small dark">
        </fieldset>
    </div>

    <fieldset>
    <hr>
        <label>'.$this->pl->trans($m,'Unfinish Upload Message').'
            <i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
                <div class="tooltip-wrapper">
                    <div class="tooltip">
                        <p>'.$this->pl->trans($m,'Error message when upload is not yet done.').'.</p>
                    </div>
                </div>
            </i>
        </label>
        <input type="text" value="File is not ready yet." prop="unfinishUpload" placeholder="" data-default-value="" class="text small dark">
    </fieldset>
    <fieldset>
        <label>'.$this->pl->trans($m,'Finished Upload Message').'
            <i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
                <div class="tooltip-wrapper">
                    <div class="tooltip">
                        <p>'.$this->pl->trans($m,'Error message when upload is done.').'.</p>
                    </div>
                </div>
            </i>
        </label>
        <input type="text" value="File Uploaded" prop="finishedUpload" placeholder="" data-default-value="" class="text small dark">
    </fieldset>
    <fieldset>
        <label>'.$this->pl->trans($m,'Uploading Message').'
            <i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
                <div class="tooltip-wrapper">
                    <div class="tooltip">
                        <p>'.$this->pl->trans($m,'Message when upload is in progress').'.</p>
                    </div>
                </div>
            </i>
        </label>
        <input type="text" value="Uploading" prop="uploading" placeholder="" data-default-value="" class="text small dark">
    </fieldset>
  </div>
  <div class="show_largeFile_0">
      <fieldset>
        <label class="option switch">
            '.$this->pl->trans($m,'Send files as attachment').'
            <span>
            <i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
                <div class="tooltip-wrapper">
                    <div class="tooltip">
                        <p>'.$this->pl->trans($m,'When using this element, selected file will be sent to email as attachment').'</p>
                    </div>
                </div>
            </i>
            <span>
            <input type="checkbox" class="sendAsAttachment" prop="sendAsAttachment">
            <span class="switch-container">
                <span class="switch-status on">'.$this->pl->trans($m,'ON').'</span>
                <span class="switch-status off">'.$this->pl->trans($m,'OFF').'</span>
                <i></i>
            </span>
        </label>
      </fieldset>
  </div>
  <hr>
  <fieldset class="queryName"></fieldset>
  <hr>
    <fieldset class="logic"></fieldset>
  ';

  $side_picture = '
  <div class="pad">
  <fieldset>
  	<label class="type">'.$this->pl->trans($m,'Field Type').':<span>'.$this->pl->trans($m,'Image').'</span></label>
  	<label class="type">'.$this->pl->trans($m,'Max Size').':<span>'. $GLOBALS['ref']['MAX_UPLOAD_SIZE']. 'MB</span></label>
  	<label class="type">'.$this->pl->trans($m,'Allowed Types').':<span>JPG, PNG, GIF</span></label>
  </fieldset>
  <hr>
  <button class="side_change_image">'.$this->pl->trans($m,'Change Image').'...</button>
  <fieldset style="margin-top:15px">
  	<label>Width (px, %, etc)
  		<i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
  			<div class="tooltip-wrapper">
  				<div class="tooltip">
  					<p>'.$this->pl->trans($m,'This is the width of the image or picture').'.</p>
  				</div>
  			</div>
  		</i>
  	</label>
  	<input type="text" prop="width" placeholder="100%" class="text small dark">
  </fieldset>
  <fieldset>
  	<label>'.$this->pl->trans($m,'Height').' (px, %, etc)
  		<i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
  			<div class="tooltip-wrapper">
  				<div class="tooltip">
  					<p>'.$this->pl->trans($m,'This is the height of the image or picture.').'</p>
  				</div>
  			</div>
  		</i>
  	</label>
  	<input type="text" prop="height" placeholder="100%" class="text small dark">
  </fieldset>
  <hr>
    <fieldset class="logic"></fieldset>
  ';

  $side_captcha = '
  <div class="pad">
  <fieldset>
    <label class="type">'.$this->pl->trans($m,'Field Type').':<span>'.$this->pl->trans($m,'Google Captcha').'</span></label>
  </fieldset>
  <fieldset>
    <label>'.$this->pl->trans($m,'Captcha Error Message').'
      <i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
        <div class="tooltip-wrapper">
          <div class="tooltip">
            <p>'.$this->pl->trans($m,'When captcha is invalid, this will show the error message').'</p>
          </div>
        </div>
      </i>
    </label>
    <input type="text" prop="captchaError" value="Please validate the captcha" class="text small dark">
  </fieldset>
  ';

  $side_starrating = '
  <div class="pad">
  <fieldset>
    <label class="type">'.$this->pl->trans($m,'Field Type').':<span>'.$this->pl->trans($m,'Star Rating').'</span></label>
  </fieldset>
  <fieldset class="reqdis"></fieldset>
  <hr>
  <fieldset class="inputLabel"></fieldset>
  <fieldset class="instructionText"></fieldset>
  <fieldset class="helpText"></fieldset>
  <hr>
  <fieldset class="queryName"></fieldset>
  <hr>
  <fieldset class="logic"></fieldset>
  ';

  $side_signature = '
  <div class="pad">
  <fieldset>
    <label class="type">'.$this->pl->trans($m,'Field Type').':<span>'.$this->pl->trans($m,'Signature').'</span></label>
  </fieldset>
  <label prop="required" class="option">
      <input type="checkbox" value=true prop="required"> '.$this->pl->trans($m,'Required').' <i></i>
      <i class="icon-info tooltip-container tooltip-position-bottom tooltip-position-left">
          <div class="tooltip-wrapper">
              <div class="tooltip">
                  <p>'.$this->pl->trans($m,'Require a user to fill out this field').'.</p>
              </div>
          </div>
      </i>
  </label>
  <fieldset>
    <label>'.$this->pl->trans($m,'Label').'
      <i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
        <div class="tooltip-wrapper">
          <div class="tooltip">
            <p>'.$this->pl->trans($m,'Label of the signature canvas').'</p>
          </div>
        </div>
      </i>
    </label>
    <input type="text" prop="label" value="Signature" class="text small dark">
  </fieldset>
  <fieldset>
    <label>'.$this->pl->trans($m,'Canvas Width (pixel)').'
      <i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
        <div class="tooltip-wrapper">
          <div class="tooltip">
            <p>'.$this->pl->trans($m,'Canvas width (pixel)').'</p>
          </div>
        </div>
      </i>
    </label>
    <input type="number" prop="width" value="300" class="text small dark">
  </fieldset>
  <fieldset>
    <label>'.$this->pl->trans($m,'Canvas Height (pixel)').'
      <i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
        <div class="tooltip-wrapper">
          <div class="tooltip">
            <p>'.$this->pl->trans($m,'Label of the signature canvas').'</p>
          </div>
        </div>
      </i>
    </label>
    <input type="number" prop="height" value="150" class="text small dark">
  </fieldset>
  <fieldset>
    <label>'.$this->pl->trans($m,'Clear Label').'
      <i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
        <div class="tooltip-wrapper">
          <div class="tooltip">
            <p>'.$this->pl->trans($m,'Label of the clear button').'</p>
          </div>
        </div>
      </i>
    </label>
    <input type="text" prop="clearLabel" value="Clear" class="text small dark">
  </fieldset>
  <hr>
    <fieldset class="logic"></fieldset>
  ';

  $side_stripe = '
  <div class="pad">
  <fieldset>
  	<label class="type">'.$this->pl->trans($m,'Field Type').':<span>'.$this->pl->trans($m,'Stripe Payment').'</span></label>
  </fieldset>
  <hr>
  <fieldset>
  	<label>'.$this->pl->trans($m,'Payment Request Paragraph').'
  		<i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
  			<div class="tooltip-wrapper">
  				<div class="tooltip">
  					<p>'.$this->pl->trans($m,'This should be a paragraph explaining what people buy').'.</p>
  				</div>
  			</div>
  		</i>
  	</label>
  	<input type="text" value="This is a sample payment label" prop="paymentsPageLabel" placeholder="" data-default-value="" class="text small dark">
  </fieldset>
  <fieldset>
  	<label>'.$this->pl->trans($m,'Button Label').'
  		<i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
  			<div class="tooltip-wrapper">
  				<div class="tooltip">
  					<p>'.$this->pl->trans($m,'This is the button label in the payments page').'.</p>
  				</div>
  			</div>
  		</i>
  	</label>
  	<input type="text" value="Buy now" prop="buttonLabel" placeholder="" data-default-value="" class="text small dark">
  </fieldset>
  <fieldset>
  	<label>'.$this->pl->trans($m,'Total Label').'
  		<i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
  			<div class="tooltip-wrapper">
  				<div class="tooltip">
  					<p>'.$this->pl->trans($m,'This is the total label in the payments page').'.</p>
  				</div>
  			</div>
  		</i>
  	</label>
  	<input type="text" value="Total" prop="totalLabel" placeholder="" data-default-value="" class="text small dark">
  </fieldset>
  <fieldset>
  	<label>'.$this->pl->trans($m,'Publishable Key').'
  		<i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
  			<div class="tooltip-wrapper">
  				<div class="tooltip">
  					<p>'.$this->pl->trans($m,'This is the public key provided by Stripe').'.</p>
  				</div>
  			</div>
  		</i>
  	</label>
  	<input type="text" prop="public_key" placeholder="" class="text small dark">
  </fieldset>
  <fieldset>
  	<label>'.$this->pl->trans($m,'Secret Key').'
  		<i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
  			<div class="tooltip-wrapper">
  				<div class="tooltip">
  					<p>'.$this->pl->trans($m,'This is the secret key provided by Stripe.').'</p>
  				</div>
  			</div>
  		</i>
  	</label>
  	<input type="text" prop="secret_key" placeholder="" class="text small dark">
  </fieldset>
  <hr>
  <fieldset>
      <label>
          '.$this->pl->trans($m,'Payment Methods').'
          <span>
          <i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
              <div class="tooltip-wrapper">
                  <div class="tooltip">
                      <p>'.$this->pl->trans($m,'Stripe Payment Method').'</p>
                  </div>
              </div>
          </i>

      </label>
  </fieldset>
  <fieldset>
    <label class="option switch" style="max-height: 30px;">
        '.$this->pl->trans($m,'Card').'
        <input type="checkbox" class="card" prop="card">
        <span class="switch-container">
            <span class="switch-status on">'.$this->pl->trans($m,'ON').'</span>
            <span class="switch-status off">'.$this->pl->trans($m,'OFF').'</span>
            <i></i>
        </span>

    </label>
  </fieldset>
  <div class="show_card_1" style="display:none">
      <fieldset>
      	<label>'.$this->pl->trans($m,'Card Label').'
      		<i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
      			<div class="tooltip-wrapper">
      				<div class="tooltip">
      					<p>'.$this->pl->trans($m,'This is the label').'.</p>
      				</div>
      			</div>
      		</i>
      	</label>
      	<input type="text" value="Cards Accepted" prop="label" placeholder="Cards Accepted" data-default-value="Cards Accepted" class="text small dark">
      </fieldset>
  </div>
  <fieldset>
    <label class="option switch">
        '.$this->pl->trans($m,'iDEAL').'
        <input type="checkbox" class="ideal" prop="ideal">
        <span class="switch-container">
            <span class="switch-status on">'.$this->pl->trans($m,'ON').'</span>
            <span class="switch-status off">'.$this->pl->trans($m,'OFF').'</span>
            <i></i>
        </span>

    </label>
  </fieldset>
  <div class="show_ideal_1" style="display:none">
      <fieldset>
      	<label>'.$this->pl->trans($m,'iDEAL Label').'
      		<i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
      			<div class="tooltip-wrapper">
      				<div class="tooltip">
      					<p>'.$this->pl->trans($m,'This is the label of stripe').'.</p>
      				</div>
      			</div>
      		</i>
      	</label>
      	<input type="text" value="Pay with iDEAL" prop="idealLabel" placeholder="Pay with iDEAL" data-default-value="Pay with iDEAL" class="text small dark">
      </fieldset>
  </div>
  <fieldset>
    <label class="option switch">
        '.$this->pl->trans($m,'Alipay').'
        <input type="checkbox" class="alipay" prop="alipay">
        <span class="switch-container">
            <span class="switch-status on">'.$this->pl->trans($m,'ON').'</span>
            <span class="switch-status off">'.$this->pl->trans($m,'OFF').'</span>
            <i></i>
        </span>

    </label>
  </fieldset>
  <div class="show_alipay_1" style="display:none">
      <fieldset>
      	<label>'.$this->pl->trans($m,'Alipay Label').'
      		<i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
      			<div class="tooltip-wrapper">
      				<div class="tooltip">
      					<p>'.$this->pl->trans($m,'This is the label of stripe').'.</p>
      				</div>
      			</div>
      		</i>
      	</label>
      	<input type="text" value="Pay with Alipay" prop="alipayLabel" placeholder="Pay with Alipay" data-default-value="Pay with Alipay" class="text small dark">
      </fieldset>
  </div>
  <fieldset>
    <label class="option switch">
        '.$this->pl->trans($m,'ACH Credit Transfer').'
        <input type="checkbox" class="ach_credit_transfer" prop="ach_credit_transfer">
        <span class="switch-container">
            <span class="switch-status on">'.$this->pl->trans($m,'ON').'</span>
            <span class="switch-status off">'.$this->pl->trans($m,'OFF').'</span>
            <i></i>
        </span>

    </label>
  </fieldset>
  <div class="show_ach_credit_transfer_1" style="display:none">
      <fieldset>
      	<label>'.$this->pl->trans($m,'ACH Credit Transfer Label').'
      		<i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
      			<div class="tooltip-wrapper">
      				<div class="tooltip">
      					<p>'.$this->pl->trans($m,'This is the label of stripe').'.</p>
      				</div>
      			</div>
      		</i>
      	</label>
      	<input type="text" value="Pay with ACH Credit Transfer" prop="ach_credit_transferLabel" placeholder="Pay with ACH Credit Transfer" data-default-value="Pay with ACH Credit Transfer" class="text small dark">
      </fieldset>
  </div>
  <fieldset>
    <label class="option switch">
        '.$this->pl->trans($m,'Bancontact').'
        <input type="checkbox" class="bancontact" prop="bancontact">
        <span class="switch-container">
            <span class="switch-status on">'.$this->pl->trans($m,'ON').'</span>
            <span class="switch-status off">'.$this->pl->trans($m,'OFF').'</span>
            <i></i>
        </span>

    </label>
  </fieldset>
  <div class="show_bancontact_1" style="display:none">
      <fieldset>
      	<label>'.$this->pl->trans($m,'Bancontact Label').'
      		<i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
      			<div class="tooltip-wrapper">
      				<div class="tooltip">
      					<p>'.$this->pl->trans($m,'This is the label of stripe').'.</p>
      				</div>
      			</div>
      		</i>
      	</label>
      	<input type="text" value="Pay with Bancontact" prop="bancontactLabel" placeholder="Pay with Bancontact" data-default-value="Pay with Bancontact" class="text small dark">
      </fieldset>
  </div>
  <fieldset>
    <label class="option switch">
        '.$this->pl->trans($m,'EPS').'
        <input type="checkbox" class="eps" prop="eps">
        <span class="switch-container">
            <span class="switch-status on">'.$this->pl->trans($m,'ON').'</span>
            <span class="switch-status off">'.$this->pl->trans($m,'OFF').'</span>
            <i></i>
        </span>

    </label>
  </fieldset>
  <div class="show_eps_1" style="display:none">
      <fieldset>
      	<label>'.$this->pl->trans($m,'EPS Label').'
      		<i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
      			<div class="tooltip-wrapper">
      				<div class="tooltip">
      					<p>'.$this->pl->trans($m,'This is the label of stripe').'.</p>
      				</div>
      			</div>
      		</i>
      	</label>
      	<input type="text" value="Pay with EPS" prop="epsLabel" placeholder="Pay with EPS" data-default-value="Pay with EPS" class="text small dark">
      </fieldset>
  </div>
  <fieldset>
    <label class="option switch">
        '.$this->pl->trans($m,'Giropay').'
        <input type="checkbox" class="giropay" prop="giropay">
        <span class="switch-container">
            <span class="switch-status on">'.$this->pl->trans($m,'ON').'</span>
            <span class="switch-status off">'.$this->pl->trans($m,'OFF').'</span>
            <i></i>
        </span>

    </label>
  </fieldset>
  <div class="show_giropay_1" style="display:none">
      <fieldset>
      	<label>'.$this->pl->trans($m,'Giropay Label').'
      		<i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
      			<div class="tooltip-wrapper">
      				<div class="tooltip">
      					<p>'.$this->pl->trans($m,'This is the label of stripe').'.</p>
      				</div>
      			</div>
      		</i>
      	</label>
      	<input type="text" value="Pay with Giropay" prop="giropayLabel" placeholder="Pay with Giropay" data-default-value="Pay with Giropay" class="text small dark">
      </fieldset>
  </div>
  <fieldset class="disabled">
    <label class="option switch">
        '.$this->pl->trans($m,'Multibanco (On request)').'
        <input type="checkbox" class="multibanco" prop="multibanco" disabled>
        <span class="switch-container">
            <span class="switch-status on">'.$this->pl->trans($m,'ON').'</span>
            <span class="switch-status off">'.$this->pl->trans($m,'OFF').'</span>
            <i></i>
        </span>

    </label>
  </fieldset>
  <div class="show_multibanco_1" style="display:none">
      <fieldset>
      	<label>'.$this->pl->trans($m,'Multibanco Label').'
      		<i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
      			<div class="tooltip-wrapper">
      				<div class="tooltip">
      					<p>'.$this->pl->trans($m,'This is the label of stripe').'.</p>
      				</div>
      			</div>
      		</i>
      	</label>
      	<input type="text" value="Pay with Multibanco" prop="multibancoLabel" placeholder="Pay with Multibanco" data-default-value="Pay with Multibanco" class="text small dark">
      </fieldset>
  </div>
  <fieldset class="disabled">
    <label class="option switch">
        '.$this->pl->trans($m,'P24 (On request)').'
        <input type="checkbox" class="p24" prop="p24" disabled>
        <span class="switch-container">
            <span class="switch-status on">'.$this->pl->trans($m,'ON').'</span>
            <span class="switch-status off">'.$this->pl->trans($m,'OFF').'</span>
            <i></i>
        </span>

    </label>
  </fieldset>
  <div class="show_p24_1" style="display:none">
      <fieldset>
      	<label>'.$this->pl->trans($m,'P24 Label').'
      		<i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
      			<div class="tooltip-wrapper">
      				<div class="tooltip">
      					<p>'.$this->pl->trans($m,'This is the label of stripe').'.</p>
      				</div>
      			</div>
      		</i>
      	</label>
      	<input type="text" value="Pay with P24" prop="p24Label" placeholder="Pay with P24" data-default-value="Pay with P24" class="text small dark">
      </fieldset>
  </div>
  <fieldset class="disabled">
    <label class="option switch">
        '.$this->pl->trans($m,'SEPA Direct Debit (On request)').'
        <input type="checkbox" class="sepa_debit" prop="sepa_debit" disabled>
        <span class="switch-container">
            <span class="switch-status on">'.$this->pl->trans($m,'ON').'</span>
            <span class="switch-status off">'.$this->pl->trans($m,'OFF').'</span>
            <i></i>
        </span>

    </label>
  </fieldset>
  <div class="show_sepa_debit_1" style="display:none">
      <fieldset>
      	<label>'.$this->pl->trans($m,'SEPA Direct Debit Label').'
      		<i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
      			<div class="tooltip-wrapper">
      				<div class="tooltip">
      					<p>'.$this->pl->trans($m,'This is the label of stripe').'.</p>
      				</div>
      			</div>
      		</i>
      	</label>
      	<input type="text" value="Pay with SEPA Direct Debit" prop="sepa_debitLabel" placeholder="Pay with SEPA Direct Debit" data-default-value="Pay with SEPA Direct Debit" class="text small dark">
      </fieldset>
  </div>
  <fieldset class="disabled">
    <label class="option switch">
        '.$this->pl->trans($m,'SOFORT (On request)').'
        <input type="checkbox" class="sofort" prop="sofort" disabled>
        <span class="switch-container">
            <span class="switch-status on">'.$this->pl->trans($m,'ON').'</span>
            <span class="switch-status off">'.$this->pl->trans($m,'OFF').'</span>
            <i></i>
        </span>

    </label>
  </fieldset>
  <div class="show_sofort_1" style="display:none">
      <fieldset>
      	<label>'.$this->pl->trans($m,'SOFORT Label').'
      		<i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
      			<div class="tooltip-wrapper">
      				<div class="tooltip">
      					<p>'.$this->pl->trans($m,'This is the label of stripe').'.</p>
      				</div>
      			</div>
      		</i>
      	</label>
      	<input type="text" value="Pay with SOFORT" prop="sofortLabel" placeholder="Pay with SOFORT" data-default-value="Pay with SOFORT" class="text small dark">
      </fieldset>
  </div>
  <hr>
  <fieldset>
  	<label>'.$this->pl->trans($m,'Card Name Label').'
  		<i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
  			<div class="tooltip-wrapper">
  				<div class="tooltip">
  					<p>'.$this->pl->trans($m,'This is the name label of the card').'.</p>
  				</div>
  			</div>
  		</i>
  	</label>
  	<input type="text" value="Name on Card" prop="cardNameLabel" placeholder="" data-default-value="" class="text small dark">
  </fieldset>
  <fieldset>
  	<label>'.$this->pl->trans($m,'Card Number Label').'
  		<i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
  			<div class="tooltip-wrapper">
  				<div class="tooltip">
  					<p>'.$this->pl->trans($m,'This is the number label of the card').'.</p>
  				</div>
  			</div>
  		</i>
  	</label>
  	<input type="text" value="Card Number" prop="cardNumberLabel" placeholder="" data-default-value="" class="text small dark">
  </fieldset>
  <fieldset>
  	<label>'.$this->pl->trans($m,'Expiry Date Label').'
  		<i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
  			<div class="tooltip-wrapper">
  				<div class="tooltip">
  					<p>'.$this->pl->trans($m,'This is the expiry date label of the card').'.</p>
  				</div>
  			</div>
  		</i>
  	</label>
  	<input type="text" value="Expiry Date" prop="expiryDateLabel" placeholder="" data-default-value="" class="text small dark">
  </fieldset>
  <fieldset>
  	<label class="option switch">
  		'.$this->pl->trans($m,'Security Code').'
  		<span>
  		<i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
  			<div class="tooltip-wrapper">
  				<div class="tooltip">
  					<p>'.$this->pl->trans($m,'Toggle the Card postcode input label on or off.').'</p>
  				</div>
  			</div>
  		</i>
  		<span>
  		<input type="checkbox" class="securityCode" prop="securityCode">
  		<span class="switch-container">
  			<span class="switch-status on">'.$this->pl->trans($m,'ON').'</span>
  			<span class="switch-status off">'.$this->pl->trans($m,'OFF').'</span>
  			<i></i>
  		</span>

  	</label>
  </fieldset>
  <div class="show_securityCode_1" style="display:none">
      <fieldset>
      	<label>'.$this->pl->trans($m,'Security Code Label').'
      		<i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
      			<div class="tooltip-wrapper">
      				<div class="tooltip">
      					<p>'.$this->pl->trans($m,'This is the security code label of the card').'.</p>
      				</div>
      			</div>
      		</i>
      	</label>
      	<input type="text" value="Security Code" prop="securityCodeLabel" placeholder="" data-default-value="" class="text small dark">
      </fieldset>
  </div>
  <fieldset>
  	<label class="option switch">
  		'.$this->pl->trans($m,'Post Code').'
  		<span>
  		<i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
  			<div class="tooltip-wrapper">
  				<div class="tooltip">
  					<p>'.$this->pl->trans($m,'Toggle the Card postcode input label on or off.').'</p>
  				</div>
  			</div>
  		</i>
  		<span>
  		<input type="checkbox" class="middleName" prop="postCode">
  		<span class="switch-container">
  			<span class="switch-status on">'.$this->pl->trans($m,'ON').'</span>
  			<span class="switch-status off">'.$this->pl->trans($m,'OFF').'</span>
  			<i></i>
  		</span>

  	</label>
  </fieldset>
  <div class="show_postCode_1" style="display:none">
  	<fieldset>
  		<label>'.$this->pl->trans($m,'Postal code Label').'
  			<i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
  				<div class="tooltip-wrapper">
  					<div class="tooltip">
  						<p>'.$this->pl->trans($m,'This is the postal code label of the card').'.</p>
  					</div>
  				</div>
  			</i>
  		</label>
  		<input type="text" value="ZIP/Postal Code" prop="postCodeLabel" placeholder="" data-default-value="" class="text small dark">
  	</fieldset>
  </div>
  <hr>
  <fieldset>
  	<label class="option switch">
  		'.$this->pl->trans($m,'Only Capture Card Data').'
  		<span>
  		<i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
  			<div class="tooltip-wrapper">
  				<div class="tooltip">
  					<p>'.$this->pl->trans($m,'Only Capture Card Data').'</p>
  				</div>
  			</div>
  		</i>
  		<span>
  		<input type="checkbox" class="captureCard" prop="captureCard">
  		<span class="switch-container">
  			<span class="switch-status on">'.$this->pl->trans($m,'ON').'</span>
  			<span class="switch-status off">'.$this->pl->trans($m,'OFF').'</span>
  			<i></i>
  		</span>

  	</label>
  </fieldset>
  <div class="show_captureCard_1" style="display:none">
      <fieldset>
      	<label>'.$this->pl->trans($m,'Capture Card Label').'
      		<i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
      			<div class="tooltip-wrapper">
      				<div class="tooltip">
      					<p>'.$this->pl->trans($m,'This is the capture card label').'.</p>
      				</div>
      			</div>
      		</i>
      	</label>
      	<input type="text" value="Capturing credit card data for later processing" prop="captureLabel" placeholder="" data-default-value="" class="text small dark">
      </fieldset>
  </div>
  <fieldset class="calculation"></fieldset>
  <fieldset>
  	<label>'.$this->pl->trans($m,'Payment Processing Button Label').'
  		<i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
  			<div class="tooltip-wrapper">
  				<div class="tooltip">
  					<p>'.$this->pl->trans($m,'This is the label when the Button label is clicked').'.</p>
  				</div>
  			</div>
  		</i>
  	</label>
  	<input type="text" value="Processing..." prop="paymentProcessButtonLabel" placeholder="" data-default-value="" class="text small dark">
  </fieldset>
  <hr>
    <fieldset class="logic"></fieldset>
  ';

  $side_paypal = '
  <div class="pad">
  <fieldset>
  	<label class="type">'.$this->pl->trans($m,'Field Type').':<span>'.$this->pl->trans($m,'Paypal Payment').'</span></label>
  </fieldset>
  <hr>
  <fieldset style="margin-top:15px">
  	<label>'.$this->pl->trans($m,'Label').'
  		<i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
  			<div class="tooltip-wrapper">
  				<div class="tooltip">
  					<p>'.$this->pl->trans($m,'This is the label').'.</p>
  				</div>
  			</div>
  		</i>
  	</label>
  	<input type="text" value="We accept paypal payments" prop="label" placeholder="We accept paypal payments" data-default-value="We accept paypal payments" class="text small dark">
  </fieldset>
  <fieldset>
  	<label>'.$this->pl->trans($m,'Payment Request Paragraph').'
  		<i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
  			<div class="tooltip-wrapper">
  				<div class="tooltip">
  					<p>'.$this->pl->trans($m,'This should be a paragraph explaining what people buy').'.</p>
  				</div>
  			</div>
  		</i>
  	</label>
  	<input type="text" value="This is a sample payment label" prop="paymentsPageLabel" placeholder="" data-default-value="" class="text small dark">
  </fieldset>
  <fieldset style="margin-top:15px">
  	<label>'.$this->pl->trans($m,'Button Label').'
  		<i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
  			<div class="tooltip-wrapper">
  				<div class="tooltip">
  					<p>'.$this->pl->trans($m,'This is the button label in the payments page').'.</p>
  				</div>
  			</div>
  		</i>
  	</label>
  	<input type="text" value="Buy now" prop="buttonLabel" placeholder="" data-default-value="" class="text small dark">
  </fieldset>
  <fieldset style="margin-top:15px">
  	<label>'.$this->pl->trans($m,'Total Label').'
  		<i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
  			<div class="tooltip-wrapper">
  				<div class="tooltip">
  					<p>'.$this->pl->trans($m,'This is the total label in the payments page').'.</p>
  				</div>
  			</div>
  		</i>
  	</label>
  	<input type="text" value="Total" prop="totalLabel" placeholder="" data-default-value="" class="text small dark">
  </fieldset>
  <fieldset style="margin-top:15px">
  	<label>'.$this->pl->trans($m,'Merchant Email').'
  		<i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
  			<div class="tooltip-wrapper">
  				<div class="tooltip">
  					<p>'.$this->pl->trans($m,'This is the email address of the marchent from Paypal').'.</p>
  				</div>
  			</div>
  		</i>
  	</label>
  	<input type="text" prop="email" placeholder="" class="text small dark">
  </fieldset>
  <hr>
        <fieldset class="calculation"></fieldset>
        <fieldset>
        	<label>'.$this->pl->trans($m,'Payment Processing Button Label').'
        		<i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
        			<div class="tooltip-wrapper">
        				<div class="tooltip">
        					<p>'.$this->pl->trans($m,'This is the label when the Button label is clicked').'.</p>
        				</div>
        			</div>
        		</i>
        	</label>
        	<input type="text" value="Processing..." prop="paymentProcessButtonLabel" placeholder="" data-default-value="" class="text small dark">
        </fieldset>
  <hr>
    <fieldset class="logic"></fieldset>
  ';

  $side_stripepaypal = '
  <div class="pad">
  <fieldset>
  	<label class="type">'.$this->pl->trans($m,'Field Type').':<span>'.$this->pl->trans($m,'Stripe + Paypal').'</span></label>
  </fieldset>
  <hr>
  <fieldset>
  	<label>'.$this->pl->trans($m,'Top Label').'
  		<i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
  			<div class="tooltip-wrapper">
  				<div class="tooltip">
  					<p>'.$this->pl->trans($m,'This is the label of stripe').'.</p>
  				</div>
  			</div>
  		</i>
  	</label>
  	<input type="text" value="Please select paypal or credit/debit card" prop="label" placeholder="Please select paypal or credit/debit card" data-default-value="Please select paypal or credit/debit card" class="text small dark">
  </fieldset>
  <fieldset>
  	<label>'.$this->pl->trans($m,'Payment Request Paragraph').'
  		<i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
  			<div class="tooltip-wrapper">
  				<div class="tooltip">
  					<p>'.$this->pl->trans($m,'This should be a paragraph explaining what people buy').'.</p>
  				</div>
  			</div>
  		</i>
  	</label>
  	<input type="text" value="This is a sample payment label" prop="paymentsPageLabel" placeholder="" data-default-value="" class="text small dark">
  </fieldset>
  <fieldset>
  	<label>'.$this->pl->trans($m,'Button Label').'
  		<i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
  			<div class="tooltip-wrapper">
  				<div class="tooltip">
  					<p>'.$this->pl->trans($m,'This is the button label in the payments page').'.</p>
  				</div>
  			</div>
  		</i>
  	</label>
  	<input type="text" value="Buy now" prop="buttonLabel" placeholder="" data-default-value="" class="text small dark">
  </fieldset>
  <fieldset>
  	<label>'.$this->pl->trans($m,'Total Label').'
  		<i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
  			<div class="tooltip-wrapper">
  				<div class="tooltip">
  					<p>'.$this->pl->trans($m,'This is the total label in the payments page').'.</p>
  				</div>
  			</div>
  		</i>
  	</label>
  	<input type="text" value="Total" prop="totalLabel" placeholder="" data-default-value="" class="text small dark">
  </fieldset>
  <hr>
  <label>Stripe Settings</label>
  <hr>
  <fieldset>
  	<label>'.$this->pl->trans($m,'Publishable Key').'
  		<i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
  			<div class="tooltip-wrapper">
  				<div class="tooltip">
  					<p>'.$this->pl->trans($m,'This is the public key provided by Stripe').'.</p>
  				</div>
  			</div>
  		</i>
  	</label>
  	<input type="text" prop="public_key" placeholder="" class="text small dark">
  </fieldset>
  <fieldset>
  	<label>'.$this->pl->trans($m,'Secret Key').'
  		<i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
  			<div class="tooltip-wrapper">
  				<div class="tooltip">
  					<p>'.$this->pl->trans($m,'This is the secret key provided by Stripe.').'</p>
  				</div>
  			</div>
  		</i>
  	</label>
  	<input type="text" prop="secret_key" placeholder="" class="text small dark">
  </fieldset>
  <hr>
  <fieldset>
      <label>
          '.$this->pl->trans($m,'Payment Methods').'
          <span>
          <i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
              <div class="tooltip-wrapper">
                  <div class="tooltip">
                      <p>'.$this->pl->trans($m,'Stripe Payment Method').'</p>
                  </div>
              </div>
          </i>

      </label>
  </fieldset>
  <fieldset>
    <label class="option switch" style="max-height: 30px;">
        '.$this->pl->trans($m,'Card').'
        <input type="checkbox" class="card" prop="card">
        <span class="switch-container">
            <span class="switch-status on">'.$this->pl->trans($m,'ON').'</span>
            <span class="switch-status off">'.$this->pl->trans($m,'OFF').'</span>
            <i></i>
        </span>

    </label>
  </fieldset>
  <div class="show_card_1" style="display:none">
      <fieldset>
      	<label>'.$this->pl->trans($m,'Card Label').'
      		<i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
      			<div class="tooltip-wrapper">
      				<div class="tooltip">
      					<p>'.$this->pl->trans($m,'This is the label of stripe').'.</p>
      				</div>
      			</div>
      		</i>
      	</label>
      	<input type="text" value="Pay with credit / debit card" prop="labelStripe" placeholder="Pay with credit / debit card" data-default-value="Pay with credit / debit card" class="text small dark">
      </fieldset>
  </div>
  <fieldset>
    <label class="option switch">
        '.$this->pl->trans($m,'iDEAL').'
        <input type="checkbox" class="ideal" prop="ideal">
        <span class="switch-container">
            <span class="switch-status on">'.$this->pl->trans($m,'ON').'</span>
            <span class="switch-status off">'.$this->pl->trans($m,'OFF').'</span>
            <i></i>
        </span>

    </label>
  </fieldset>
  <div class="show_ideal_1" style="display:none">
      <fieldset>
      	<label>'.$this->pl->trans($m,'iDEAL Label').'
      		<i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
      			<div class="tooltip-wrapper">
      				<div class="tooltip">
      					<p>'.$this->pl->trans($m,'This is the label of stripe').'.</p>
      				</div>
      			</div>
      		</i>
      	</label>
      	<input type="text" value="Pay with iDEAL" prop="idealLabel" placeholder="Pay with iDEAL" data-default-value="Pay with iDEAL" class="text small dark">
      </fieldset>
  </div>
  <fieldset>
    <label class="option switch">
        '.$this->pl->trans($m,'Alipay').'
        <input type="checkbox" class="alipay" prop="alipay">
        <span class="switch-container">
            <span class="switch-status on">'.$this->pl->trans($m,'ON').'</span>
            <span class="switch-status off">'.$this->pl->trans($m,'OFF').'</span>
            <i></i>
        </span>

    </label>
  </fieldset>
  <div class="show_alipay_1" style="display:none">
      <fieldset>
      	<label>'.$this->pl->trans($m,'Alipay Label').'
      		<i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
      			<div class="tooltip-wrapper">
      				<div class="tooltip">
      					<p>'.$this->pl->trans($m,'This is the label of stripe').'.</p>
      				</div>
      			</div>
      		</i>
      	</label>
      	<input type="text" value="Pay with Alipay" prop="alipayLabel" placeholder="Pay with Alipay" data-default-value="Pay with Alipay" class="text small dark">
      </fieldset>
  </div>
  <fieldset>
    <label class="option switch">
        '.$this->pl->trans($m,'ACH Credit Transfer').'
        <input type="checkbox" class="ach_credit_transfer" prop="ach_credit_transfer">
        <span class="switch-container">
            <span class="switch-status on">'.$this->pl->trans($m,'ON').'</span>
            <span class="switch-status off">'.$this->pl->trans($m,'OFF').'</span>
            <i></i>
        </span>

    </label>
  </fieldset>
  <div class="show_ach_credit_transfer_1" style="display:none">
      <fieldset>
      	<label>'.$this->pl->trans($m,'ACH Credit Transfer Label').'
      		<i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
      			<div class="tooltip-wrapper">
      				<div class="tooltip">
      					<p>'.$this->pl->trans($m,'This is the label of stripe').'.</p>
      				</div>
      			</div>
      		</i>
      	</label>
      	<input type="text" value="Pay with ACH Credit Transfer" prop="ach_credit_transferLabel" placeholder="Pay with ACH Credit Transfer" data-default-value="Pay with ACH Credit Transfer" class="text small dark">
      </fieldset>
  </div>
  <fieldset>
    <label class="option switch">
        '.$this->pl->trans($m,'Bancontact').'
        <input type="checkbox" class="bancontact" prop="bancontact">
        <span class="switch-container">
            <span class="switch-status on">'.$this->pl->trans($m,'ON').'</span>
            <span class="switch-status off">'.$this->pl->trans($m,'OFF').'</span>
            <i></i>
        </span>

    </label>
  </fieldset>
  <div class="show_bancontact_1" style="display:none">
      <fieldset>
      	<label>'.$this->pl->trans($m,'Bancontact Label').'
      		<i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
      			<div class="tooltip-wrapper">
      				<div class="tooltip">
      					<p>'.$this->pl->trans($m,'This is the label of stripe').'.</p>
      				</div>
      			</div>
      		</i>
      	</label>
      	<input type="text" value="Pay with Bancontact" prop="bancontactLabel" placeholder="Pay with Bancontact" data-default-value="Pay with Bancontact" class="text small dark">
      </fieldset>
  </div>
  <fieldset>
    <label class="option switch">
        '.$this->pl->trans($m,'EPS').'
        <input type="checkbox" class="eps" prop="eps">
        <span class="switch-container">
            <span class="switch-status on">'.$this->pl->trans($m,'ON').'</span>
            <span class="switch-status off">'.$this->pl->trans($m,'OFF').'</span>
            <i></i>
        </span>

    </label>
  </fieldset>
  <div class="show_eps_1" style="display:none">
      <fieldset>
      	<label>'.$this->pl->trans($m,'EPS Label').'
      		<i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
      			<div class="tooltip-wrapper">
      				<div class="tooltip">
      					<p>'.$this->pl->trans($m,'This is the label of stripe').'.</p>
      				</div>
      			</div>
      		</i>
      	</label>
      	<input type="text" value="Pay with EPS" prop="epsLabel" placeholder="Pay with EPS" data-default-value="Pay with EPS" class="text small dark">
      </fieldset>
  </div>
  <fieldset>
    <label class="option switch">
        '.$this->pl->trans($m,'Giropay').'
        <input type="checkbox" class="giropay" prop="giropay">
        <span class="switch-container">
            <span class="switch-status on">'.$this->pl->trans($m,'ON').'</span>
            <span class="switch-status off">'.$this->pl->trans($m,'OFF').'</span>
            <i></i>
        </span>

    </label>
  </fieldset>
  <div class="show_giropay_1" style="display:none">
      <fieldset>
      	<label>'.$this->pl->trans($m,'Giropay Label').'
      		<i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
      			<div class="tooltip-wrapper">
      				<div class="tooltip">
      					<p>'.$this->pl->trans($m,'This is the label of stripe').'.</p>
      				</div>
      			</div>
      		</i>
      	</label>
      	<input type="text" value="Pay with Giropay" prop="giropayLabel" placeholder="Pay with Giropay" data-default-value="Pay with Giropay" class="text small dark">
      </fieldset>
  </div>
  <fieldset class="disabled">
    <label class="option switch">
        '.$this->pl->trans($m,'Multibanco (On request)').'
        <input type="checkbox" class="multibanco" prop="multibanco" disabled>
        <span class="switch-container">
            <span class="switch-status on">'.$this->pl->trans($m,'ON').'</span>
            <span class="switch-status off">'.$this->pl->trans($m,'OFF').'</span>
            <i></i>
        </span>

    </label>
  </fieldset>
  <div class="show_multibanco_1" style="display:none">
      <fieldset>
      	<label>'.$this->pl->trans($m,'Multibanco Label').'
      		<i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
      			<div class="tooltip-wrapper">
      				<div class="tooltip">
      					<p>'.$this->pl->trans($m,'This is the label of stripe').'.</p>
      				</div>
      			</div>
      		</i>
      	</label>
      	<input type="text" value="Pay with Multibanco" prop="multibancoLabel" placeholder="Pay with Multibanco" data-default-value="Pay with Multibanco" class="text small dark">
      </fieldset>
  </div>
  <fieldset class="disabled">
    <label class="option switch">
        '.$this->pl->trans($m,'P24 (On request)').'
        <input type="checkbox" class="p24" prop="p24" disabled>
        <span class="switch-container">
            <span class="switch-status on">'.$this->pl->trans($m,'ON').'</span>
            <span class="switch-status off">'.$this->pl->trans($m,'OFF').'</span>
            <i></i>
        </span>

    </label>
  </fieldset>
  <div class="show_p24_1" style="display:none">
      <fieldset>
      	<label>'.$this->pl->trans($m,'P24 Label').'
      		<i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
      			<div class="tooltip-wrapper">
      				<div class="tooltip">
      					<p>'.$this->pl->trans($m,'This is the label of stripe').'.</p>
      				</div>
      			</div>
      		</i>
      	</label>
      	<input type="text" value="Pay with P24" prop="p24Label" placeholder="Pay with P24" data-default-value="Pay with P24" class="text small dark">
      </fieldset>
  </div>
  <fieldset class="disabled">
    <label class="option switch">
        '.$this->pl->trans($m,'SEPA Direct Debit (On request)').'
        <input type="checkbox" class="sepa_debit" prop="sepa_debit" disabled>
        <span class="switch-container">
            <span class="switch-status on">'.$this->pl->trans($m,'ON').'</span>
            <span class="switch-status off">'.$this->pl->trans($m,'OFF').'</span>
            <i></i>
        </span>

    </label>
  </fieldset>
  <div class="show_sepa_debit_1" style="display:none">
      <fieldset>
      	<label>'.$this->pl->trans($m,'SEPA Direct Debit Label').'
      		<i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
      			<div class="tooltip-wrapper">
      				<div class="tooltip">
      					<p>'.$this->pl->trans($m,'This is the label of stripe').'.</p>
      				</div>
      			</div>
      		</i>
      	</label>
      	<input type="text" value="Pay with SEPA Direct Debit" prop="sepa_debitLabel" placeholder="Pay with SEPA Direct Debit" data-default-value="Pay with SEPA Direct Debit" class="text small dark">
      </fieldset>
  </div>
  <fieldset class="disabled">
    <label class="option switch">
        '.$this->pl->trans($m,'SOFORT (On request)').'
        <input type="checkbox" class="sofort" prop="sofort" disabled>
        <span class="switch-container">
            <span class="switch-status on">'.$this->pl->trans($m,'ON').'</span>
            <span class="switch-status off">'.$this->pl->trans($m,'OFF').'</span>
            <i></i>
        </span>

    </label>
  </fieldset>
  <div class="show_sofort_1" style="display:none">
      <fieldset>
      	<label>'.$this->pl->trans($m,'SOFORT Label').'
      		<i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
      			<div class="tooltip-wrapper">
      				<div class="tooltip">
      					<p>'.$this->pl->trans($m,'This is the label of stripe').'.</p>
      				</div>
      			</div>
      		</i>
      	</label>
      	<input type="text" value="Pay with SOFORT" prop="sofortLabel" placeholder="Pay with SOFORT" data-default-value="Pay with SOFORT" class="text small dark">
      </fieldset>
  </div>
  <hr>
  <fieldset>
  	<label>'.$this->pl->trans($m,'Name Label').'
  		<i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
  			<div class="tooltip-wrapper">
  				<div class="tooltip">
  					<p>'.$this->pl->trans($m,'This is the name label of the card').'.</p>
  				</div>
  			</div>
  		</i>
  	</label>
  	<input type="text" value="Name on Card" prop="cardNameLabel" placeholder="" data-default-value="" class="text small dark">
  </fieldset>
  <fieldset>
  	<label>'.$this->pl->trans($m,'Card Number Label').'
  		<i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
  			<div class="tooltip-wrapper">
  				<div class="tooltip">
  					<p>'.$this->pl->trans($m,'This is the number label of the card').'.</p>
  				</div>
  			</div>
  		</i>
  	</label>
  	<input type="text" value="Card Number" prop="cardNumberLabel" placeholder="" data-default-value="" class="text small dark">
  </fieldset>
  <fieldset>
  	<label>'.$this->pl->trans($m,'Expiry Date Label').'
  		<i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
  			<div class="tooltip-wrapper">
  				<div class="tooltip">
  					<p>'.$this->pl->trans($m,'This is the expiry date label of the card').'.</p>
  				</div>
  			</div>
  		</i>
  	</label>
  	<input type="text" value="Expiry Date" prop="expiryDateLabel" placeholder="" data-default-value="" class="text small dark">
  </fieldset>
  <fieldset>
  	<label>'.$this->pl->trans($m,'Security Code Label').'
  		<i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
  			<div class="tooltip-wrapper">
  				<div class="tooltip">
  					<p>'.$this->pl->trans($m,'This is the security code label of the card').'.</p>
  				</div>
  			</div>
  		</i>
  	</label>
  	<input type="text" value="Security Code" prop="securityCodeLabel" placeholder="" data-default-value="" class="text small dark">
  </fieldset>
  <fieldset>
  	<label class="option switch">
  		'.$this->pl->trans($m,'Post Code').'
  		<span>
  		<i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
  			<div class="tooltip-wrapper">
  				<div class="tooltip">
  					<p>'.$this->pl->trans($m,'Toggle the Card postcode input label on or off.').'</p>
  				</div>
  			</div>
  		</i>
  		<span>
  		<input type="checkbox" class="middleName" prop="postCode">
  		<span class="switch-container">
  			<span class="switch-status on">'.$this->pl->trans($m,'ON').'</span>
  			<span class="switch-status off">'.$this->pl->trans($m,'OFF').'</span>
  			<i></i>
  		</span>

  	</label>


  </fieldset>
  <div class="show_postCode_1" style="display:none">
  	<fieldset>
  		<label>'.$this->pl->trans($m,'Postal code Label').'
  			<i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
  				<div class="tooltip-wrapper">
  					<div class="tooltip">
  						<p>'.$this->pl->trans($m,'This is the postal code label of the card').'.</p>
  					</div>
  				</div>
  			</i>
  		</label>
  		<input type="text" value="ZIP/Postal Code" prop="postCodeLabel" placeholder="" data-default-value="" class="text small dark">
  	</fieldset>
  </div
  <hr>
  Paypal Settings
  <hr>
  <fieldset>
  	<label>'.$this->pl->trans($m,'Paypal Label').'
  		<i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
  			<div class="tooltip-wrapper">
  				<div class="tooltip">
  					<p>'.$this->pl->trans($m,'This is the label of paypal').'.</p>
  				</div>
  			</div>
  		</i>
  	</label>
  	<input type="text" value="Pay with Paypal" prop="labelPaypal" placeholder="Pay with Paypal" data-default-value="Pay with Paypal" class="text small dark">
  </fieldset>
  <fieldset style="margin-top:15px">
  	<label>'.$this->pl->trans($m,'Merchant Email').'
  		<i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
  			<div class="tooltip-wrapper">
  				<div class="tooltip">
  					<p>'.$this->pl->trans($m,'This is the email address of the marchent from Paypal').'.</p>
  				</div>
  			</div>
  		</i>
  	</label>
  	<input type="text" prop="email" placeholder="" class="text small dark">
  </fieldset
  <hr>
        <fieldset class="calculation"></fieldset>
        <fieldset>
        	<label>'.$this->pl->trans($m,'Payment Processing Button Label').'
        		<i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
        			<div class="tooltip-wrapper">
        				<div class="tooltip">
        					<p>'.$this->pl->trans($m,'This is the label when the Button label is clicked').'.</p>
        				</div>
        			</div>
        		</i>
        	</label>
        	<input type="text" value="Processing..." prop="paymentProcessButtonLabel" placeholder="" data-default-value="" class="text small dark">
        </fieldset>
  <hr>
    <fieldset class="logic"></fieldset>
  ';

  $side_name='
  <div class="pad">
  <fieldset>
  	<label class="type">'.$this->pl->trans($m,'Field Type').':<span>'.$this->pl->trans($m,'Full name').'</span></label>
  </fieldset>
  <fieldset class="reqdis"></fieldset>
  <hr>
  <fieldset class="inputLabel"></fieldset>
  <fieldset class="instructionText"></fieldset>
  <fieldset class="helpText"></fieldset>
  <fieldset>
    <label class="option switch">
        '.$this->pl->trans($m,'Title').'
        <span>
        <i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
            <div class="tooltip-wrapper">
                <div class="tooltip">
                    <p>'.$this->pl->trans($m,'Toggle the Title Name input on or off.').'</p>
                </div>
            </div>
        </i>
        <span>
        <input type="checkbox" class="nameTitle" prop="nameTitle">
        <span class="switch-container">
            <span class="switch-status on">'.$this->pl->trans($m,'ON').'</span>
            <span class="switch-status off">'.$this->pl->trans($m,'OFF').'</span>
            <i></i>
        </span>
    </label>
  </fieldset>
  <div class="show_nameTitle_1" style="display:none">
    <fieldset>
        <label>'.$this->pl->trans($m,'Placeholder Title Text').'
            <i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
                <div class="tooltip-wrapper">
                    <div class="tooltip">
                        <p>'.$this->pl->trans($m,'This is the placeholder of name title').'.</p>
                    </div>
                </div>
            </i>
        </label>
        <input type="text" value="Title" prop="placeholderTitleText" placeholder="" data-default-value="" class="text small dark">
    </fieldset>
  </div>
  <fieldset class="placeholderFirstText"></fieldset>
  <fieldset>
  	<label class="option switch">
  		'.$this->pl->trans($m,'Middle Name').'
  		<span>
  		<i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
  			<div class="tooltip-wrapper">
  				<div class="tooltip">
  					<p>'.$this->pl->trans($m,'Toggle the Middle Name input on or off.').'</p>
  				</div>
  			</div>
  		</i>
  		<span>
  		<input type="checkbox" class="middleName" prop="middleName">
  		<span class="switch-container">
  			<span class="switch-status on">'.$this->pl->trans($m,'ON').'</span>
  			<span class="switch-status off">'.$this->pl->trans($m,'OFF').'</span>
  			<i></i>
  		</span>

  	</label>
  </fieldset>
  <div class="show_middleName_1" style="display:none">
  <fieldset class="placeholderMiddleText"></fieldset>
  </div>
  <fieldset class="placeholderLastText"></fieldset>
  <hr>
  <hr>
  <fieldset class="queryName"></fieldset>
  <hr>
    <fieldset class="logic"></fieldset>
  </div>
  ';

  $formatOptions = '';
  foreach($GLOBALS['ref']['element_address'] as $country => $val) {
  	$formatOptions .= '<option value="'.$country.'">'.$country.'</option>';
  }

  $side_usaddress='
  		<div class="pad">
      <fieldset>
  	<label class="type">'.$this->pl->trans($m,'Field Type').':<span>'.$this->pl->trans($m,'Address').'</span></label>
  </fieldset>
  <fieldset class="reqdis"></fieldset>
  <hr>
  <fieldset class="inputLabel"></fieldset>
  <fieldset class="instructionText"></fieldset>
  <fieldset class="helpText"></fieldset>
  <fieldset class="placeholderAddress1Text"></fieldset>
  <fieldset class="placeholderAddress2Text"></fieldset>
  <fieldset class="placeholderCityText"></fieldset>
  <fieldset class="placeholderStateText"></fieldset>
  <fieldset class="placeholderZipText"></fieldset>
  <hr>
  <fieldset>
  	<label prop="country" class="option">
  		<input type="checkbox" value=true prop="country" data-default-value="true"> '.$this->pl->trans($m,'Country').' <i></i>
  		<i class="icon-info tooltip-container tooltip-position-bottom tooltip-position-left">
  			<div class="tooltip-wrapper">
  				<div class="tooltip">
  					<p>'.$this->pl->trans($m,'Add country field to address form').'</p>
  				</div>
  			</div>
  		</i>
  	</label>
  </fieldset>
  <div class="show_country_1">
  	<fieldset class="placeholderCountryText"></fieldset>
    <fieldset class="defaultCountry">
        <label>'.$this->pl->trans($m,'Default Country').'
            <i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
                <div class="tooltip-wrapper">
                    <div class="tooltip">
                        <p>'.$this->pl->trans($m,'United States, Belgium, etc.').'.</p>
                    </div>
                </div>
            </i>
        </label>
        <input type="text" prop="defaultCountry" class="text small dark">
      </fieldset>
  </div>
  <fieldset>
  	<label class="type">'.$this->pl->trans($m,'Address Format').':</label>
  	<select class="text" prop="format" data-default-value="OTHER">
  		'.$formatOptions.'
  	</select>
  </fieldset>
  <hr>
  <fieldset class="queryName"></fieldset>
  <hr>
    <fieldset class="logic"></fieldset>
  </div>
  ';

  $side_section='
  		<div class="pad"><fieldset>
  	<label class="type">'.$this->pl->trans($m,'Field Type').':<span>'.$this->pl->trans($m,'Title display').'</span></label>
  </fieldset>
  <hr>
  <fieldset>
      <label>Text Size
          <i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
              <div class="tooltip-wrapper">
                  <div class="tooltip">
                      <p>'.$this->pl->trans($m,'date format').'</p>
                  </div>
              </div>
          </i>
      </label>
      <select class="text" prop="textSize">
          <option value="">Normal</option>
          <option value="h1">Heading 1</option>
          <option value="h2">Heading 2</option>
          <option value="h3">Heading 3</option>
          <option value="h4">Heading 4</option>
          <option value="h5">Heading 5</option>
          <option value="h6">Heading 6</option>
      </select>
  </fieldset>
  <fieldset class="labelText">
  	<label>'.$this->pl->trans($m,'Section Title').'
  		<i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
  			<div class="tooltip-wrapper">
  				<div class="tooltip">
  					<p>'.$this->pl->trans($m,'This is the section title').'.</p>
  				</div>
  			</div>
  		</i>
  	</label>
  	<input type="text" prop="labelText" placeholder="'.$this->pl->trans($m,'Section Title').'" class="text small dark">
  </fieldset>
  <hr>
    <fieldset class="logic"></fieldset>
  </div>
  ';

  $side_range='
  <div class="pad"><fieldset>
  	<label class="type">'.$this->pl->trans($m,'Field Type').':<span>'.$this->pl->trans($m,'Range').'</span>
    </label>
  </fieldset>
  <fieldset class="reqdis"></fieldset>
  <hr>
  <fieldset class="inputLabel"></fieldset>
  <fieldset class="instructionText"></fieldset>
  <fieldset class="helpText"></fieldset>
  <hr>
  <div>
  	<div class="6">
  		<label>'.$this->pl->trans($m,'Minimum Value').'</label>
  		<input prop="rangeMin" type="number" placeholder="0" class="text small dark">
  	</div>

  	<div class="6">
  		<label>'.$this->pl->trans($m,'Maximum Value').'</label>
  		<input prop="rangeMax" type="number" placeholder="100" class="text small dark">
  	</div>
  </div>
  <hr>
  <fieldset class="queryName"></fieldset>
  <hr>
    <fieldset class="logic"></fieldset>
  </div>
  ';


  $side_switch='
    		<div class="pad"><fieldset>
    	<label class="type">'.$this->pl->trans($m,'Field Type').'<span>'.$this->pl->trans($m,'Switch').'</span>
    	</label>
    </fieldset>
    <fieldset class="reqdis"></fieldset>
    <hr>
    <fieldset class="inputLabel"></fieldset>
    <fieldset class="instructionText"></fieldset>
    <fieldset class="helpText"></fieldset>
    <fieldset>
  	<label>'.$this->pl->trans($m,'On Label').'
  		<i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
  			<div class="tooltip-wrapper">
  				<div class="tooltip">
  					<p>'.$this->pl->trans($m,'This value sets the On label of the switch').'</p>
  				</div>
  			</div>
  		</i>
  	</label>
  	<input type="text" value="ON" maxlength="4" prop="onLabel" class="text small dark">
    </fieldset>
    <fieldset>
  	<label>'.$this->pl->trans($m,'Off Label').'
  		<i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
  			<div class="tooltip-wrapper">
  				<div class="tooltip">
  					<p>'.$this->pl->trans($m,'This value sets the Off label of the switch').'</p>
  				</div>
  			</div>
  		</i>
  	</label>
  	<input type="text" value="OFF" maxlength="4" prop="offLabel" class="text small dark">
    </fieldset>
    <hr>
    <fieldset class="otherOption"></fieldset>
    <div class="show_otherOption_1" style="display:none">
  	<fieldset class="otherOptionLabel"></fieldset>
  </div>
  <hr>
  <fieldset class="optionsList"></fieldset>
      <hr>
      <fieldset class="queryName"></fieldset>
      <hr>
    <fieldset class="logic"></fieldset>
    </div>
    ';

  $side_select='
  <div class="pad">
  <fieldset>
  	<label class="type">'.$this->pl->trans($m,'Field Type').':<span>'.$this->pl->trans($m,'Select dropdown').'</span></label>
  </fieldset>
  <fieldset class="reqdis"></fieldset>
  <hr>
  <fieldset class="inputLabel"></fieldset>
  <fieldset class="instructionText"></fieldset>
  <fieldset class="helpText"></fieldset>
  <fieldset class="placeholderText"></fieldset>
  	<hr>
  <fieldset class="optionsList"></fieldset>
    <hr>
  <fieldset class="queryName"></fieldset>
  <hr>
    <fieldset class="logic"></fieldset>
  </div>
  ';

  $side_radio='
  		<div class="pad"><fieldset>
  	<label class="type">'.$this->pl->trans($m,'Field Type').':<span>'.$this->pl->trans($m,'Single choice').'</span>
  	</label>
  </fieldset>
  <fieldset class="reqdis"></fieldset>
  <hr>
  <fieldset class="inputLabel"></fieldset>
  <fieldset class="instructionText"></fieldset>
  <fieldset class="helpText"></fieldset>
  <hr>
  <fieldset class="otherOption"></fieldset>
  <div class="show_otherOption_1" style="display:none">
  	<fieldset class="otherOptionLabel"></fieldset>
  </div>
  <hr>
  <fieldset class="optionsList"></fieldset>
  <a href="javascript:;" class="clearDefault">Remove defaults</a>
    <hr>
    <fieldset class="queryName"></fieldset>
    <hr>
    <fieldset class="logic"></fieldset>
  </div>
  ';

  $side_checkbox='
  		<div class="pad"><fieldset>
  	<label class="type">'.$this->pl->trans($m,'Field Type').':<span>'.$this->pl->trans($m,'Multiple choice').'</span>
    </label>
  </fieldset>
  <fieldset class="reqdis"></fieldset>
  <hr>
  <fieldset class="inputLabel"></fieldset>
  <fieldset class="instructionText"></fieldset>
  <fieldset class="helpText"></fieldset>
  <hr>
  <fieldset class="otherOption"></fieldset>
  <div class="show_otherOption_1" style="display:none">
  	<fieldset class="otherOptionLabel"></fieldset>
  </div>
  <hr>
  <fieldset class="optionsList"></fieldset>
    <hr>
    <fieldset class="queryName"></fieldset>
    <hr>
    <fieldset class="logic"></fieldset>
  </div>
  ';

  $side_products='
  		<div class="pad"><fieldset>
  	<label class="type">'.$this->pl->trans($m,'Field Type').':<span>'.$this->pl->trans($m,'Multi Product Select').'</span>
    </label>
  </fieldset>
  <fieldset class="reqdis"></fieldset>
  <hr>
  <fieldset class="inputLabel"></fieldset>
  <fieldset class="instructionText"></fieldset>
  <fieldset class="helpText"></fieldset>
    <fieldset>
        <label>Unit
            <i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
                <div class="tooltip-wrapper">
                    <div class="tooltip">
                        <p>'.$this->pl->trans($m,'This value sets the unit of the Product.').'</p>
                    </div>
                </div>
            </i>
        </label>
        <select class="text" prop="unit">
            <option value="currency">Currency</option>
            <option value="hours">Hours</option>
            <option value="minutes">Minutes</option>
            <option value="inch">Inches</option>
            <option value="cm">Centimeters</option>
            <option value="m">Meters</option>
        </select>
    </fieldset>
  <fieldset>
  	<label class="option switch">
  		'.$this->pl->trans($m,'Use Single Option').'
  		<input id="s_useSelect" prop="useSelect" type="checkbox">
  		<span class="switch-container">
  			<span class="switch-status on">'.$this->pl->trans($m,'ON').'</span>
  			<span class="switch-status off">'.$this->pl->trans($m,'OFF').'</span>
  			<i></i>
  		</span>
  	</label>
  </fieldset>
  <hr>
  <fieldset class="productsList"></fieldset>
  <hr>
  <fieldset class="enableAmount"></fieldset>
  <div class="show_enableAmount_1" style="display:none">
  	<fieldset class="amountOptionsList"></fieldset>
  </div>
    <hr>
    <fieldset class="queryName"></fieldset>
    <hr>
    <fieldset class="logic"></fieldset>
  </div>
  ';

  $side_inputtable='
        <div class="pad"><fieldset>
    <label class="type">'.$this->pl->trans($m,'Field Type').':<span>'.$this->pl->trans($m,'Input Table').'</span>
    </label>
  </fieldset>
  <fieldset class="reqdis"></fieldset>
  <hr>
  <fieldset class="inputLabel"></fieldset>
  <fieldset class="instructionText"></fieldset>
  <fieldset class="helpText"></fieldset>
  <fieldset>
      <label>
      Type
      </label>
      <div class="select">
      <select id="s_inputtype" prop="inputtype" class="text">
           <option value="radio">Single Option</option>
           <option value="checkbox">Multiple Option</option>
           <option value="text">Textbox</option>
      </select>
      </div>
    </fieldset>
  <fieldset>
  <fieldset class="questionList"></fieldset>
  <fieldset class="answerList"></fieldset>
    <hr>
    <fieldset class="queryName"></fieldset>
    <hr>
    <fieldset class="logic"></fieldset>
  </div>
  ';

  $side_label='
  <div class="pad">
  <fieldset>
  	<label class="type">'.$this->pl->trans($m,'Field Type').':<span>'.$this->pl->trans($m,'Text display').'</span></label>
  </fieldset>
  <hr
  <fieldset class="labelText"><label>'.$this->pl->trans($m,'Instruction Text').'<i class="icon-info">
  <div class="tooltip-wrapper"><div class="tooltip"><p>'.$this->pl->trans($m,'This is a fixed text in your form to explain a section of your form.').'</p>
  </div>
  </div></i></label>
  <textarea type="text" placeholder="'.$this->pl->trans($m,'Free text').'" prop="labelText" class="text small dark"></textarea></fieldset>
  <hr>
    <fieldset class="logic"></fieldset>
  </div>
  ';

  $side_textarea='
  <div class="pad">
  <fieldset>
  	<label class="type">'.$this->pl->trans($m,'Field Type').':<span>'.$this->pl->trans($m,'Text input long').'</span></label>
  </fieldset>
  <fieldset class="reqdis"></fieldset>
  <hr>
  <fieldset class="inputLabel"></fieldset>
  <fieldset class="instructionText"></fieldset>
  <fieldset class="helpText"></fieldset>
  <fieldset class="placeholderText"></fieldset>
  <fieldset class="defaultValue"></fieldset>
  <fieldset>
  	<label>Max Length
  		<i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
  			<div class="tooltip-wrapper">
  				<div class="tooltip">
  					<p>'.$this->pl->trans($m,'This value sets the maximum numbers of characters allowed for this field.').'</p>
  				</div>
  			</div>
  		</i>
  	</label>
  	<input type="number" placeholder="'.$this->pl->trans($m,'Unlimited').'" prop="textMaxLength" class="text small dark">
  </fieldset>
  <fieldset>
    <label>Max Length Error Message
        <i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
            <div class="tooltip-wrapper">
                <div class="tooltip">
                    <p>'.$this->pl->trans($m,'This value sets the error message of max length').'</p>
                </div>
            </div>
        </i>
    </label>
    <input type="text" prop="maxLengthErrorMessage" value="This field exceeded the maximum characters allowed." class="text small dark">
  </fieldset>
  <fieldset>
  	<label>'.$this->pl->trans($m,'Text field height in pixels').'
  		<i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
  			<div class="tooltip-wrapper">
  				<div class="tooltip">
  					<p>'.$this->pl->trans($m,'How many Pixels heigh the textarea will be').'</p>
  				</div>
  			</div>
  		</i>
  	</label>
  	<input type="number" prop="textAreaHeight" value="96" class="text small dark">
  </fieldset>
  <hr>
  <fieldset class="queryName"></fieldset>
  <hr>
    <fieldset class="logic"></fieldset>
  </div>
  ';

  $side_pagebreak='
  <div class="pad">
  <fieldset>
  	<label class="type">'.$this->pl->trans($m,'Field Type').':<span>'.$this->pl->trans($m,'PDF Page Break').'</span></label>
  </fieldset>
  <hr>
    <fieldset class="logic"></fieldset>
  </div>';


  $submissions='
  <fieldset>
  	<label>
  	'.$this->pl->trans($m,'Submission Success Page').'
  	<i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
  		<div class="tooltip-wrapper">
  			<div class="tooltip" style="margin-left:-40px">
  				<p>'.$this->pl->trans($m,'This section enables you to configure Submission Success Page URL').'</p>
  			</div>
  		</div>
  	</i>
  	</label>
  	<label class="option switch">
  		'.$this->pl->trans($m,'Redirect to your own Page').'
  		<input id="s_doRedirect" prop="doRedirect" type="checkbox">
  		<span class="switch-container">
  			<span class="switch-status on">'.$this->pl->trans($m,'ON').'</span>
  			<span class="switch-status off">'.$this->pl->trans($m,'OFF').'</span>
  			<i></i>
  		</span>
  	</label>
  </fieldset>
  <div class="show_doRedirect_1">
  <fieldset class="redirectUrl">
  	<label>

  	'.$this->pl->trans($m,'Web Page URL').'
  	<i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
  		<div class="tooltip-wrapper">
  			<div class="tooltip">
  				<p>'.$this->pl->trans($m,'The user will be redirected to this URL after a successful submission.').'</p>
  			</div>
  		</div>
  	</i>
  	</label>
  	<input id="s_redirectUrl" prop="redirectUrl" type="text" value="http://example.com" class="text small dark">
  </fieldset>
  </div>
  <div class="show_doRedirect_0">
  </div>

  ';

$pages = $this->form['pages'];
//var_dump($pages);
$pageCount = count($pages);
$customButtons = '';
if($pageCount > 1) {
    $customDefaultButtons = '';
    $ctr = 1;
    foreach($pages as $page) {
        $next = 'Next';
        $previous = 'Previous';
        if($page->nextButtonText) {$next=$page->nextButtonText;}
        if($page->previousButtonText) {$previous=$page->previousButtonText;}

        $nextFieldset = '
            <fieldset class="customFormButton" data-pageid="'.$page->_id.'">
              <label>
              '.$this->pl->trans($m,'Page '.$ctr.' Next button text').'
              </label>
              <input prop="nextButtonText" type="text" placeholder="Next" value="'.$next.'" class="text small dark" maxlength="20">
            </fieldset>
        ';
        $previousFieldset = '
            <fieldset class="customFormButton" data-pageid="'.$page->_id.'">
              <label>
              '.$this->pl->trans($m,'Page '.$ctr.' Previous button text').'
              </label>
              <input prop="previousButtonText" type="text" placeholder="Previous" value="'.$previous.'" class="text small dark" maxlength="20">
            </fieldset>
        ';

        if($ctr == 1) {
            $customButtons.=$nextFieldset;
        } else if($ctr == $pageCount) {
            $customButtons.=$previousFieldset;
        } else {
            $customButtons.=$previousFieldset.$nextFieldset;
        }
        $ctr++;
    }
}

    $side_confirmation='
        <div class="pad">
            '.$submissions.'
        </div>
    ';

  $side_settings='
  <div class="pad">
  <fieldset class="name">
  	<label>
  	'.$this->pl->trans($m,'Name').'
  	<i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
  		<div class="tooltip-wrapper">
  			<div class="tooltip">
  				<p>'.$this->pl->trans($m,'This is the title of the form').'.</p>
  			</div>
  		</div>
  	</i>
  	</label>
  	<input id="s_name" prop="name" type="text" placeholder="'.$this->pl->trans($m,'Form name').'" class="text small dark" maxlength="45">
  </fieldset>
  <fieldset class="displayHeader">
  	<label>
  	'.$this->pl->trans($m,'Form header').'
  	<i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
  		<div class="tooltip-wrapper">
  			<div class="tooltip">
  				<p>'.$this->pl->trans($m,'The form header contains the name &amp; description.').'</p>
  			</div>
  		</div>
  	</i>
  	</label>
  	<label class="option switch">
  		'.$this->pl->trans($m,'Show name').' &amp; '.$this->pl->trans($m,'description above form').'
  		<input id="s_displayHeader" prop="displayHeader" type="checkbox">
  		<span class="switch-container">
  			<span class="switch-status on">'.$this->pl->trans($m,'ON').'</span>
  			<span class="switch-status off">'.$this->pl->trans($m,'OFF').'</span>
  			<i></i>
  		</span>
  	</label>
  </fieldset>
  <div class="show_displayHeader_1">

  <fieldset class="description">
  	<label>

  	'.$this->pl->trans($m,'Description').'
  	<i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
  		<div class="tooltip-wrapper">
  			<div class="tooltip">
  				<p>'.$this->pl->trans($m,'This is the description of the form.').'</p>
  			</div>
  		</div>
  	</i>
  	</label>
  	<textarea id="s_description" prop="description" placeholder="'.$this->pl->trans($m,'Form description').'" class="text small dark" maxlength="250"></textarea>
  </fieldset>
  </div>
  <hr>
  <fieldset class="logoContainer">
  	<label>

  	'.$this->pl->trans($m,'Form image').'
  	<i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
  		<div class="tooltip-wrapper">
  			<div class="tooltip">
  				<p>'.$this->pl->trans($m,'This image will be displayed at the top of the form.').'</p>
  			</div>
  		</div>
  	</i>
  	</label>
  	<fieldset class="file" layout="table">
  		<input type="file" name="logo" accept=".gif,.jpg,.jpeg,.png" style="display: none;"><button class="small">'.$this->pl->trans($m,'Choose Image').'...</button>
  		<div class="file-dropzone" style="display: none;">
  			<i class="icon-cloud-upload"></i>
  			'.$this->pl->trans($m,'Drop file here').'
  		</div>
  	</fieldset>
  </fieldset>
  <hr>
  	<fieldset class="notifyNewSubmissions">
  		<label>'.$this->pl->trans($m,'Email notifications to admin(You)').'</label>
  		<label class="option switch">
  			'.$this->pl->trans($m,'Send Email').'
  			<input id="s_notifyNewSubmissions" prop="notifyNewSubmissions" type="checkbox">
  			<span class="switch-container">
  				<span class="switch-status on">'.$this->pl->trans($m,'ON').'</span>
  				<span class="switch-status off">'.$this->pl->trans($m,'OFF').'</span>
  				<i></i>
  			</span>
  		</label>
  	</fieldset>

  <div class="show_notifyNewSubmissions_1">
  	<fieldset class="email">
  		<label>

  		'.$this->pl->trans($m,'Send To Email Address').'
  		<i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
  		<div class="tooltip-wrapper">
  			<div class="tooltip" style="margin-left:-40px">
  					<p>'.$this->pl->trans($m,'The email notification will be sent to this email address').'. <br>'.$this->pl->trans($m,'For multiple notification, emails must be separated by comma').' ","</p>
  				</div>
  			</div>
  		</i>
  		</label>
  		<input id="s_email" prop="email" value="'.$this->uemail.'" type="text" class="text small dark">
  	</fieldset>
  </div>
  <hr>
  <fieldset class="notifySubmitter">
  	<label>
  	'.$this->pl->trans($m,'Notify Respondents').'
  	<i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
  		<div class="tooltip-wrapper">
  			<div class="tooltip" style="margin-left:-70px">
  				<p>'.$this->pl->trans($m,'Email notification to respondents, it needs to have atleast one email field in the form').'.</p>
  			</div>
  		</div>
  	</i>
    <span><a href="/settings/subscription/" fm-button="blue small" class="gopro">Try before upgrade</a></span>
  	</label>
  	<label class="option switch">
  		<input id="s_notifySubmitter" prop="notifySubmitter" type="checkbox">
  		<span class="switch-container">
  			<span class="switch-status on">'.$this->pl->trans($m,'ON').'</span>
  			<span class="switch-status off">'.$this->pl->trans($m,'OFF').'</span>
  			<i></i>
  		</span>
  	</label>
  </fieldset>
  <div class="show_notifySubmitter_1">
  	<br />
  	<label>
  	'.$this->pl->trans($m,'Use Autoresponder Template').'
  	<i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
  		<div class="tooltip-wrapper">
  			<div class="tooltip" style="margin-left:-20px">
  				<p>'.$this->pl->trans($m,'Turn on the button below to use the Autoresponser template you setup').'.</p>
  			</div>
  		</div>
  	</i>
  	</label>
  	<label class="option switch">
  		<input id="s_notifyUseTemplate" prop="notifyUseTemplate" type="checkbox">
  		<span class="switch-container">
  			<span class="switch-status on">'.$this->pl->trans($m,'ON').'</span>
  			<span class="switch-status off">'.$this->pl->trans($m,'OFF').'</span>
  			<i></i>
  		</span>
  	</label>

  	<fieldset class="emailFrom" style="margin-top:15px">
  		<label>

  		'.$this->pl->trans($m,'From Name or Company').'
  		<i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
  		<div class="tooltip-wrapper">
  			<div class="tooltip">
  					<p>'.$this->pl->trans($m,'This text is displayed in the email From Header').'.</p>
  				</div>
  			</div>
  		</i>
  		</label>
  		<input id="s_emailFrom" prop="emailFrom" type="text" placeholder="Formlets" value="Formlets" class="text small dark">
  	</fieldset>
      <fieldset class="emailReply">
  		<label>

  		'.$this->pl->trans($m,'Reply To Email Address').'
  		<i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
  		<div class="tooltip-wrapper">
  			<div class="tooltip">
  					<p>'.$this->pl->trans($m,'This email address will be used when the user hits reply on the email notification').'.</p>
  				</div>
  			</div>
  		</i>
  		</label>
  		<input id="s_emailReply" prop="emailReply" type="text" placeholder="hello@formlets.com" value="hello@formlets.com" class="text small dark">
  	</fieldset>
  </div>
  <hr>
  <div class="customButtons">
        '.$customButtons.'
  </div>
  <fieldset class="submitButtonText">
    <label>

    '.$this->pl->trans($m,'Submit button text').'
    <i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
      <div class="tooltip-wrapper">
        <div class="tooltip" style="margin-left:-50px">
          <p>'.$this->pl->trans($m,'This text is displayed in the “submit” button').'.</p>
        </div>
      </div>
    </i>
    </label>
    <input id="s_submitButtonText" prop="submitButtonText" type="text" placeholder="Submit" value="Submit" class="text small dark" maxlength="20">
  </fieldset>
  <fieldset class="footerPaginationText">
    <label>

    '.$this->pl->trans($m,'Footer Pagination Text').'
    <i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
      <div class="tooltip-wrapper">
        <div class="tooltip" style="margin-left:-60px">
          <p>'.$this->pl->trans($m,'This text is displayed for "Page X of X').'.</p>
        </div>
      </div>
    </i>
    </label>
    <div>
      <input style="width:70px" id="s_footerPaginationPageText" prop="footerPaginationPageText" type="text" placeholder="Page" value="Page" class="text small dark" maxlength="20">
      <span style="padding:0px 10px;line-height:30px">X</span>
      <input style="width:70px" id="s_footerPaginationOfText" prop="footerPaginationOfText" type="text" placeholder="of" value="of" class="text small dark" maxlength="20">
      <span style="padding:0px 10px;line-height:30px">X</span>
    </div>
  </fieldset>
  <hr>
  <fieldset class="autoComplete">
  	<label>
  	'.$this->pl->trans($m,'Autocomplete').'
  	<i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
  		<div class="tooltip-wrapper">
  			<div class="tooltip" style="margin-left:-70px">
  				<p>'.$this->pl->trans($m,'Enables autocomplete of the form').'.</p>
  			</div>
  		</div>
  	</i>
  	</label>
  	<label class="option switch">
  		<input id="s_autoComplete" prop="autoComplete" type="checkbox">
  		<span class="switch-container">
  			<span class="switch-status on">'.$this->pl->trans($m,'ON').'</span>
  			<span class="switch-status off">'.$this->pl->trans($m,'OFF').'</span>
  			<i></i>
  		</span>
  	</label>
  </fieldset>
  <hr>
  <fieldset>
    <label>'.$this->pl->trans($m,'Currency').'
        <i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
            <div class="tooltip-wrapper">
                <div class="tooltip">
                    <p>'.$this->pl->trans($m,'Currency Code that the customer will pay').'</p>
                </div>
            </div>
        </i>
    </label>
    '.$this->currency_options().'
  </fieldset>
  <hr>
  <fieldset class="isExternalData">
    <label>
    '.$this->pl->trans($m,'Pass external data').'
    <i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
        <div class="tooltip-wrapper">
            <div class="tooltip" style="margin-left:-70px">
                <p>'.$this->pl->trans($m,'This is the switch to enable pass external data').'.</p>
            </div>
        </div>
    </i>
    <span><a href="/settings/subscription/" fm-button="blue small" class="gopro">Try before upgrade</a></span>
    </label>
    <label class="option switch">
        <input id="s_isExternalData" prop="isExternalData" type="checkbox">
        <span class="switch-container">
            <span class="switch-status on">'.$this->pl->trans($m,'ON').'</span>
            <span class="switch-status off">'.$this->pl->trans($m,'OFF').'</span>
            <i></i>
        </span>
    </label>
  </fieldset>
  <div class="show_isExternalData_1">
    <fieldset class="externalData" style="margin-top:15px">
        <label>

        '.$this->pl->trans($m,'Parameters').'
        <i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
        <div class="tooltip-wrapper">
            <div class="tooltip">
                    <p>'.$this->pl->trans($m,'These are the get parameters we will accept from the url separated by comma').'.</p>
                </div>
            </div>
        </i>
        </label>
        <input id="s_externalData" prop="externalData" type="text" placeholder="Parameter1, Parameter2" class="text small dark">
    </fieldset>
  </div>
  <hr>
  <fieldset class="enableCSRF">
    <label>
    '.$this->pl->trans($m,'Enable CSRF Protection').'
    <i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
        <div class="tooltip-wrapper">
            <div class="tooltip" style="margin-left:-70px">
                <p>'.$this->pl->trans($m,'This will enable CSRF protection of the form.').'.</p>
            </div>
        </div>
    </i>
    </label>
    <label class="option switch">
        <input id="s_enableCSRF" prop="enableCSRF" type="checkbox">
        <span class="switch-container">
            <span class="switch-status on">'.$this->pl->trans($m,'ON').'</span>
            <span class="switch-status off">'.$this->pl->trans($m,'OFF').'</span>
            <i></i>
        </span>
    </label>
  </fieldset>
  <hr>
  <fieldset class="autoFill">
    <label>
    '.$this->pl->trans($m,'Auto-fill with previous responses').'
    <i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
        <div class="tooltip-wrapper">
            <div class="tooltip" style="margin-left:-70px">
                <p>'.$this->pl->trans($m,'Handy for return users, will remember and auto-fill the values previously submitted').'.</p>
            </div>
        </div>
    </i>
    </label>
    <label class="option switch">
        <input id="s_autoFill" prop="autoFill" type="checkbox">
        <span class="switch-container">
            <span class="switch-status on">'.$this->pl->trans($m,'ON').'</span>
            <span class="switch-status off">'.$this->pl->trans($m,'OFF').'</span>
            <i></i>
        </span>
    </label>
  </fieldset>
  <hr>
  <fieldset class="rtl">
    <label>
    '.$this->pl->trans($m,'RTL Content').'
    <i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
        <div class="tooltip-wrapper">
            <div class="tooltip" style="margin-left:-70px">
                <p>'.$this->pl->trans($m,'Your form will be right to left content').'.</p>
            </div>
        </div>
    </i>
    </label>
    <label class="option switch">
        <input id="s_rtl" prop="rtl" type="checkbox">
        <span class="switch-container">
            <span class="switch-status on">'.$this->pl->trans($m,'ON').'</span>
            <span class="switch-status off">'.$this->pl->trans($m,'OFF').'</span>
            <i></i>
        </span>
    </label>
  </fieldset>
  <hr>
  <fieldset class="trackGeoAndTimezone">
    <label>
    '.$this->pl->trans($m,'Track Geo and Timezone').'
    <i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
        <div class="tooltip-wrapper">
            <div class="tooltip" style="margin-left:-70px">
                <p>'.$this->pl->trans($m,'Form will include the country and timezone').'.</p>
            </div>
        </div>
    </i>
    </label>
    <label class="option switch">
        <input id="s_trackGeoAndTimezone" prop="trackGeoAndTimezone" type="checkbox">
        <span class="switch-container">
            <span class="switch-status on">'.$this->pl->trans($m,'ON').'</span>
            <span class="switch-status off">'.$this->pl->trans($m,'OFF').'</span>
            <i></i>
        </span>
    </label>
  </fieldset>
  <hr>
  <fieldset>
    <label>'.$this->pl->trans($m,'Response Storage').'
        <i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
            <div class="tooltip-wrapper">
                <div class="tooltip">
                    <p>'.$this->pl->trans($m,'Response Storage').'</p>
                </div>
            </div>
        </i>
    </label>
    <select class="text fontawesome-select" id="s_responseStorage" prop="responseStorage">
  		<option value="standard">Standard Clear Text</option>
  		<option value="encrypted">Encrypted</option>
  		<option value="erase">Auto Erase From Server After Transaction</option>
  	</select>
  </fieldset>
  <hr>
  '.$submissions.'
  <hr>
  <fieldset class="usePassword">
    <label>
    '.$this->pl->trans($m,'Form Password').'
    <i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
        <div class="tooltip-wrapper">
            <div class="tooltip" style="margin-left:-70px">
                <p>'.$this->pl->trans($m,'Enable form password').'.</p>
            </div>
        </div>
    </i>
    <span><a href="/settings/subscription/" fm-button="blue small" class="gopro">Try before upgrade</a></span>
    </label>
    <label class="option switch">
        <input id="s_usePassword" prop="usePassword" type="checkbox">
        <span class="switch-container">
            <span class="switch-status on">'.$this->pl->trans($m,'ON').'</span>
            <span class="switch-status off">'.$this->pl->trans($m,'OFF').'</span>
            <i></i>
        </span>
    </label>
  </fieldset>
  <div class="show_usePassword_1">
    <fieldset class="passwordLabel" style="margin-top:15px">
        <label>

        '.$this->pl->trans($m,'Password Label').'
        <i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
        <div class="tooltip-wrapper">
            <div class="tooltip">
                    <p>'.$this->pl->trans($m,'Password Label').'.</p>
                </div>
            </div>
        </i>
        </label>
        <input id="s_passwordLabel" prop="passwordLabel" type="text" placeholder="" class="text small dark">
    </fieldset>
    <fieldset class="passwordButtonLabel" style="margin-top:15px">
        <label>

        '.$this->pl->trans($m,'Password Button Label').'
        <i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
        <div class="tooltip-wrapper">
            <div class="tooltip">
                    <p>'.$this->pl->trans($m,'Password Label').'.</p>
                </div>
            </div>
        </i>
        </label>
        <input id="s_passwordButtonLabel" prop="passwordButtonLabel" type="text" placeholder="" class="text small dark">
    </fieldset>
    <fieldset class="invalidPassword" style="margin-top:15px">
        <label>

        '.$this->pl->trans($m,'Invalid Password Label').'
        <i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
        <div class="tooltip-wrapper">
            <div class="tooltip">
                    <p>'.$this->pl->trans($m,'Invalid Password Label').'.</p>
                </div>
            </div>
        </i>
        </label>
        <input id="s_invalidPassword" prop="invalidPassword" type="text" placeholder="" class="text small dark">
    </fieldset>
    <fieldset class="password" style="margin-top:15px">
        <label>

        '.$this->pl->trans($m,'Password').'
        <i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
        <div class="tooltip-wrapper">
            <div class="tooltip">
                    <p>'.$this->pl->trans($m,'Password').'.</p>
                </div>
            </div>
        </i>
        </label>
        <input id="s_password" prop="password" type="text" placeholder="" class="text small dark">
    </fieldset>
  </div>
  <hr>
  <fieldset class="leavePrompt">
    <label>
    '.$this->pl->trans($m,'Leave Prompt').'
    <i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
        <div class="tooltip-wrapper">
            <div class="tooltip" style="margin-left:-70px">
                <p>'.$this->pl->trans($m,'A prompt will be given if they leave the unsaved form').'.</p>
            </div>
        </div>
    </i>
    </label>
    <label class="option switch">
        <input id="s_leavePrompt" prop="leavePrompt" type="checkbox">
        <span class="switch-container">
            <span class="switch-status on">'.$this->pl->trans($m,'ON').'</span>
            <span class="switch-status off">'.$this->pl->trans($m,'OFF').'</span>
            <i></i>
        </span>
    </label>
  </fieldset>
  <hr>
  <fieldset class="inactiveMessage">
  	<label>

  	'.$this->pl->trans($m,'Inactive message').'
  	<i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
  		<div class="tooltip-wrapper">
  			<div class="tooltip">
  				<p>'.$this->pl->trans($m,'This is the message displayed when the form is not active').'.</p>
  			</div>
  		</div>
  	</i>
  	</label>
  	<input id="s_inactiveMessage" prop="inactiveMessage" type="text" value="'.$this->pl->trans($m,'Form is not active').'." class="text small dark">
  </fieldset>
  <hr>
  <fieldset class="requiredMessage">
  	<label>

  	'.$this->pl->trans($m,'Required message').'
  	<i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
  		<div class="tooltip-wrapper">
  			<div class="tooltip">
  				<p>'.$this->pl->trans($m,'This is the message displayed when the required field is not filled').'.</p>
  			</div>
  		</div>
  	</i>
  	</label>
  	<input id="s_requiredMessage" prop="requiredMessage" type="text" value="'.$this->pl->trans($m,'This field is required').'." class="text small dark">
  </fieldset>
  </div>
  ';

  $side_endpoint='
  <div class="pad">
  <fieldset class="name">
  	<label>

  	'.$this->pl->trans($m,'Name').'
  	<i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
  		<div class="tooltip-wrapper">
  			<div class="tooltip">
  				<p>'.$this->pl->trans($m,'This is the title of the form').'.</p>
  			</div>
  		</div>
  	</i>
  	</label>
  	<input id="s_name" prop="name" type="text" placeholder="'.$this->pl->trans($m,'form Name').'" class="text small dark" maxlength="45">
  </fieldset>'.$submissions.'
  <hr>
  	<fieldset class="notifyNewSubmissions">
  		<label>'.$this->pl->trans($m,'Email notifications to admin(You)').'</label>
  		<label class="option switch">
  			'.$this->pl->trans($m,'Send Email').'
  			<input id="s_notifyNewSubmissions" prop="notifyNewSubmissions" type="checkbox">
  			<span class="switch-container">
  				<span class="switch-status on">'.$this->pl->trans($m,'ON').'</span>
  				<span class="switch-status off">'.$this->pl->trans($m,'OFF').'</span>
  				<i></i>
  			</span>
  		</label>
  	</fieldset>

  <div class="show_notifyNewSubmissions_1">
  	<fieldset class="email">
  		<label>

  		'.$this->pl->trans($m,'Send To Email Address').'
  		<i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
  		<div class="tooltip-wrapper">
  			<div class="tooltip" style="margin-left:-40px">
  					<p>'.$this->pl->trans($m,'The email notification will be sent to this email address').'. <br>'.$this->pl->trans($m,'For multiple notification, emails must be separated by comma').' ","</p>
  				</div>
  			</div>
  		</i>
  		</label>
  		<input id="s_email" prop="email" value="'.$this->uemail.'" type="text" class="text small dark">
  	</fieldset>
  </div>
  <fieldset class="notifySubmitter">
  	<label>'.$this->pl->trans($m,'Notification for the Submitter').'</label>
  	<label class="option switch">
  		<input id="s_notifySubmitter" prop="notifySubmitter" type="checkbox">
  		<span class="switch-container">
  			<span class="switch-status on">'.$this->pl->trans($m,'ON').'</span>
  			<span class="switch-status off">'.$this->pl->trans($m,'OFF').'</span>
  			<i></i>
  		</span>
  	</label>
  </fieldset>
  <div class="show_notifyNewSubmissions_1">
  	<fieldset class="emailFrom">
  		<label>

  		'.$this->pl->trans($m,'From Name or Company').'
  		<i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
  		<div class="tooltip-wrapper">
  			<div class="tooltip">
  					<p>'.$this->pl->trans($m,'This text is displayed in the email From Header').'.</p>
  				</div>
  			</div>
  		</i>
  		</label>
  		<input id="s_emailFrom" prop="emailFrom" type="text" placeholder="Formlets" class="text small dark">
  	</fieldset>
      <fieldset class="emailReply">
  		<label>

  		'.$this->pl->trans($m,'Reply To Email Address').'
  		<i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
  		<div class="tooltip-wrapper">
  			<div class="tooltip">
  					<p>'.$this->pl->trans($m,'This email address will be used when the user hits reply on the email notification').'.</p>
  				</div>
  			</div>
  		</i>
  		</label>
  		<input id="s_emailReply" prop="emailReply" type="text" placeholder="hello@formlets.com" class="text small dark">
  	</fieldset>
  </div>
  </div>';

  $addDisabledClassWhenFree = $this->uaccountstatus=='FREE' || $this->uaccountstatus=='PREVIEW' ? 'disabled':'';
  //$addDisabledClassWhenFree = '';

  $side_elements='
  		<div class="pad">
                  '.$this->pl->trans($m,'Drag these boxes to the form at the right to add fields').'
  <div>
  	<fieldset>
  		<label>'.$this->pl->trans($m,'Quick presets').'</label>
        <div id="sideA">
            <div did="pre_text" data-text-type="email" data-default-value="Email" class="el"><i class="icon-mail"></i>&nbsp;<span>'.$this->pl->trans($m,'Email').'</span></div>
            <div did="pre_text" data-text-type="phone" data-default-value="Phone" class="el"><i class="icon-phone"></i>&nbsp;<span>'.$this->pl->trans($m,'Phone').'</span></div>
            <div did="pre_text" data-text-type="number" data-default-value="Number" class="el"><i class="icon-hashtag"></i>&nbsp;<span>'.$this->pl->trans($m,'Number').'</span></div>
            <div did="pre_name" data-default-value="Name" class="el"><i class="icon-user"></i>&nbsp;<span>'.$this->pl->trans($m,'Full name').'</span></div>
            <div did="pre_us_address" data-default-value="Address" class="el"><i class="icon-home"></i>&nbsp;<span>'.$this->pl->trans($m,'Address').'</span></div>
        </div>
    </fieldset>
  	<fieldset>
  		<label>'.$this->pl->trans($m,'Inputs').'</label>
        <div id="sideB">
            <div did="pre_text" data-default-value="Text input short" class="el"><i class="fa fa-font" aria-hidden="true" style="border:1px solid #000;"></i>&nbsp;<span>'.$this->pl->trans($m,'Text input short').'</span></div>
            <div did="pre_textarea" data-default-value="Text input long" class="el"><i class="icon-textarea"></i>&nbsp;<span>'.$this->pl->trans($m,'Text input long').'</span></div>
            <div did="pre_radio" data-default-value="Single choice" class="el"><i class="icon-radio"></i>&nbsp;<span>'.$this->pl->trans($m,'Single choice').'</span></div>
            <div did="pre_checkbox" data-default-value="Multiple choice" class="el"><i class="icon-checkbox"></i>&nbsp;<span>'.$this->pl->trans($m,'Multiple choice').'</span></div>
            <div did="pre_select" data-default-value="Select Dropdown" class="el"><i class="icon-dropdown"></i>&nbsp;<span>'.$this->pl->trans($m,'Select dropdown').'</span></div>
            <div did="pre_switch" data-default-value="Switch" class="el"><i class="icon-switch"></i>&nbsp;<span>'.$this->pl->trans($m,'Switch').'</span></div>
            <div did="pre_range" data-default-value="Range" class="el"><i class="icon-range"></i>&nbsp;<span>'.$this->pl->trans($m,'Range').'</span></div>
            <div did="pre_date" data-default-value="Date" class="el"><i class="icon-calendar"></i>&nbsp;<span>'.$this->pl->trans($m,'Date').'</span></div>
            <div did="pre_time" data-default-value="Time" class="el"><i class="far fa-clock"></i>&nbsp;<span>'.$this->pl->trans($m,'Time').'</span></div>
            <div did="pre_datetime" data-default-value="Date and Time" class="el"><i class="icon-calendar"></i>&nbsp;<span>'.$this->pl->trans($m,'Date and Time').'</span></div>
            <div did="pre_products" data-default-value="Multi Product Select" class="el"><i class="fa fa-th-list"></i>&nbsp;<span>'.$this->pl->trans($m,'Multi Product Select').'</span></div>
            <div did="pre_inputtable" data-default-value="Input Table" class="el"><i class="fa fa-table"></i>&nbsp;<span>'.$this->pl->trans($m,'Input Table').'</span></div>
            <div did="pre_captcha" data-default-value="Captcha" class="el captcha"><i class="fa fa-refresh"></i>&nbsp;<span>'.$this->pl->trans($m,'Google Captcha').'</span></div>
            <div did="pre_signature" data-default-value="Signature" class="el signature"><i class="fa fa-pencil-square-o" aria-hidden="true"></i>&nbsp;<span>'.$this->pl->trans($m,'Signature').'</span></div>
            <div did="pre_starrating" data-default-value="Star Rating" class="el starrating"><i class="fa fa-star" aria-hidden="true"></i>&nbsp;<span>'.$this->pl->trans($m,'Star Rating').'</span></div>
            <div did="pre_lookup" data-default-value="Lookup" class="el lookup"><i class="fa fa-eye" aria-hidden="true"></i>&nbsp;<span>'.$this->pl->trans($m,'Lookup').'</span></div>
            <div did="pre_calculation" data-default-value="Calculation" class="el calculation"><i class="fa fa-calculator" aria-hidden="true"></i>&nbsp;<span>'.$this->pl->trans($m,'Calculation').'</span></div>
        </div>
    </fieldset>
    <fieldset>
        <label>'.$this->pl->trans($m,'Displays').'</label>
        <div id="sideD">
            <div did="pre_label" data-default-value="Text display" class="el"><i class="icon-text"></i>&nbsp;<span>'.$this->pl->trans($m,'Text display').'</span></div>
            <div did="pre_section" data-default-value="Title display" class="el"><i class="icon-section-break"></i>&nbsp;<span>'.$this->pl->trans($m,'Title display').'</span></div>
            <div did="pre_picture" data-default-value="Image" class="el picture"><i class="fa fa-picture-o"></i><span> Image</span></div>
        </div>
    </fieldset>
  	<fieldset>
      	<label style="margin-bottom:10px">Advanced <span><a href="/settings/subscription/" fm-button="blue small" class="gopro">Try before upgrade</a></span></label>
        <div id="sideC">
      		<div did="pre_file" data-default-value="File Upload" class="el '.$addDisabledClassWhenFree.'"><i class="icon-cloud-upload"></i><span> File upload</span></div>
      		<div did="pre_stripe" data-default-value="Stripe Payment" class="el stripe '.$addDisabledClassWhenFree.'"><i class="fa fa-credit-card"></i><span> Stripe Payment</span></div>
      		<div did="pre_paypal" data-default-value="Paypal Payment" class="el paypal '.$addDisabledClassWhenFree.'"><i class="fab fa-paypal"></i><span> Paypal Payment</span></div>
      		<div did="pre_stripepaypal" data-default-value="Stripe + Paypal" class="el stripepaypal '.$addDisabledClassWhenFree.'"><i class="fa fa-credit-card"></i><span> Stripe + Paypal</span></div>
        </div>
    </fieldset>
  </div>
  </div>
  ';

  $side_elementsEdit = '
  	<div class="pad">
          '.$this->pl->trans($m,'Click the field you want to edit below').'
          <section class="elementsContainer">
          </section>
      </div>
  ';

  $themes = $this->lo->listThemes(array("uid"=>$this->uid));
  $themeOptions = '';
  foreach($themes as $theme) {
  	$themeOptions.='<option value="'.$theme['_id'].'">'.$theme['name'].'</option>';
  }

  $side_theme='
  <div class="pad">
  <div class="themeEnabled">
  	<label class="option switch">
     '.$this->pl->trans($m,'Custom form Theme').'
     <input id="s_themeEnabled" prop="themeEnabled" type="checkbox">
     <span class="switch-container">
       <span class="switch-status on">'.$this->pl->trans($m,'ON').'</span>
       <span class="switch-status off">'.$this->pl->trans($m,'OFF').'</span>
       <i></i>
     </span>
   </label>
   <hr>
  </div>
  <div class="customCSS">
  <fieldset class="customCSSFieldset">
  	<label class="option switch">
  		'.$this->pl->trans($m,'Custom CSS').'
  		<input id="s_customCSS" prop="customCSS" type="checkbox">
  		<span class="switch-container">
  			<span class="switch-status on">'.$this->pl->trans($m,'ON').'</span>
  			<span class="switch-status off">'.$this->pl->trans($m,'OFF').'</span>
  			<i></i>
  		</span>
  	</label>
  </fieldset>
   <hr>
   <div class="warning_message_custom_css" style="display:none;color:#d1603d">
   Custom CSS is active: Please apply your own CSS style sheets
   </div>
   </div>
   <fieldset>
       <label>

       '.$this->pl->trans($m,'Global Font').'
       <i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
       <div class="tooltip-wrapper" class="">
         <div class="tooltip">
           <p>'.$this->pl->trans($m,'This selection changes the font').'.</p>
         </div>
       </div>
     </i>
       </label>
       <div class="select">
       <select id="s_themeFont" prop="themeFont" class="text">
       	<option value="">Standard</option>
         	<option value="Arvo">Arvo</option>
         	<option value="Droid Sans">Droid Sans</option>
         	<option value="Josefin Slab">Josefin Slab</option>
         	<option value="Lato">Lato</option>
         	<option value="Open Sans">Open Sans</option>
         	<option value="PT Sans">PT Sans</option>
         	<option value="Roboto">Roboto</option>
         	<option value="Source Sans Pro">Source Sans Pro</option>
         	<option value="Ubuntu">Ubuntu</option>
         	<option value="Vollkorn">Vollkorn</option>
       </select>
       </div>
     </fieldset>
   <fieldset>
     <hr>
     <fieldset class="backgrounds">
       <label>

       '.$this->pl->trans($m,'Page Background').'
       <i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
           <div class="tooltip-wrapper">
             <div class="tooltip">
               <p>'.$this->pl->trans($m,'This color changes the browser background color').'.</p>
             </div>
           </div>
         </i>
       </label>
       <div class="controls-container">
   		<input id="s_themeBrowserBackground" prop="themeBrowserBackground" value="#F5F5F5" data-default-value="#F5F5F5" class="text small dark jscolor" maxlength="7">
       </div>
     </fieldset>
     <fieldset class="backgrounds">
       <label>

       '.$this->pl->trans($m,'Form Background').'
       <i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
           <div class="tooltip-wrapper">
             <div class="tooltip">
               <p>'.$this->pl->trans($m,'This color changes the form background color').'.</p>
             </div>
           </div>
         </i>
       </label>
       <div class="controls-container">
   		<input id="s_themeFormBackground" prop="themeFormBackground" value="#FFFFFF" data-default-value="#FFFFFF" class="text small dark jscolor" maxlength="7">
       </div>
     </fieldset>
     <fieldset class="backgrounds">
       <label>

       '.$this->pl->trans($m,'Form Border').'
       <i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
           <div class="tooltip-wrapper">
             <div class="tooltip">
               <p>'.$this->pl->trans($m,'This color changes the border color of the form').'.</p>
             </div>
           </div>
         </i>
       </label>
       <div class="controls-container">
   		<input id="s_themeFormBorder" prop="themeFormBorder" value="#D6D7D6" data-default-value="#D6D7D6" class="text small dark jscolor" maxlength="7">
       </div>
     </fieldset>
     <fieldset class="backgrounds">
       <label>

       '.$this->pl->trans($m,'Field Background').'
       <i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
           <div class="tooltip-wrapper">
             <div class="tooltip">
               <p>'.$this->pl->trans($m,'This color changes the field background color').'.</p>
             </div>
           </div>
         </i>
       </label>
       <div class="controls-container">
   		<input id="s_themeFieldBackground" prop="themeFieldBackground" value="#FFFFFF" data-default-value="#FFFFFF" class="text small dark jscolor" maxlength="7">
       </div>
     </fieldset>
     <fieldset class="backgrounds">
       <label>

       '.$this->pl->trans($m,'Field Border').'
       <i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
           <div class="tooltip-wrapper">
             <div class="tooltip">
               <p>'.$this->pl->trans($m,'The color of the field border when in natural state').'</p>
             </div>
           </div>
         </i>
       </label>
       <div class="controls-container">
   		<input id="s_themeFieldBorder" prop="themeFieldBorder" value="#D6D7D6" data-default-value="#D6D7D6" class="text small dark jscolor" maxlength="7">
     	</div>
     </fieldset>
     <fieldset class="backgrounds">
       <label>

       '.$this->pl->trans($m,'Hover Field Border').'
       <i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
           <div class="tooltip-wrapper">
             <div class="tooltip">
               <p>'.$this->pl->trans($m,'This color changes the field border color when the field is hovered').'.</p>
             </div>
           </div>
         </i>
       </label>
       <div class="controls-container">
   		<input id="s_themeFieldHover" prop="themeFieldHover" value="#3E4943" data-default-value="#3E4943" class="text small dark jscolor" maxlength="7">
       </div>
     </fieldset>
     <fieldset class="backgrounds">
       <label>

       '.$this->pl->trans($m,'Active Field Border').'
       <i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
           <div class="tooltip-wrapper">
             <div class="tooltip">
              <p>'.$this->pl->trans($m,'This color changes the field border color when the field is active').'.</p>
             </div>
           </div>
         </i>
       </label>
       <div class="controls-container">
   		<input id="s_themeFieldActive" prop="themeFieldActive" value="#4BAEC2" data-default-value="#4BAEC2" class="text small dark jscolor" maxlength="7">
       </div>
     </fieldset>
     <fieldset class="backgrounds">
       <label>

       '.$this->pl->trans($m,'Error Field Border').'
       <i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
           <div class="tooltip-wrapper">
             <div class="tooltip">
               <p>'.$this->pl->trans($m,'This color changes the field border color and helper text when the the input is invalid or required').'.</p>
             </div>
           </div>
         </i>
       </label>
       <div class="controls-container">
   		<input id="s_themeFieldError" prop="themeFieldError" value="#D1603D" data-default-value="#D1603D" class="text small dark jscolor" maxlength="7">
       </div>
     </fieldset>
     <fieldset class="backgrounds">
       <label>

       '.$this->pl->trans($m,'Selected Option').'
       <i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
           <div class="tooltip-wrapper">
             <div class="tooltip">
               <p>'.$this->pl->trans($m,'This color changes the field border color when the field is selected').'.</p>
             </div>
           </div>
         </i>
       </label>
       <div class="controls-container">
   		<input id="s_themeFieldSelected" prop="themeFieldSelected" value="#4BAEC2" data-default-value="#4BAEC2" class="text small dark jscolor" maxlength="7">
       </div>
     </fieldset>
     <fieldset class="backgrounds">
       <label>

       '.$this->pl->trans($m,'Button Background').'
       <i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
           <div class="tooltip-wrapper">
             <div class="tooltip">
               <p>'.$this->pl->trans($m,'This color changes the button background color').'.</p>
             </div>
           </div>
         </i>
       </label>
       <div class="controls-container">
   		<input id="s_themeSubmitButton" prop="themeSubmitButton" value="#4BAEC2" data-default-value="#4BAEC2" class="text small dark jscolor" maxlength="7">
       </div>
     </fieldset>
     <fieldset class="backgrounds">
       <label>

       '.$this->pl->trans($m,'Button Text Color').'
       <i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
           <div class="tooltip-wrapper">
             <div class="tooltip">
               <p>'.$this->pl->trans($m,'This color changes the button text color').'.</p>
             </div>
           </div>
         </i>
       </label>
       <div class="controls-container">
   		<input id="s_themeSubmitButtonText" prop="themeSubmitButtonText" value="#FFFFFF" data-default-value="#FFFFFF" class="text small dark jscolor" maxlength="7">
       </div>
     </fieldset>
     <fieldset class="backgrounds">
       <label>

       '.$this->pl->trans($m,'Form Label Text Color').'
       <i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
           <div class="tooltip-wrapper">
             <div class="tooltip">
               <p>'.$this->pl->trans($m,'This color changes the field label text and title of the form').'.</p>
             </div>
           </div>
         </i>
       </label>
       <div class="controls-container">
   		<input id="s_themeText" prop="themeText" value="#3E4943" data-default-value="#3E4943" class="text small dark jscolor" maxlength="7">    </div>
     </fieldset>
     <fieldset class="backgrounds">
       <label>

       '.$this->pl->trans($m,'Form Description Text Color').'
       <i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
           <div class="tooltip-wrapper">
             <div class="tooltip">
               <p>'.$this->pl->trans($m,'This color changes the description of the form').'.</p>
             </div>
           </div>
         </i>
       </label>
       <div class="controls-container">
   		<input id="s_themeDescriptionText" prop="themeDescriptionText" value="#9DA3A0" data-default-value="#9DA3A0" class="text small dark jscolor" maxlength="7">    </div>
     </fieldset>
     <fieldset class="backgrounds">
       <label>

       '.$this->pl->trans($m,'Form Field Text Color').'
       <i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
           <div class="tooltip-wrapper">
             <div class="tooltip">
               <p>'.$this->pl->trans($m,'This color changes the field text and field icon colors').'.</p>
             </div>
           </div>
         </i>
       </label>
       <div class="controls-container">
   		<input id="s_themeFieldText" prop="themeFieldText" value="#3E4943" data-default-value="#3E4943" class="text small dark jscolor" maxlength="7">
       </div>
     </fieldset>
   </fieldset></div>
  ';

  // end of sides

  // there are the elements that render the editor

  $ff_required  ='<button prop="required" class="inline-edit required toggle" tabindex="-1"><i class="icon-asterisk"></i></button>';
  $ff_inputlabel='<textarea class="ed" prop="inputLabel" placeholder="'.$this->pl->trans($m,'Field Label').'"></textarea>';
  $ff_trash     ='<button class="right inline-edit delete" tabindex="-1" title="Delete"><i class="icon-trash"></i></button>';
  $ff_duplicate ='<button class="right inline-edit duplicate" tabindex="-1" title="Duplicate"><i class="fa fa-clone"></i></button>';
  $ff_logic     ='<button class="right inline-edit logic" tabindex="-1" title="only show when"><i class="fa fa-cogs"></i></button>';
  $ff_instruct  ='<textarea class="ed help" prop="instructionText" placeholder="'.$this->pl->trans($m,'Inline Instructions').'"></textarea>';

  $form_text='<div class="gc" et="text" type="TEXT">'.$ff_required.$ff_inputlabel.$ff_trash.$ff_duplicate.'</div>
    <fieldset class="icon-left">
      <input prop="placeholderText" type="text">
    </fieldset>
    <div class="gc">
  '.$ff_instruct.'
    </div>
  ';

  $form_lookup='<div class="gc" et="text" type="LOOKUP">'.$ff_required.$ff_inputlabel.$ff_trash.$ff_duplicate.'</div>
    <fieldset class="icon-left">
      <input prop="placeholderText" type="text">
    </fieldset>
    <div class="gc">
  '.$ff_instruct.'
    </div>
  ';

  $form_calculation='
    <div class="nohide" style="display:none;">
        <div class="gc" et="text" type="CALCULATION">'.$ff_required.$ff_inputlabel.$ff_trash.$ff_duplicate.'</div>
            <fieldset class="icon-left">
              <input prop="placeholderText" type="text">
            </fieldset>
        <div class="gc">
      '.$ff_instruct.'
        </div>
    </div>
    <div class="hide">
        <div style="line-height:80px;"><span style="float: left;margin-top: -8px;">Hidden: use {</span> <span class="ed" prop="inputLabel" style="width:auto;background:inherit;float: left;margin-top: -8px;">Calculation</span><span style="float: left;margin-top: -8px;">} in other fields to use / display value</span>'.$ff_trash.$ff_duplicate.'</div>
    </div>
  ';

  $form_starrating='<div class="gc" et="text" type="STARRATING">'.$ff_required.$ff_inputlabel.$ff_trash.$ff_duplicate.'</div>

        <div class="rating">
            <label>
                <input type="radio" name="rating" value="5" title="5 stars" data-rule="required"> 5
            </label>
            <label>
                <input type="radio" name="rating" value="4" title="4 stars" data-rule="required"> 4
            </label>
            <label>
                <input type="radio" name="rating" value="3" title="3 stars" data-rule="required"> 3
            </label>
            <label>
                <input type="radio" name="rating" value="2" title="2 stars" data-rule="required"> 2
            </label>
            <label>
                <input type="radio" name="rating" value="1" title="1 star" data-rule="required"> 1
            </label>
        </div>
    <div class="gc">
  '.$ff_instruct.'
    </div>
  ';

  $form_date='<div class="gc" et="text" type="DATE">'.$ff_required.$ff_inputlabel.$ff_trash.$ff_duplicate.'</div>
    <fieldset class="icon-left inline-edit-container">
      <input prop="placeholderText" type="text" class="datePicker">
      <i class="fa fa-calendar"></i>
    </fieldset>
    <div class="gc">
  '.$ff_instruct.'
    </div>
  ';

  $form_time='<div class="gc" et="text" type="TIME">'.$ff_required.$ff_inputlabel.$ff_trash.$ff_duplicate.'</div>
    <fieldset class="icon-left inline-edit-container">
      <input prop="placeholderText" type="text" class="timePicker">
      <i class="far fa-clock"></i>
    </fieldset>
    <div class="gc">
  '.$ff_instruct.'
    </div>
  ';

  $form_datetime='<div class="gc" et="text" type="DATETIME">'.$ff_required.$ff_inputlabel.$ff_trash.$ff_duplicate.'</div>
    <fieldset class="icon-left inline-edit-container">
      <input prop="placeholderText" type="text" class="datetimePicker">
      <i class="fa fa-calendar"></i>
    </fieldset>
    <div class="gc">
  '.$ff_instruct.'
    </div>
  ';

  $form_file='<div class="gc" et="file" type="FILE" data-default-value="File Upload">'.$ff_required.$ff_inputlabel.$ff_trash.$ff_duplicate.'</div>
    <fieldset class="icon-left file" style="background:none !important;box-shadow:none !important;border:0px">
      <span contenteditable="" class="button button-blue editable" type="button" data-trigger="s_fileButtonLabel" data-prop="fileButtonLabel">'.$this->pl->trans($m,'Choose File').'...</span>
    </fieldset>
    <div class="gc">
  '.$ff_instruct.'
    </div>
  ';

  $form_picture='<div class="gc" et="file" style="display:none" type="PICTURE" data-default-value="Image">'.$ff_trash.'</div>
    <fieldset class="icon-left picture" style="background:none !important;box-shadow:none;border:0px">
      <div class="image_container">
      	<img src="/static/img/default-image.png" class="img" style="max-width:100%" />
      	<input type="file" accept=".gif,.jpg,.jpeg,.png" style="display: none;">
      	<button type="button" class="upload_picture">'.$this->pl->trans($m,'Change Image').'...</button>
      </div>
    </fieldset>
  ';

  $form_captcha='<div class="gc" et="captcha" type="CAPTCHA" data-default-value="Captcha">'.$ff_trash.'</div>
    <fieldset class="icon-left picture" style="background:none !important;box-shadow:none;border:0px">
      <div class="image_container">
        <img src="/static/img/captcha.png" class="img" style="width: 304px; height: 78px;" />
      </div>
    </fieldset>
  ';

  $form_signature='<div class="gc" et="signature" type="SIGNATURE" data-default-value="Signature">'.$ff_required.'<textarea class="ed" prop="label">Signature</textarea>'.$ff_trash.'</div>
    <fieldset class="icon-left picture" style="background:none !important;box-shadow:none;border:0px">
        <div class="canvasC"><canvas class="signature-pad" width="300" height="150"></canvas><div class="actions"><a class="clear" href="javascript:;">Clear</a></div></div>
    </fieldset>
  ';

  $symbol='$';
  if($this->form['currency']!='USD') {$symbol='';}

  $form_stripe='<div class="gc" et="stripe" type="STRIPE" data-default-value="Stripe Payment">'.$ff_trash.'</div>
    <fieldset class="icon-left picture" style="background:none !important;box-shadow:none;border:0px">
      <div class="image_container">
      	<div class="gr g12 notcapture" style="background:#eee;padding:10px"><span prop="totalLabel">Total</span>: <span class="symbol">'.$symbol.'</span>0 <span class="currency">'.$this->form['currency'].'</span></div>
      	<div class="gr g12 capture" style="background:#eee;padding:10px;display:none">Capturing credit card data for later processing</div>
        <div class="gr show_ideal_1" style="display:none">
  	    	<div class="gr g1" style="margin-top:17px">
  				<label class="option">
  					<i class="fake-radio"></i>
  				</label>
  			</div>
  	    	<div style="margin: 8px 1px;" class="gr g7 div-textarea payment_label" prop="idealLabel" contenteditable="true">Pay with iDEAL</div>
  	    	<div class="gr g4"><img src="/static/img/ideal_logo.png" class="img" style="max-height:50px" /></div>
      	</div>
        <div class="gr show_alipay_1" style="display:none">
  	    	<div class="gr g1" style="margin-top:17px">
  				<label class="option">
  					<i class="fake-radio"></i>
  				</label>
  			</div>
  	    	<div style="margin: 8px 1px;" class="gr g7 div-textarea payment_label" prop="alipayLabel" contenteditable="true">Pay with Alipay</div>
  	    	<div class="gr g4"><img src="/static/img/alipay_logo.png" class="img" style="max-height:50px" /></div>
      	</div>
        <div class="gr show_ach_credit_transfer_1" style="display:none">
  	    	<div class="gr g1" style="margin-top:17px">
  				<label class="option">
  					<i class="fake-radio"></i>
  				</label>
  			</div>
  	    	<div style="margin: 8px 1px;" class="gr g7 div-textarea payment_label" prop="ach_credit_transferLabel" contenteditable="true">Pay with ACH Credit Transfer</div>
  	    	<div class="gr g4"><img src="/static/img/ach_credit_transfer_logo.png" class="img" style="max-height:50px" /></div>
      	</div>
        <div class="gr show_bancontact_1" style="display:none">
  	    	<div class="gr g1" style="margin-top:17px">
  				<label class="option">
  					<i class="fake-radio"></i>
  				</label>
  			</div>
  	    	<div style="margin: 8px 1px;" class="gr g7 div-textarea payment_label" prop="bancontactLabel" contenteditable="true">Pay with Bancontact</div>
  	    	<div class="gr g4"><img src="/static/img/bancontact_logo.png" class="img" style="max-height:50px" /></div>
      	</div>
        <div class="gr show_eps_1" style="display:none">
  	    	<div class="gr g1" style="margin-top:17px">
  				<label class="option">
  					<i class="fake-radio"></i>
  				</label>
  			</div>
  	    	<div style="margin: 8px 1px;" class="gr g7 div-textarea payment_label" prop="epsLabel" contenteditable="true">Pay with EPS</div>
  	    	<div class="gr g4"><img src="/static/img/eps_logo.png" class="img" style="max-height:50px" /></div>
      	</div>
        <div class="gr show_giropay_1" style="display:none">
  	    	<div class="gr g1" style="margin-top:17px">
  				<label class="option">
  					<i class="fake-radio"></i>
  				</label>
  			</div>
  	    	<div style="margin: 8px 1px;" class="gr g7 div-textarea payment_label" prop="giropayLabel" contenteditable="true">Pay with Giropay</div>
  	    	<div class="gr g4"><img src="/static/img/giropay_logo.png" class="img" style="max-height:50px" /></div>
      	</div>
        <div class="gr show_multibanco_1" style="display:none">
  	    	<div class="gr g1" style="margin-top:17px">
  				<label class="option">
  					<i class="fake-radio"></i>
  				</label>
  			</div>
  	    	<div style="margin: 8px 1px;" class="gr g7 div-textarea payment_label" prop="multibancoLabel" contenteditable="true">Pay with Multibanco</div>
  	    	<div class="gr g4"><img src="/static/img/multibanco_logo.png" class="img" style="max-height:50px" /></div>
      	</div>
        <div class="gr show_p24_1" style="display:none">
  	    	<div class="gr g1" style="margin-top:17px">
  				<label class="option">
  					<i class="fake-radio"></i>
  				</label>
  			</div>
  	    	<div style="margin: 8px 1px;" class="gr g7 div-textarea payment_label" prop="p24Label" contenteditable="true">Pay with P24</div>
  	    	<div class="gr g4"><img src="/static/img/p24_logo.png" class="img" style="max-height:50px" /></div>
      	</div>
        <div class="gr show_sepa_debit_1" style="display:none">
  	    	<div class="gr g1" style="margin-top:17px">
  				<label class="option">
  					<i class="fake-radio"></i>
  				</label>
  			</div>
  	    	<div style="margin: 8px 1px;" class="gr g7 div-textarea payment_label" prop="sepa_debitLabel" contenteditable="true">Pay with SEPA Direct Debit</div>
  	    	<div class="gr g4"><img src="/static/img/sepa_debit_logo.png" class="img" style="max-height:50px" /></div>
      	</div>
        <div class="gr show_sofort_1" style="display:none">
  	    	<div class="gr g1" style="margin-top:17px">
  				<label class="option">
  					<i class="fake-radio"></i>
  				</label>
  			</div>
  	    	<div style="margin: 8px 1px;" class="gr g7 div-textarea payment_label" prop="sofortLabel" contenteditable="true">Pay with SOFORT</div>
  	    	<div class="gr g4"><img src="/static/img/sofort_logo.png" class="img" style="max-height:50px" /></div>
      	</div>
        <div class="gr show_card_1">
  	    	<div class="gr g1" style="margin-top:17px">
  				<label class="option">
  					<i class="fake-radio"></i>
  				</label>
  			</div>
  	    	<div style="margin: 8px 1px;" class="gr g7 div-textarea payment_label" prop="label" contenteditable="true">Cards Accepted</div>
  	    	<div class="gr g4"><img src="/static/img/credit-cards.png" class="img" style="max-height:50px" /></div>
      	</div>
      </div>
    </fieldset>
  ';

  $form_paypal='<div class="gc" et="paypal" type="PAYPAL" data-default-value="Paypal Payment">'.$ff_trash.'</div>
    <fieldset class="icon-left picture" style="background:none !important;box-shadow:none;border:0px">
      <div class="image_container">
      	<div class="gr g12" style="background:#eee;padding:10px"><span prop="totalLabel">Total</span>: <span class="symbol">'.$symbol.'</span>0 <span class="currency">'.$this->form['currency'].'</span></div>
      	<div class="gr g6 div-textarea payment_label ed" prop="label" contenteditable="true">We accept paypal payments</div>
      	<div class="gr g6"><img src="/static/img/paypal.png" class="img" style="max-height:50px" /></div>
      </div>
    </fieldset>
  ';

  $form_stripepaypal='<div class="gc" et="stripepaypal" type="STRIPEPAYPAL" data-default-value="Stripe + Paypal">'.$ff_trash.'</div>
    <fieldset class="icon-left picture" style="background:none !important;box-shadow:none;border:0px">
      <div class="image_container">
      	<div class="gr g12" style="background:#eee;padding:10px"><span prop="totalLabel">Total</span>: <span class="symbol">'.$symbol.'</span>0 <span class="currency">'.$this->form['currency'].'</span></div>
      	<div class="gr g12 div-textarea payment_label" prop="label" contenteditable="true">Please select paypal or credit/debit card</div>
        <div class="gr show_ideal_1" style="display:none">
  	    	<div class="gr g1" style="margin-top:17px">
  				<label class="option">
  					<i class="fake-radio"></i>
  				</label>
  			</div>
  	    	<div style="margin: 8px 1px;" class="gr g7 div-textarea payment_label" prop="idealLabel" contenteditable="true">Pay with iDEAL</div>
  	    	<div class="gr g4"><img src="/static/img/ideal_logo.png" class="img" style="max-height:50px" /></div>
      	</div>
        <div class="gr show_alipay_1" style="display:none">
  	    	<div class="gr g1" style="margin-top:17px">
  				<label class="option">
  					<i class="fake-radio"></i>
  				</label>
  			</div>
  	    	<div style="margin: 8px 1px;" class="gr g7 div-textarea payment_label" prop="alipayLabel" contenteditable="true">Pay with Alipay</div>
  	    	<div class="gr g4"><img src="/static/img/alipay_logo.png" class="img" style="max-height:50px" /></div>
      	</div>
        <div class="gr show_ach_credit_transfer_1" style="display:none">
  	    	<div class="gr g1" style="margin-top:17px">
  				<label class="option">
  					<i class="fake-radio"></i>
  				</label>
  			</div>
  	    	<div style="margin: 8px 1px;" class="gr g7 div-textarea payment_label" prop="ach_credit_transferLabel" contenteditable="true">Pay with ACH Credit Transfer</div>
  	    	<div class="gr g4"><img src="/static/img/ach_credit_transfer_logo.png" class="img" style="max-height:50px" /></div>
      	</div>
        <div class="gr show_bancontact_1" style="display:none">
  	    	<div class="gr g1" style="margin-top:17px">
  				<label class="option">
  					<i class="fake-radio"></i>
  				</label>
  			</div>
  	    	<div style="margin: 8px 1px;" class="gr g7 div-textarea payment_label" prop="bancontactLabel" contenteditable="true">Pay with Bancontact</div>
  	    	<div class="gr g4"><img src="/static/img/bancontact_logo.png" class="img" style="max-height:50px" /></div>
      	</div>
        <div class="gr show_eps_1" style="display:none">
  	    	<div class="gr g1" style="margin-top:17px">
  				<label class="option">
  					<i class="fake-radio"></i>
  				</label>
  			</div>
  	    	<div style="margin: 8px 1px;" class="gr g7 div-textarea payment_label" prop="epsLabel" contenteditable="true">Pay with EPS</div>
  	    	<div class="gr g4"><img src="/static/img/eps_logo.png" class="img" style="max-height:50px" /></div>
      	</div>
        <div class="gr show_giropay_1" style="display:none">
  	    	<div class="gr g1" style="margin-top:17px">
  				<label class="option">
  					<i class="fake-radio"></i>
  				</label>
  			</div>
  	    	<div style="margin: 8px 1px;" class="gr g7 div-textarea payment_label" prop="giropayLabel" contenteditable="true">Pay with Giropay</div>
  	    	<div class="gr g4"><img src="/static/img/giropay_logo.png" class="img" style="max-height:50px" /></div>
      	</div>
        <div class="gr show_multibanco_1" style="display:none">
  	    	<div class="gr g1" style="margin-top:17px">
  				<label class="option">
  					<i class="fake-radio"></i>
  				</label>
  			</div>
  	    	<div style="margin: 8px 1px;" class="gr g7 div-textarea payment_label" prop="multibancoLabel" contenteditable="true">Pay with Multibanco</div>
  	    	<div class="gr g4"><img src="/static/img/multibanco_logo.png" class="img" style="max-height:50px" /></div>
      	</div>
        <div class="gr show_p24_1" style="display:none">
  	    	<div class="gr g1" style="margin-top:17px">
  				<label class="option">
  					<i class="fake-radio"></i>
  				</label>
  			</div>
  	    	<div style="margin: 8px 1px;" class="gr g7 div-textarea payment_label" prop="p24Label" contenteditable="true">Pay with P24</div>
  	    	<div class="gr g4"><img src="/static/img/p24_logo.png" class="img" style="max-height:50px" /></div>
      	</div>
        <div class="gr show_sepa_debit_1" style="display:none">
  	    	<div class="gr g1" style="margin-top:17px">
  				<label class="option">
  					<i class="fake-radio"></i>
  				</label>
  			</div>
  	    	<div style="margin: 8px 1px;" class="gr g7 div-textarea payment_label" prop="sepa_debitLabel" contenteditable="true">Pay with SEPA Direct Debit</div>
  	    	<div class="gr g4"><img src="/static/img/sepa_debit_logo.png" class="img" style="max-height:50px" /></div>
      	</div>
        <div class="gr show_sofort_1" style="display:none">
  	    	<div class="gr g1" style="margin-top:17px">
  				<label class="option">
  					<i class="fake-radio"></i>
  				</label>
  			</div>
  	    	<div style="margin: 8px 1px;" class="gr g7 div-textarea payment_label" prop="sofortLabel" contenteditable="true">Pay with SOFORT</div>
  	    	<div class="gr g4"><img src="/static/img/sofort_logo.png" class="img" style="max-height:50px" /></div>
      	</div>
        <div class="gr show_card_1">
  	    	<div class="gr g1" style="margin-top:17px">
  				<label class="option">
  					<i class="fake-radio"></i>
  				</label>
  			</div>
  	    	<div style="margin: 8px 1px;" class="gr g7 div-textarea payment_label" prop="labelStripe" contenteditable="true">Pay with credit / debit card</div>
  	    	<div class="gr g4"><img src="/static/img/credit-cards.png" class="img" style="max-height:50px" /></div>
      	</div>
      	<div class="gr">
  	    	<div class="gr g1" style="margin-top:17px">
  	    		<label class="option">
  					<i class="fake-radio"></i>
  				</label>
  	    	</div>
  	    	<div style="margin: 8px 1px;" class="gr g7 div-textarea payment_label ed" prop="labelPaypal" contenteditable="true">We accept paypal payments</div>
  	    	<div class="gr g4"><img src="/static/img/paypal.png" class="img" style="max-height:50px" /></div>
      	</div>
      </div>
    </fieldset>
  ';

  $form_name='
    <div class="gc" et="name" type="NAME">
    '.$ff_required.$ff_inputlabel.$ff_trash.$ff_duplicate.'
    </div>
            <div class="gr pad-half titlename g2" style="display:none">
                <fieldset class="select" style="padding:8px;box-shadow: 0 0 0 1px #d9d9d9;">
                    <input type="text" data-default-value="Title" class="text static" prop="placeholderTitleText">
                </fieldset>
            </div>
            <div class="gr pad-half firstname g6">
                    <fieldset><input type="text" data-default-value="Firstname" prop="placeholderFirstText" placeholder="" class="text"></fieldset>
            </div>
            <div class="gr pad-half middlename g2" style="display:none">
                    <fieldset><input type="text" data-default-value="Middlename" prop="placeholderMiddleText" class="text"></fieldset>
            </div>
            <div class="gr pad-half lastname g6">
                    <fieldset><input type="text" data-default-value="Lastname" prop="placeholderLastText" placeholder="" class="text"></fieldset>
            </div>
    <div class="gc">
    '.$ff_instruct.'
    </div>
  ';


  // textarea
  $form_textarea='<div class="gc" et="textarea" type="TEXTAREA">
    '.$ff_required.$ff_inputlabel.$ff_trash.$ff_duplicate.'
  <textarea prop="placeholderText" class="text" style="height: 96px;"></textarea>
    '.$ff_instruct.'
    <div class="rcContainer"><div class="rc"><span class="remainingChar">0</span> / <span class="maxChar">100</span></div></div>
  </div>';

  // checkbox
  $form_checkbox= '<div class="gc" et="checkbox" type="CHECKBOX">'.$ff_required.$ff_inputlabel.$ff_trash.$ff_duplicate.'
  <div class="option_container">
    <div class="foption">
      <fieldset class="option-container">
        <label class="option">
          <input type="checkbox">
          <i></i>
          <input type="text" class="inline-edit input-text" value="option 1">
        </label>
        <button class="inline-edit red"><i class="fm-icon-close-thick"></i></button>
      </fieldset>
    </div>
    <div class="foption">
      <fieldset class="option-container">
        <label class="option">
          <input type="checkbox">
          <i></i>
          <input type="text" class="inline-edit input-text" value="option 2">
        </label>
        <button class="inline-edit red"><i class="fm-icon-close-thick"></i></button>
      </fieldset>
    </div>
  	<div class="new">
  		<fieldset class="option-container">
  			<label class="option">
  				<input type="checkbox" value="" disabled>
  				<i style="visibility:hidden"></i>
  				<input type="text" class="inline-edit input-text" placeholder="+ Add new">
  			</label>
  		</fieldset>
  	</div>
  	<div class="other" style="display:none">
  		<fieldset class="option-container">
  			<label class="option">
  				<input type="checkbox">
  				<i></i>
  				<span class="otherText">Other</span>
  				<span class="otherColon">:</span>
  				<input type="text" class="other_text">
  			</label>
  		</fieldset>
  	</div>
  </div>
    '.$ff_instruct;

  // checkbox
  $form_products= '<div class="gc" et="products" data-unit="currency" type="PRODUCTS">'.$ff_required.$ff_inputlabel.$ff_trash.$ff_duplicate.'
  <div class="selectContainer" style="display:none">
    <fieldset class="select"><select class="text"><option></option><option>Product 1 (<span class="symbol">$</span>100 <span class="currency">USD</span>)</option><option>Product 2 (<span class="symbol">$</span>100 <span class="currency">USD</span>)</option></select></fieldset>
    <fieldset class="select qty"><select><option class="default_value_amt">Quantity</option></select></fieldset>
  </div>
  <div class="option_container">
    <table>
    <tr class="foption option-container trow">
      <td>
        <label class="option">
          <input type="checkbox">
          <i></i>
          <input type="text" class="inline-edit input-text product-input" width="auto" size="9" value="Product 1">
        </label>
        <span class="price">(<span class="symbol">$</span><span class="amount">100</span> <span class="currency">USD</span>)</span>
      </td>
      <td class="tamt" style="display: none;">
        <span class="tcell"><select><option class="default_value_amt">Quantity</option></select></span>
      </td>
      <td class="tbtn">
        <button class="inline-edit red"><i class="fm-icon-close-thick"></i></button>
      </td>
    </tr>
    <tr class="foption option-container trow">
      <td>
        <label class="option">
          <input type="checkbox">
          <i></i>
          <input type="text" class="inline-edit input-text product-input" width="auto" size="9" value="Product 2">
        </label>
        <span class="price">(<span class="symbol">$</span><span class="amount">100</span> <span class="currency">USD</span>)</span>
      </td>
      <td class="tamt" style="display: none;">
        <span class="tcell"><select><option class="default_value_amt">Quantity</option></select></span>
      </td>
      <td class="tbtn">
        <button class="inline-edit red"><i class="fm-icon-close-thick"></i></button>
      </td>
    </tr>
    </table>
  	<div class="new">
  		<fieldset class="option-container">
  			<label class="option">
  				<input type="checkbox" value="" disabled>
  				<i style="visibility:hidden"></i>
  				<input type="text" class="inline-edit input-text product-input" placeholder="+ Add new">
  			</label>
  		</fieldset>
  	</div>
  </div>
    '.$ff_instruct;

  $form_inputtable= '<div class="gc" et="inputtable" type="INPUTTABLE">'.$ff_required.$ff_inputlabel.$ff_trash.$ff_duplicate.'
  <table class="inputTable" style="width:auto;">
    <tbody class="option_container">
        <tr class="ans">
            <td></td>
            <td class="gray"><label class="option"><input type="text" class="inline-edit input-text table-input" value="Bad"></label><div class="del"><i class="icon-trash"></i></div></td>
            <td class="gray"><label class="option"><input type="text" class="inline-edit input-text table-input" value="Good"></label><div class="del"><i class="icon-trash"></i></div></td>
            <td class="gray"><label class="option"><input type="text" class="inline-edit input-text table-input" value="Excellent"></label><div class="del"><i class="icon-trash"></i></div></td>
        </tr>
        <tr class="foption option-container trow">
            <td class="gray">
                <label class="option">
                    <input type="text" class="inline-edit input-text table-input" value="Question 1">
                </label>
                <div class="del"><i class="icon-trash"></i></div>
            </td>
            <td class="ans">
                <input type="radio" disabled class="radio_ans">
            </td>
            <td class="ans">
                <input type="radio" disabled class="radio_ans">
            </td>
            <td class="ans">
                <input type="radio" disabled class="radio_ans">
            </td>
        </tr>
        <tr class="foption option-container trow">
            <td class="gray">
                <label class="option">
                    <input type="text" class="inline-edit input-text table-input" value="Question 2">
                </label>
                <div class="del"><i class="icon-trash"></i></div>
            </td>
            <td class="ans">
                <input type="radio" disabled class="radio_ans">
            </td>
            <td class="ans">
                <input type="radio" disabled class="radio_ans">
            </td>
            <td class="ans">
                <input type="radio" disabled class="radio_ans">
            </td>
        </tr>
        <tr class="new"><td><a href="javascript:;"><i class="fa fa-plus-square"></i></a></td></tr>
    </tbody>
  </table>
  <div class="newColumn"><a href="javascript:;"><i class="fa fa-plus-square"></i></a></div>
    '.$ff_instruct;

  // us address
  $form_usaddress='<div class="flush">
  <div class="gr" et="usaddress" type="US_ADDRESS">
    '.$ff_required.$ff_inputlabel.$ff_trash.$ff_duplicate.'
  </div>
    <div class="gr pad-half-all" style="padding:0">
      <div class="gr pad-half-compact g12">
        <fieldset><input type="text" data-default-value="Address 1" class="text static" prop="placeholderAddress1Text"></fieldset>
      </div>
      <div class="gr pad-half-compact g12">
        <fieldset><input type="text" data-default-value="Address 2" class="text static" prop="placeholderAddress2Text"></fieldset>
      </div>
      <div class="gr pad-half-compact g5">
        <fieldset><input type="text" data-default-value="City" class="text static" prop="placeholderCityText"></fieldset>
      </div>
      <div class="gr pad-half-compact g1"></div>
      <div class="gr pad-half-compact g3 state_text">
        <fieldset style="margin-right:15px"><input type="text" data-default-value="State" class="text static" prop="placeholderStateText"></fieldset>
      </div>
      <div class="gr pad-half-compact g3 state_select" style="display:none;">
        <fieldset class="select" style="padding:8px;box-shadow: 0 0 0 1px #d9d9d9;">
        	<input type="text" data-default-value="State" class="text static" prop="placeholderStateText">
        </fieldset>
      </div>
      <div class="gr pad-half-compact g3">
        <fieldset><input type="text" data-default-value="Zip Code" class="text static" prop="placeholderZipText"></fieldset>
      </div>
      <div class="gr pad-half-compact g12 countrySelect" id="countrySelect">
      	<fieldset><input type="text" data-default-value="Country" class="text static" prop="placeholderCountryText"></fieldset>
      </div>
    </div>
    <div class="gr">
    '.$ff_instruct.' </div>
  </div></div>';
  // section
  $form_section='<div class="gc" et="section" type="SECTION">'.$ff_trash.$ff_duplicate.'<textarea class="ed h2 inline-edit" prop="labelText" placeholder="'.$this->pl->trans($m,'Section Title').'"></textarea></div><hr>';

  // range
  $form_range='<div class="gc" et="range" type="RANGE">'.$ff_required.$ff_inputlabel.$ff_trash.$ff_duplicate.'</div>
  <fieldset class="range">
  	<div class="table">
  		<div style="height: 21px;" class="range">
  			<input type="range" value="0" max="100" min="0">
  		</div>
  		<div class="output-container">
  			<span>100</span>
  		</div>
  	</div>
  </fieldset>
    '.$ff_instruct;
  // switch
  $form_switch= '<div class="gc" et="switch" type="SWITCH">'.$ff_required.$ff_inputlabel.$ff_trash.$ff_duplicate.'

  <div class="option_container">
    <div class="foption">
      <fieldset class="option-container">
        <label class="option switch">
          <input type="checkbox">
          <span class="switch-container">
            <span class="switch-status on">'.$this->pl->trans($m,'ON').'</span>
            <span class="switch-status off">'.$this->pl->trans($m,'OFF').'</span><i></i>
          </span>
          <input type="text" class="inline-edit input-text" value="option 1" style="margin-left:30px;width: 94%;">
        </label>
        <button class="inline-edit red"><i class="fm-icon-close-thick"></i></button>
      </fieldset>
    </div>
    <div class="foption">
      <fieldset class="option-container">
        <label class="option switch">
          <input type="checkbox">
          <span class="switch-container">
            <span class="switch-status on">'.$this->pl->trans($m,'ON').'</span>
            <span class="switch-status off">'.$this->pl->trans($m,'OFF').'</span><i></i>
          </span>
          <input type="text" class="inline-edit input-text" value="option 2" style="margin-left:30px;width: 94%;">
        </label>
        <button class="inline-edit red"><i class="fm-icon-close-thick"></i></button>
      </fieldset>
    </div>
  	<div class="new">
  		<fieldset class="option-container">
  			<label class="option switch add-option">
  				<input type="checkbox" value="" disabled>
  				<span class="switch-container"><span class="switch-status on">'.$this->pl->trans($m,'ON').'</span><span class="switch-status off">'.$this->pl->trans($m,'OFF').'</span><i></i></span>
  				<input type="text" class="inline-edit input-text" placeholder="'.$this->pl->trans($m,'+ Add new').'" style="margin-left:30px;width: 94%;">
  			</label>
  		</fieldset>
  	</div>
  	<div class="other" style="display:none">
  		<fieldset class="option-container">
  			<label class="option switch">
  				<input type="checkbox">
  				<span class="switch-container">
  					<span class="switch-status on">'.$this->pl->trans($m,'ON').'</span>
  					<span class="switch-status off">'.$this->pl->trans($m,'OFF').'</span><i></i>
  				</span>
  				<span class="otherText">Other</span>
  				<span class="otherColon">:</span>
  				<input type="text" class="other_text">
  			</label>
  		</fieldset>
  	</div>
  </div>
    '.$ff_instruct.'
  </div>';
  // select
  $form_select='<div class="gc" et="select" type="SELECT">'.$ff_required.$ff_inputlabel.$ff_trash.$ff_duplicate.'
  <fieldset class="select">
  	<select class="text" prop="optionsList">
    <option selected disabled prop="placeholderText"></option></select>
  </fieldset>
    '.$ff_instruct.'
  </div>';
  // radio
  $form_radio= '<div class="gc" et="radio" type="RADIO">'.$ff_required.$ff_inputlabel.$ff_trash.$ff_duplicate.'
  	<div class="option_container">
      <div class="foption">
        <fieldset class="option-container">
          <label class="option">
            <input type="radio">
            <i></i>
            <input type="text" class="inline-edit input-text" value="option 1">
          </label>
          <button class="inline-edit red"><i class="fm-icon-close-thick"></i></button>
        </fieldset>
      </div>
      <div class="foption">
        <fieldset class="option-container">
          <label class="option">
            <input type="radio">
            <i></i>
            <input type="text" class="inline-edit input-text" value="option 2">
          </label>
          <button class="inline-edit red"><i class="fm-icon-close-thick"></i></button>
        </fieldset>
      </div>
  		<div class="new">
  			<fieldset class="option-container">
  				<label class="option">
  					<input type="checkbox" value="" disabled>
  					<i style="visibility:hidden"></i>
  					<input type="text" class="inline-edit input-text" placeholder="'.$this->pl->trans($m,'+ Add new').'">
  				</label>
  			</fieldset>
  		</div>
  		<div class="other" style="display:none">
  			<fieldset class="option-container">
  				<label class="option">
                    <input type="radio">
  					<i></i>
  					<span class="otherText">Other</span>
  					<span class="otherColon">:</span>
  					<input type="text" class="other_text">
  				</label>
  			</fieldset>
  		</div>
  	</div>
    '.$ff_instruct.'
  </div>';
  // label
  $form_label='<div class="gc" et="label" type="LABEL">'.$ff_trash.$ff_duplicate.'
  <div prop="labelText" class="div-textarea" placeholder="'.$this->pl->trans($m,'Enter your content here').'..." contenteditable></div>';
  //pagebreak
  $form_pagebreak='<div class="gc" et="pagebreak" type="PAGEBREAK">'.$ff_trash.$ff_duplicate.'</div>
  <p class="help" align="center">'.$this->pl->trans($m,'PDF Page Break').'</p>';

  // end of editor form components

  $field_inputLabel='
          <label>'.$this->pl->trans($m,'Field Label').'
  		<i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
  			<div class="tooltip-wrapper">
  				<div class="tooltip">
  					<p>'.$this->pl->trans($m,'This is the title that sits above the input').'.</p>
  				</div>
  			</div>
  		</i>
  	</label>
  	<input type="text" placeholder="'.$this->pl->trans($m,'Field Label').'" prop="inputLabel" class="text small dark">
  ';

  $field_validationMessage='
          <label>'.$this->pl->trans($m,'Validation Message').'
  		<i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
  			<div class="tooltip-wrapper">
  				<div class="tooltip">
  					<p>'.$this->pl->trans($m,'This is the message when the value of the field did not meet the validation').'</p>
  				</div>
  			</div>
  		</i>
  	</label>
  	<input type="text" placeholder="'.$this->pl->trans($m,'Validation Message').'" prop="validationMessage" class="text small dark">
  ';

  $field_instructionText='
  	<label>'.$this->pl->trans($m,'Inline Instruction Text').'
  		<i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
  			<div class="tooltip-wrapper">
  				<div class="tooltip">
  					<p>'.$this->pl->trans($m,'These are the instructions that sit below an input. They are used if the input needs further explaination or a hint').'.</p>
  				</div>
  			</div>
  		</i>
  	</label>
  	<input type="text" placeholder="'.$this->pl->trans($m,'Inline Instructions').'" prop="instructionText" class="text small dark">
  ';
  $field_helpText='';
  /*
  $field_helpText='
  <label>Tooltip
  	<i class="icon-info">
  		<div class="tooltip-wrapper">
  			<div class="tooltip">
  				<p>This is text that appears when a user hovers over the "?" above an input. You\'re hovering over a tooltip right now.</p>
  			</div>
  		</div>
  	</i>
  </label>
  <input type="text" placeholder="Tooltip Instructions" prop="helpText" class="text small dark">';
  */
  $field_reqdis='
  	<label prop="required" class="option">
  		<input type="checkbox" value=true prop="required"> '.$this->pl->trans($m,'Required').' <i></i>
  		<i class="icon-info tooltip-container tooltip-position-bottom tooltip-position-left">
  			<div class="tooltip-wrapper">
  				<div class="tooltip">
  					<p>'.$this->pl->trans($m,'Require a user to fill out this field').'.</p>
  				</div>
  			</div>
  		</i>
  	</label>
          <label class="option" prop="disabled">
  	<input type="checkbox" value=true prop="disabled"> '.$this->pl->trans($m,'Read only').' <i></i>
  		<i class="icon-info tooltip-container tooltip-position-bottom tooltip-position-left">
  			<div class="tooltip-wrapper">
  				<div class="tooltip" style="margin-left:-50px;">
  					<p>'.$this->pl->trans($m,'Don\'t allow the user to enter anything in this field').'.</p>
  				</div>
  			</div>
  		</i>
  	</label>
  ';

  $field_otherOption = '
  	<label class="option switch">
  		'.$this->pl->trans($m,'Other option').'
  		<input prop="otherOption" type="checkbox">
  		<span class="switch-container">
  			<span class="switch-status on">'.$this->pl->trans($m,'ON').'</span>
  			<span class="switch-status off">'.$this->pl->trans($m,'OFF').'</span>
  			<i></i>
  		</span>
  	</label>
  ';

  $field_enableAmount = '
  	<label class="option switch">
  		'.$this->pl->trans($m,'Enable Amount').'
  		<input prop="enableAmount" type="checkbox">
  		<span class="switch-container">
  			<span class="switch-status on">'.$this->pl->trans($m,'ON').'</span>
  			<span class="switch-status off">'.$this->pl->trans($m,'OFF').'</span>
  			<i></i>
  		</span>
  	</label>
  ';

  $field_otherOptionLabel = '
  <label>'.$this->pl->trans($m,'Other option Label').'
  	<i class="icon-info">
  		<div class="tooltip-wrapper">
  			<div class="tooltip">
  				<p>'.$this->pl->trans($m,'this is the label of the other option').'</p>
  			</div>
  		</div>
  	</i>
  </label>
  <input type="text" value="Other" placeholder="'.$this->pl->trans($m,'Placeholder Text').'" prop="otherOptionLabel" class=" text small dark">
  ';

  $field_amountOptionsList = '
  <label>'.$this->pl->trans($m,'Select Box Label').'
  	<i class="icon-info">
  		<div class="tooltip-wrapper">
  			<div class="tooltip">
  				<p>'.$this->pl->trans($m,'this is the label of the select box').'</p>
  			</div>
  		</div>
  	</i>
  </label>
  <input type="text" value="Quantity" placeholder="'.$this->pl->trans($m,'Quantity').'" prop="enableAmountLabel" class=" text small dark">
  <br><br>
  <label>'.$this->pl->trans($m,'Option List').'
  	</label>
    <fieldset>
  <div class="gr g6"><label>'.$this->pl->trans($m,'Label').'</label></div>
  <div class="gr g5" style="padding-left:20px"><label><span class="product_unit">'.$this->pl->trans($m,'Price').'</span> '.$this->pl->trans($m,'Multiplier').'</label></div>
    </fieldset>
        <div prop="optionsList" class="optionsList"></div>
      <fieldset class="addoption" style="opacity:0.5">
  			<input prop="option_label" type="text" class="g5 addoption text small dark">
        <input prop="option_value" type="number" class="g5 addoption text small dark marleft">
  		</fieldset>
  ';




  $field_placeholderText='
  <label>'.$this->pl->trans($m,'Placeholder Text').$this->tt($m,'This is the text that appears in an input before someone has entered anything. It is usually an example of what goes inside the input','email@example.com').'</label>
  <input type="text" placeholder="'.$this->pl->trans($m,'Placeholder Text').'" prop="placeholderText" class=" text small dark">';

  $field_placeholderFirstText='
  <label>'.$this->pl->trans($m,'Placeholder Firstname Text').'
  	<i class="icon-info">
  		<div class="tooltip-wrapper">
  			<div class="tooltip" style="margin-left:-50px">
  				<p>'.$this->pl->trans($m,'This is the text that appears in an input before someone has entered anything. It is usually an example of what goes inside the input').' (e.g. "John").</p>
  			</div>
  		</div>
  	</i>
  </label>
  <input type="text" value="Firstname" prop="placeholderFirstText" class=" text small dark">';

  $field_placeholderMiddleText='
  <label>'.$this->pl->trans($m,'Placeholder Middlename Text').'
  	<i class="icon-info">
  		<div class="tooltip-wrapper">
  			<div class="tooltip" style="margin-left:-50px">
  				<p>'.$this->pl->trans($m,'This is the text that appears in an input before someone has entered anything. It is usually an example of what goes inside the input').' (e.g. "John").</p>
  			</div>
  		</div>
  	</i>
  </label>
  <input type="text" value="Middlename" prop="placeholderMiddleText" class=" text small dark">';

  $field_placeholderLastText='
  <label>'.$this->pl->trans($m,'Placeholder Lastname Text').'
  	<i class="icon-info">
  		<div class="tooltip-wrapper">
  			<div class="tooltip" style="margin-left:-50px">
  				<p>'.$this->pl->trans($m,'This is the text that appears in an input before someone has entered anything. It is usually an example of what goes inside the input').' (e.g. "Doe").</p>
  			</div>
  		</div>
  	</i>
  </label>
  <input type="text" value="Lastname" prop="placeholderLastText" class=" text small dark">';

  $field_placeholderAddress1Text='
  <label>'.$this->pl->trans($m,'Placeholder Address1 Text').'
  	<i class="icon-info">
  		<div class="tooltip-wrapper">
  			<div class="tooltip" style="margin-left:-50px">
  				<p>'.$this->pl->trans($m,'This is the text that appears in an input before someone has entered anything. It is usually an example of what goes inside the input').' (e.g. "Doe").</p>
  			</div>
  		</div>
  	</i>
  </label>
  <input type="text" value="Address 1" prop="placeholderAddress1Text" class=" text small dark">';

  $field_placeholderAddress2Text='
  <label>'.$this->pl->trans($m,'Placeholder Address2 Text').'
  	<i class="icon-info">
  		<div class="tooltip-wrapper">
  			<div class="tooltip" style="margin-left:-50px">
  				<p>'.$this->pl->trans($m,'This is the text that appears in an input before someone has entered anything. It is usually an example of what goes inside the input').' (e.g. "Doe").</p>
  			</div>
  		</div>
  	</i>
  </label>
  <input type="text" value="Address 2" prop="placeholderAddress2Text" class=" text small dark">';

  $field_placeholderCityText='
  <label>'.$this->pl->trans($m,'Placeholder City Text').'
  	<i class="icon-info">
  		<div class="tooltip-wrapper">
  			<div class="tooltip" style="margin-left:-50px">
  				<p>'.$this->pl->trans($m,'This is the text that appears in an input before someone has entered anything. It is usually an example of what goes inside the input').' (e.g. "Doe").</p>
  			</div>
  		</div>
  	</i>
  </label>
  <input type="text" value="City" prop="placeholderCityText" class=" text small dark">';

  $field_placeholderStateText='
  <label>'.$this->pl->trans($m,'Placeholder State Text').'
  	<i class="icon-info">
  		<div class="tooltip-wrapper">
  			<div class="tooltip" style="margin-left:-50px">
  				<p>'.$this->pl->trans($m,'This is the text that appears in an input before someone has entered anything. It is usually an example of what goes inside the inpu').' (e.g. "Doe").</p>
  			</div>
  		</div>
  	</i>
  </label>
  <input type="text" value="State" prop="placeholderStateText" class=" text small dark">';

  $field_placeholderZipText='
  <label>'.$this->pl->trans($m,'Placeholder Zip Code Text').'
  	<i class="icon-info">
  		<div class="tooltip-wrapper">
  			<div class="tooltip" style="margin-left:-50px">
  				<p>'.$this->pl->trans($m,'This is the text that appears in an input before someone has entered anything. It is usually an example of what goes inside the input').' (e.g. "Doe").</p>
  			</div>
  		</div>
  	</i>
  </label>
  <input type="text" value="Zip Code" prop="placeholderZipText" class=" text small dark">';

  $field_placeholderCountryText='
  <label>'.$this->pl->trans($m,'Placeholder Country Text').'
  	<i class="icon-info">
  		<div class="tooltip-wrapper">
  			<div class="tooltip" style="margin-left:-50px">
  				<p>'.$this->pl->trans($m,'This is the text that appears in an input before someone has entered anything. It is usually an example of what goes inside the input').' (e.g. "Doe").</p>
  			</div>
  		</div>
  	</i>
  </label>
  <input type="text" value="Country" prop="placeholderCountryText" class=" text small dark">';

  $field_defaultValue='
  	<label>'.$this->pl->trans($m,'Default Value').'
  		<i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
  			<div class="tooltip-wrapper">
  				<div class="tooltip">
  					<p>'.$this->pl->trans($m,'If you want an input to already have text in it, before a user enters any, use Default Value').'.</p>
  				</div>
  			</div>
  		</i>
  	</label>
  	<input type="text" placeholder="Default Value" prop="defaultValue" class="text small dark">
  ';

$datasources = $this->lo->_listDatasources(array('uid'=>$this->lAccountOwner['_id'], 'accountId'=>$this->lAccount['_id']));
$options_ds = '';
foreach($datasources as $datasource) {
    $options_ds.='<option value="'.$datasource['_id'].'">'.$datasource['title'].'</option>';
}

  $field_optionsList='<label>'.$this->pl->trans($m,'Option List').'
  		<i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
  			<div class="tooltip-wrapper">
  				<div class="tooltip">
  					<p>'.$this->pl->trans($m,'These are options a user can select on a select, multiple choice, or checkbox input').'.</p>
  				</div>
  			</div>
  		</i>
  	</label>
    <fieldset class="datasource_link">
        <label style="float:left;line-height:30px;margin-right:5px">Linked:</label>
        <fieldset class="select" style="float:left;">
            <select class="text" prop="datasource_id" style="float:left;width:150px;">
                <option></option>
                '.$options_ds.'
            </select>
        </fieldset>
        <label style="float:left;line-height:30px;margin-left:5px"><a class="datasourceLink" href="javascript:;">Manage</a></label>
    </fieldset>
    <fieldset>
  <div class="gr g6"><label>'.$this->pl->trans($m,'Label').'</label></div>
  <div class="gr g5" style="padding-left:20px"><label>'.$this->pl->trans($m,'Value').'</label></div>
    </fieldset>
        <div prop="optionsList" class="optionsList"></div>
      <fieldset class="addoption" style="opacity:0.5">
  			<input prop="option_label" type="text" class="g5 addoption text small dark">
        <input prop="option_value" type="text" class="g5 addoption text small dark marleft">
  		</fieldset>';

  $field_productsList='<label>'.$this->pl->trans($m,'Products List').'
  		<i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
  			<div class="tooltip-wrapper">
  				<div class="tooltip">
  					<p>'.$this->pl->trans($m,'These are the products that will be listed').'.</p>
  				</div>
  			</div>
  		</i>
  	</label>
    <fieldset>
  <div class="gr g6"><label>'.$this->pl->trans($m,'Product').'</label></div>
  <div class="gr g5" style="padding-left:20px"><label class="product_unit">'.$this->pl->trans($m,'Price').'</label></div>
    </fieldset>
        <div prop="productsList" class="optionsList"></div>
      <fieldset class="addoption" style="opacity:0.5">
  			<input prop="option_label" type="text" class="g5 addoption text small dark">
        <input prop="option_value" type="number" class="g5 addoption text small dark marleft">
  		</fieldset>';

  $field_questionList='<label>'.$this->pl->trans($m,'Questions').'
        <i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
            <div class="tooltip-wrapper">
                <div class="tooltip">
                    <p>'.$this->pl->trans($m,'These are the questions that will be listed').'.</p>
                </div>
            </div>
        </i>
    </label>
    <fieldset>
    </fieldset>
        <div prop="questionList" class="optionsList"></div>
      <fieldset class="addoption" style="opacity:0.5">
            <input prop="option_label" type="text" class="g10 addoption text small dark">
        </fieldset>';

  $field_answerList='<label>'.$this->pl->trans($m,'Answers').'
        <i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
            <div class="tooltip-wrapper">
                <div class="tooltip">
                    <p>'.$this->pl->trans($m,'These are the questions that will be listed').'.</p>
                </div>
            </div>
        </i>
    </label>
    <fieldset>
    </fieldset>
        <div prop="answerList" class="optionsList"></div>
      <fieldset class="addoption" style="opacity:0.5">
            <input prop="option_label" type="text" class="g5 addoption text small dark">
            <input prop="option_value" type="text" class="g5 addoption text small dark marleft">
        </fieldset>';

  $field_queryName='
  <label>'.$this->pl->trans($m,'Short data field name').'
  	<i class="icon-info">
  		<div class="tooltip-wrapper">
  			<div class="tooltip">
  				<p>'.$this->pl->trans($m,'This is the field name that will be used in data exports').'</p>
  			</div>
  		</div>
  	</i>
  </label>
  	<input type="text" placeholder="'.$this->pl->trans($m,'Short fieldlabel to use in exports').'" prop="queryName" class="text small dark">
  ';

  $field_disabledDays='
  <label>'.$this->pl->trans($m,'block days in the week').'
  	<i class="icon-info">
  		<div class="tooltip-wrapper">
  			<div class="tooltip">
  				<p>'.$this->pl->trans($m,'block days in the week of calendar').'</p>
  			</div>
  		</div>
  	</i>
  </label>

  <label class="option days">
      <input type="checkbox" prop="dM">'.$this->pl->trans($m,'Mo').'&nbsp;&nbsp;&nbsp;
      <i></i>
  </label>
  <label class="option days">
      <input type="checkbox" prop="dT">'.$this->pl->trans($m,'Tu').'
      <i></i>
  </label>
  <label class="option days">
      <input type="checkbox" prop="dW">'.$this->pl->trans($m,'We').'
      <i></i>
  </label>
  <label class="option days">
      <input type="checkbox" prop="dTH">'.$this->pl->trans($m,'Th').'
      <i></i>
  </label>
  <label class="option days">
      <input type="checkbox" prop="dF">'.$this->pl->trans($m,'Fr').'
      <i></i>
  </label>
  <label class="option days">
      <input type="checkbox" prop="dSat">'.$this->pl->trans($m,'Sa').'
      <i></i>
  </label>
  <label class="option days">
      <input type="checkbox" prop="dSun">'.$this->pl->trans($m,'Su').'
      <i></i>
  </label>
  ';

  $field_logic='
  <fieldset class="enableLogic">
    <label>
    '.$this->pl->trans($m,'Conditional display').'
    <i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
        <div class="tooltip-wrapper">
            <div class="tooltip" style="margin-left:-70px">
                <p>'.$this->pl->trans($m,'Allows you to show or hide this field only on specific conditions').'.</p>
            </div>
        </div>
    </i>
    <span><a href="/settings/subscription/" fm-button="blue small" class="gopro">Try before upgrade</a></span>
    </label>
    <label class="option switch">
        <input id="s_enableLogic" prop="enableLogic" type="checkbox">
        <span class="switch-container">
            <span class="switch-status on">'.$this->pl->trans($m,'ON').'</span>
            <span class="switch-status off">'.$this->pl->trans($m,'OFF').'</span>
            <i></i>
        </span>
    </label>
  </fieldset>
  <div class="show_enableLogic_1" style="display:none">
        <br>
        <label>
        '.$this->pl->trans($m,' Action').'
        </label>
        <fieldset class="select">
            <select class="text" prop="logicAction">
                <option value="show">Only show</option>
                <option value="hide">Only hide</option>
            </select>
        </fieldset>
        <br>
        <div style="line-height:35px;">
            If <select class="conditionAndOr" prop="conditionAndOr"><option>ANY</option><option>ALL</option></select> of the "IF" rules below are matched.
        </div>
        <hr>
        <div class="conditions">
            <div class="clist">
                <div class="gr g6">
                    <label>
                    '.$this->pl->trans($m,'If').'
                    </label>
                    <fieldset class="select">
                        <select class="text" prop="logicField">
                            <option></option>
                        </select>
                    </fieldset>
                </div>
                <div class="gr g6" style="margin-left:15px;">
                    <label>
                    '.$this->pl->trans($m,'State').'
                    </label>
                    <fieldset class="select">
                        <select class="text" prop="logicCondition">
                            <option></option>
                            <option value="=">Equals</option>
                            <option value=">">Greater than</option>
                            <option value="<">Lesser than</option>
                            <option value="!=">Not equal</option>
                            <option value=">=">Greater than or equal</option>
                            <option value="<=">Lesser than or equal</option>
                        </select>
                    </fieldset>
                </div>
                <br><br><br><br>
                <label>
                '.$this->pl->trans($m,'Value').'
                </label>
                <input type="text" placeholder="" prop="logicValue" class="text small dark">
                <div class="deleteCondition"><a href="javascript:;"><i class="fa fa-trash"></i></a></div>
                <hr>
            </div>
        </div>
        <div class="newCondition">
            <a href="javascript:;"><i class="fa fa-plus"></i> New Condition</a>
        </div>
  </div>
  <hr>
  ';

  $field_calculation = '
  <div>
    <label>
        <span class="calc_description">
    '.$this->pl->trans($m,'Add field values to Payment amount').'
        </span>
    <i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
        <div class="tooltip-wrapper">
            <div class="tooltip" style="margin-left:-100px">
                <p>'.$this->pl->trans($m,'Make this field as result of the calculation').'.</p>
            </div>
        </div>
    </i>
        <br>
        <div prop="fieldList" class="fieldLists side_calculation">
            <div class="fieldList">
                <div class="g2 f label">A</div>
                <div class="g8 f field"><select class="text"><option></option></select></div>
                <div class="g2 f action"><span class="remove"><i class="fa fa-trash"></i></span><span class="add"><i class="fa fa-plus-square"></i></span></div>
            </div>
        </div>
        <hr>
            <label>
                <span class="calc_description2">
            '.$this->pl->trans($m,'Payment Amount').'
                </span>
            <i class="icon-info" class="tooltip-container tooltip-position-bottom tooltip-position-left">
                <div class="tooltip-wrapper">
                    <div class="tooltip" style="margin-left:-20px">
                        <p>'.$this->pl->trans($m,'ex: A + B + C').'.</p>
                    </div>
                </div>
            </i>
            </label>
            <input type="text" placeholder="" prop="calculationTotal" class="text small dark">
  </div>
  ';

  $endpoint='<div class="endpoint-canvas">
  	<p class="endpoint-step">1. '.$this->pl->trans($m,'Add').' <b>'.$this->pl->trans($m,'action').'</b> '.$this->pl->trans($m,'attribute to your form').'.</p>
  	<div class="code">
  		<code>&lt;form action="'.$GLOBALS['protocol'].'://'.$_SERVER['HTTP_HOST'].'/f/'.$this->urlpart[2].'/" method="POST"&gt;<br />
    			...<br />
  		&lt;/form&gt;</code>
  	</div>
  	<p class="endpoint-step">2. '.$this->pl->trans($m,'Add').' <b>'.$this->pl->trans($m,'name').'</b> '.$this->pl->trans($m,'attribute to each field').'.</p>
  	<div class="code">
  		<code>&lt;input name="email" placeholder="'.$this->pl->trans($m,'Email Address').'" /&gt;<br /></code>
  	</div>
  	<p class="endpoint-step">3. '.$this->pl->trans($m,'Add Submit button').'.</p>
  	<div class="code">
  		<code>&lt;button type="submit"&gt;Submit&lt;/button&gt;</code>
  	</div>
  	<p class="endpoint-step">4. '.$this->pl->trans($m,'Configure Options').'.</p>
  	<p style="margin-bottom:1.75rem;">'.$this->pl->trans($m,'Use the sidebar to configure redirects and notifications').'.</p>
  	<h1 style="margin-top:2rem;">'.$this->pl->trans($m,'Full Example').'</h1>
  	<div class="code">
  		<code>
  		&lt;form action="'.$GLOBALS['protocol'].'://'.$_SERVER['HTTP_HOST'].'/f/'.$this->urlpart[2].'/" method="POST"&gt;<br />
    			&lt;input name="email" placeholder="'.$this->pl->trans($m,'Email Address').'" /&gt;<br />
    			&lt;button type="submit"&gt;Submit&lt;/button&gt;<br />
  		&lt;/form&gt;
  		</code>
  	</div>
  </div>';


  $comp["side"]['NAME']=str_replace(array("\r", "\n", "\t", "   "), '', $side_name);
  $comp["side"]['TEXT']=str_replace(array("\r", "\n", "\t", "   "), '', $side_text);
  $comp["side"]['LOOKUP']=str_replace(array("\r", "\n", "\t", "   "), '', $side_lookup);
  $comp["side"]['CALCULATION']=str_replace(array("\r", "\n", "\t", "   "), '', $side_calculation);
  $comp["side"]['DATE']=str_replace(array("\r", "\n", "\t", "   "), '', $side_date);
  $comp["side"]['TIME']=str_replace(array("\r", "\n", "\t", "   "), '', $side_time);
  $comp["side"]['DATETIME']=str_replace(array("\r", "\n", "\t", "   "), '', $side_datetime);

  $comp["side"]['TEXT_EMAIL']=str_replace(array("\r", "\n", "\t", "   "), '', $side_email);
  $comp["side"]['TEXT_PHONE']=str_replace(array("\r", "\n", "\t", "   "), '', $side_phone);
  $comp["side"]['TEXT_DATE']=str_replace(array("\r", "\n", "\t", "   "), '', $side_date);
  $comp["side"]['TEXT_NR']=str_replace(array("\r", "\n", "\t", "   "), '', $side_number);

  $comp["side"]['FILE']=str_replace(array("\r", "\n", "\t", "   "), '', $side_file);
  $comp["side"]['PICTURE']=str_replace(array("\r", "\n", "\t", "   "), '', $side_picture);
  $comp["side"]['CAPTCHA']=str_replace(array("\r", "\n", "\t", "   "), '', $side_captcha);
  $comp["side"]['STARRATING']=str_replace(array("\r", "\n", "\t", "   "), '', $side_starrating);
  $comp["side"]['SIGNATURE']=str_replace(array("\r", "\n", "\t", "   "), '', $side_signature);
  $comp["side"]['STRIPE']=str_replace(array("\r", "\n", "\t", "   "), '', $side_stripe);
  $comp["side"]['PAYPAL']=str_replace(array("\r", "\n", "\t", "   "), '', $side_paypal);
  $comp["side"]['STRIPEPAYPAL']=str_replace(array("\r", "\n", "\t", "   "), '', $side_stripepaypal);

  $comp["side"]['US_ADDRESS']=str_replace(array("\r", "\n", "\t", "   "), '', $side_usaddress);
  $comp["side"]['SECTION']=str_replace(array("\r", "\n", "\t", "   "), '', $side_section);
  $comp["side"]['RANGE']=str_replace(array("\r", "\n", "\t", "   "), '', $side_range);
  $comp["side"]['SWITCH']=str_replace(array("\r", "\n", "\t", "   "), '', $side_switch);
  $comp["side"]['SELECT']=str_replace(array("\r", "\n", "\t", "   "), '', $side_select);
  $comp["side"]['RADIO']=str_replace(array("\r", "\n", "\t", "   "), '', $side_radio);
  $comp["side"]['CHECKBOX']=str_replace(array("\r", "\n", "\t", "   "), '', $side_checkbox);
  $comp["side"]['PRODUCTS']=str_replace(array("\r", "\n", "\t", "   "), '', $side_products);
  $comp["side"]['INPUTTABLE']=str_replace(array("\r", "\n", "\t", "   "), '', $side_inputtable);
  $comp["side"]['LABEL']=str_replace(array("\r", "\n", "\t", "   "), '', $side_label);
  $comp["side"]['TEXTAREA']=str_replace(array("\r", "\n", "\t", "   "), '', $side_textarea);
  //$comp["side"]['PAGEBREAK']=str_replace(array("\r", "\n", "\t", "   "), '', $side_pagebreak);
  $comp["side"]['settings']=str_replace(array("\r", "\n", "\t", "   "), '', $side_settings);
  $comp["side"]['confirmation']=str_replace(array("\r", "\n", "\t", "   "), '', $side_confirmation);
  $comp["side"]['theme']=str_replace(array("\r", "\n", "\t", "   "), '', $side_theme);
  $comp["side"]['elements']=str_replace(array("\r", "\n", "\t", "   "), '', $side_elements);
  $comp["side"]['elementsEdit']=str_replace(array("\r", "\n", "\t", "   "), '', $side_elementsEdit);
  $comp["side"]['endpoint']=str_replace(array("\r", "\n", "\t", "   "), '', $side_endpoint);

  $comp["frm"]['NAME']=str_replace(array("\r", "\n", "\t", "   "), '', $form_name);
  $comp["frm"]['TEXT']=str_replace(array("\r", "\n", "\t", "   "), '', $form_text);
  $comp["frm"]['LOOKUP']=str_replace(array("\r", "\n", "\t", "   "), '', $form_lookup);
  $comp["frm"]['CALCULATION']=str_replace(array("\r", "\n", "\t", "   "), '', $form_calculation);
  $comp["frm"]['STARRATING']=str_replace(array("\r", "\n", "\t", "   "), '', $form_starrating);
  $comp["frm"]['DATE']=str_replace(array("\r", "\n", "\t", "   "), '', $form_date);
  $comp["frm"]['TIME']=str_replace(array("\r", "\n", "\t", "   "), '', $form_time);
  $comp["frm"]['DATETIME']=str_replace(array("\r", "\n", "\t", "   "), '', $form_datetime);
  $comp["frm"]['FILE']=str_replace(array("\r", "\n", "\t", "   "), '', $form_file);
  $comp["frm"]['PICTURE']=str_replace(array("\r", "\n", "\t", "   "), '', $form_picture);
  $comp["frm"]['CAPTCHA']=str_replace(array("\r", "\n", "\t", "   "), '', $form_captcha);
  $comp["frm"]['SIGNATURE']=str_replace(array("\r", "\n", "\t", "   "), '', $form_signature);
  $comp["frm"]['STRIPE']=str_replace(array("\r", "\n", "\t", "   "), '', $form_stripe);
  $comp["frm"]['PAYPAL']=str_replace(array("\r", "\n", "\t", "   "), '', $form_paypal);
  $comp["frm"]['STRIPEPAYPAL']=str_replace(array("\r", "\n", "\t", "   "), '', $form_stripepaypal);

  $comp["frm"]['US_ADDRESS']=str_replace(array("\r", "\n", "\t", "   "), '', $form_usaddress);
  $comp["frm"]['SECTION']=str_replace(array("\r", "\n", "\t", "   "), '', $form_section);
  $comp["frm"]['RANGE']=str_replace(array("\r", "\n", "\t", "   "), '', $form_range);
  $comp["frm"]['SWITCH']=str_replace(array("\r", "\n", "\t", "   "), '', $form_switch);
  $comp["frm"]['SELECT']=str_replace(array("\r", "\n", "\t", "   "), '', $form_select);
  $comp["frm"]['RADIO']=str_replace(array("\r", "\n", "\t", "   "), '', $form_radio);
  $comp["frm"]['CHECKBOX']=str_replace(array("\r", "\n", "\t", "   "), '', $form_checkbox);
  $comp["frm"]['PRODUCTS']=str_replace(array("\r", "\n", "\t", "   "), '', $form_products);
  $comp["frm"]['INPUTTABLE']=str_replace(array("\r", "\n", "\t", "   "), '', $form_inputtable);
  $comp["frm"]['LABEL']=str_replace(array("\r", "\n", "\t", "   "), '', $form_label);
  $comp["frm"]['TEXTAREA']=str_replace(array("\r", "\n", "\t", "   "), '', $form_textarea);
  //$comp["frm"]['PAGEBREAK']=str_replace(array("\r", "\n", "\t", "   "), '', $form_pagebreak);

  $comp["fld"]['inputLabel']=str_replace(array("\r", "\n", "\t", "   "), '', $field_inputLabel);
  $comp["fld"]['validationMessage']=str_replace(array("\r", "\n", "\t", "   "), '', $field_validationMessage);
  $comp["fld"]['instructionText']=str_replace(array("\r", "\n", "\t", "   "), '', $field_instructionText);
  $comp["fld"]['helpText']=str_replace(array("\r", "\n", "\t", "   "), '', $field_helpText);
  $comp["fld"]['placeholderText']=str_replace(array("\r", "\n", "\t", "   "), '', $field_placeholderText);
  $comp["fld"]['placeholderFirstText']=str_replace(array("\r", "\n", "\t", "   "), '', $field_placeholderFirstText);
  $comp["fld"]['placeholderMiddleText']=str_replace(array("\r", "\n", "\t", "   "), '', $field_placeholderMiddleText);
  $comp["fld"]['placeholderLastText']=str_replace(array("\r", "\n", "\t", "   "), '', $field_placeholderLastText);

  $comp["fld"]['placeholderAddress1Text']=str_replace(array("\r", "\n", "\t", "   "), '', $field_placeholderAddress1Text);
  $comp["fld"]['placeholderAddress2Text']=str_replace(array("\r", "\n", "\t", "   "), '', $field_placeholderAddress2Text);
  $comp["fld"]['placeholderCityText']=str_replace(array("\r", "\n", "\t", "   "), '', $field_placeholderCityText);
  $comp["fld"]['placeholderStateText']=str_replace(array("\r", "\n", "\t", "   "), '', $field_placeholderStateText);
  $comp["fld"]['placeholderZipText']=str_replace(array("\r", "\n", "\t", "   "), '', $field_placeholderZipText);
  $comp["fld"]['placeholderCountryText']=str_replace(array("\r", "\n", "\t", "   "), '', $field_placeholderCountryText);

  $comp["fld"]['reqdis']=str_replace(array("\r", "\n", "\t", "   "), '', $field_reqdis);
  $comp["fld"]['otherOption']=str_replace(array("\r", "\n", "\t", "   "), '', $field_otherOption);
  $comp["fld"]['otherOptionLabel']=str_replace(array("\r", "\n", "\t", "   "), '', $field_otherOptionLabel);
  $comp["fld"]['enableAmount']=str_replace(array("\r", "\n", "\t", "   "), '', $field_enableAmount);
  $comp["fld"]['amountOptionsList']=str_replace(array("\r", "\n", "\t", "   "), '', $field_amountOptionsList);
  $comp["fld"]['defaultValue']=str_replace(array("\r", "\n", "\t", "   "), '', $field_defaultValue);
  $comp["fld"]['optionsList']=str_replace(array("\r", "\n", "\t", "   "), '', $field_optionsList);
  $comp["fld"]['productsList']=str_replace(array("\r", "\n", "\t", "   "), '', $field_productsList);
  $comp["fld"]['questionList']=str_replace(array("\r", "\n", "\t", "   "), '', $field_questionList);
  $comp["fld"]['answerList']=str_replace(array("\r", "\n", "\t", "   "), '', $field_answerList);
  $comp["fld"]['queryName']=str_replace(array("\r", "\n", "\t", "   "), '', $field_queryName);
  $comp["fld"]['disabledDays']=str_replace(array("\r", "\n", "\t", "   "), '', $field_disabledDays);
  $comp["fld"]['logic']=str_replace(array("\r", "\n", "\t", "   "), '', $field_logic);
  $comp["fld"]['calculation']=str_replace(array("\r", "\n", "\t", "   "), '', $field_calculation);
  $comp["endp"]['form']=str_replace(array("\r", "\n", "\t", "   "), '', $endpoint);
    echo "var tmpl=".json_encode($comp).";";
    echo "var themeFont='';";
    echo "var accountStatus='".$this->uaccountstatus."';";
    echo "var paidElements=".json_encode($GLOBALS['ref']['RESTRICTED_ELEMENTS_FOR_FREE_USERS']).";";
    echo "var forms_url='".$GLOBALS['conf']['forms_url']."';";
    $isprev = $this->pl->isPreviewUser($this->lUser) ? 'true':'false';
    echo "var isPreviewUser=".$isprev.";";
  }
  //




}
?>
