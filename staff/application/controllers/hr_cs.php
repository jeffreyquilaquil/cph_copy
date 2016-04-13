<?php
	if ( ! defined('BASEPATH')) exit('No direct script access allowed');

	class Hr_cs extends MY_Controller{


		public function __construct(){
		parent::__construct();
		
		$this->load->model('ask_hr');		
		
			}

		public function index(){
            $data['content']='askHR_submissionpage';
            $data['msg_newID']=0;
			$this->load->view('includes/templatecolorbox',$data);
		} // end of index function

		public function askhr(){
			//if(isset($_POST) AND !empty($POST)){

			//checking if there is session data
			$empID = $this->user->empID;
			/*$subj = $this->input->post('hr_subject');
			$cc = $this->input->post('hr_cc');
			
			$data =array('cs_post_empID_fk'=>$empID,
						  'cs_post_subject'=>$subj,
						  'cd_post_other_empID_fk'=>$cc,
						  'cs_post_date_submitted'=>date('Y-m-d'),
					  'cs_post_status'=>0);
			
			if($this->ask->askhr($data)){
			$details = $this->input->post('hr_details');
			$rslt = $this->ask->get_new_max_ID();*/
			// getting posted data
			$data['cs_post_empID_fk'] = $empID;
			$data['cs_post_subject'] = $this->input->post('cs_post_subject');
			$data['cs_post_urgency']= $this->input->post('cs_post_urgency');
			$data['cs_post_date_submitted']= date('Y-m-d');
			$data['cs_post_status']= 0; 
			
			$data2['cs_msg_postID_fk'] = $this->ask_hr->askhr('hr_cs_post',$data);
			$data2['cs_msg_date_submitted']= date('Y-m-d');
			$data2['cs_msg_type']=1;
			$data2['cs_msg_text']= $this->input->post('askHR_details');

			//}
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
            	$data['content']='hr_helpdesk';;
            	
				$this->load->view('includes/template',$data);
				//getting data from db
				//for New incident data
				$data['HrHelpDesk']=$this->ask_hr->hrhelpdesk('hr_cs_post','*','cs_post_status = 0','LEFT JOIN staff ON empID = cs_post_empID_fk');
				$data1['HrHelpDesk']=$this->ask_hr->hrhelpdesk('hr_cs_post','*','cs_post_status = 1','LEFT JOIN staff ON empID = cs_post_empID_fk');
				$data2['HrHelpDesk']=$this->ask_hr->hrhelpdesk('hr_cs_post','*','cs_post_status = 2','LEFT JOIN staff ON empID = cs_post_empID_fk');
				$data3['HrHelpDesk']=$this->ask_hr->hrhelpdesk('hr_cs_post','*','cs_post_status = 3','LEFT JOIN staff ON empID = cs_post_empID_fk');
				$data4['HrHelpDesk']=$this->ask_hr->hrhelpdesk('hr_cs_post','*','cs_post_status = 4','LEFT JOIN staff ON empID = cs_post_empID_fk');




            }//end of HrHelpDesk

} // end of class



  ?>
	
	

