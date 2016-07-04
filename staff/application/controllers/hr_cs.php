<?php
	if ( ! defined('BASEPATH')) exit('No direct script access allowed');

	class Hr_cs extends MY_Controller{ 


		public function __construct(){
		parent::__construct();

		$this->load->helper('security');
		$this->load->helper('url');
		$this->load->model('ask_hr');	
		$this->load->model('commonmodel', 'commonM');

			}

		public function index(){
            $data['content']= 'askHR_submissionpage';
            $data['msg_newID']= 0;

			$this->load->view('includes/templatecolorbox',$data);
		} 

		// Adding of Inquiry/Question to HR/Accounting
		public function askhr(){
			
			// Get current login user
			$empID = $this->user->empID;
						
			if($this->input->post()){

			$this->load->library('form_validation');

			// Form validation in server side
			$this->form_validation->set_rules('cs_post_subject','Cs_post_subject','required');
			$this->form_validation->set_rules('askHR_details','AskHR_details','required');
			
			// If form validation is valid
			if($this->form_validation->run() !== false){

			// Get inputted data from views to be passed to hr_cs_msg
			$data['cs_post_empID_fk'] = $empID;
			$data['cs_post_subject'] = $this->input->post('cs_post_subject');
			$data['report_related']= $this->input->post('inquiry_type');
			$data['cs_post_urgency']= $this->input->post('cs_post_urgency');
			$data['cs_post_date_submitted']= date('Y-m-d h:i:sa');			
			$data['cs_post_status']= 0; 

			//compute due date
			$data['due_date'] = $this->_computeDueDate( $data['cs_post_date_submitted'], $data['cs_post_urgency'] );

			//auto-assign HR related 
			
			$shiftSched = $this->dbmodel->getSingleField('staffs', 'shiftSched', 'empID ='. $empID);
			if( $this->input->post('inquiry_type') == 0){
				if ($shiftSched == 1) {
				//nightshift
					$data['hr_own_empUSER'] = 'kybanez';
					$data['cs_post_agent'] = 330;				
				} else {
					//morningshift
					$data['hr_own_empUSER'] = 'aobrero';
					$data['cs_post_agent'] = 203;
				}	
			}
			
			//end auto-assign
			
			// Insert data1 to hr_cs_post
			$data2['cs_msg_postID_fk'] = $this->ask_hr->askhr('hr_cs_post',$data);

			// Get inputted data from views to be passed to hr_cs_post
			$data2['reply_empUser'] = $this->input->post('hr_username');
			$data2['cs_msg_date_submitted']= date('Y-m-d h:i:sa');
			$data2['cs_msg_type']= 0;
			$data2['cs_msg_text'] = $this->input->post('askHR_details');
			$data2['cs_msg_text'] = $this->security->xss_clean($data2['cs_msg_text']);
			
			if( $this->isSetNotEmpty($_FILES)){
						
				$files = $_FILES;					
				// config of file upload 
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

					// Get extention
					$ext = pathinfo( $_FILES['to_upload']['name'], PATHINFO_EXTENSION );
					$uniq_id = uniqid();

					$upload_config['file_name'] = $this->user->empID .'_'. $uniq_id .'_'. $x .'.'.$ext;
					
					$this->upload->initialize($upload_config);
					
						if( ! $this->upload->do_upload('to_upload') ){
							$error_data[$x] = $this->upload->display_errors();
						} else {
							$upload_data[$x] = $this->upload->data();
					}

				} 

				//if file is invalid
                if( isset($error_data) AND !empty($error_data) ){
                    $data['error'] = '';
					foreach( $error_data as $key1 => $val1 ){
						$data['error'] .= $val1 ."\n";
					}						
				}

				// If file is valid
				if( isset($upload_data) AND !empty($upload_data) ){
					
					foreach( $upload_data as $key => $val ){
						$docs_url[] = $val['full_path'];
					}

					$data2['cs_msg_attachment'] = json_encode( $docs_url );
					$data2['cs_msg_attachment'] = addslashes($data2['cs_msg_attachment']);
				}					
			
			}

			// If no file upload
			else{
				$data2['cs_msg_attachment'] = null;

				} // End file upload
									
					if(isset($data2['cs_msg_postID_fk'])){

						// Insert data2 to hr_cs_msg
						$this->ask_hr->askhr('hr_cs_msg',$data2);

			 
					}

					$data3['msg_newID']= 0;

					$data3['msg_newID']= $this->ask_hr->max_id();
					$this->load->view('askHR_submissionpage',$data3);

			}

			else{
				$data['content']='askHR_submissionpage';
				$this->load->view('includes/templatecolorbox',$data);
				}

			}
						
        } 

            public function HrHelpDesk()
            {
            	//for viewing
            	$data['content']='hr_helpdesk';
            	$empuser = $this->user->username;
   				
   				//mother of query
				$select_query = 'hr_cs_post.*, hr_cs_msg.*, staffs.fname, staffs.lname, CONCAT(staffs.fname, " ", staffs.lname) AS "customer", assign_category.*';
				$join_query = 'INNER JOIN staffs ON staffs.empID = hr_cs_post.cs_post_empID_fk LEFT JOIN hr_cs_msg ON hr_cs_msg.cs_msg_postID_fk = hr_cs_post.cs_post_id LEFT JOIN assign_category ON assign_category.categorys = hr_cs_post.assign_category';

				$all_tickets = $this->dbmodel->getSQLQueryResults("SELECT $select_query, MAX(cs_msg_date_submitted) AS 'last_update' FROM hr_cs_post $join_query GROUP BY cs_msg_postID_fk ORDER BY hr_cs_post.cs_post_id");
				//dd($all_tickets);
				foreach( $all_tickets as $ticket ){

					//for new incidents
					if( $ticket->cs_post_status == 0 ){
						$data['NewIncidentFull'][] = $ticket; 
						//unassigned new tickets
						if( $ticket->report_related == 0 ){
 							$data['NewIncidentHR'][] = $ticket;
 						} else if( $ticket->report_related == 1 ){
 							$data['NewIncidentAcc'][] = $ticket;
 						}
					}

					//for active incidents
					if( $ticket->cs_post_status == 1 ){




						$data['ActiveIncidentFull'][] = $ticket; 
						//unassigned new tickets
						if( $ticket->report_related == 0 ){
 							$data['ActiveIncidentHR'][] = $ticket;
 						} else if( $ticket->report_related == 1 ){
 							$data['ActiveIncidentAcc'][] = $ticket;
 						} 
					}

					//for resolve incidents
					if( $ticket->cs_post_status == 3 ){
						//unassigned new tickets
						$data['ResolveIncidentFull'][] = $ticket; 						

						if( $ticket->report_related == 0 ){
 							$data['ResolveIncidentHR'][] = $ticket;
 						} else if( $ticket->report_related == 1 ){
 							$data['ResolveIncidentAcc'][] = $ticket;
 						}
					}

					//for cancel incidents
					if( $ticket->cs_post_status == 4 OR $ticket->cs_post_status == 5 ){
						$data['CancelIncidentFull'][] = $ticket;
						//unassigned new tickets
						if( $ticket->report_related == 0 ){
 							$data['CancelIncidentHR'][] = $ticket;
 						} else if( $ticket->report_related == 1 ){
 							$data['CancelIncidentAcc'][] = $ticket;
 						} 
					}

					if( $ticket->hr_own_empUSER == $empuser AND $ticket->incident_status == 1 ){
						$data['Canceltransfer'][] = $ticket;	
					}

					if( $ticket->cs_post_agent == $this->user->empID ){
						$data['MyTicket'][] = $ticket;
					}
				
				}

				

				// get the name of all hr employee
				$data['getHRlist']=$this->ask_hr->getdata('username, lname, fname, empID','staffs','access LIKE "%hr%" AND active = 1 AND username NOT IN ("jnapoles", "vemeterio")' );
				// get the name of all accounting employee
				$data['getACClist']=$this->ask_hr->getdata('username, lname, fname, empID','staffs','access LIKE "%finance%" AND active = 1');
				// get the name of all Full Access employee
				$data['getFULLlist']=$this->ask_hr->getdata('username, lname, fname, empID','staffs','access IN ("hr","finance","full") AND active = 1');
				//get all department infomation
				$data['department_email'] = $this->ask_hr->getdata('dept_emil_id,email,department','redirection_department');

				$data['all_staff'] = $this->commonM->_getAllStaff('username');

				$data['all_staff_empID'] = $this->commonM->_getAllStaff('empID');
				//dd($data);
				//$this->output->enable_profiler(true);
				$categories = $this->ask_hr->getdata('categorys, category_id', 'assign_category');
				foreach($categories as $category){
					$data['categories'][$category->category_id] = $category->categorys;
				}

				$data['category'] = $categories;
				$this->load->view('includes/template',$data);



            }//end of HrHelpDesk function

		public function HrIncident(){

			$data['content']='hr_incidentinfo_';

			$insedent_id = $this->uri->segment(3);

			$data['all_staff'] = $this->commonM->_getAllStaff('username');
			$data['all_staff_empID'] = $this->commonM->_getAllStaff('empID');

			$data['ticket'] = $this->dbmodel->getSingleInfo('hr_cs_post', 'hr_cs_post.*, s.fname, s.lname, n.title, n.dept, (SELECT CONCAT(fname, " ", lname) FROM staffs ss WHERE ss.empID = s.supervisor ) AS "supervisor", MAX( hr_cs_msg.cs_msg_date_submitted ) AS last_update, post_id', 'cs_post_id = '.$insedent_id, 'LEFT JOIN staffs s ON s.empID = hr_cs_post.cs_post_empID_fk LEFT JOIN newPositions n ON n.posID = s.position LEFT JOIN hr_cs_msg ON cs_msg_postID_fk = cs_post_id LEFT JOIN incident_rating ON post_id = cs_post_id');

			$categories = $this->ask_hr->getdata('categorys, category_id', 'assign_category');
			foreach($categories as $category){
				$data['categories'][$category->category_id] = $category->categorys;
			}

			$data['category'] = $categories;
			
			$checkRemark = $this->ask_hr->getdata('*','incident_rating','post_id = '.$insedent_id);

			if($checkRemark != null){
				$return = $checkRemark;
			}else{
				$return = 0;
			}
			$data['check_remark'] = $return;

			//check if we have reply
			if( $this->input->post() ){
				//dd($_POST);
				//if we have attachment upload
				//dd($_FILES);

				//ratings
				if( null !== $this->input->post('rating') AND !empty($this->input->post('rating')) ){
					$insert_array['post_id'] = $this->input->post('ticket_id');
					$insert_array['remark'] = $this->input->post('remark');
					$insert_array['post_empID'] = $this->input->post('who_posted');
					$insert_array['date_submited'] = date('Y-m-d H:i:s');
					$insert_array['last_update'] = date('Y-m-d H:i:s');
					$insert_array['rating'] = $this->input->post('rating');

					$this->dbmodel->insertQuery('incident_rating', $insert_array);
					
					$this->_addNote( $this->input->post('ticket_id'), 'Remark has been posted');
				} else {
				 	if( !empty($_FILES['arr_attachments']['name'][0]) ){
						
						$files = $_FILES;					
						// config of file upload 
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

							// Get extention
							$ext = pathinfo( $_FILES['to_upload']['name'], PATHINFO_EXTENSION );
							$uniq_id = uniqid();

							$upload_config['file_name'] = $this->input->post('ticket_id') .'_'. $uniq_id .'_'. $x .'.'.$ext;
							
							$this->upload->initialize($upload_config);
							
							if( ! $this->upload->do_upload('to_upload') ){
								$error_data[$x] = $this->upload->display_errors();
							} else {
								$upload_data[$x] = $this->upload->data();
							}

						} 

						//if file is invalid
		                if( isset($error_data) AND !empty($error_data) ){
		                    	$data['error'] = '';
							foreach( $error_data as $key1 => $val1 ){
								$data['error'] .= $val1 ."\n";
							}						
						}

						// If file is valid
						if( isset($upload_data) AND !empty($upload_data) ){						
							foreach( $upload_data as $key => $val ){
								$docs_url[] = $val['file_name'];
							}
							$insert_array['cs_msg_attachment'] = json_encode( $docs_url );
							
						}					
					
					} //end upload
				
					if( !isset($data['error']) OR empty($data['error']) ){
						$insert_array['cs_msg_postID_fk'] = $this->input->post('ticket_id');

						if( !empty($this->input->post('reply_msg')) ){

							//assign if first reply
							if( $this->input->post('who_posted') != $data['ticket']->cs_post_empID_fk ){
								if ( $data['ticket']->cs_post_status == 0 ) {
									$update_array['cs_post_status'] = 1;
									$update_array['cs_post_agent'] = $this->user->empID;
									$msg = 'Ticket assigned to '.$data['all_staff_empID'][ $this->user->empID ]->name;
								}	
							}
							


							if( $this->input->post('who_posted') == $data['ticket']->cs_post_empID_fk ){
								$insert_array['cs_msg_type'] = 0;
							} else {
								$insert_array['cs_msg_type'] = 1;
							}
							$insert_array['cs_msg_text'] = $this->input->post('reply_msg');

							//reopen
							if( $data['ticket']->cs_post_status == 5 ){
								$this->dbmodel->updateQuery('hr_cs_post', 'cs_post_id = '. $this->input->post('ticket_id'), ['cs_post_status' => 1] );
								//add note
								$this->_addNote($this->input->post('ticket_id'), 'Reopened by '.$data['all_staff_empID'][ $this->input->post('who_posted')]->name );
							}
						}
						if( !empty($this->input->post('note_msg')) ){
							$insert_array['cs_msg_type'] = 2;
							$insert_array['cs_msg_text'] = $this->input->post('note_msg');
						}


						$insert_array['reply_empUser'] = $this->input->post('reply_empUser');
						$insert_array['cs_msg_date_submitted'] = date('Y-m-d H:i:s');
						
						$this->dbmodel->insertQuery('hr_cs_msg', $insert_array);

						if( $this->input->post('resolution') == 5 ){
							$update_array['cs_post_status'] = 3;
							$msg = 'Marked as Resolved.';
						} else if( $this->input->post('resolution') == 6 ){
							$update_array['cs_post_status'] = 5;
							$msg = 'Marked as Cancelled.';
						}
						if( isset($update_array) AND !empty($update_array) ){
							
							$this->dbmodel->updateQuery('hr_cs_post', 'cs_post_id = '. $this->input->post('ticket_id'), $update_array );
							//add note
							$this->_addNote($this->input->post('ticket_id'), $msg);
						}
					}

				}
				$data['redirect'] = true;
			}
			$data['conversations'] = $this->ask_hr->getdata('*','hr_cs_msg','cs_msg_postID_fk = '.$insedent_id);
			$this->load->view('includes/templatecolorbox',$data);


		}
		private function _addNote( $ticket_id, $note ){
			$insert_array = [];
			$insert_array['cs_msg_postID_fk'] = $ticket_id;
			$insert_array['cs_msg_date_submitted'] = date('Y-m-d H:i:s');

			$insert_array['cs_msg_text'] = $note;
			$insert_array['cs_msg_type'] = 2;

			$this->dbmodel->insertQuery('hr_cs_msg', $insert_array);
		}

		public function update(){
			$ticket_id = $this->uri->segment(3);
			if( $this->input->is_ajax_request() ){
				$all_staff_empID = $this->commonM->_getAllStaff('empID');

				switch ($this->input->post('which')) {
					case 'category':
						$this->dbmodel->updateQuery('hr_cs_post', 'cs_post_id = '. $ticket_id, ['assign_category' => $this->input->post('category') ]);
						$this->_addNote( $ticket_id, 'Category reassigned to '. $this->input->post('category') );

						switch( $this->input->post('category') ){
							case "Health Insurance":
							case "Maxicare":							
								$update_array['cs_post_agent'] = 526;
								$update_array['hr_own_empUSER'] = 'kbriones';
							break;
							case "Recruitment Related":
								$update_array['cs_post_agent'] = 473;
								$update_array['hr_own_empUSER'] = 'kbagao';
							break;
							case "Benefits-SSS":
							case "Benefits-Philhealth":
							case "Benefits-Pag-IBIG":
								$update_array['cs_post_agent'] = 522;
								$update_array['hr_own_empUSER'] = 'jnapoles';
							break;
							case "Benefits-Leave Application":
							case "Benefits-Medical Reimbursement":
							case "Facilities and Maintenance":
								$update_array['cs_post_agent'] = 530;
								$update_array['hr_own_empUSER'] = 'vemeterio';
							break;
						}
						if( isset($update_array) AND !empty($update_array) ){
							$this->dbmodel->updateQuery('hr_cs_post', 'cs_post_id = '. $ticket_id, $update_array);
							$this->_addNote( $ticket_id, 'Reassigned to '. $all_staff_empID[ $update_array['cs_post_agent'] ]->name );	
						}
						


						break;
					case 'urgency':
						$date_submitted = $this->dbmodel->getSingleField('hr_cs_post', 'cs_post_date_submitted', 'cs_post_id = '. $ticket_id);
						$this->dbmodel->updateQuery('hr_cs_post', 'cs_post_id = '. $ticket_id, ['cs_post_urgency' => $this->input->post('urgency') ], ['due_date' => $this->_computeDueDate( $date_submitted, $this->input->post('urgency') ) ]);
						$this->_addNote( $ticket_id, 'Urgency reassigned.');


					default:
						# code...
						break;
				}
				
				echo json_encode(['success']);
				exit();
			}
		}

		//helper function that will compute the due_date based on urgency
		private function _computeDueDate( $today, $urgency )
		{
			
			switch( $urgency ){
				case "Urgent": $time = 'P1D';
				break;
				case "Not Urgent": $time = 'P3D';
				break;
				case "Need Attention": $time = 'P2D';
				break;
				default: $time = 'P3D';
				break;				
			}
			//update due date when based on urgency
			$endDateObj = new DateTime($today);
			$endDateObj->add( new DateInterval($time) );

			$endDate = $endDateObj->format('Y-m-d');
			return $endDate;
		}

		public function testPage()
		{
			$today = date('Y-m-d H:i:s');
			$urgency = 'Urgent';
			$due_date = $this->_computeDueDate( $today, $urgency );
			dd($due_date);
		}

		//end of HrIncident function

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

         	// Add internal note ajax
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

            // Add internal note form submission
            public function add_internal_note(){

            	$incident_id = $this->input->post('incident_id');

        		$data['cs_msg_postID_fk'] =  $incident_id;

        		$data['cs_msg_text']=  $this->input->post('internal_note_textarea');
        		$data['cs_msg_text'] = $this->security->xss_clean($data['cs_msg_text']);
        		$data['cs_msg_date_submitted'] = date('Y-m-d h:i:sa');
        		$data['cs_msg_type'] = 2;
        		$data['reply_empUser']  = $this->input->post('reply_username');
        		
        		$this->ask_hr->askhr('hr_cs_msg',$data);

        		$assign_category = $this->input->post('assign_category');		    
        		$this->ask_hr->updatestatus('hr_cs_post','assign_category = "'. $assign_category .'"','cs_post_id = '.$incident_id);

        		//$data2['content']='hr_helpdesk';
  				//$this->load->view('includes/template',$data2);
  				redirect('hr_cs/HrHelpDesk');
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
				
	  			$this->load->view('includes/template',$data);

            }
            
              

            function test(){ // this for testing function
            	$data['content']='employee_incident_info';
	  			$this->load->view('includes/template',$data);
            }// end test function

} // end of class



  ?>
	
	

