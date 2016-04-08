<?php
	if ( ! defined('BASEPATH')) exit('No direct script access allowed');

	class Hr_cs extends MY_Controller{


		public function __construct(){
		parent::__construct();
		
		$this->load->model('ask_hr', 'ask');		
		
			}

		public function index(){
            $data['content']='askHR_submissionpage';
			$this->load->view('includes/templatecolorbox',$data);
		}

		public function askhr(){
			if(isset($_POST) AND !empty($POST)){
                var_dump($_POST);
                exit();
			//checking if there is session data
			$empID = $this->user->empID;
			$subj = $this->input->post('hr_subject');
			$cc = $this->input->post('hr_cc');
			
			$data =array('cs_post_empID_fk'=>$empID,
						  'cs_post_subject'=>$subj,
						  'cd_post_other_empID_fk'=>$cc,
						  'cs_post_date_submitted'=>date('Y-m-d'),
					  'cs_post_status'=>0);
			/*
			if($this->ask->askhr($data)){
			$details = $this->input->post('hr_details');
			$rslt = $this->ask->get_new_max_ID();*/

			// getting posted data
			/* $data['cs_post_empID_fk'] = $empID;
			$data['cs_post_subject'] = $this->input->post('hr_subject');
			$data['cd_post_other_empID_fk']= $this->input->post('hr_cc');
			$data['cs_post_date_submitted']= date('Y-m-d');
			$data['cs_post_status']= 0; */

			$data2['cs_msg_postID_fk'] = $this->ask->askhr($data);
			$data2['cs_msg_date_submitted']=$data['cs_post_date_submitted'];
			$data2['cs_msg_type']=1;
			$data2['cs_msg_text']= $this->post('hr_details');

			}
			if( $this->isSetNotEmpty($_FILES) )
			{
						$files = $_FILES;					
						/* config data */
						$upload_config['upload_path'] = FCPATH .'uploads/medrequest';
						$upload_config['allowed_types'] = 'gif|jpg|png|pdf|docx|doc';
						$upload_config['max_size']	= '2048';
						$upload_config['overwrite']	= 'FALSE';					
						$this->load->library('upload');	


						$upload_count = count( $_FILES['attachment']['name'] );
						for( $x = 0; $x < $upload_count; $x++ )
						{
							$_FILES['to_upload']['name'] = $files['attachment']['name'][$x];
							$_FILES['to_upload']['type'] = $files['attachment']['type'][$x];
							$_FILES['to_upload']['tmp_name'] = $files['attachment']['tmp_name'][$x];
							$_FILES['to_upload']['error'] = $files['attachment']['error'][$x];
							$_FILES['to_upload']['size'] = $files['attachment']['size'][$x];

							$ext = pathinfo( $_FILES['to_upload']['na*me'], PATHINFO_EXTENSION );
							$uniq_id = uniqid();
							$upload_config['file_name'] = $this->user->empID .'_'. $uniq_id .'_'. $x .'.'.$ext;
							
							$this->upload->initialize( $upload_config );
							
							if( ! $this->upload->do_upload('to_upload') ){
								$error_data[$x] = $this->upload->display_errors();
							} else {
								$upload_data[$x] = $this->upload->data();
							}

						}

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
							$data2['cs_msg_attachment'] = addslashes($insert_array['cs_msg_attachment']);
						}					
					} // end of file upload


					if(isset($data2['cs_msg_attachment']))
					{
						$data3['msg_newID']=$this->ask->insertQuery('hr_cs_msg',$data2);
					}

                    $this->output->enable_profiler(true);
				}








}



  ?>
	
	

