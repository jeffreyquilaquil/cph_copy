<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');


$config['frequencies'] = ['Never', 'Rarely', 'Sometimes', 'Often', 'Very Often'];
$config['questions'] = [
	['name' => 'maxicare', 'label' => 'HMO (Maxicare)'],
	['name' => 'reimbursement', 'label' => '3K Medicine Reimbursement'],
	['name' => 'leave', 'label' => '10 Paid Time-Off'],
	['name' => 'incremental_leave', 'label' => 'Incremental Leave (additional one leave credit in every year of service)'],
	['name' => 'deminimis', 'label' => 'De Minimis Allowance'],
	['name' => 'offset', 'label' => 'Offset Work Hours'],
];

$config['maxicare_rating'] = ['Much Better', 'Slightly Better', 'About the same', 'Slightly Worse', 'Much Worse'];

$config['ratings'] = ['Very Dissatisfied', 'Dissatisfied', 'Neutral', 'Satisfied', 'Very Satisfied'];
$config['second_questions'] = [
	'reimbursement' => ['name' => 'reimbursement', 'label' => '3K Medicine Reimbursement per year.'],
	'leave' => ['name' => 'leave', 'label' => '10 days Paid Time-off' ],
	'incremental_leave' => ['name' => 'incremental_leave', 'label' => 'Incremental Leave ( additional 1 leave credit in every year of service)' ],
	'deminimis' => ['name' => 'deminimis', 'label' => 'Are you satisfied with our De Miminis or 2.5K allowance every month?' ],
	'offset' => ['name' => 'offset', 'label' => 'Offset Work Hours' ],

];

