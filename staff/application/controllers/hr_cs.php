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

			/*$this->load->flibrary('form_validation');
			$this->form_validation->set_rules('cs_post_subject','cs_post_subject','required');
			$this->form_validation->set_rules('cs_post_subject','cs_post_subject','required');*/

			
			$data['cs_post_empID_fk'] = $empID;
			$data['cs_post_subject'] = $this->input->post('cs_post_subject');
			$data['cs_post_urgency']= $this->input->post('cs_post_urgency');
			$data['cs_post_date_submitted']= date('Y-m-d h:i:sa');
			$data['cs_post_status']= 0; 
			
			$data2['cs_msg_postID_fk'] = $this->ask_hr->askhr('hr_cs_post',$data);
			$data2['cs_msg_date_submitted']= date('Y-m-d h:i:sa');
			$data2['cs_msg_type']=1;
			$data2['cs_msg_text'] = $this->input->post('askHR_details');
			$data2['cs_msg_text'] = $this->security->xss_clean($data2['cs_msg_text']);

			
			if( $this->isSetNotEmpty($_FILES)){
						$files = $_FILES;					
						// config data 
						$upload_config['upload_path'] = FCPATH .'uploads/cs_hr_attachments';
						$upload_config['allowed_types'] = 'gif|jpg|png|pdf|docx|doc';
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

					//redirect($this->config->base_url(), 'refresh');
				
            } // end of askhr function

            public function HrHelpDesk()
            {
            	//for viewing
            	$data['content']='hr_helpdesk';
            	
				
				//getting data from db
				//for New incident data HR HELP DESK
				$data['NewIncident']=$this->ask_hr->hrhelpdesk('hr_cs_post.cs_post_id, staffs.fname, hr_cs_post.cs_post_date_submitted, hr_cs_post.cs_post_subject, hr_cs_post.cs_post_urgency, hr_cs_msg.cs_msg_text','hr_cs_post','INNER JOIN staffs ON staffs.empID = hr_cs_post.cs_post_empID_fk LEFT JOIN hr_cs_msg ON hr_cs_msg.cs_msg_postID_fk = hr_cs_post.cs_post_id WHERE hr_cs_post.cs_post_status = 0 GROUP BY cs_msg_postID_fk HAVING COUNT(cs_msg_postID_fk) = 1','hr_cs_post.cs_post_id');

			

				$data['ActiveIncident']=$this->ask_hr->hrhelpdesk('hr_cs_msg.cs_msg_postID_fk, hr_cs_post.cs_post_id, staffs.fname, staffs.lname, hr_cs_post.cs_post_date_submitted, hr_cs_post.assign_category, hr_cs_post.cs_post_subject, hr_cs_post.cs_post_urgency, assign_category.assign_sla, hr_cs_post.hr_own_empUSER, MAX( hr_cs_msg.cs_msg_date_submitted ) AS last_update','hr_cs_post','INNER JOIN staffs ON staffs.empID = hr_cs_post.cs_post_empID_fk INNER JOIN hr_cs_msg ON hr_cs_msg.cs_msg_postID_fk = hr_cs_post.cs_post_id LEFT JOIN assign_category ON assign_category.categorys = hr_cs_post.assign_category GROUP BY hr_cs_msg.cs_msg_postID_fk HAVING COUNT( * ) >1');


				$this->load->view('includes/template',$data);



            }//end of HrHelpDesk function

            public function HrIncident(){
            	
            	$data['content']='hr_incidentinfo';

	            	if($this->input->get('id') != ''){
	            		$insedent_id = $this->input->get('id');
	            	}else{
	            		$insedent_id = $this->input->get('postid');
	            	}
                $insedent_id = $this->uri->segment(3);
            	

            	$data['HrIncident']=$this->ask_hr->hrhelpdesk('hr_cs_post.cs_post_id,staffs.fname,staffs.lname,hr_cs_post.cs_post_date_submitted,hr_cs_post.cs_post_subject,hr_cs_post.cs_post_urgency,hr_cs_msg.cs_msg_text','hr_cs_post','INNER JOIN staffs ON staffs.empID = hr_cs_post.cs_post_empID_fk INNER JOIN hr_cs_msg ON hr_cs_msg.cs_msg_postID_fk = hr_cs_post.cs_post_id AND hr_cs_post.cs_post_id ='.$insedent_id);

            		$data['category'] = $this->ask_hr->getdata('categorys','assign_category');
            		$data['department_email'] = $this->ask_hr->getdata('email,department','redirection_department');


            	
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

            public function found_answer_solution(){


            		$id = $this->input->post('insedentid');

               		$link = $this->input->post('found_answer_link');
            		$message = $this->input->post('found_answer_custom');
            		$cus_message = $this->security->xss_clean($message);

            		
            		$data['cs_msg_postID_fk'] =  $id;
            		$data['cs_msg_text'] = $link.'<br>'.$cus_message;
            		$data['cs_msg_date_submitted'] = date('Y-m-d h:i:sa');
            		
					$this->ask_hr->askhr('hr_cs_msg',$data);

					
            		$categ = $this->input->post('assign_category');

            		if($categ != ''){
            		$id_msg = $this->input->post('categid');
					$this->ask_hr->updatestatus('hr_cs_post','assign_category = "'. $categ .'"','cs_post_id = '.$id_msg);
            		}

            		$empUSER = $this->input->post('hr_username');
	            	$this->ask_hr->updatestatus('hr_cs_post','hr_own_empUSER = "' .$empUSER. '"','cs_post_id = '.$id_msg);


	            	$data3['content']='hr_helpdesk';
		  			$this->load->view('includes/template',$data3);


            }
            public function custom_answer_solution(){
            		$id = $this->input->post('insedentid');

            		$data['cs_msg_postID_fk'] =  $id;
            		$custommessage=  $this->input->post('custom_answer_msg');
            		$data['cs_msg_text'] = $this->security->xss_clean($custommessage);
            		$data['cs_msg_date_submitted'] = date('Y-m-d h:i:sa');
            		
            		$this->ask_hr->askhr('hr_cs_msg',$data);

            		$id_msg = $this->input->post('customcategid');
	            	$categ = $this->input->post('assign_category');
	            	
	            	if($categ != ''){
						$this->ask_hr->updatestatus('hr_cs_post','assign_category = "'. $categ .'"','cs_post_id = '.$id_msg);
					}

					$empUSER = $this->input->post('hr_username');
	            	$this->ask_hr->updatestatus('hr_cs_post','hr_own_empUSER = "' .$empUSER. '"','cs_post_id = '.$id_msg);

            		$data2['content']='hr_helpdesk';
	  				$this->load->view('includes/template',$data2);

            }

            public function notfound_answe_solution(){
            
            		$id = $this->input->post('insedentid');

            		$direct_email = $this->input->post('redirect_department'); 
            		$message = $this->input->post('notfound_answer_custom');
            		$cus_message = $this->security->xss_clean($message);

            		$data['cs_msg_postID_fk'] =  $id;
            		$data['cs_msg_text'] = $direct_email." <br>".$cus_message;
            		$data['cs_msg_date_submitted'] = date('Y-m-d h:i:sa');
            
            		$this->ask_hr->askhr('hr_cs_msg',$data);

            		$id_msg = $this->input->post('notfoundcategid');
	            	$categ = $this->input->post('assign_category');
	            	if($categ != ''){
	            		$this->ask_hr->updatestatus('hr_cs_post','assign_category = "' .$categ. '"','cs_post_id = '.$id_msg);
	            	}

	            	$empUSER = $this->input->post('hr_username');
	            	$this->ask_hr->updatestatus('hr_cs_post','hr_own_empUSER = "' .$empUSER. '"','cs_post_id = '.$id_msg);	

            		$data2['content']='hr_helpdesk';
	  				$this->load->view('includes/template',$data2);

            }

             public function further_investigation(){
            	

            		$id = $this->input->post('insedentid');

            		$data['cs_msg_postID_fk'] =  $id;
            		$data['cs_msg_text'] =  $this->input->post('found_answer_custom');
            		$data['cs_msg_text'] = $this->security->xss_clean($data['cs_msg_text']);
            		$data['cs_msg_date_submitted'] = date('Y-m-d h:i:sa');

            		$this->ask_hr->askhr('hr_cs_msg',$data);

					$id_msg = $this->input->post('furthercategid');
	            	$categ = $this->input->post('assign_category');
	            	if($categ != ''){
	            		$this->ask_hr->updatestatus('hr_cs_post','assign_category = "' .$categ. '"','cs_post_id = '.$id_msg);
	            	}

	            	$empUSER = $this->input->post('hr_username');
	            	$this->ask_hr->updatestatus('hr_cs_post','hr_own_empUSER = "' .$empUSER. '"','cs_post_id = '.$id_msg);

            		$data2['content']='hr_helpdesk';
	  				$this->load->view('includes/template',$data2);

            }

            function hr_custom_satisfaction(){
            	$data['content']='hr_cust_satisfaction_survey';
	  			$this->load->view('includes/template',$data);

            }

           

            function test(){ // this for testing function
            	$data['category'] = $this->ask_hr->max_id();
            	echo json_encode($data);
            }// end test function

} // end of class



  ?>
	
	

