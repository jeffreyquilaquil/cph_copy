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
    <table class="tableInfo">
        <tr>
            <td>
                <h2>HR Incident Number <?php echo $this->uri->segment(3); ?>
                <br>
                <small>You have owned responsibilty for incident number 000003</small>
                </h2>
            </td>
        </tr>
        <tr>
            <td>Please write below your'e update you want to give the customer:</td>
        </tr>
        <tr>
            <td><textarea style="height:200px;  resize: none;"></textarea></td>
        </tr>
        <tr>
            <td><input type="button" name="" class="btn_ans" value="Send Update"></td>
        </tr>
        <tr>
            <td><a id="back_regular_update">Back</a></td>
        </tr>
    </table>
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

                <a id="found_answer">The answer can be found in employee.tatepublishing.net</a>
                
                <!-- ===== FOUND ANSWER  ===== -->
                <div id="found_answer_form"> 
                    <form id="found_answer_forms">

                        <!-- 
                        <input id="foundid" type="hidden" name="insedentid" value="<?php echo $value->cs_post_id; ?> ">
                        <input id="foundcategid" type="hidden" name="insedentcategid" value="<?php echo $value->cs_post_id; ?> ">
                        -->
                        <table class="tableInfo">
                            <tr>
                                <td>
                                    <small>Please place below the link to the page in employee.tatepublishing.net</small>

                                    <br>

                                    <input type="text" id="found_answer_link" name="found_answer_link" required>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <small>You may right below an additional custom message to the customer (optional):</small>

                                    <br>

                                    <textarea style="height:200px;  resize: none;" id="found_answer_custom" name="found_answer_custom"></textarea>

                                    <br><br>

                                    <input id="found_answerer_submit" type="submit" class="btn_ans" value="Resolve Incident">
                                </td>
                            </tr>
                        </table>

                        <br>

                    </form>
                </div>

                <br><br>

                <a id="custom_answer">Send custom resoltion response</a>


                <!-- ===== CUSTOM ANSWER  ===== -->
                <div id="custom_answer_form"> 
                    <form id="custom_ans_form">
                    <!--
                    <input id="customid" type="hidden" name="insedentid" value="<?php echo $value->cs_post_id; ?> ">
                    <input id="customcategid" type="hidden" name="insedentid" value="<?php echo $value->cs_post_id; ?> ">
                    -->
                        <table class="tableInfo">
                            <tr>
                                <td>
                                    <small>Please write below your resolution message to employee:</small>

                                    <br>

                                    <textarea id="custom_answer_msg" name="custom_answer_msg" style="height:200px; resize: none;"></textarea>

                                    <br><br>

                                    <input type="submit" id="custom_answer_submit" class="btn_ans" value="Resolve Incident">
                                </td>
                            </tr>
                        </table>

                        <br>

                        </form>
                </div>

                <br><br>
                
                <a id="notfound_answer">This is not an HR inquiry. Redirect to another department</a>

                <!-- ===== NOT FOUND ANSWER  ===== -->
                <div id="notfound_answer_form"> 
                    <!--
                    <input id="notfoundid" type="hidden" name="insedentid" value="<?php echo $value->cs_post_id; ?> ">
                    <input id="notfoundcategid" type="hidden" name="insedentid" value="<?php echo $value->cs_post_id; ?> ">
                    -->
                    <form id="not_found_ans_form">
                        <table class="tableInfo">
                            <tr>
                                <td>To what deparment does this person need to be redirected to?</td>
                                <td>
                                    <select id="redirect_department" name="redirect_department" required style="width: 100%">
                                        <option></option>

                                        <?php foreach ($department_email as $k => $v): ?>
                                        <option value="<?php echo $v->email; ?>"><?php echo $v->department." (".$v->email.")"; ?></option>
                                    <?php endforeach ?>

                                    </select>
                                </td>   
                            </tr>
                            <tr>
                                <td valign="top">Add custom message</td>
                                <td><textarea id="not_found_custom_msg" name="not_found_custom_msg" style="height:200px; resize: none;" placeholder="<Insert Custom Message Here>"></textarea></td>
                            </tr>
                            <tr>
                                <td></td>
                                <td> 
                                    <a id="add_redirect_dept">Add redirection department</a>
                                    <input type="submit" id="not_found_answer_submit" class="btn_ans_small" value="Resolve incident" style="float:right">
                                </td>
                            </tr>
                        </table>

                        <br>

                    </form>
                </div>

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

        // ===== FOUND ANSWER =====
        $('#found_answer_form').hide();

        $('#found_answer').click(function(){

            $('#give_update_form').hide();
            $('#custom_answer_form').hide();
            $('#notfound_answer_form').hide();
            $('#found_answer_form').toggle();
            
        })  

        // ===== CUSTOM ANSWER =====
        $('#custom_answer_form').hide();

        $('#custom_answer').click(function(){

            $('#give_update_form').hide();
             $('#found_answer_form').hide();
            $('#notfound_answer_form').hide();
            $('#custom_answer_form').toggle();
            
        })  

        // ===== NOT FOUND ANSWER =====
        $('#notfound_answer_form').hide();

        $('#notfound_answer').click(function(){

            $('#give_update_form').hide();
             $('#custom_answer_form').hide();
            $('#found_answer_form').hide();
            $('#notfound_answer_form').toggle();
            
        })         

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

    })
</script>