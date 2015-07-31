$(function () { 
	$(".imgiframe").colorbox({rel:'PT profile picture'});
	tinymce.init({
		selector: "textarea",	
		menubar : false,
		plugins: [
			"link",
			"code",
			"table"
		],
		toolbar: "undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link table code"
	});
		
	$('.pdetailshide').addClass('hidden');
	$('.jdetailshide').addClass('hidden');
			
	if($('#maritalStatus').val()=='Single'){
		$('#spouse').parents('tr').addClass('hidden');
		$('#dependents').parents('tr').addClass('hidden');
	}
	
	$('#maritalStatus').change(function(){
		if($('#maritalStatus').val() == 'Single'){
			$('#spouse').parents('tr').addClass('hidden');
			$('#dependents').parents('tr').addClass('hidden');
			$('#spouse').val('');
			$('#dependents').val('');
		}else{
			$('#spouse').parents('tr').removeClass('hidden');
			$('#dependents').parents('tr').removeClass('hidden');
		}
	});
	
	$('#title').change(function(){
		alert('Request for changing position title will also request change in department if position belongs to different department.');
	});
	
	$('#nselection').change(function(){
		v = $(this).val();
			$('iframe#iframeNotes').contents().find('.nnotes').removeClass('hidden');
		if(v!=''){
			for(i=0; i<=5; i++){
				if(v!=i)
					$('iframe#iframeNotes').contents().find('.nstat_'+i).addClass('hidden');
			}
		}
	});
	
	$('#ntypeselect').change(function(){
		if($(this).val()=='0'){
			$('#acctype').removeClass('hidden');
		}else{
			$('#acctype').addClass('hidden');
			$('#accesstype').val('');
		}
	});
	
	$('#updatePTpic').click(function(){
		$('#PTpicture').trigger('click');
	});
	$('#PTpicture').change(function(){
		displaypleasewait();
		$('#PTpform').submit();				
	});
		
	$('#rupdateTO').click(function(){
		$('.toTRclass').removeClass('hidden');
	});
	
	$('#updateLC').click(function(){
		$('.updateLCtr').removeClass('hidden');
	});
	
	$('#updateLCButton').click(function(){
		hobbit = $('#correctLC').val();
		if(hobbit==''){
			alert('Correct leave credit is empty.');
		}else if($.isNumeric(hobbit)==false){
			alert('Inputted is invalid.');
		}else{
			displaypleasewait();
			$.post(CAREERURI,{
				submitType:'editleavecredits',					
				newleavecredits:$('#correctLC').val(),
				oldleavecredit:LEAVECREDITS,
				empID:EMPID,
				empName:RNAME
			},function(){
				alert('Leave credits has been updated.');
				parent.location.reload();
			});
		}
	});
	
	
			
	$('#pbtnsubmit').click(function(){
		valid = true;
		var validText = '';
		
		var ssspattern = new RegExp(/^[0-9]{2}-[0-9]{7}-[0-9]{1}$/);
		var tinpattern = new RegExp(/^[0-9]{3}-[0-9]{3}-[0-9]{3}-[0-9]{4}$/); 
		var phpattern = new RegExp(/^[0-9]{2}-[0-9]{9}-[0-9]{1}$/);  
		var hdmfpattern = new RegExp(/^[0-9]{4}-[0-9]{4}-[0-9]{4}$/);
		var emailpattern = new RegExp(/^(("[\w-\s]+")|([\w-]+(?:\.[\w-]+)*)|("[\w-\s]+")([\w-]+(?:\.[\w-]+)*))(@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$)|(@\[?((25[0-5]\.|2[0-4][0-9]\.|1[0-9]{2}\.|[0-9]{1,2}\.))((25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\.){2}(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\]?$)/i);			
		
		if($('#lname').val()==''){ validText += '\t- Last Name is empty.\n'; valid = false; }
		if($('#fname').val()==''){ validText += '\t- First Name is empty.\n'; valid = false; }
		
		if($('#sss').val() != '' &&  ssspattern.test($('#sss').val())==false){
			validText += '\t- SSS Invalid\n';
			valid = false;	
		}
		if($('#tin').val() != '' &&  tinpattern.test($('#tin').val())==false){
			validText += '\t- TIN Invalid\n';
			valid = false;	
		}
		if($('#philhealth').val() != '' &&  phpattern.test($('#philhealth').val())==false){
			validText += '\t- Philhealth Invalid\n';
			valid = false;	
		}
		if($('#hdmf').val() != '' &&  hdmfpattern.test($('#hdmf').val())==false){
			validText += '\t- HDMF Invalid\n';
			valid = false;	
		}
		if($('#pemail').val() != '' &&  emailpattern.test($('#pemail').val())==false){
			validText += '\t- Email address is invalid\n';
			valid = false;	
		}
		
		if(validText != ''){
			valid = false;
			alert('Please check missing/error values: \n'+validText);
		}
		
		if(valid){
			displaypleasewait();
			$.post(CAREERURI,{
				submitType:'pdetails',
				empID:EMPID,
				lname:$('#lname').val(),
				fname:$('#fname').val(),
				mname:$('#mname').val(),
				suffix:$('#suffix').val(),
				pemail:$('#pemail').val(),
				address:$('#address').val(),
				city:$('#city').val(),
				country:$('#country').val(),
				zip:$('#zip').val(),
				phone1:$('#phone1').val(),
				phone2:$('#phone2').val(),
				bdate:$('#bdate').val(),
				gender:$('#gender').val(),
				maritalStatus:$('#maritalStatus').val(),
				spouse:$('#spouse').val(),
				dependents:$('#dependents').val(),
				sss:$('#sss').val(),
				tin:$('#tin').val(),
				philhealth:$('#philhealth').val(),
				hdmf:$('#hdmf').val(),
				skype:$('#skype').val(),
				google:$('#google').val()
			},function(){
				window.location.reload();
			});
		}
	});
	
	$('#jbtnsubmit').click(function(){
		validtxt = '';
		/* var spattern = new RegExp(/^[0-9]{2}:[0-9]{2}[a-z]{2}\s-\s[0-9]{2}:[0-9]{2}[a-z]{2}\s[a-zA-Z]{3}-[a-zA-Z]{3}$/);
		if($('#shift').val()!='' && spattern.test($('#shift').val())==false){
			validtxt = 'Shift Schedule is Invalid. Valid format sample is: 07:00am - 04:00pm Mon-Fri\n';
		} */
		
		if($('#supervisor').val()=='')
			validtxt += 'Immediate supervisor is empty.\n';
		if($('#title').val()=='')
			validtxt += 'Position title is empty.\n';
		if($('#empStatus').val()=='regular' && $('#regDate').val()=='')
			validtxt += 'Regularization date is empty.\n';
		if($('#endDate').val()!='' && ($('#terminationType').val()=='' || $('#terminationType').val()==0))
			validtxt += 'Termination reason is empty.\n';
		
		if(validtxt!=''){
			alert(validtxt);
		}else{
			displaypleasewait();
			$.post(CAREERURI,{
				submitType:'jdetails',
				empID:EMPID,
				office:$('#office').val(),
				holidaySched:$('#holidaySched').val(),
				shift:$('#shift').val(),
				startDate:$('#startDate').val(),
				supervisor:$('#supervisor').val(),
				title:$('#title').val(),
				empStatus:$('#empStatus').val(),
				regDate:$('#regDate').val(),
				endDate:$('#endDate').val(),
				accessEndDate:$('#accessEndDate').val(),
				active:$('#active').val(),
				levelID_fk:$('#levelID_fk').val(),
				terminationType:$('#terminationType').val()
			},function(){
				window.location.reload();
			});
		}
	});
	
	$('#cbtnsubmit').click(function(){			
		displaypleasewait();
		$.post(CAREERURI,{
			submitType:'cdetails',
			empID:EMPID,
			sal:$('#sal').val(),
			allowance:$('#allowance').val(),
			bankAccnt:$('#bankAccnt').val(),
			hmoNumber:$('#hmoNumber').val(),
			taxstatus:$('#taxstatus').val()
		},function(){
			window.location.reload();
		});
	});
	
	$('#tosendtoHR').click(function(){
		if($('#noteHR').val()==''){
			alert('Please input note to HR');
		}else{
			displaypleasewait();
			$.post(CAREERURI,{
				submitType:'uLeaveC',
				note:$('#noteHR').val()
			}, function(){
				window.location.reload();
			});
		}
	});
		 
}); 
	
function addnote(d){
	if(d=='show'){
		$('.traddnote').removeClass('hidden');
		$('.trheadnote').addClass('hidden');
	}else{
		$('.traddnote').addClass('hidden');
		$('.trheadnote').removeClass('hidden');
	}
}

function delFile(id, fname){
	if(confirm('Are you sure you want to delete this file?')){
		displaypleasewait();
		$.post(CAREERURI,{
			submitType:'delFile',
			upID:id,
			fileName:fname
		},function(){
			alert('File has been deleted.');
			location.href=CAREERURI;
		});
	}
}

function cancelRequest(id, f, fname){
	if(confirm('Are you sure you want to cancel this request?')){
		displaypleasewait();
		$.post(BASEURL+"myinfo/",{
			submitType:'cancelRequest',
			updateID:id,
			fld:f,
			fname:fname
		},function(){
			alert('Request has been cancelled.');
			window.location.reload();
		});
	}
}	

function reqUpdate(fld){
	$('.'+fld+'tr').attr('bgcolor','#ccc');
	$('.'+fld+'input').removeClass('hidden');
	$('.'+fld+'last').removeClass('hidden');
	$('.'+fld+'hide').removeClass('hidden');
	
	$('.'+fld+'fld').addClass('hidden');	
	$('.'+fld+'show').addClass('hidden');
	$('#'+fld+'upb').addClass('hidden');
}
	
function reqUpdateCancel(fld){
	$('.'+fld+'tr').attr('bgcolor','');
	$('.'+fld+'input').addClass('hidden');
	$('.'+fld+'last').addClass('hidden');
	$('.'+fld+'hide').addClass('hidden');
	
	$('.'+fld+'fld').removeClass('hidden');	
	$('.'+fld+'show').removeClass('hidden');
	$('#'+fld+'upb').removeClass('hidden');
}
	
function editUploadDoc(id, v){
	if(v==1){
		if($('#uploadDoc_'+id).val()==''){
			alert('Document Name is empty.');
		}else{
			$('#uploadDocimg'+id).show();
			$('button.uploadDoc'+id).hide();
			$.post(CAREERURI, {submitType:'editUploadName', upID:id, docName: $('#uploadDoc_'+id).val() }, 
			function(){
				$('span.upClass_'+id).html($('#uploadDoc_'+id).val());
				$('.uploadDoc'+id).hide();
				$('.upClass_'+id).show();
				$('#uploadDocimg'+id).hide();
			});
		}
	}else{
		$('.uploadDoc'+id).show();
		$('.upClass_'+id).hide();
	}
}

function toggleDisplay(id, jun){
	$('#'+id+'Data').toggle(0, function(){
		$(jun).text($(jun).text() == 'Show' ? 'Hide' : 'Show'); 
		if(id=="jobtbl" || id=="compensationtbl")
			$('#'+id+' a.edit').toggleClass("hidden");
	});
}

function addFile(type){
	$('#'+type+'tbl .pfilei').trigger('click');
}

function formSubmitfile(tbl){
	displaypleasewait();
	$('#'+tbl+'tbl form.pfformi').submit();	
}