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
						$data3['msg_newID']=$this->ask_hr->askhr('hr_cs_msg',$data2);

			 
					}
					
					$this->load->view('askHR_submissionpage',$data3);

					//redirect($this->config->base_url(), 'refresh');
				
            } // end of askhr function

            public function HrHelpDesk()
            {
            	//for viewing
            	$data['content']='hr_helpdesk';
            	
				
				//getting data from db
				//for New incident data HR HELP DESK
				$data['HrHelpDesk']=$this->ask_hr->hrhelpdesk('hr_cs_post.cs_post_id,staffs.fname,hr_cs_post.cs_post_date_submitted,hr_cs_post.cs_post_subject,hr_cs_post.cs_post_urgency','hr_cs_post','LEFT JOIN staffs ON staffs.empID = hr_cs_post.cs_post_empID_fk AND hr_cs_post.cs_post_status = 0','hr_cs_post.cs_post_id');


				$this->load->view('includes/template',$data);



            }//end of HrHelpDesk function

            public function HrIncident(){
            	
            	$data['content']='hr_incidentinfo';

	            	if($this->input->get('id') != ''){
	            		$insedent_id = $this->input->get('id');
	            	}else{
	            		$insedent_id = $this->input->get('postid');
	            	}

            	

            	$data['HrIncident']=$this->ask_hr->hrhelpdesk('hr_cs_post.cs_post_id,staffs.fname,staffs.lname,hr_cs_post.cs_post_date_submitted,hr_cs_post.cs_post_subject,hr_cs_post.cs_post_urgency,hr_cs_msg.cs_msg_text','hr_cs_post','INNER JOIN staffs ON staffs.empID = hr_cs_post.cs_post_empID_fk INNER JOIN hr_cs_msg ON hr_cs_msg.cs_msg_postID_fk = hr_cs_post.cs_post_id AND hr_cs_post.cs_post_id ='.$insedent_id);

            		$data['category'] = $this->ask_hr->getdata('categorys','assign_category');

            	
					$this->load->view('includes/templatecolorbox',$data);
            }//end of HrIncident function

	        function addcategory(){
	            	$data['categorys'] = $this->input->post('category_name');
	            	
	  			   	$this->ask_hr->askhr('assign_category',$data);
	  			   	$data2['content']='hr_incidentinfo';
	  			  	$this->load->view('includes/templatecolorbox',$data2);

            }// end insertion new category

            public function found_answer_solution(){

            	       
            		$data['insedent_id'] = $this->input->post('insedentid');
            		$data['assign_category'] = $this->input->post('assign_category');
            		$data['type_solution'] = $this->input->post('typ_solution');

            		$data['sulotion_link'] = $this->input->post('found_answer_link');
            		$data['sulotion_link'] = $this->security->xss_clean($data['sulotion_link']);

					$data['message'] = $this->input->post('found_answer_custom');
            		$data['message'] = $this->security->xss_clean($data['message']);
            		

            	$this->ask_hr->askhr('insedent_answer',$data);
            	$data2['content']='hr_helpdesk';
	  			$this->load->view('includes/template',$data2);


            }
            public function custom_answer_solution(){
            	

            		$data['insedent_id'] = $this->input->post('insedentid');
            		$data['assign_category'] = $this->input->post('assign_category');
            		$data['type_solution'] = $this->input->post('typ_solution');

            		$data['message'] =  $this->input->post('found_answer_custom');
            		$data['message'] = $this->security->xss_clean($data['message']);

            		$this->ask_hr->askhr('insedent_answer',$data);
            		$data2['content']='hr_helpdesk';
	  				$this->load->view('includes/template',$data2);

            }

            public function notfound_answe_solution(){
            
            		$data['insedent_id'] = $this->input->post('insedentid');
            		$data['redirect_link'] = $this->input->post('redirect_department');           		
            		$data['assign_category'] = $this->input->post('assign_category');
            		$data['type_solution'] = $this->input->post('typ_solution');

            		$data['message'] = $this->input->post('notfound_answer_custom');
            		$data['message'] = $this->security->xss_clean($data['message']);

            		$this->ask_hr->askhr('insedent_answer',$data);
            		$data2['content']='hr_helpdesk';
	  				$this->load->view('includes/template',$data2);

            }

           

            function test(){ // this for testing function
            	$data['category'] = $this->ask_hr->getdata('categorys','assign_category');
            	echo json_encode($data);
            }// end test function

} // end of class



  ?>
	
	

