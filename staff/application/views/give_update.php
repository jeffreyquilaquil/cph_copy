<style type="text/css">

    .dvstyle{
        padding:30px; border:1px solid #ccc; border-radius:10px; width:45%; cursor:pointer; 
    }
    .dvstyle:hover{
        background-color:#dedede;
    }

    .btn_ans{
        background-color: #CCCCCC; 
        font-weight: bold;
        width: 100%;
        padding: 3px;
    }

    input[type=text]{

        width: 100%;

    }

    a{
        text-decoration: underline;
    }

</style>
<input type="hidden" id="give_up" name="postid" value="give_update">
<input type="hidden" id="give_update_id" name="postid" value="<?php echo $this->uri->segment(3); ?>">
<div id="give_update_form">
    <center style="margin:40px 0;">
        <h1>What Update You Want to Give?</h1>

        <div id="regular_update" class="dvstyle">
            Regular Update
        </div>    


        <div class="dvstyle">
            Request/Follow Up Required Information from Customer    
        </div>

        <div id="resolve_incident" class="dvstyle">
            Resolve Incident
        </div>
    </center>   
</div>

 
<div id="regular_update_form">
<form id="regular_update_f">
    <table class="tableInfo">
        <tr>
            <td>
                <h2>HR Incident Number <?php echo $this->uri->segment(3); ?>
                <br>
                <small>You have owned responsibilty for incident number <?php echo $this->uri->segment(3); ?></small>
                </h2>
            </td>
        </tr>
        <tr>
            <td>Please write below your'e update you want to give the customer:</td>
        </tr>
        <tr>
            <td><textarea id="regular_update_txtareas" style="height:200px;  resize: none;"></textarea></td>
        </tr>
        <tr>
            <td><input id="regular_update_btns" type="button" name="" class="btn_ans" value="Send Update"></td>
        </tr>
        <tr>
            <td><a id="back_regular_update">Back</a></td>
        </tr>
    </table>
    </form>
</div>

<div id="resolve_incident_form">
    <table class="tableInfo">
        <tr>
            <td>
                <h2>HR Incident Number <?php echo $this->uri->segment(3); ?>
                <br>
                <small>You have owned responsibilty for incident number <?php echo $this->uri->segment(3); ?></small>
                </h2>        
            </td>
        </tr>
        <tr>
            <td>
                <div style="background: #CCCCCC; font-weight: bold; width: 100%; padding: 5px 0px 5px 5px">Resolution Options</div>
                
                <br>

                <!-- ===== FOUND ANSWER  ===== -->
                <div id="resol_ans">
                <a id="resol_ans_link">The answer can be found in employee.tatepublishing.net</a>   
                    <div id="resol_ans_container"> 
                            <!--
                            <input id="foundid" type="hidden" name="insedentid" value="<?php echo $value->cs_post_id; ?> ">
                            <input id="foundcategid" type="hidden" name="insedentcategid" value="<?php echo $value->cs_post_id; ?> ">
                            -->
                            <form id="resolve_incident_link_form">
                            <table class="tableInfo">
                                <tr>
                                    <td>
                                        <small>Please place below the link to the page in employee.tatepublishing.net</small>

                                        <br>

                                        <input id="resolve_incident_link" type="text" id="found_answer_link" name="found_answer_link" required>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <small>You may right below an additional custom message to the customer (optional):</small>

                                        <br>

                                        <textarea id="resolve_incident_link_txtarea" style="height:200px;  resize: none;" id="found_answer_custom" name="found_answer_custom"></textarea>

                                        <br><br>

                                        <input id="resolve_incident_link_btn" type="submit" class="btn_ans" value="Resolve Incident">
                                    </td>
                                </tr>
                            </table>

                            <br>

                        </form>
                    </div>          
                </div>
                
            
                <br>
                
                <!-- ===== CUSTOM ANSWER  ===== -->
                <div id="resol_ans">
                    <a id="resol_ans_link">Send custom resolution response</a>  
                        <div id="resol_ans_container"> 
                            <form id="resolve_incident_custom_form">
                            <!--
                            <input id="customid" type="hidden" name="insedentid" value="<?php echo $value->cs_post_id; ?> ">
                            <input id="customcategid" type="hidden" name="insedentid" value="<?php echo $value->cs_post_id; ?> ">
                            -->
                                <table class="tableInfo">
                                    <tr>
                                        <td>
                                            <small>Please write below your resolution message to employee:</small>

                                            <br>

                                            <textarea id="resolve_incident_custom_txtarea" name="custom_answer_msg" style="height:200px; resize: none;"></textarea>

                                            <br><br>

                                            <input type="submit" id="resolve_incident_custom_btn" class="btn_ans" value="Resolve Incident">
                                        </td>
                                    </tr>
                                </table>

                                <br>

                            </form>
                        </div>
                </div>  
                
                <br>
                
                <!-- ===== NOT FOUND ANSWER  ===== -->
                <div id="resol_ans">
                    <a id="resol_ans_link">This is not an HR inquiry. Redirect to another department.</a>   
                        <div id="resol_ans_container"> 
                            <!--
                            <input id="notfoundid" type="hidden" name="insedentid" value="<?php echo $value->cs_post_id; ?> ">
                            <input id="notfoundcategid" type="hidden" name="insedentid" value="<?php echo $value->cs_post_id; ?> ">
                            -->
                            <form id="resolve_incident_redirect_form">
                                <table class="tableInfo">
                                    <tr>
                                        <td>To what deparment does this person need to be redirected to?</td>
                                        <td>
                                            <select id="resolve_incident_redirect_department" name="redirect_department" required style="width: 100%">
                                                <option></option>

                                                <?php foreach ($department_email as $k => $v): ?>
                                                <option value="<?php echo $v->email; ?>"><?php echo $v->department." (".$v->email.")"; ?></option>
                                            <?php endforeach ?>

                                            </select>
                                        </td>   
                                    </tr>
                                    <tr>
                                        <td valign="top">Add custom message</td>
                                        <td><textarea id="resolve_incident_redirect_txtarea" name="not_found_custom_msg" style="height:200px; resize: none;" placeholder="<Insert Custom Message Here>"></textarea></td>
                                    </tr>
                                    <tr>
                                        <td></td>
                                        <td> 
                                            <a id="add_redirect_dept">Add redirection department</a>
                                            <input type="submit" id="resolve_incident_redirect_btn" class="btn_ans_small" value="Resolve incident" style="float:right">
                                        </td>
                                    </tr>
                                </table>

                                <br>

                            </form>

                            <!--  ====== ADD A REDIRECTION DEPARTMENT ===== -->
                            <div id="add_redirect_dept_form">
                            <form id="add_new_department_form">
                                <table class="tableInfo">
                                    <tr>
                                        <td colspan="2"><h2>Add a Redirection Department</h2></td>
                                    </tr>
                                    <tr>
                                        <td colspan="2">
                                            <small>Name of the department/team customers can be directed to:</small>

                                            <br>
                                            
                                            <input id="name_department" type="text" required>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2">
                                            <small>What is the email address/es that the employee can</small>

                                            <br>
                                            
                                            <input id="email_department" type="text" required>

                                        </td>
                                    </tr>
                                    <tr>
                                        <td align="left">
                                            <a id="see_all_redirect_dept" class="resol_link">See All Redirection Departments</a>
                                        </td>
                                        <td align="right"><input type="submit" id="submit_add_department" class="btn_ans_small" value="Submit"></td>
                                    </tr>
                                </table>
                                </form>
                            </div>

                            <!-- ====== SEE ALL DIRECTION DEPARTMENTS ===== -->
                            <div id="see_all_redirect_dept_form">
                                <h2>All Direction Departments</h2>
                                <table class="datatable">
                                    <thead>
                                        <tr>
                                            <th>Department Name</th>
                                            <th>Email Address</th>
                                            <th>Edit</th>
                                            <th>Delete</th>
                                    </tr>   
                                    </thead>
                                    <tbody>
                                    <?php foreach ($department_email as $name => $dep): ?>
                                        <tr>
                                            <td><?php echo $dep->department; ?></td>
                                            <td><?php echo $dep->email; ?></td>
                                            <td align="center"><a id="edit_redirect" href="">Edit</a></td>
                                            <td align="center"><a id="delete_redirect" href="">Delete</a></td>
                                        </tr>

                                        <?php endforeach ?>
                                        
                                    </tbody>
                                </table>

                                <br>

                            </div>
                        </div>
                </div>      
            </td>
        </tr>
    </table>
</div>

<script type="text/javascript">
   
    $(document).ready(function(){

         $('#regular_update_form').hide();
         $('#regular_update').click(function(){
            
            $('#give_update_form').hide();
            $('#regular_update_form').show();

         })

        $('#back_regular_update').click(function(){
            
            $('#regular_update_form').hide();
            $('#give_update_form').show();

         })

        $('#resolve_incident_form').hide();
        $('#resolve_incident').click(function(){
            
            $('#give_update_form').hide();
            $('#resolve_incident_form').show();

         })

        // ===== RESOLUTION OTPTIONS =====
        $('div#resol_ans_container').hide();
        $('div#resol_ans').each(function() {

            var $resol_ans = $(this);

            $("a#resol_ans_link", $resol_ans).click(function(e) {
            e.preventDefault();

            $container = $("div#resol_ans_container", $resol_ans);
            $container.toggle();
            $("div#resol_ans_container").not($container).hide();

            return false;
            });

        });

        // ===== ADD REDIRECT DEPARTMENT =====
        $('#add_redirect_dept_form').hide();

        $('#add_redirect_dept').click(function(){
            
            $('#add_redirect_dept_form').slideToggle("slow");
        });
        
        // ===== SEE ALL REDIRECT DEPARTMENT =====
        $('#see_all_redirect_dept_form').hide();

        $('#see_all_redirect_dept').click(function(){
            
            $('#see_all_redirect_dept_form').slideToggle("slow");
        });



    // ===== JQUERY FOR INSERTION REGULAR UPDATE =====
    $("#regular_update_btns").click(function() {
        var giv_up = $("#give_up").val();
        var ins_id = $("#give_update_id").val();
       
        var custom_ans = $("#regular_update_txtareas").val();


        var dataString = 'give_update_id='+ ins_id +'&custom_answer_msg='+ custom_ans + '&reply=' + giv_up;
        
        
            if (custom_ans == '') {

                alert("Some Field is Empty!");
            }else{

                    // ===== AJAX CODE TO SUBMIT FORM =====
                    $.ajax({
                    type: "POST",
                    url: "<?php echo $this->config->base_url(); ?>hr_cs/custom_answer_solution",
                    data: dataString,
                    cache: false,
                        success: function(result){
                        alert("Success!");
                        $('#regular_update_f')[0].reset(); // ===== TO RESET FORM FIELDS =====
                        window.parent.location.href = "<?php echo $this->config->base_url(); ?>hr_cs/HrHelpDesk";
                        close();
                        }
                    });
                }
                return false;
        });


    // ===== JQUERY FOR INSERTION FOUND =====
    $("#resolve_incident_link_btn").click(function() {
       var giv_up = $("#give_up").val();
        var ins_id = $("#give_update_id").val();
        var fnd_answer_link = $("#resolve_incident_link").val();
        var custom_ans = $("#resolve_incident_link_txtarea").val();

        var dataString = 'give_update_id='+ ins_id +'&found_answer_link=' + fnd_answer_link +'&found_answer_custom='+ custom_ans + '&reply=' + giv_up;
        
        

        if (fnd_answer_link == '') {
            alert("Some Field is Empty!");
        }else{
        // ===== AJAX CODE TO SUBMIT FORM =====
                $.ajax({
                    type: "POST",
                    url: "<?php echo $this->config->base_url(); ?>hr_cs/found_answer_solution",
                    data: dataString,
                    cache: false,
                        success: function(result){
                        alert('success!');
                        // ===== TO RESET FORM FIELDS =====
                        $('#resolve_incident_link_form')[0].reset(); 
                        window.parent.location.href = "<?php echo $this->config->base_url(); ?>hr_cs/HrHelpDesk";
                        close();
                        }
                });
            }

            return false;
    });


    // ===== JQUERY FOR INSERTION CUSTOM FOUND ANSWER IN LINK =====
    $("#resolve_incident_custom_btn").click(function() {
        var giv_up = $("#give_up").val();
        var ins_id = $("#give_update_id").val();
       
        var custom_ans = $("#resolve_incident_custom_txtarea").val();


        var dataString = 'give_update_id='+ ins_id +'&custom_answer_msg='+ custom_ans + '&reply=' + giv_up;
        
        
            if (custom_ans == '') {

                alert("Some Field is Empty!");
            }else{

                    // ===== AJAX CODE TO SUBMIT FORM =====
                    $.ajax({
                    type: "POST",
                    url: "<?php echo $this->config->base_url(); ?>hr_cs/custom_answer_solution",
                    data: dataString,
                    cache: false,
                        success: function(result){
                        alert("Success!");
                        $('#resolve_incident_custom_form')[0].reset(); // ===== TO RESET FORM FIELDS =====
                        window.parent.location.href = "<?php echo $this->config->base_url(); ?>hr_cs/HrHelpDesk";
                        close();
                        }
                    });
                }
                return false;
        });

    // ===== JQUERY FOR INSERTION NOT FOUND ANSWER IN LINK =====
    $("#resolve_incident_redirect_btn").click(function() {
         var giv_up = $("#give_up").val();
        var ins_id = $("#give_update_id").val();  
        var redirect = $("#resolve_incident_redirect_department").val();
        var custom_ans = $("#resolve_incident_redirect_txtarea").val();
        var dataString = 'give_update_id=' + ins_id + '&notfound_answer_custom=' + custom_ans + '&redirect_department=' + redirect + '&reply=' + giv_up;
        
            if (redirect == '' || custom_ans== '') {

                alert("Some Field is Empty!");
            }else{

                    // ===== AJAX CODE TO SUBMIT FORM =====
                    $.ajax({
                    type: "POST",
                    url: "<?php echo $this->config->base_url(); ?>hr_cs/notfound_answe_solution",
                    data: dataString,
                    cache: false,
                    
                        success: function(result){
                        alert("Success!");
                        $('#resolve_incident_redirect_form')[0].reset(); // ===== TO RESET FORM FIELDS =====
                        window.parent.location.href = "<?php echo $this->config->base_url(); ?>hr_cs/HrHelpDesk";
                        close();

                        }
                    });
                }

            return false;
    }); 
/*
    // ===== JQUERY FOR INSERTION FURTHER ANSWER IN LINK =====
    $("#furder_submit").click(function() {
        var hr_sname = $("#hr_username").val();
        var ins_id = $("#give_update_id").val();
        var ass_categ = $("#assign_category option:selected").val();
        var custom_ans = $("#further_answer_msg").val();

        var dataString = 'give_update_id='+ ins_id + '&assign_category=' + ass_categ +'&found_answer_custom='+ custom_ans +'&furthercategid=' + inscateg_id +'&hr_username='+ hr_sname;
        
        if (custom_ans == '') {
            alert("Some Field is Empty!");
        }else{
            
            // ===== AJAX CODE TO SUBMIT FORM =====
            $.ajax({
            type: "POST",
            url: "<?php echo $this->config->base_url(); ?>hr_cs/further_investigation",
            data: dataString,
            cache: false,
            
                success: function(result){
                alert("Success!");
                $('#further_ans_form')[0].reset(); // ===== TO RESET FORM FIELDS =====
                window.parent.location.href = "<?php echo $this->config->base_url(); ?>hr_cs/HrHelpDesk";
                        close();
                }
            });
        }

            return false;
    }); */
 });
</script>