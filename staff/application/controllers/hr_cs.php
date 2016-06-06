<?php
	if ( ! defined('BASEPATH')) exit('No direct script access allowed');

	class Hr_cs extends MY_Controller{ 


		public function __construct(){
		parent::__construct();
		$this->load->helper('security');
		$this->load->model('ask_hr');		
		
			}

		public function index(){
            $data['content']='askHR_submissionpage';
            $data['msg_newID']=0;

			$this->load->view('includes/templatecolorbox',$data);
		} // end of index function

		public function askhr(){
			

			//checking if there is session data
			$empID = $this->user->empID;
			
			
			if($this->input->post()){
			$this->load->library('form_validation');
			$this->form_validation->set_rules('cs_post_subject','cs_post_subject','required');
			$this->form_validation->set_rules('cs_post_subject','cs_post_subject','required');

			if($this->form_validation->run() !== false)
			{
			$data['cs_post_empID_fk'] = $empID;
			$data['cs_post_subject'] = $this->input->post('cs_post_subject');
			$data['report_related']= $this->input->post('inquiry_type');
			$data['cs_post_urgency']= $this->input->post('cs_post_urgency');
			$data['cs_post_date_submitted']= date('Y-m-d h:i:sa');
			$data['cs_post_status']= 0; 
			
			$data2['cs_msg_postID_fk'] = $this->ask_hr->askhr('hr_cs_post',$data);
			$data2['reply_empUser'] = $this->input->post('hr_username');
			$data2['cs_msg_date_submitted']= date('Y-m-d h:i:sa');
			$data2['cs_msg_type']=0;
			$data2['cs_msg_text'] = $this->input->post('askHR_details');
			$data2['cs_msg_text'] = $this->security->xss_clean($data2['cs_msg_text']);
			
			if( $this->isSetNotEmpty($_FILES)){
						$files = $_FILES;					
						// config data 
						$upload_config['upload_path'] ='uploads/cs_hr_attachments';
						$upload_config['allowed_types'] = 'gif|jpg|png|pdf|docx|doc|csv';
						$upload_config['max_size']	= '2048';
						$upload_config['overwrite']	= 'FALSE';					
						$this->load->library('upload');	


						$upload_count = count( $_FILES['arr_attachments']['name'] );
						for( $x = 0; $x < $upload_count; $x++ )
						{
							$_FILES['to_upload']['name'] = $files['arr_attachments']['name'][$x];
							$_FILES['to_upload']['type'] = $files['arr_attachments']['type'][$x];
							$_FILES['to_upload']['tmp_name'] = $files['arr_attachments']['tmp_name'][$x];
							$_FILES['to_upload']['error'] = $files['arr_attachments']['error'][$x];
							$_FILES['to_upload']['size'] = $files['arr_attachments']['size'][$x];

							$ext = pathinfo( $_FILES['to_upload']['name'], PATHINFO_EXTENSION );
							$uniq_id = uniqid();
							$upload_config['file_name'] = $this->user->empID .'_'. $uniq_id .'_'. $x .'.'.$ext;
							
							$this->upload->initialize( $upload_config );
							
							if( ! $this->upload->do_upload('to_upload') ){
								$error_data[$x] = $this->upload->display_errors();
							} else {
								$upload_data[$x] = $this->upload->data();
							}

						} // end of fro loop

						//if we have error, throw it to views
                        if( isset($error_data) AND !empty($error_data) ){
                            $data['error'] = '';
							foreach( $error_data as $key1 => $val1 ){
								$data['error'] .= $val1 ."\n";
							}						
						}

						if( isset($upload_data) AND !empty($upload_data) ){
							foreach( $upload_data as $key => $val ){
								$docs_url[] = $val['full_path'];
							}
							$data2['cs_msg_attachment'] = json_encode( $docs_url );
							$data2['cs_msg_attachment'] = addslashes($data2['cs_msg_attachment']);
						}					
					}else{
						$data2['cs_msg_attachment'] = null;

					} // end of file upload

									
					$data3['msg_newID']=0;
					if(isset($data2['cs_msg_postID_fk'])){
						$this->ask_hr->askhr('hr_cs_msg',$data2);

			 
					}
					$data3['msg_newID']= $this->ask_hr->max_id();
					$this->load->view('askHR_submissionpage',$data3);

			}
			else{
				$data['content']='askHR_submissionpage';
				$this->load->view('includes/templatecolorbox',$data);
				}


			}
			
			

			
			

					//redirect($this->config->base_url(), 'refresh');
				
            } // end of askhr function

            public function HrHelpDesk()
            {
            	//for viewing
            	$data['content']='hr_helpdesk';
            	$empuser = $this->user->username;
            	
				
				//getting data from db
				//for New incident data HR HELP DESK
				//$data['NewIncidentFull']=$this->ask_hr->hrhelpdesk('hr_cs_post.cs_post_id, staffs.fname, hr_cs_post.cs_post_date_submitted, hr_cs_post.cs_post_subject, hr_cs_post.cs_post_urgency, hr_cs_msg.cs_msg_text','hr_cs_post','INNER JOIN staffs ON staffs.empID = hr_cs_post.cs_post_empID_fk LEFT JOIN hr_cs_msg ON hr_cs_msg.cs_msg_postID_fk = hr_cs_post.cs_post_id WHERE hr_cs_post.cs_post_status = 0 GROUP BY cs_msg_postID_fk HAVING COUNT(cs_msg_postID_fk) = 1','hr_cs_post.cs_post_id');

				//This is for New incident 
				$data['NewIncidentFull']=$this->ask_hr->hrhelpdesk('hr_cs_post.cs_post_id, hr_cs_post.cs_post_empID_fk, hr_cs_post.due_date, staffs.fname, staffs.lname,hr_cs_post.cs_post_date_submitted, hr_cs_post.cs_post_subject, hr_cs_post.cs_post_urgency, hr_cs_msg.cs_msg_text','hr_cs_post','INNER JOIN staffs ON staffs.empID = hr_cs_post.cs_post_empID_fk LEFT JOIN hr_cs_msg ON hr_cs_msg.cs_msg_postID_fk = hr_cs_post.cs_post_id WHERE hr_cs_post.cs_post_status = 0  AND hr_cs_post.hr_own_empUSER = "" GROUP BY cs_msg_postID_fk','hr_cs_post.cs_post_id');
				$data['NewIncidentHR']=$this->ask_hr->hrhelpdesk('hr_cs_post.cs_post_id, hr_cs_post.cs_post_empID_fk, staffs.fname, staffs.lname, hr_cs_post.due_date, hr_cs_post.cs_post_date_submitted, hr_cs_post.cs_post_subject, hr_cs_post.cs_post_urgency, hr_cs_msg.cs_msg_text','hr_cs_post','INNER JOIN staffs ON staffs.empID = hr_cs_post.cs_post_empID_fk LEFT JOIN hr_cs_msg ON hr_cs_msg.cs_msg_postID_fk = hr_cs_post.cs_post_id WHERE hr_cs_post.cs_post_status = 0 AND hr_cs_post.report_related = 0 AND hr_cs_post.hr_own_empUSER = "" GROUP BY cs_msg_postID_fk','hr_cs_post.cs_post_id');
				$data['NewIncidentAcc']=$this->ask_hr->hrhelpdesk('hr_cs_post.cs_post_id, hr_cs_post.cs_post_empID_fk ,staffs.fname, staffs.lname, hr_cs_post.due_date, hr_cs_post.cs_post_date_submitted, hr_cs_post.cs_post_subject, hr_cs_post.cs_post_urgency, hr_cs_msg.cs_msg_text','hr_cs_post','INNER JOIN staffs ON staffs.empID = hr_cs_post.cs_post_empID_fk LEFT JOIN hr_cs_msg ON hr_cs_msg.cs_msg_postID_fk = hr_cs_post.cs_post_id WHERE hr_cs_post.cs_post_status = 0 AND hr_cs_post.report_related = 1 AND hr_cs_post.hr_own_empUSER = "" GROUP BY cs_msg_postID_fk','hr_cs_post.cs_post_id');

			 
				// this is for the active incident
				$data['ActiveIncidentFull']=$this->ask_hr->hrhelpdesk('hr_cs_msg.cs_msg_postID_fk, hr_cs_post.cs_post_empID_fk, hr_cs_post.due_date ,hr_cs_post.cs_post_id, staffs.fname, staffs.lname, hr_cs_post.cs_post_date_submitted, hr_cs_post.assign_category, hr_cs_post.cs_post_subject, hr_cs_post.cs_post_urgency, assign_category.assign_sla, hr_cs_post.hr_own_empUSER, MAX( hr_cs_msg.cs_msg_date_submitted ) AS last_update','hr_cs_post','INNER JOIN staffs ON staffs.empID = hr_cs_post.cs_post_empID_fk INNER JOIN hr_cs_msg ON hr_cs_msg.cs_msg_postID_fk = hr_cs_post.cs_post_id LEFT JOIN assign_category ON assign_category.categorys = hr_cs_post.assign_category WHERE hr_cs_post.cs_post_status = 1 GROUP BY cs_msg_postID_fk');
				$data['ActiveIncidentHR']=$this->ask_hr->hrhelpdesk('hr_cs_msg.cs_msg_postID_fk, hr_cs_post.cs_post_empID_fk, hr_cs_post.due_date ,hr_cs_post.cs_post_id, staffs.fname, staffs.lname, hr_cs_post.cs_post_date_submitted, hr_cs_post.assign_category, hr_cs_post.cs_post_subject, hr_cs_post.cs_post_urgency, assign_category.assign_sla, hr_cs_post.hr_own_empUSER, MAX( hr_cs_msg.cs_msg_date_submitted ) AS last_update','hr_cs_post','INNER JOIN staffs ON staffs.empID = hr_cs_post.cs_post_empID_fk INNER JOIN hr_cs_msg ON hr_cs_msg.cs_msg_postID_fk = hr_cs_post.cs_post_id LEFT JOIN assign_category ON assign_category.categorys = hr_cs_post.assign_category WHERE hr_cs_post.cs_post_status = 1 AND hr_cs_post.report_related = 0 GROUP BY cs_msg_postID_fk');
				$data['ActiveIncidentAcc']=$this->ask_hr->hrhelpdesk('hr_cs_msg.cs_msg_postID_fk, hr_cs_post.cs_post_empID_fk, hr_cs_post.due_date ,hr_cs_post.cs_post_id, staffs.fname, staffs.lname, hr_cs_post.cs_post_date_submitted, hr_cs_post.assign_category, hr_cs_post.cs_post_subject, hr_cs_post.cs_post_urgency, assign_category.assign_sla, hr_cs_post.hr_own_empUSER, MAX( hr_cs_msg.cs_msg_date_submitted ) AS last_update','hr_cs_post','INNER JOIN staffs ON staffs.empID = hr_cs_post.cs_post_empID_fk INNER JOIN hr_cs_msg ON hr_cs_msg.cs_msg_postID_fk = hr_cs_post.cs_post_id LEFT JOIN assign_category ON assign_category.categorys = hr_cs_post.assign_category WHERE hr_cs_post.cs_post_status = 1 AND hr_cs_post.report_related = 1 GROUP BY cs_msg_postID_fk');

				// this is for Resolve incident
				$data['ResolveIncidentFull']=$this->ask_hr->hrhelpdesk('hr_cs_msg.cs_msg_postID_fk, hr_cs_post.cs_post_empID_fk, hr_cs_post.due_date,hr_cs_post.cs_post_id, staffs.fname, staffs.lname, hr_cs_post.cs_post_date_submitted, hr_cs_post.assign_category, hr_cs_post.cs_post_subject, hr_cs_post.cs_post_urgency, assign_category.assign_sla, hr_cs_post.hr_own_empUSER, MAX( hr_cs_msg.cs_msg_date_submitted ) AS last_update','hr_cs_post','INNER JOIN staffs ON staffs.empID = hr_cs_post.cs_post_empID_fk INNER JOIN hr_cs_msg ON hr_cs_msg.cs_msg_postID_fk = hr_cs_post.cs_post_id LEFT JOIN assign_category ON assign_category.categorys = hr_cs_post.assign_category WHERE hr_cs_post.cs_post_status = 3 GROUP BY hr_cs_msg.cs_msg_postID_fk');
				$data['ResolveIncidentHR']=$this->ask_hr->hrhelpdesk('hr_cs_msg.cs_msg_postID_fk, hr_cs_post.cs_post_empID_fk, hr_cs_post.due_date,hr_cs_post.cs_post_id, staffs.fname, staffs.lname, hr_cs_post.cs_post_date_submitted, hr_cs_post.assign_category, hr_cs_post.cs_post_subject, hr_cs_post.cs_post_urgency, assign_category.assign_sla, hr_cs_post.hr_own_empUSER, MAX( hr_cs_msg.cs_msg_date_submitted ) AS last_update','hr_cs_post','INNER JOIN staffs ON staffs.empID = hr_cs_post.cs_post_empID_fk INNER JOIN hr_cs_msg ON hr_cs_msg.cs_msg_postID_fk = hr_cs_post.cs_post_id LEFT JOIN assign_category ON assign_category.categorys = hr_cs_post.assign_category WHERE hr_cs_post.cs_post_status = 3 AND hr_cs_post.report_related = 0 GROUP BY hr_cs_msg.cs_msg_postID_fk');
				$data['ResolveIncidentAcc']=$this->ask_hr->hrhelpdesk('hr_cs_msg.cs_msg_postID_fk, hr_cs_post.cs_post_empID_fk, hr_cs_post.due_date,hr_cs_post.cs_post_id, staffs.fname, staffs.lname, hr_cs_post.cs_post_date_submitted, hr_cs_post.assign_category, hr_cs_post.cs_post_subject, hr_cs_post.cs_post_urgency, assign_category.assign_sla, hr_cs_post.hr_own_empUSER, MAX( hr_cs_msg.cs_msg_date_submitted ) AS last_update','hr_cs_post','INNER JOIN staffs ON staffs.empID = hr_cs_post.cs_post_empID_fk INNER JOIN hr_cs_msg ON hr_cs_msg.cs_msg_postID_fk = hr_cs_post.cs_post_id LEFT JOIN assign_category ON assign_category.categorys = hr_cs_post.assign_category WHERE hr_cs_post.cs_post_status = 3 AND hr_cs_post.report_related = 1 GROUP BY hr_cs_msg.cs_msg_postID_fk');

				//this is for Cancel incident
				$data['CancelIncidentFull']=$this->ask_hr->hrhelpdesk('hr_cs_msg.cs_msg_postID_fk, hr_cs_post.cs_post_empID_fk, hr_cs_post.due_date,hr_cs_post.cs_post_id, hr_cs_post.cs_post_status,staffs.fname, staffs.lname, hr_cs_post.cs_post_date_submitted, hr_cs_post.assign_category, hr_cs_post.cs_post_subject, hr_cs_post.cs_post_urgency, assign_category.assign_sla, hr_cs_post.hr_own_empUSER, MAX( hr_cs_msg.cs_msg_date_submitted ) AS last_update','hr_cs_post','INNER JOIN staffs ON staffs.empID = hr_cs_post.cs_post_empID_fk INNER JOIN hr_cs_msg ON hr_cs_msg.cs_msg_postID_fk = hr_cs_post.cs_post_id LEFT JOIN assign_category ON assign_category.categorys = hr_cs_post.assign_category WHERE (hr_cs_post.cs_post_status = 4 OR hr_cs_post.cs_post_status = 5) GROUP BY hr_cs_msg.cs_msg_postID_fk');
				$data['CancelIncidentHR']=$this->ask_hr->hrhelpdesk('hr_cs_msg.cs_msg_postID_fk, hr_cs_post.cs_post_empID_fk, hr_cs_post.due_date,hr_cs_post.cs_post_id, hr_cs_post.cs_post_status, staffs.fname, staffs.lname, hr_cs_post.cs_post_date_submitted, hr_cs_post.assign_category, hr_cs_post.cs_post_subject, hr_cs_post.cs_post_urgency, assign_category.assign_sla, hr_cs_post.hr_own_empUSER, MAX( hr_cs_msg.cs_msg_date_submitted ) AS last_update','hr_cs_post','INNER JOIN staffs ON staffs.empID = hr_cs_post.cs_post_empID_fk INNER JOIN hr_cs_msg ON hr_cs_msg.cs_msg_postID_fk = hr_cs_post.cs_post_id LEFT JOIN assign_category ON assign_category.categorys = hr_cs_post.assign_category WHERE (hr_cs_post.cs_post_status = 4 OR hr_cs_post.cs_post_status = 5) AND hr_cs_post.report_related = 0 GROUP BY hr_cs_msg.cs_msg_postID_fk');
				$data['CancelIncidentAcc']=$this->ask_hr->hrhelpdesk('hr_cs_msg.cs_msg_postID_fk, hr_cs_post.cs_post_empID_fk, hr_cs_post.due_date,hr_cs_post.cs_post_id, hr_cs_post.cs_post_status, staffs.fname, staffs.lname, hr_cs_post.cs_post_date_submitted, hr_cs_post.assign_category, hr_cs_post.cs_post_subject, hr_cs_post.cs_post_urgency, assign_category.assign_sla, hr_cs_post.hr_own_empUSER, MAX( hr_cs_msg.cs_msg_date_submitted ) AS last_update','hr_cs_post','INNER JOIN staffs ON staffs.empID = hr_cs_post.cs_post_empID_fk INNER JOIN hr_cs_msg ON hr_cs_msg.cs_msg_postID_fk = hr_cs_post.cs_post_id LEFT JOIN assign_category ON assign_category.categorys = hr_cs_post.assign_category WHERE (hr_cs_post.cs_post_status = 4 OR hr_cs_post.cs_post_status = 5)  AND hr_cs_post.report_related = 1 GROUP BY hr_cs_msg.cs_msg_postID_fk');
				// this is for  cancel transfer
				$data['Canceltransfer']=$this->ask_hr->hrhelpdesk('hr_cs_msg.cs_msg_postID_fk, hr_cs_post.cs_post_empID_fk, hr_cs_post.due_date,hr_cs_post.cs_post_id, hr_cs_post.cs_post_status,staffs.fname, staffs.lname, hr_cs_post.cs_post_date_submitted, hr_cs_post.assign_category, hr_cs_post.cs_post_subject, hr_cs_post.cs_post_urgency, assign_category.assign_sla, hr_cs_post.hr_own_empUSER, MAX( hr_cs_msg.cs_msg_date_submitted ) AS last_update','hr_cs_post','INNER JOIN staffs ON staffs.empID = hr_cs_post.cs_post_empID_fk INNER JOIN hr_cs_msg ON hr_cs_msg.cs_msg_postID_fk = hr_cs_post.cs_post_id LEFT JOIN assign_category ON assign_category.categorys = hr_cs_post.assign_category WHERE hr_cs_msg.reply_empUser = "'.$empuser.'" AND hr_cs_msg.incident_status = 1 GROUP BY hr_cs_msg.cs_msg_postID_fk');


				// this is for Agent incident
				$data['MyTicket']=$this->ask_hr->hrhelpdesk('hr_cs_msg.cs_msg_postID_fk, hr_cs_post.cs_post_status, hr_cs_post.due_date, hr_cs_post.cs_post_empID_fk,hr_cs_post.cs_post_id, staffs.fname, staffs.lname, hr_cs_post.cs_post_date_submitted, hr_cs_post.assign_category, hr_cs_post.cs_post_subject, hr_cs_post.cs_post_urgency, assign_category.assign_sla, hr_cs_post.hr_own_empUSER, MAX( hr_cs_msg.cs_msg_date_submitted ) AS last_update, incident_rating.remark','hr_cs_post','INNER JOIN staffs ON staffs.empID = hr_cs_post.cs_post_empID_fk INNER JOIN hr_cs_msg ON hr_cs_msg.cs_msg_postID_fk = hr_cs_post.cs_post_id LEFT JOIN assign_category ON assign_category.categorys = hr_cs_post.assign_category LEFT JOIN incident_rating ON incident_rating.post_id = hr_cs_post.cs_post_id WHERE hr_cs_post.hr_own_empUSER = "'.$empuser.'" GROUP BY cs_msg_postID_fk');

				// get the name of all hr employee
				$data['getHRlist']=$this->ask_hr->getdata('username, lname, fname, empID','staffs','access = "hr"');
				// get the name of all accounting employee
				$data['getACClist']=$this->ask_hr->getdata('username, lname, fname, empID','staffs','access = "finance"');
				// get the name of all Full Access employee
				$data['getFULLlist']=$this->ask_hr->getdata('username, lname, fname, empID','staffs','access IN ("hr","finance","full")');
				//get all department infomation
				$data['department_email'] = $this->ask_hr->getdata('dept_emil_id,email,department','redirection_department');

				$this->load->view('includes/template',$data);



            }//end of HrHelpDesk function

            public function HrIncident(){
            	
           		$data['content']='hr_incidentinfo';

                $insedent_id = $this->uri->segment(3);

            	

            	$data['HrIncident']=$this->ask_hr->hrhelpdesk('hr_cs_post.cs_post_id, hr_cs_msg.cs_msg_attachment, hr_cs_post.due_date, hr_cs_post.cs_post_empID_fk, hr_cs_post.cs_post_empID_fk, hr_cs_post.assign_category, hr_cs_post.invi_req, staffs.fname, staffs.lname, hr_cs_post.cs_post_date_submitted, hr_cs_post.cs_post_subject, hr_cs_post.cs_post_urgency, hr_cs_msg.cs_msg_text, MAX( hr_cs_msg.cs_msg_date_submitted ) AS last_update, staffs.supervisor, staffs.position, newPositions.title, newPositions.dept','hr_cs_post','INNER JOIN staffs ON staffs.empID = hr_cs_post.cs_post_empID_fk INNER JOIN hr_cs_msg ON hr_cs_msg.cs_msg_postID_fk = hr_cs_post.cs_post_id LEFT JOIN newPositions ON newPositions.posID = staffs.position  WHERE hr_cs_post.cs_post_id ='.$insedent_id);
            	
            		$data['HrIncident'][0]->supervisor = $this->ask_hr->getdata('CONCAT(fname, " ", lname) AS name','staffs','empID ='.$data['HrIncident'][0]->supervisor)[0]->name;

            	
            	//$this->textM->aaa($data['HrIncident'], false);


            		$data['category'] = $this->ask_hr->getdata('categorys','assign_category');
            		$data['conversation'] = $this->ask_hr->getdata('*','hr_cs_msg','cs_msg_postID_fk = '.$insedent_id);

            		$checkRemark = $this->ask_hr->getdata('*','incident_rating','post_id = '.$insedent_id);

            		if($checkRemark != null){
            			$return = $checkRemark;
            		}else{
            			$return = 0;
            		}
            		$data['check_remark'] = $return;
            	
					$this->load->view('includes/templatecolorbox',$data);
            	

            }//end of HrIncident function

	        function addcategory(){
	            	$data['categorys'] = $this->input->post('category_name');
	            	
	  			   	$this->ask_hr->askhr('assign_category',$data);
	  			   	$data2['content']='hr_incidentinfo';
	  			  	$this->load->view('includes/templatecolorbox',$data2);

            }// end insertion new category
            function addnewdeparment(){
	            	$data['department'] = $this->input->post('name_department');
	            	$data['email'] = $this->input->post('email_department');
	            	
	  			   	$this->ask_hr->askhr('redirection_department',$data);
	  			   	$data2['content']='hr_incidentinfo';
	  			  	$this->load->view('includes/templatecolorbox',$data2);

            }// end insertion new category


             function DeleteDeparment(){ // delete the department in the depatabase
	            	$id = $this->input->post('id_delete');
	  			   	$this->ask_hr->delete('redirection_department','dept_emil_id = '.$id);

            }// end insertion new category

            public function found_answer_solution(){
            		$reply = $this->input->post('reply');

            		if($reply == 'new_reply'){

	            		$id = $this->input->post('insedentid');

	               		$link = $this->input->post('found_answer_link');
	            		$message = $this->input->post('found_answer_custom');
	            		$cus_message = $this->security->xss_clean($message);

	            		
	            		$data['cs_msg_postID_fk'] =  $id;
	            		$data['cs_msg_text'] = $link.'<br>'.$cus_message;
	            		$data['cs_msg_date_submitted'] = date('Y-m-d h:i:sa');
	            		
						$this->ask_hr->askhr('hr_cs_msg',$data);

						
	            		$categ = $this->input->post('assign_category');

	            		
		            	$id_msg = $this->input->post('categid');
						$this->ask_hr->updatestatus('hr_cs_post','assign_category = "'. $categ .'"','cs_post_id = '.$id_msg);
	            		

	            		$empUSER = $this->input->post('hr_username');
		            	$this->ask_hr->updatestatus('hr_cs_post','hr_own_empUSER = "' .$empUSER. '"','cs_post_id = '.$id_msg);

	            	}else if($reply == 'give_update'){

	            		$id = $this->input->post('give_update_id');

	            		$link = $this->input->post('found_answer_link');
	            		$message = $this->input->post('found_answer_custom');
	            		$cus_message = $this->security->xss_clean($message);

	            		$data['cs_msg_postID_fk'] =  $id;
	            		$data['cs_msg_text'] = $link.'<br>'.$cus_message;
	            		$data['cs_msg_date_submitted'] = date('Y-m-d h:i:sa');
	            		
						$this->ask_hr->askhr('hr_cs_msg',$data);

	            	}

	            	$data3['content']='hr_helpdesk';
		  			$this->load->view('includes/template',$data3);


            }

            public function custom_answer_solution(){

            	$reply = $this->input->post('reply');

            	$newstat = $this->input->post('stat'); // reply on new and active tab is either resolve or close
            	 
            	if($reply == 'new'){ 

            		if($newstat != 5){

	            		$id = $this->input->post('insedentid');
	            		$empUSER = $this->input->post('hr_username');
	            		$empID = $this->input->post('hr_userID');


	            		$data['cs_msg_postID_fk'] =  $id;
	            		$custommessage=  $this->input->post('custom_answer_msg');
	            		$data['cs_msg_text'] = $this->security->xss_clean($custommessage);
	            		$data['cs_msg_date_submitted'] = date('Y-m-d h:i:sa');
	            		$data['cs_msg_type'] = 1;
	            		$data['reply_empUser'] = $empUSER;
	            		$this->ask_hr->askhr('hr_cs_msg',$data);

		            	$categ = $this->input->post('assign_category');
		            	$inv_req = $this->input->post('inve_req');
		            	if($inv_req == "Urgent"){
		            		$due = date('Y-m-d', strtotime(date('Y-m-d'). ' + 2 days'));
		            	}else if($inv_req == "Need Attention"){
		            		$due = date('Y-m-d', strtotime(date('Y-m-d'). ' + 3 days'));
		            	}else if($inv_req == "Not Urgent"){
		            		$due = date('Y-m-d', strtotime(date('Y-m-d'). ' + 4 days'));
		            	}

		            	
						$this->ask_hr->updatestatus('hr_cs_post','assign_category = "'. $categ .'", due_date = "'.$due.'", cs_post_urgency = "'.$inv_req.'", hr_own_empUSER = "' .$empUSER. '", cs_post_agent = "'.$empID.'", cs_post_status = "' .$newstat. '"','cs_post_id = '.$id);
						
						
		            	//$this->ask_hr->updatestatus('hr_cs_post','hr_own_empUSER = "' .$empUSER. '", cs_post_agent = "'.$empID.'"','cs_post_id = '.$id);

			            	
			            //$this->ask_hr->updatestatus('hr_cs_post','cs_post_status = "' .$newstat. '"','cs_post_id = '.$id);
					}else{
						
						$id = $this->input->post('insedentid');

						// check if the incident ID is have a history of close transfer
						$msg_id = $this->ask_hr->GetID('cs_msg_id','hr_cs_msg','WHERE cs_msg_postID_fk = "'.$id.'" AND incident_status = 1');
		            		
		            	if($msg_id != null){ // change the status of the incident into regular status
		            			$this->ask_hr->updatestatus('hr_cs_msg','incident_status = 0 ','cs_msg_postID_fk = "'.$id.'"');
		            	}

						$data['reply_empUser'] = $this->input->post('hr_username');
						$custommessage=  $this->input->post('custom_answer_msg');
	            		$data['cs_msg_text'] = $this->security->xss_clean($custommessage);
	            		$data['cs_msg_date_submitted'] = date('Y-m-d h:i:sa');
	            		$data['cs_msg_type'] = 1;
	            		$data['cs_msg_postID_fk'] =  $id;

	            		$this->ask_hr->askhr('hr_cs_msg',$data);


	            	$this->ask_hr->updatestatus('hr_cs_post','cs_post_status = "' .$newstat. '"','cs_post_id = '.$id);
						

					}


						}else if($reply == 'emp' || $reply == 'active'){

							$id = $this->input->post('insedentid');

							if($reply == 'active' && $newstat == 5){
								// check if the incident ID is have a history of close transfer
							$msg_id = $this->ask_hr->GetID('cs_msg_id','hr_cs_msg','WHERE cs_msg_postID_fk = "'.$id.'" AND incident_status = 1');
		            		
		            		if($msg_id != null){ // change the status of the incident into regular status
		            			$this->ask_hr->updatestatus('hr_cs_msg','incident_status = 0 ','cs_msg_postID_fk = "'.$id.'"');

							}
						}

		            		$data['cs_msg_postID_fk'] =  $id;
		            		$custommessage=  $this->input->post('custom_answer_msg');
		            		$data['cs_msg_text'] = $this->security->xss_clean($custommessage);
		            		$data['cs_msg_date_submitted'] = date('Y-m-d h:i:sa');
		            		if ($reply=='emp') {
		            			$data['cs_msg_type'] = 0;
		            		}else if($reply=='active'){
		            			$data['cs_msg_type'] = 1;
		            			if ($newstat == 0) {
		            				$this->ask_hr->updatestatus('hr_cs_post','cs_post_status = "' .$newstat. '", rate_status = 0','cs_post_id = '.$id);
		            				$this->ask_hr->updatestatus('incident_rating','remark = "" ,remark_status = 1','post_id = '.$id);
		            			}else{
		            				$this->ask_hr->updatestatus('hr_cs_post','cs_post_status = "' .$newstat. '"','cs_post_id = '.$id);
		            			}

		            		}
		            		
		            		$data['reply_empUser']  = $this->input->post('hr_username');
		            		
		            		$this->ask_hr->askhr('hr_cs_msg',$data);

		            		

						}else if($reply == 'resolved' || $reply == 'reopen' || $reply = 'cinc'){

							$id = $this->input->post('insedentid');

							if($reply == 'reopen'){

								// check if the incident ID is have a history of close transfer
								$msg_id = $this->ask_hr->GetID('cs_msg_id','hr_cs_msg','WHERE cs_msg_postID_fk = "'.$id.'" AND incident_status = 1');
			            		
			            		if($msg_id != null){ // change the status of the incident into regular status
			            			$this->ask_hr->updatestatus('hr_cs_msg','incident_status = 0 ','cs_msg_postID_fk = "'.$id.'"');
			            		}
							}


		            		$data['cs_msg_postID_fk'] =  $id;
		            		$custommessage=  $this->input->post('custom_answer_msg');
		            		$data['cs_msg_text'] = $this->security->xss_clean($custommessage);
		            		$data['cs_msg_date_submitted'] = date('Y-m-d h:i:sa');
		            		if ($reply == 'resolved') {
		            			$data['cs_msg_type'] = 1;	
		            		}elseif (($reply == 'reopen' || $reply == 'cinc') && $reply == 'emp') {
		            			$data['cs_msg_type'] = 0;	
		            		}elseif(($reply == 'reopen' || $reply == 'cinc') && $reply == 'active'){
		            			$data['cs_msg_type'] = 1;	
		            		}
		            		
		            		$data['reply_empUser']  = $this->input->post('hr_username');
		            		
		            		$this->ask_hr->askhr('hr_cs_msg',$data);

		            		
		            	
							$this->ask_hr->updatestatus('hr_cs_post','cs_post_status = "'. $newstat .'", rate_status = 0','cs_post_id = '.$id);
							$this->ask_hr->updatestatus('incident_rating','remark = "" ,remark_status = 1','post_id = '.$id);
						}  


            		$data2['content']='hr_helpdesk';
	  				$this->load->view('includes/template',$data2);


            }

         
             public function submit_notes(){
            	

            		$id = $this->input->post('insedentid');


		            		$data['cs_msg_postID_fk'] =  $id;
		            		$custommessage=  $this->input->post('custom_answer_msg');
		            		$data['cs_msg_text'] = $this->security->xss_clean($custommessage);
		            		$data['cs_msg_date_submitted'] = date('Y-m-d h:i:sa');
		            		$data['cs_msg_type'] = 2;
		            		$data['reply_empUser']  = $this->input->post('hr_username');
		            		
		            		$this->ask_hr->askhr('hr_cs_msg',$data);

		            		$categ = $this->input->post('assign_category');		    
		            		$this->ask_hr->updatestatus('hr_cs_post','assign_category = "'. $categ .'"','cs_post_id = '.$id);


            		$data2['content']='hr_helpdesk';
	  				$this->load->view('includes/template',$data2);

            }

            function hr_custom_satisfaction(){
            	$data['content']='hr_cust_satisfaction_survey';

            	$data['Remark_incident']=$this->ask_hr->hrhelpdesk('incident_rating.post_id, staffs.fname, staffs.lname, date_submited, last_update, hr_cs_post.assign_category, hr_cs_post.cs_post_subject, hr_cs_post.cs_post_urgency, hr_cs_post.hr_own_empUSER, remark','incident_rating','INNER JOIN staffs ON staffs.empID = incident_rating.post_EmpID INNER JOIN hr_cs_post ON hr_cs_post.cs_post_id = incident_rating.post_id WHERE remark_status = 0');

	  			$this->load->view('includes/template',$data);

            }
            function give_update(){
            	$data['content']='give_update.php';
            	$data['department_email'] = $this->ask_hr->getdata('email,department','redirection_department');

            	
	  			$this->load->view('includes/templatecolorbox',$data);

            }

            function hr_incident_notes(){

          		$insedent_id = $this->uri->segment(3);
       
            	$data['hr_note']=$this->ask_hr->hrhelpdesk('hr_cs_post.cs_post_id,staffs.fname,staffs.lname,hr_cs_post.cs_post_date_submitted,hr_cs_post.cs_post_subject,hr_cs_post.cs_post_urgency,hr_cs_msg.cs_msg_text','hr_cs_post','INNER JOIN staffs ON staffs.empID = hr_cs_post.cs_post_empID_fk INNER JOIN hr_cs_msg ON hr_cs_msg.cs_msg_postID_fk = hr_cs_post.cs_post_id AND hr_cs_post.cs_post_id ='.$insedent_id);

            	$data['content']='hr_incident_notes';
	  			$this->load->view('includes/templatecolorbox',$data);
            }
            function employee_dashboard()
            {
				$empid = $this->uri->segment(3);

            	$data['content']='employee_incident_info';  

  				$data['EmployeeDashboard']=$this->ask_hr->hrhelpdesk('hr_cs_post.cs_post_id, hr_cs_post.rate_status, hr_cs_post.cs_post_empID_fk,hr_cs_post.cs_post_status, hr_cs_post.cs_post_date_submitted, hr_cs_post.cs_post_subject, hr_cs_post.hr_own_empUSER','hr_cs_post',' LEFT JOIN hr_cs_msg ON hr_cs_msg.cs_msg_postID_fk = hr_cs_post.cs_post_id WHERE cs_post_empID_fk = '.$empid.' GROUP BY cs_msg_postID_fk HAVING COUNT( cs_msg_postID_fk ) >=1');

  				$data['reamark_status'] = $this->ask_hr->getdata('COUNT(rate_status) as num_rate','hr_cs_post','cs_post_status = 3 AND rate_status = 0 AND cs_post_empID_fk ='.$empid);

	  			$this->load->view('includes/template',$data);


            }

            function employee_incident_info_events(){

            	$data['content']='employee_incident_info_events';  
            	
            	$empid = $this->uri->segment(3);

            	$data['EmployeeIncidentEvents']=$this->ask_hr->hrhelpdesk
            	(

            	'hr_cs_post.cs_post_id, staffs.fname, staffs.lname',

            	'hr_cs_post','INNER JOIN staffs  
            	
            	ON hr_cs_post.cs_post_empID_fk = staffs.empID  
            	
            	WHERE cs_post_id = '.$empid.' 
            	
            	GROUP BY hr_cs_post.cs_post_id'
            	);
         
            	$this->load->view('includes/templatecolorbox',$data);		

            }
            function emp_cancel() // method of employee cancel the incident
            {
            	$id = $this->input->post('msg_id');

            	$data['cs_msg_postID_fk'] = $id;
            	$data['cs_msg_date_submitted'] = date('Y-m-d h:i:sa');
            	$data['cs_msg_type'] = 1;
            	$data['cs_msg_text'] = $this->security->xss_clean($this->input->post('emp_msg'));

            	$this->ask_hr->askhr('hr_cs_msg',$data);

            	$status = 3;

            	$this->ask_hr->updatestatus('hr_cs_post','cs_post_status = "'. $status .'"','cs_post_id = '.$id);

            }

            function remark(){ // give survy
            	$id = $this->input->post('insedentid');
            	$msg = "<b>Remark : ".$this->input->post('remark')." </b><br><br> ".$this->input->post('remark_msg');
            	$ret = $this->ask_hr->Check('post_id','incident_rating','WHERE post_id = '.$id);
            	if($ret != true){

            		$data['post_id'] = $id;
	            	$data['post_EmpID'] = $this->input->post('post_emp_id');
	            	$data['date_submited'] = $this->input->post('date_submit');
	            	$data['last_update'] = $this->input->post('last_update');
	            	$data['remark'] = $this->input->post('remark');
	            	$this->ask_hr->askhr('incident_rating',$data);

	            	
	            	
	            	$data2['cs_msg_postID_fk'] = $id;
	            	$data2['reply_empUser'] = $this->input->post('hr_username');
	            	$data2['cs_msg_text'] = $msg;
	            	$data2['cs_msg_date_submitted'] = date('Y-m-d h:i:sa');
	            	$data2['cs_msg_type'] = 0;
	            	$this->ask_hr->askhr('hr_cs_msg',$data2);

	            	$this->ask_hr->updatestatus('hr_cs_post','rate_status = 1','cs_post_id = '.$id);



            	}else{
            		$remark = $this->input->post('remark');
            		$this->ask_hr->updatestatus('incident_rating','remark_status = 0, remark = "'.$remark.'"','post_id = '.$id);

            		$data2['cs_msg_postID_fk'] = $id;
	            	$data2['reply_empUser'] = $this->input->post('hr_username');
	            	$data2['cs_msg_text'] = $msg;
	            	$data2['cs_msg_date_submitted'] = date('Y-m-d h:i:sa');
	            	$data2['cs_msg_type'] = 0;
	            	$this->ask_hr->askhr('hr_cs_msg',$data2);


            		$this->ask_hr->updatestatus('hr_cs_post','rate_status = 1','cs_post_id = '.$id);

            	}

            }
            function remark_update(){ // update survy

            	$id = $this->input->post('insedentid');
            	$newstat = $this->input->post('stat');
            	$datateupdated = date('Y-m-d h:i:sa');

		        $data['cs_msg_postID_fk'] =  $id;
		        $custommessage=  $this->input->post('custom_answer_msg');
		        $data['cs_msg_text'] = $this->security->xss_clean($custommessage);
		        $data['cs_msg_date_submitted'] = $datateupdated;
		        $data['cs_msg_type'] = 0;
		        $data['reply_empUser']  = $this->input->post('hr_username');
		            		
		        $this->ask_hr->askhr('hr_cs_msg',$data);
 	
				$this->ask_hr->updatestatus('hr_cs_post','cs_post_status = "'. $newstat .'", rate_status = 0','cs_post_id = '.$id);

				$stat_update = $this->input->post('survstatus');
				$date_update = $datateupdated;
				$this->ask_hr->updatestatus('incident_rating','remark= "" ,last_update = "'. $date_update .'",remark_status = "'.$stat_update.'"','post_id = '.$id);


            }
            function Redirect_new(){

            	$new_red_dep = $this->input->post('new_redirect_dep');

            		$new_l = array();
            		$new_l = explode(",",$new_red_dep);

            		$data['cs_msg_postID_fk'] = $new_l[0];
	            	$data['reply_empUser'] = $new_l[1];
	            	$data['cs_msg_date_submitted'] = date('Y-m-d h:i:sa');
	            	$data['cs_msg_type'] = 1;
	            	$data['cs_msg_text'] = "<b>Good Day!<br><br></b>This Incident is Transfered by <b>".$new_l[1]." </b>";
	            	$this->ask_hr->askhr('hr_cs_msg',$data);


            		$this->ask_hr->updatestatus('hr_cs_post','hr_own_empUSER = "", cs_post_agent = "", report_related = '.$new_l[2].', cs_post_status = 0','cs_post_id = '.$new_l[0]);

            }

            function Redirect() // method to redirect the incident owner
            { 
            	
            	$val = $this->input->post('redirect_to');
            	

            	$list = array();
            	$list = explode(",",$val);
            	$id = $list[0];
            	$dep = $list[4];

	            	if($dep == "HR"){

	            		$msg_id = $this->ask_hr->GetID('cs_msg_id','hr_cs_msg','WHERE reply_empUser = "'.$list[1].'" AND (cs_msg_postID_fk = "'.$id.'" AND incident_status = 1)');
	            		
	            		if($msg_id != null){
	            			$this->ask_hr->updatestatus('hr_cs_msg','incident_status = 0 ','cs_msg_postID_fk = "'.$id.'"');
	            		}

	            		$data['cs_msg_postID_fk'] = $id;
		            	$data['reply_empUser'] = $list[3];
		            	$data['cs_msg_date_submitted'] = date('Y-m-d h:i:sa');
		            	$data['cs_msg_type'] = 1;
		            	$data['incident_status'] = 1;
		            	$data['cs_msg_text'] = "<b>Good Day!<br><br></b>This Incident is Transfered by <b>".$list[3]." </b>";

		            	$this->ask_hr->askhr('hr_cs_msg',$data);
		            	

	            		$this->ask_hr->updatestatus('hr_cs_post','hr_own_empUSER = "", cs_post_agent = "", cs_post_status = 0, report_related = 0','cs_post_id = '.$id);

	            	}else if($dep == "Finance"){

	            		$msg_id = $this->ask_hr->GetID('cs_msg_id','hr_cs_msg','WHERE reply_empUser =  "'.$list[1].'" AND (cs_msg_postID_fk = "'.$id.'" AND incident_status = 1)');
	            		
	            		if($msg_id != null){
	            			$this->ask_hr->updatestatus('hr_cs_msg','incident_status = 0 ','cs_msg_postID_fk = "'.$id.'"');
	            		}

	            		$data['cs_msg_postID_fk'] = $id;
		            	$data['reply_empUser'] = $list[3];
		            	$data['cs_msg_date_submitted'] = date('Y-m-d h:i:sa');
		            	$data['cs_msg_type'] = 1;
		            	$data['incident_status'] = 1;
		            	$data['cs_msg_text'] = "<b>Good Day!<br><br></b>This Incident is Transfered by <b>".$list[3]." </b>";

		            	$this->ask_hr->askhr('hr_cs_msg',$data);

	            		$this->ask_hr->updatestatus('hr_cs_post','hr_own_empUSER = "", cs_post_agent = "", report_related = 1, cs_post_status = 0','cs_post_id = '.$id);
	            	}else{

	            		$msg_id = $this->ask_hr->GetID('cs_msg_id','hr_cs_msg','WHERE reply_empUser =  "'.$list[1].'" AND (cs_msg_postID_fk = "'.$id.'" AND incident_status = 1)');
	            		
	            		if($msg_id != null){
	            			$this->ask_hr->updatestatus('hr_cs_msg','incident_status = 0 ','cs_msg_postID_fk = "'.$id.'"');
	            		}

		            	$data['cs_msg_postID_fk'] = $id;
		            	$data['reply_empUser'] = $list[3];
		            	$data['cs_msg_date_submitted'] = date('Y-m-d h:i:sa');
		            	$data['cs_msg_type'] = 1;
		            	$data['incident_status'] = 1;
		            	$data['cs_msg_text'] = "<b>Good Day!<br><br>Mr/Ms:".$dep."<br><br></b>This Incident is Reassign to you from <b>".$list[3]." </b>";

		            	$this->ask_hr->askhr('hr_cs_msg',$data);

		            	$new_owner = $list[1];
		            	$new_agent= $list[2];
		            	$this->ask_hr->updatestatus('hr_cs_post','hr_own_empUSER = "'. $new_owner .'", cs_post_agent = "'.$new_agent.'"','cs_post_id = '.$id);
		            }
	   
            }

            function AdditionalDays() // method to redirect the incident owner
            {
            	$id = $this->input->post('inci_id');
            	$dueDate = $this->input->post('due_date');
            	$num_days = $this->input->post('add_days');

            	$Updated_date = date('Y-m-d', strtotime($dueDate. ' + '.$num_days.' days'));


            	$this->ask_hr->updatestatus('hr_cs_post','due_date = "'. $Updated_date .'"','cs_post_id = '.$id);

            }

            function hr_generate_reports(){

            	$data['content']='hr_generate_reports';

            	// get the name of all hr employee
				$data['hr_list']=$this->ask_hr->getdata('username, lname, fname, empID','staffs','access = "hr"');
				// get the name of all accounting employee
				$data['finance_list']=$this->ask_hr->getdata('username, lname, fname, empID','staffs','access = "finance"');
				// get the name of all Full Access employee
				$data['full_list']=$this->ask_hr->getdata('username, lname, fname, empID','staffs','access IN ("hr","finance","full")');
				//get all department infomation
				$data['department_email'] = $this->ask_hr->getdata('dept_emil_id,email,department','redirection_department');
				
	  			$this->load->view('includes/template',$data);

            }
            
              

            function test(){ // this for testing function
            	$data['content']='employee_incident_info';
	  			$this->load->view('includes/template',$data);
            }// end test function

} // end of class



  ?>
	
	

