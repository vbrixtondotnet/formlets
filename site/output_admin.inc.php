<?php

abstract class output_admin extends output_elements {


    function create_pagination($page,$total,$limit=20,$uri='') {
    	// '<a href="/submission/'.$forms[$f]['_id'].'/%d/"><button href="" class="button small" style="background:#d6d7d6;">%d</button></a>',
    	// 											'<button class="button small red">%d</button>',
        $adjacents = 2;
        /* Setup page vars for display. */
    	if ($page == 0) $page = 1;					//if no page var is given, default to 1.
    	$prev = $page - 1;							//previous page is page - 1
    	$next = $page + 1;							//next page is page + 1
    	$lastpage = ceil($total/$limit);		//lastpage is = total pages / items per page, rounded up.
    	$lpm1 = $lastpage - 1;						//last page minus 1

    	/*
    		Now we apply our rules and draw the pagination object.
    		We're actually saving the code to a variable in case we want to draw it more than once.
    	*/
    	$pagination = "";
    	if($lastpage > 1){
    		$pagination .= "<div class=\"pagination\">";
    		if($lastpage > 10) {
    			//previous button
    			if ($page > 1) {
    				$pagination.= "<a href=\"/$uri/$prev/\" class=\"previous button small\"><< Previous</a>";
    			} else {
    				$pagination.= "<button class=\"previous button small current\"><< Previous</button>";
    			}
    		}

    		//pages
    		if ($lastpage < 7 + ($adjacents * 2)) {	//not enough pages to bother breaking it up
    			for ($counter = 1; $counter <= $lastpage; $counter++){
    				if ($counter == $page){
    					$pagination.= "<button class=\"current button small\">$counter</button>";
    				} else {
    					$pagination.= "<a href=\"/$uri/$counter/\"><button class=\"button small\">$counter</button></a>";
    			}
        }
    		} elseif($lastpage > 5 + ($adjacents * 2)) {	//enough pages to hide some
    			//close to beginning; only hide later pages
    			if($page < 1 + ($adjacents * 2)){
    				for ($counter = 1; $counter < 4 + ($adjacents * 2); $counter++){
    					if ($counter == $page){
    						$pagination.= "<button class=\"button small current\">$counter</button>";
    					     } else {
    						$pagination.= "<a href=\"/$uri/$counter/\"><button class=\"button small\">$counter</button></a>";
    				      }
                }
    				$pagination.= "<span class=\"space\">...</span>";
    				$pagination.= "<a href=\"/$uri/$lpm1/\"><button class=\"button small\">$lpm1</button></a>";
    				$pagination.= "<a href=\"/$uri/$lastpage/\"><button class=\"button small\">$lastpage</button></a>";
    			}
    			//in middle; hide some front and some back
    			elseif($lastpage - ($adjacents * 2) > $page && $page > ($adjacents * 2)){
    				$pagination.= "<a href=\"/$uri/1/\"><button class=\"button small\">1</button></a>";
    				$pagination.= "<a href=\"/$uri/2/\"><button class=\"button small\">2</button></a>";
    				$pagination.= "<span class=\"space\">...</span>";
    				for ($counter = $page - $adjacents; $counter <= $page + $adjacents; $counter++){
    					if ($counter == $page){
    						$pagination.= "<button class=\"button small current\">$counter</button>";
    					} else {
    						$pagination.= "<a href=\"/$uri/$counter/\"><button class=\"button small\">$counter</button></a>";
    				}
          }
    				$pagination.= "<span class=\"space\">...</span>";
    				$pagination.= "<a href=\"/$uri/$lpm1/\"><button class=\"button small\">$lpm1</button></a>";
    				$pagination.= "<a href=\"/$uri/$lastpage/\"><button class=\"button small\">$lastpage</button></a>";
    			}
    			//close to end; only hide early pages
    			else {
    				$pagination.= "<a href=\"/$uri/1/\"><button class=\"button small\">1</button></a>";
    				$pagination.= "<a href=\"/$uri/2/\"><button class=\"button small\">2</button></a>";
    				$pagination.= "<span class=\"space\">...</span>";
    				for ($counter = $lastpage - (2 + ($adjacents * 2)); $counter <= $lastpage; $counter++){
    					if ($counter == $page){
    						$pagination.= "<button class=\"button small current\">$counter</button>";
    					} else {
    						$pagination.= "<a href=\"/$uri/$counter/\"><button class=\"button small\">$counter</button></a>";
    				}
          }
    			}
    		}

    		if($lastpage > 10) {
    			//next button
    			if ($page < $counter - 1) {
    				$pagination.= "<a href=\"/$uri/$next/\" class=\"next button small\">Next >></a>";
    			} else {
    				$pagination.= "<button class=\"next button small current\">Next >></button>";
    			}
    		}
    		$pagination.= "</div>\n";
    	}

    	return $pagination;
    }

  //
  function OutputAdmin(){
    if (isset($this->lUser['admin']['rights'])){
    if($this->lUser['admin']['rights']==15){
          $this->InsideHeader();

          $this->InsideCardWrapperOpen();

      if ($this->urlpart[1] === 'admin' && empty($this->urlpart[2])){ ?>
          <div class="head"><h2>Admin Panel</h2></div><br/>
          <div class="support_head">
              <div style="float:left">
                  <a class ="statistics-button" href='<?php echo $GLOBALS['level'];?>admin/stats'>Statistics</a>
                  <a class ="support-button" href='<?php echo $GLOBALS['level'];?>admin/support'>Support</a>
              </div>
          </div>
      <?php } else if ($this->urlpart[2] == 'support'){
            	$this->outputAdmin_support();
          } else if($this->urlpart[2] == 'features'){
            	$this->outputAdmin_features();
          } else if($this->urlpart[2] == 'stats'){
            	$this->outputAdmin_stats();
          } else if($this->urlpart[2] == 'users'){
            	$this->outputAdmin_User();
          } else if($this->urlpart[2] == 'templates') {
          	$this->outputAdmin_FormTemplates();
          } else if($this->urlpart[2] == 'phishing') {
          	$this->OutputPhishing();
          } else if($this->urlpart[2] == 'ccform') {
          	$this->OutputCCForm();
          } else if($this->urlpart[2] == 'formimages') {
          	$this->outputAdmin_FormImages();
          } else if($this->urlpart[2] == 'topviews') {
            $this->outputAdmin_TopViews();
          } else if($this->urlpart[2] == 'topresponse') {
            $this->outputAdmin_TopResponse();
          } else {
            	$this->Output404();
          }
      $this->InsideCardWrapperClose();

      $this->OutputMarketingFooter();
    } else {
    	 $this->output404();
    }
  } else {
  	$this->output404();
  }
  }

function Outputadmin_UserEvents(){
    $m = "userevents";
    $events = $this->events;

?>
<div class="head"><h3 style="visibility: hidden;"><a href="<?php echo $GLOBALS['level'];?>admin">Admin Panel</a></h3></div>
<div class="statistics_index">
    <h2>Events</h2><br/>
    <table>
        <tr>
            <th>Type</th>
            <th>Tag</th>
            <th>Datetime</th>
        </tr>
    <?php foreach($events as $event) { ?>
        <tr>
            <td><?php echo $event['type']; ?></td>
            <td><?php echo $event['tag']; ?></td>
            <td><?php echo $event['inserttime']; ?></td>
        </tr>
    <?php } ?>
    </table>
</div>
<?php
    $this->InsidesettingsFooter();
}

//
function Outputadmin_User(){

    if($this->urlpart[3]=='events') {
        $this->Outputadmin_UserEvents();
        exit;
    }

	$m = "user";
    //$this->InsidesettingsHeader('settings');
    if (isset($this->lUser['admin']['rights'])){
      if($this->lUser['admin']['rights']==15){
        if(!$_GET['order']){$_GET['order']='dateJoined';}
        $rows=$this->lo->_getUsersAdmin(array('page'=>$this->urlpart[4],'_order'=>$_GET['order'], 'type'=>$this->pl->xssenc($_GET['type']), 'keyword'=>$this->pl->xssenc($_GET['keyword']), 'verified'=>$this->pl->xssenc($_GET['verified']), 'accountStatus'=>$this->pl->xssenc($_GET['accountStatus'])));
      }
    }
    $usercount=count($rows);

?>
<div class="pad-double">
	<div class="team">
<div>
	<div><div class="team">
	<div class="centered">
        <form action="" method="get">
		<div class="gr g4">
			<h1>Users<span class="members-count"></span></h1>
		</div>
		<div class="gr g8" class="right">
            <div class="gr g3">
                <select name="type" class="form-control">
                    <option value="email" <?php if($_GET['type']=='email') {echo 'selected';} ?>>Email</option>
                    <option value="formId" <?php if($_GET['type']=='formId') {echo 'selected';} ?>>Form ID</option>
                    <option value="stripeCustomerId" <?php if($_GET['type']=='stripeCustomerId') {echo 'selected';} ?>>Stripe Customer ID</option>
                    <option value="accountId" <?php if($_GET['type']=='accountId') {echo 'selected';} ?>>Account ID</option>
                    <option value="verified" <?php if($_GET['type']=='verified') {echo 'selected';} ?>>Verified</option>
                    <option value="accountStatus" <?php if($_GET['type']=='accountStatus') {echo 'selected';} ?>>Account Status</option>
                </select>
            </div>
            <div class="gr g7">
			      <input type="text" class="form-control" name="keyword" placeholder="Enter Keyword" value="<?php echo $this->pl->xssenc($_GET['keyword']); ?>" />
            </div>
            <div class="gr g2">
                <button class="btn btn-small btn-info" style="float:left"><?php echo $this->pl->trans($m,'Search'); ?></button>
            </div>
		</div>

        </form>
		<div class="centered">
			<table class="team-members" width="100%" style="table-layout: auto;">
				<thead>
                    <tr>
                        <td>
                            <?php
                            if($this->urlpart[4]>0){
                            ?>
                                <a href="<?php echo $GLOBALS['level'];?>admin/users/list/<?php echo $this->pl->xssenc($this->urlpart[4]-1);?>/<?php if(isset($_GET['keyword'])){ echo "?type=".$_GET['type']."&keyword=".$this->pl->xssenc($_GET['keyword']);}?>">&lt;</a>
                            <?php
                            }
                            ?>
                        </td>
                        <td colspan="6"></td>
                        <td>
                            <?php
                            if($usercount==50) {
                            ?>
                                <a href="<?php echo $GLOBALS['level'];?>admin/users/list/<?php echo $this->pl->xssenc($this->urlpart[4]+1);?>/<?php if(isset($_GET['keyword'])){ echo "?type=".$_GET['type']."&keyword=".$this->pl->xssenc($_GET['keyword']);}?>">&gt;</a>
                            <?php
                            }
                            ?>
                        </td>
                    </tr>
					<tr>
						<td><?php echo $this->pl->trans($m,'Firstname -Lastname'); ?></td>
						<td><?php echo $this->pl->trans($m,'Email'); ?></td>
                        <td><?php echo $this->pl->trans($m,'Verified'); ?></td>
                        <td><?php echo $this->pl->trans($m,'Account'); ?></td>
						<td><?php echo $this->pl->trans($m,'Joined'); ?></td>
						<td><?php echo $this->pl->trans($m,'Location'); ?></td>
						<td><?php echo $this->pl->trans($m,'Login Count'); ?></td>
						<td><?php echo $this->pl->trans($m,'Forms Created'); ?></td>
            			<td><?php echo $this->pl->trans($m,'Responses'); ?></td>
						<td></td>
					</tr>
				</thead>
				<tbody>
                    <?php for($r=0;$r<count($rows);$r++){?>
                    <tr class="members-list">
						<td><?php echo $rows[$r]['firstName'];?>
							<?php echo $rows[$r]['lastName'];?></td>
                        <td><?php echo $rows[$r]['email'];?></td>
                        <td><?php echo $rows[$r]['emailVerified'];?></td>
                        <td><?php echo $rows[$r]['accountStatus'];?></td>
						<td><?php echo $rows[$r]['dateCreated'];?></td>
						<td><?php echo $rows[$r]['location'];?></td>
                        <td><?php echo $rows[$r]['loginCount'];?></td>
                        <td><?php echo $rows[$r]['forms_count'];?></td>
                        <td><?php echo $rows[$r]['submissions_count'];?></td>
					    <td>
                            <?php
                            if (isset($this->lUser['admin']['rights'])){
                                if(($this->lUser['admin']['rights']==15)&&($this->uid!=$rows[$r]['_id'])){
                            ?>
    					           <a href="<?php echo $this->pl->set_csrfguard($GLOBALS['level'].'admin/users/take/'.$rows[$r]['_id'].'/','takeuser');?>" rel="nofollow" title="take">
                                       <?php echo $this->pl->trans($m,'Take'); ?>
                                   </a>
                                   |
                            <?php
                                }
                                ?>
                                <a href="<?php echo $GLOBALS['level'].'admin/users/events/'.$rows[$r]['_id'].'/';?>" rel="nofollow" title="events">
                                    <?php echo $this->pl->trans($m,'Events'); ?>
                                </a>
                                <?php
                                if($this->uid!=$rows[$r]['_id']){
                                    if($rows[$r]['blocked']) {
                            ?>
                                        | <a onclick="return confirm('<?php echo $this->pl->trans($m,'Are you sure you want to unblock the user?');?>');" href="<?php echo $this->pl->set_csrfguard($GLOBALS['level'].'admin/users/unblock/'.$rows[$r]['_id'].'/'.$rows[$r]['accountId'].'/','unblockuser');?>" rel="nofollow" title="Unblock">
                                            <?php echo $this->pl->trans($m,'Unblock'); ?>
                                          </a>
                            <?php
                                    } else {
                            ?>
                                        | <a onclick="return confirm('<?php echo $this->pl->trans($m,'Are you sure you want to block the user?');?>');" href="<?php echo $this->pl->set_csrfguard($GLOBALS['level'].'admin/users/block/'.$rows[$r]['_id'].'/'.$rows[$r]['accountId'].'/','blockuser');?>" rel="nofollow" title="Block">
                                            <?php echo $this->pl->trans($m,'Block'); ?>
                                          </a>
                            <?php
                                    }
                                }
                            }
                            ?>
            			</td>
					</tr>
                    <?php } ?>
                    <tr>
                        <td>
                            <?php
                            if($this->urlpart[4]>0){
                            ?>
                                <a href="<?php echo $GLOBALS['level'];?>admin/users/list/<?php echo $this->pl->xssenc($this->urlpart[4]-1);?>/<?php if(isset($_GET['keyword'])){ echo "?type=".$_GET['type']."&keyword=".$this->pl->xssenc($_GET['keyword']);}?>">&lt;</a>
                            <?php
                            }
                            ?>
                        </td>
                        <td colspan="6"></td>
                        <td>
                            <?php
                            if($usercount==50) {
                            ?>
                                <a href="<?php echo $GLOBALS['level'];?>admin/users/list/<?php echo $this->pl->xssenc($this->urlpart[4]+1);?>/<?php if(isset($_GET['keyword'])){ echo "?type=".$_GET['type']."&keyword=".$this->pl->xssenc($_GET['keyword']);}?>">&gt;</a>
                            <?php
                            }
                            ?>
                        </td>
                    </tr>
			</tbody></table>
		</div>
	</div>
</div></div>
						</div>
					</div>
				</div>
<?php
    $this->InsidesettingsFooter();
}
//





  function outputAdmin_stats(){
              $data=$this->lo->getStats();
     ?>
      <div class="head"><h3 style="visibility: hidden;"><a href="<?php echo $GLOBALS['level'];?>admin">Admin Panel</a></h3></div>
      <div class="statistics_index">
          <h2>Statistics</h2><br/>
          <?php
              $this->OutputTable($data['users'],'Users');
              $this->OutputTable($data['forms'],'Forms');
              $this->OutputTable($data['submissions'],'Submissions');
          ?>
      </div>
  <?php
  }

  function outputAdmin_features(){
      $lang=$this->urlpart[3];
      if(!$lang){$lang="en";}
     ?>
     <section class="flush" well>
         <div class="large-10 xl-8 centered">
             <?php
             if (($this->urlpart[4] == "edit") && ($this->urlpart[5])){
                   $this->outputAdminFeatureForm();
             } else if (empty($this->urlpart[4])){
               // we show the list view

                 if (isset($_GET['deleted'])){
                     $msg = "Article Successfully deleted!";
                 }
             ?>
             <div class="support_head">
                 <div>
                     <a class ="add_button" href="<?php echo $GLOBALS['level']."admin/features/".$lang."/edit/new"?>/">
                         Create New Feature
                     </a>
                 </div>
             </div>
             <div class="support_info_index">
                 <table width="100%">
                     <tr><th align="left">Title</th><th align="left">Action</th></tr>
                <?php
                $features = $this->lo->getFeatures();
                foreach($features as $feature) {
                ?>
                    <tr>
                        <td><a href="<?php echo $GLOBALS['level']."admin/features/".$lang."/edit/".$feature['_id']?>/"><?php echo $feature['title'];?></a></td>
                        <td><a href="/admin/features/delete/<?php echo $feature['_id']; ?>" onclick="return confirm('are you sure you want to delete?')">Delete</a></td>
                    </tr>
                <?php
                }
                ?>
                </table>
             </div>
            <?php } ?>
         </div>
     </section>
  <?php
  }

  function outputAdminFeatureForm(){
      $data = array();
      if($this->urlpart[5]){
          $data = $this->lo->getFeatures(array('id' => $this->urlpart[5]));
      }
  ?>
    <style>
    #editor, #editor2 {
        min-height: 200px;
    }
    </style>
      <link href="<?php echo $GLOBALS['level'];?>static/css/wysiwyg/quill.snow.css?<?php echo $_SERVER["CURRENT_VERSION_ID"];?>" rel="stylesheet">

      <div class="support_head">
          <div style="float:left">
              <a class="back_button" href="<?php echo $GLOBALS['level'];?>admin/features/"> Back To List </a> &nbsp;
          </div>
      </div>
      <div class="support_info">

          <h2><?php if($this->urlpart[5]=='new'){echo 'Create New Feature'; } else { echo 'Edit Feature';}?></h2>
          <?php if ($data){ ?><a target="new" href="<?php echo $GLOBALS['level'];?>features/<?php echo $data['url'];?>/">Published view</a><?php } ?>
          <div class="form-div">
               <?php
                  $msg = '';
                  if (isset($_GET['updated'])){
                      $msg = "Successfully updated!";
                  } elseif (isset($_GET['saved'])){
                      $msg = "Successfully saved!";
                  }
                  if ($msg){ ?>
               <div class="" class="success">
                  <span class="success"><?php echo $msg;?></span>
              </div><br/>
              <?php }
               if (isset($this->errorMessage)){ ?>
               <div class="" class="error">
                  <span class="error"><?php echo $this->errorMessage;?></span>
              </div><br/>
              <?php } ?>
              <form accept-charset="UTF-8" enctype="multipart/form-data" action="<?php echo $_SERVER['REQUEST_URI'];?>" method="POST" onsubmit="return getContent()">
                <label>Title <span class="required">*</span></label>
                  <input placeholder="Enter title" type="text" name="title" value="<?php echo $data['title'];?>" required>
                <label>URL<span class="required">*</span></label>
                  <input placeholder="Enter url" type="text" name="url" value="<?php echo $data['url'];?>" required>
                  <br><br>
                <label>Body <span class="required">*</span></label>
                    <div id="toolbar"></div>
                    <div id="editor">
                        <?php echo $data['body']; ?>
                    </div>
                    <textarea id="editor-textarea" name="body" style="display:none"></textarea>

                    <label>Img1</label>
                    <input name="img1" type="file" />
                    <?php if ($data['img1']){?>
                      <input name="hidden_img1" type="hidden" value="<?php echo $data['img1'];?>"/>
                      <input name="remove_img1" type="checkbox" value="true"/> Remove image 1
                      <img src="<?php echo $GLOBALS['level']."img/".$data['img1'];?>"><?php } ?>
                    <br><br>
                    <label>Body 2</label>
                    <div id="editor2">
                        <?php echo $data['body2']; ?>
                    </div>
                    <label>Img2</label>
                    <input name="img2" type="file" />
                    <?php if ($data['img2']){?>
                      <input name="hidden_img2" type="hidden" value="<?php echo $data['img2'];?>"/>
                      <input name="remove_img2" type="checkbox" value="true"/> Remove image 2
                      <img src="<?php echo $GLOBALS['level']."img/".$data['img2'];?>"><?php } ?>
                    <br><br>
                    <textarea id="editor-textarea2" name="body2" style="display:none"></textarea>
                  <input type="submit" class="save_button" name="submit_support_form" value="Save"> &nbsp;
                  <a class="cancel_button" href="<?php echo $GLOBALS['level']. 'admin/features/';?>"> Cancel </a>
              </form>
          </div>
       </div>

       <script src="<?php echo $GLOBALS['level'];?>static/js/wysiwyg/quill.min.js?<?php echo $_SERVER["CURRENT_VERSION_ID"];?>"></script>
       <script src="<?php echo $GLOBALS['level'];?>static/js/wysiwyg/image-resize.min.js?<?php echo $_SERVER["CURRENT_VERSION_ID"];?>"></script>
       <script>
       var toolbarOptions = [
          ['bold', 'italic', 'underline', 'strike'],        // toggled buttons
          ['blockquote', 'code-block'],

          [{ 'header': 1 }, { 'header': 2 }],               // custom button values
          [{ 'list': 'ordered'}, { 'list': 'bullet' }],
          [{ 'script': 'sub'}, { 'script': 'super' }],      // superscript/subscript
          [{ 'indent': '-1'}, { 'indent': '+1' }],          // outdent/indent
          [{ 'direction': 'rtl' }],                         // text direction

          [{ 'header': [1, 2, 3, 4, 5, 6, false] }],

          [{ 'color': [] }, { 'background': [] }],          // dropdown with defaults from theme
          [{ 'align': [] }],

          ['clean'],                                   // remove formatting button
          ['image']
        ];
       var quill = new Quill('#editor', {
           modules: {
            toolbar: toolbarOptions,
            imageResize: {
              displaySize: true
            }
           },
           theme: 'snow'
       });
       var quill2 = new Quill('#editor2', {
           modules: {
            toolbar: toolbarOptions,
            imageResize: {
              displaySize: true
            }
           },
           theme: 'snow'
       });
        function getContent(){
            document.getElementById("editor-textarea").value = $(document).find('#editor .ql-editor').html();
            document.getElementById("editor-textarea2").value = $(document).find('#editor2 .ql-editor').html();
        }
        </script>
      <?php
  }

  function OutputPhishing() {
  	$data=$this->lo->getPhishingForms();
  ?>
  	  <div class="head"><h3 style="visibility: hidden;"><a href="<?php echo $GLOBALS['level'];?>admin">Admin Panel</a></h3></div>
      <div class="statistics_index">
          <h2>Possible Phishing Forms</h2><br/>
          <?php
              $this->OutputTable($data['forms'],'Forms', 'phishing');
          ?>
      </div>
  <?php
  }

  function outputAdmin_TopViews() {
  	$data=$this->lo->getTopViewForms(array(
        'yearMonth'=>date('Ym')
    ));
  ?>
  	  <div class="head"><h3 style="visibility: hidden;"><a href="<?php echo $GLOBALS['level'];?>admin">Admin Panel</a></h3></div>
      <div class="statistics_index">
          <h2>Top form Views for this month</h2><br/>
          <?php
              $this->OutputTable($data['forms'],'Forms', 'phishing');
          ?>
      </div>
  <?php
  }

  function outputAdmin_TopResponse() {
  	$data=$this->lo->getTopResponseForms(array(
        'yearMonth'=>date('Ym')
    ));
  ?>
  	  <div class="head"><h3 style="visibility: hidden;"><a href="<?php echo $GLOBALS['level'];?>admin">Admin Panel</a></h3></div>
      <div class="statistics_index">
          <h2>Top form response for this month</h2><br/>
          <?php
              $this->OutputTable($data['forms'],'Forms', 'phishing');
          ?>
      </div>
  <?php
  }

  function OutputCCForm() {
  	$data=$this->lo->getCCForms();
  ?>
  	  <div class="head"><h3 style="visibility: hidden;"><a href="<?php echo $GLOBALS['level'];?>admin">Admin Panel</a></h3></div>
      <div class="statistics_index">
          <h2>Credit Card Form Detection</h2><br/>
          <?php
              $this->OutputTable($data['forms'],'Forms', 'phishing');
          ?>
      </div>
  <?php
  }

  function outputAdmin_FormImages() {
      $page = 1;
  	  if($this->urlpart[3]) {$page = $this->urlpart[3];}
      $rows=$this->lo->getFormsWithImages(array('page'=>$page));
  ?>
        <div class="head"><h3 style="visibility: hidden;"><a href="<?php echo $GLOBALS['level'];?>admin">Admin Panel</a></h3></div>
        <div class="statistics_index">
          <h2>Active Form with Images</h2><br/>

          <?php if($rows["page_count"] > 1){ ?>
                <div style="text-align:center">
                    <?php echo $this->create_pagination($rows["page"], $rows["rows_count"], 25, 'admin/formimages'); ?>
                </div>
          <?php } ?>

          <h3>Forms</h3>
          <table>
              <tr>
                  <th>Id</th>
                  <th>Name</th>
                  <th>Pictures</th>
                  <th>dateCreated</th>
                  <th>Blocked</th>
                  <th>Action</th>
              </tr>
          <?php foreach($rows['data'] as $form) { ?>
              <tr>
                  <td><a href="/admin/users/?type=formId&keyword=<?php echo $form['ID']; ?>"><?php echo stripslashes($form['ID']); ?></a></td>
                  <td><?php echo $form['name']; ?></td>
                  <td>
                      <?php
                      if($form['logo']){
                          if(json_decode($form['logo'])) {
                              $form['logo'] = json_decode($form['logo']);
                          }
                      ?>
      	            	<?php if(is_object($form['logo'])){ ?>
      	                	<a href="https://s3.amazonaws.com/<?php echo $form['logo']->bucket;?>/<?php echo $form['logo']->key;?>" target="_blank"><img src="https://s3.amazonaws.com/<?php echo $form['logo']->bucket;?>/<?php echo $form['logo']->key;?>" style="width:100px;margin-right:10px"></a>
      	            	<?php } else { ?>
      	            		<a href="<?php echo $GLOBALS['protocol']; ?>://<?php echo $_SERVER['HTTP_HOST']; ?>/logo/<?php echo $form['logo']; ?>" target="_blank"><img src="<?php echo $GLOBALS['protocol']; ?>://<?php echo $_SERVER['HTTP_HOST']; ?>/logo/<?php echo $form['logo']; ?>" style="width:100px;margin-right:10px"></a>
      	            	<?php } ?>
      	            <?php } ?>

                    <?php
                    $elements = json_decode(str_replace('\\','',$form['elements']), true);
                    if(!$elements) {
                        $elements = json_decode($form['elements'], true);
                    }

                    if(count($elements)) {
                        foreach($elements as $element) {
                            if($element['type']=='PICTURE' && $element['picture']) {
                                echo '<a href="/images/'.$element['picture'].'" target="_blank"><img src="/images/'.$element['picture'].'" style="width:100px;margin-right:10px"></a>';
                            }
                        }
                    }
                    ?>
                  </td>
                  <td><?php echo $form['dateCreated']; ?></td>
                  <td><?php echo $form['blocked']; ?></td>
                  <td><a href="/forms/<?php echo $form['ID']; ?>/" target="_blank">View Public Form</a></td>
              </tr>
          <?php } ?>
          </table>
          <?php if($rows["page_count"] > 1){ ?>
                <div style="text-align:center;margin-top:30px">
                    <?php echo $this->create_pagination($rows["page"], $rows["rows_count"], 25, 'admin/formimages'); ?>
                </div>
          <?php } ?>
        </div>
  <?php
  }

  function outputAdmin_support(){

    $lang=$this->urlpart[3];
    if(!$lang){$lang="en";}

  ?>
  <section class="flush" well>
      <div class="large-10 xl-8 centered">
      <?php
      if (($this->urlpart[4] == "edit") && ($this->urlpart[5])){
            $this->outputAdminSupportForm();
        } else if (empty($this->urlpart[4])){
        // we show the list view
        $faq_content = [];
        $faqs = $this->lo->getFaqList(array('lang'=>$lang));

        foreach($faqs as $data){
            $urls[] = $data['url'];
            $faq_content[] = $data;
        }
                  if (isset($_GET['deleted'])){
                      $msg = "Article Successfully deleted!";
                  }
              ?>
              <div class="support_head">
                  <div style="float:left">
                      <a class ="add_button" href="<?php echo $GLOBALS['level']."admin/support/".$lang."/edit/new"?>/">
                          Create New Article
                      </a>
                  </div>
                  <div style="width:100%;text-align: right;">
                <ul class="submenu_settings" style="background: none;">
                  <?php 	foreach ($GLOBALS[ref][languages] as $l) {  ?>
                  <li><a href="<?php echo $GLOBALS['level'];?>admin/support/<?php echo $l; ?>/" class="button small <?php echo $this->urlpart[3]==$l ? 'active':'' ?>"><?php echo $l; ?></a>
                  </li>
                  <?php } ?>
                </ul>
                </div>
              </div>
              <div class="support_info_index">
              <div class="success">
                      <?php echo $this->pl->trans($a_s,$msg);?>
                  </div>
              <div class="faq-info">
              <?php
                  if ($faqs){
                      foreach($this->lo->getCategory() as $category){ ?>
                          <div class="faq-detail">
                              <div><h2><?php echo $category['name'];?></h2></div>
                              <?php
                              foreach($faq_content as $article){ ?>
                                  <?php if ($category['_id'] === $article['category']){?>
                                                  <a href="<?php echo $GLOBALS['level']."admin/support/".$lang."/edit/".$article['_id']?>/"><?php echo $article['title'];?></a><br/><?php
                                  }
                              } ?>
                          <br/>
                          </div>
              <?php }
                 } else { ?>
                      <h2><center><?php echo $this->pl->trans($a_s,'No Data for now'); ?>!</center></h2>
              <?php
                  } ?>
              </div>
           </div>
      <?php
    }  else {
          $this->Output404();
      }
      ?>
          </div>
           </div>
   </section>
  <?php
  }

  function outputAdminSupportForm(){
  //if($this->urlpart[5]){
    $data = $this->lo->getFaqDetail(array('id' => $this->urlpart[5],'lang' => $this->urlpart[3]));
  ?>
      <div class="support_head">
          <div style="float:left">
              <a class="back_button" href="<?php echo $GLOBALS['level'];?>admin/support/"> Back To List </a> &nbsp;
              <a class="delete_button" onclick="return confirm('Are you sure you want to delete the user and all his data?');" href="<?php echo $GLOBALS['level'];?>admin/support/delete/<?php echo $data['_id'];?>/" title="Delete">Delete</a>
          </div>
      </div>
      <div class="support_info">

          <h2><?php if($this->urlpart[4]=='new'){echo 'Create New Article'; } else { echo 'Edit Article';}?></h2>
          <?php if ($data){ ?><a target="new" href="<?php echo $GLOBALS['level'];?>support/<?php echo $data['url'];?>/">Published view</a><?php } ?>
          <div class="form-div">
               <?php
                  $msg = '';
                  if (isset($_GET['updated'])){
                      $msg = "Successfully updated!";
                  } elseif (isset($_GET['saved'])){
                      $msg = "Successfully saved!";
                  }
                  if ($msg){ ?>
               <div class="" class="success">
                  <span class="success"><?php echo $msg;?></span>
              </div><br/>
              <?php }
               if (isset($this->errorMessage)){ ?>
               <div class="" class="error">
                  <span class="error"><?php echo $this->errorMessage;?></span>
              </div><br/>
              <?php } ?>
              <form accept-charset="UTF-8" enctype="multipart/form-data" action="<?php echo $_SERVER['REQUEST_URI'];?>" method="POST">
                <label>Title <span class="required">*</span></label>
                  <input placeholder="Enter title" type="text" name="title" value="<?php echo $data['title'];?>" required>
                <label>URL<span class="required">*</span></label>
                  <input placeholder="Enter url" type="text" name="url" value="<?php echo $data['url'];?>" required>
                  <br><br>
                <label>Category <span class="required">*</span></label>
                  <select class="category_list" name="category" required>
                      <option value="">-- Select Category --</option>
                      <?php foreach($this->lo->getCategory() as $category){
                          $selected = '';
                          if (isset($data['category'])){
                              if ($data['category'] === $category['_id']){$selected = 'selected';}
                          }
                          ?><option <?php echo $selected; ?> value="<?php echo $category['_id']?>"><?php echo $category['name']?></option><?php
                          }
                      ?>
                  </select>
                  <br><br>
                <label>Introduction <span class="required">*</span></label>
                  <textarea class="textarea" name="intro"><?php echo $data['intro'];?></textarea>
                  <br/><br/>
                <!--   <label>Image 1</label>
                  <input name="image" type="file" /> -->
                  <label class="label" for="image">Image</label>
                  <input name="img1" type="file" />
                  <?php if ($data['img1']){?>
                    <input name="hidden_img1" type="hidden" value="<?php echo $data['img1'];?>"/>
                    <input name="remove_img1" type="checkbox" value="true"/> Remove image 1
                    <img src="<?php echo $GLOBALS['level']."img/".$data['img1'];?>"><?php } ?>
                  <br><br>
                <label>Text Body 1</label>
                  <textarea class="textarea" name="body1"><?php echo $data['body1'];?></textarea>
                <br><br>
                <label>Image 2</label>
                <?php if ($data['img2']){?>
                    <input name="hidden_img2" type="hidden" value="<?php echo $data['img2'];?>"/>
                    <input name="remove_img2" type="checkbox" value="true"/> Remove image 2
                  <img src="<?php echo $GLOBALS['level']."img/".$data['img2'];?>"><?php } ?>
                <input name="img2" type="file" />
                <br><br>
                <label>Text Body 2</label>
                 <textarea class="textarea" name="body2"><?php echo $data['body2'];?></textarea>
                <br><br>
                  <input type="submit" class="save_button" name="submit_support_form" value="Save"> &nbsp;
                  <a class="cancel_button" href="<?php echo $GLOBALS['level']. 'admin/support/';?>"> Cancel </a>
              </form>
          </div>
       </div>
      <?php
  }


  function outputAdmin_FormTemplates() {
  ?>
  <section class="flush" well>
      <div class="large-10 xl-8 centered">
      	<div class="head">
              <h3 style="visibility: hidden;"><a href="<?php echo $GLOBALS['level'];?>admin">Admin Panel</a></h3>
          </div>
      <?php
      if (($this->urlpart[4] == "edit") && ($this->urlpart[3])){
            $this->outputEditFormTemplates();
      } else if (empty($this->urlpart[3])){

        $templates = $this->lo->getFormTemplates();

              ?>
              <div class="support_info_index">
              <div class="faq-info">
              <?php
                  if (count($templates)){
              ?>
                     	<div><h2>Templates</h2></div>
                     	<table width="100%">
                     		<tr>
                     			<th align="left">Name</th>
                     			<th align="left">Description</th>
                     			<th align="left">Use count</th>
                     			<th align="left">Form</th>
                     			<th align="left">Published</th>
                     			<th align="left">Action</th>
                     		</tr>
                      <?php
                      foreach($templates as $template){ ?>
                         	<tr>
                         		<td><?php echo $template['name'] ?: 'N/A' ?></td>
                     			<td><?php echo $template['description'] ?: 'N/A' ?></td>
                     			<td><?php echo $template['usecount']; ?></td>
                     			<td><a href="/forms/<?php echo $template['sourceform']; ?>/" target="_blank"><?php echo $template['sourceform']; ?></a></td>
                     			<td><?php echo $template['published']; ?></td>
                     			<td>
                     				<a href="/admin/templates/<?php echo $template['_id']; ?>/edit/">Edit</a> |
                     				<a href="/admin/templates/<?php echo $template['_id']; ?>/delete/" onclick="return confirm('are you sure you want to delete?')">Delete</a>
                     			</td>
                         	</tr>
                      <?php } ?>
                      </table>
              <?php
                 	} else { ?>
                      <h2><center>No Data for now!</center></h2>
              <?php
                  } ?>
           	</div>
      <?php
    	}  else {
          $this->Output404();
      }
      ?>
          </div>
           </div>
   </section>
  <?php
  }


}
?>
