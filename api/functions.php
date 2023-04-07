<?php
date_default_timezone_set('Africa/Kampala');
/* server timezone 
ob_start();
session_start();*/
session_start();
header('Access-Control-Allow-Origin: *');
?> 
<?php


/*
define("HOST", "localhost");
define("USER", "britam_mtp");
define("PASS", "CB_V1@2019");
define("DB", "britam_mtp_db"); 
define("STICKER_FEES", "6000");
define("STAMP_DUTY", "35000");

*/


define("HOST", "localhost");
define("USER", "clearbas_root");
define("PASS", "Skycode@2018");
define("DB", "clearbas_britam"); 
define("STICKER_FEES", "8000");
define("STAMP_DUTY", "35000");


function strip_html_tags($text)
{
	$text = preg_replace(
		array(
			// Remove invisible content
			'@<head[^><meta http-equiv="Content-Type" content="text/html; charset=utf-8">]*?>.*?</head>@siu',
			'@<style[^>]*?>.*?</style>@siu',
			'@<script[^>]*?.*?</script>@siu',
			'@<object[^>]*?.*?</object>@siu',
			'@<embed[^>]*?.*?</embed>@siu',
			'@<applet[^>]*?.*?</applet>@siu',
			'@<noframes[^>]*?.*?</noframes>@siu',
			'@<noscript[^>]*?.*?</noscript>@siu',
			'@<noembed[^>]*?.*?</noembed>@siu',

			// Add line breaks before & after blocks
			'@<((br)|(hr))@iu',
			'@</?((address)|(blockquote)|(center)|(del))@iu',
			'@</?((div)|(h[1-9])|(ins)|(isindex)|(p)|(pre))@iu',
			'@</?((dir)|(dl)|(dt)|(dd)|(li)|(menu)|(ol)|(ul))@iu',
			'@</?((table)|(th)|(td)|(caption))@iu',
			'@</?((form)|(button)|(fieldset)|(legend)|(input))@iu',
			'@</?((label)|(select)|(optgroup)|(option)|(textarea))@iu',
			'@</?((frameset)|(frame)|(iframe))@iu',
		),
		array(' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', "\\n\\$0", "\\n\\$0", "\\n\\$0", "\\n\\$0", "\\n\\$0", "\\n\\$0", "\\n\\$0", "\\n\\$0"),
		$text
	);

	// Remove all remaining tags and comments and return.
	return strip_tags($text);
}


function makeYopay_request()
{

	require './YoAPI.php';

	$invoice_id = addslashes($_REQUEST['invoice_id']);
	$phone = addslashes($_REQUEST['phone']);
	$amount = addslashes($_REQUEST['amount']);

	if (isset($amount, $phone, $invoice_id)) {

		$json = array();

		$username = '100852432062';
		$password = 'xX1l-hCx5-3rqG-6FEn-F8uB-GRMb-ajzX-DP1L';

		$yoAPI = new YoAPI($username, $password);

		// Create a unique transaction reference that you will reference this payment with
		$transaction_reference = date("YmdHis") . rand(1, 100);

		$yoAPI->set_external_reference($transaction_reference);

		$response = $yoAPI->ac_deposit_funds($phone, $amount, 'Thirdparty sticker bought with Reciept ID ' . $invoice_id);

		if ($response['Status'] == 'OK') {

			$json['status'] = 'ok';
			$json['message'] = 'Payment for sticker successfully made.';
		} else {
			$json['status'] = 'error';
			$json['message'] = 'Payment error with status ' . $response['StatusMessage'];
		}
	} else {
		//if login fails
		$json['status'] = 'missing';
		$json['message'] = 'Telephone, Amount are missing';
	}

	echo json_encode($json);
}

function connection ()
{
	$dsn = "mysql:host=".HOST.";dbname=".DB.";charset=utf8mb4";
	$options = [
		PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
		PDO::ATTR_EMULATE_PREPARES   => false,
	];
	try {
		return new PDO($dsn, USER, PASS, $options);
	} catch (\PDOException $e) {
		throw new \PDOException($e->getMessage(), (int) $e->getCode());
	}
}

function login()
{

	$con = connection();

	//convert text to encrypted data
	$username = urldecode($_REQUEST['username']);
	$password = urldecode($_REQUEST['password']);

	if (isset($username, $password)) {

		$json = array();

		$username = ($username);
		$password = md5($password);


		$sel = $con->query("select * from users where telephone='$username' OR email='$username' AND password='$password' AND status=1") or die($con->errorInfo());

		if ($sel->rowCount() > 0) {

			while ($row = $sel->fetch()) {
				$json['user_id'] = $row['user_id'];
				$json['license_no'] = $row['license_no'];
				$json['name'] = $row['name'];
				$json['email'] = $row['email'];
				$json['telephone'] = $row['telephone'];
				$json['role'] = $row['role'];
				$json['address'] = $row['address'];
				$json['img'] = getpicture('../images/users', $row['user_id']);

				$_SESSION['user_id'] = $row['user_id'];
			}
			
			$user_id = $json['user_id'];
			$con->exec("INSERT INTO system_logs SET event='User Login', user_id='$user_id'") or die($con->errorInfo());
			//if login is successful
			$json['status'] = 'success';
			$json['message'] = 'You have successfully logged into BRITAM MTP System, please wait redirecting....';
		} else {
			//if login fails
			$json['status'] = 'error';
			$json['message'] = 'Login failed, please try again!!!';
		}
	} else {
		//if login fails
		$json['status'] = 'error';
		$json['message'] = 'Username/telephone or Password Invalid';
	}

	echo json_encode($json);
}

function logout()
{
	$con = connection();

	$json = array();

	if (isset($_SESSION['user_id'])) {
		//logout un set session variables

		$user_id = $_SESSION['user_id'];
		$con->exec("INSERT INTO system_logs SET event='User Logout', user_id='$user_id'") or  die($con->errorInfo());

		unset($_SESSION['user_id']);
		$json['status'] = 'success';
		$json['message'] = 'You successfully logout of BRITAM MTP System';
	}

	echo json_encode($json);
}

function setsession()
{
	$con = connection();
	$json = array();

	if (isset($_SESSION['user_id'])) {
		//set session variables on successful login

		$user_id = $_SESSION['user_id'];

		$sel = $con->query("select * from users where (user_id='$user_id') AND status=1") or die($con->errorInfo());

		if ($sel->rowCount() > 0) {

			while ($row = $sel->fetch()) {

				$json['user_id'] = $row['user_id'];
				$json['license_no'] = $row['license_no'];
				$json['NIN'] = $row['NIN'];
				$json['dob'] = $row['dob'];
				$json['name'] = $row['name'];
				$json['gender'] = $row['gender'];
				$json['status'] = $row['status'];
				$json['email'] = $row['email'];
				$json['telephone'] = $row['telephone'];
				$json['role'] = $row['role'];
				$json['address'] = $row['address'];
				$json['branch_name'] = $row['branch_name'];
				$json['img'] = getpicture('../images/users', $_SESSION['user_id']);
			}
		}
	}

	echo json_encode($json);
}


function updateUserInfo()
{
	$con = connection();

	$json = array();

	if (isset($_REQUEST['user_id'])) {
		$user_id = addslashes($_REQUEST['user_id']);
		$username = addslashes($_REQUEST['username']);
		$email = addslashes($_REQUEST['email']);
		$phone = addslashes($_REQUEST['phone']);
		$password = addslashes($_REQUEST['password']);

		//$qr_code = $converter->encode($pin); 

		$sel3 = $con->query("select user_id, name, telephone, email from users where user_id='$user_id'") or die($con->errorInfo());

		if ($sel3->rowCount() > 0) {


			$sel = $con->exec("UPDATE users set name='$username',email='$email',telephone='$phone',password='" . md5($password) . "' WHERE user_id='$user_id'") or die($con->errorInfo());


			if ($sel) {

				$sel2 = $con->query("select * from users where user_id='$user_id' ") or die($con->errorInfo());

				if ($sel2->rowCount() > 0) {

					while ($row = $sel2->fetch()) {
						$json['user_id'] = $row['user_id'];
					}
				}

				$json['status'] = 'ok';
				$json['type'] = 'success';
				$json['message'] = 'User successfully updated';
			}
		} else {
			$json['status'] = 'notfound';
			$json['type'] = 'error';
			$json['message'] = 'User not found';
		}
	} else {
		$json['status'] = "missing";
		$json['type'] = 'error';
		$json['message'] = 'Missing Fields';
	}
	echo json_encode($json);
}



function viewUser()
{
	$con = connection();

	$json = array();

	if (isset($_REQUEST['user_id'])) {
		$user_id = addslashes($_REQUEST['user_id']);
		$sel = $con->query("select * FROM users WHERE user_id='$user_id' ORDER BY time desc") or die($con->errorInfo());

		if ($sel->rowCount() > 0) {
			while ($row = $sel->fetch()) {

				$id = $row['user_id'];
				$pic = getpicture('../images/users', $id);
				$row['avatar'] = $pic;

				$json['results'][] = $row;
			}
			//
			$json['status'] = "ok";
		} else {

			$json['status'] = "empty";
		}
	} else {
		$json['status'] = "missing";
	}
	echo json_encode($json);
}




function viewUserSummary()
{
	$con = connection();

	$json = array();

	if (isset($_REQUEST['user_id'])) {
		$user_id = addslashes($_REQUEST['user_id']);

		$sel = $con->query("select * FROM users WHERE user_id='$user_id' ORDER BY time desc") or die($con->errorInfo());

		if ($sel->rowCount() > 0) {
			while ($row = $sel->fetch()) {

				$id = $row['user_id'];
				$pic = getpicture('../images/users', $id);
				$row['avatar'] = $pic;

				$json['results'][] = $row;



				$row['no_stickers'] = 0;
				$row['total_gross_amount'] = 0;
				$row['total_commission'] = 0;
				$row['taxes_levies'] = 0;
				$row['net_commission'] = 0;
				$row['agent_paid'] = 0;
				$row['agent_balance'] = 0;


				$selThird = $con->query("select count(invoice_id) as party FROM motor_invoices WHERE user_id='$user_id'") or die($con->errorInfo());

				while ($rowM = $selThird->fetch()) {
					$row['no_stickers'] = $rowM['party'];
				}

				$selAdverts = $con->query("select count(invoice_id) as policy_no FROM motor_invoices WHERE user_id='$user_id'") or die($con->errorInfo());

				while ($rowA = $selAdverts->fetch()) {
					$json['policies'] = $rowA['policy_no'];
				}

				$json['count'] = 0;
				// $comm1=$con->query("select count(b.invoice_id) as comm FROM motor_invoices b INNER JOIN users u ON u.user_id=b.user_id WHERE (b.status='paid' OR b.status='replaced' OR b.status='completed') ")or die($con->errorInfo());

				// while($rowcomm1=$comm1->fetch()){   
				// $json['count'] =$rowcomm1['comm'];
				// } 

				$row['no_stickers'] = ($json['count'] + $row['no_stickers']);



				$json['summary'][] = $row;
			}
			//
			$json['status'] = "ok";
		}
	} else {
		$json['status'] = "missing";
	}
	echo json_encode($json);
}

function sendReminders()
{
	$con = connection();

	$json = array();

	require_once "../telerivet/telerivet.php";

	$API_KEY = '944wg_AAQIeQJ5P2LxXct10474n1xrrfLhTW';           // from https://telerivet.com/api/keys
	$PROJECT_ID = 'PJ7fdf8389d2066c66';

	$telerivet = new Telerivet_API($API_KEY);

	$project = $telerivet->initProjectById($PROJECT_ID);


	if (isset($_REQUEST['user_id'], $_REQUEST['role'])) {

		$user_id = addslashes($_REQUEST['user_id']);
		$role = addslashes($_REQUEST['role']);

		if ($role == 'admin') {

			$sel = $con->query("SELECT m.*,d.*,(SELECT c.telephone FROM clients c WHERE c.client_id=m.client_id) AS telephone,(SELECT cc.name FROM clients cc WHERE cc.client_id=m.client_id) AS client_name FROM motor_invoices m INNER JOIN motor_invoice_details d ON d.invoice_id=m.invoice_id WHERE (m.end_date >= DATE(NOW()) - INTERVAL 300 DAY) GROUP BY d.invoice_detail_id") or die($con->errorInfo());

			if ($sel->rowCount() > 0) {
				while ($row = $sel->fetch()) {

					$from_number = $row['telephone'];

					if (strlen($from_number) == 12) {
						$from_number;
					} else {
						$from_number = substr_replace($from_number, "256", 0, 1);
					}

					$message = '[BRITAM MTP] Dear ' . strtoupper($row['client_name']) . ' vehicle Registration No ' . $row['vehicle_plate_no'] . ' MTP Policy is expiring on ' . $row['end_date'];


					$sel = $con->query("select * from mtp_reminders  WHERE message='$message'") or die($con->errorInfo());

					if ($sel->rowCount() > 0) {

						$json['status'] = 'duplicate';
					} else {

						$con->exec("INSERT INTO mtp_reminders SET client_name='" . strtoupper($row['client_name']) . "', message='$message',user_id='$user_id'") or die($con->errorInfo());
					}
					$project->sendMessage(array(
						'to_number' => $from_number,
						'content' => $message
					));

					$row['message'] = $message;

					$json['results'][] = $row;
				}
				//
				$json['status'] = "ok";
			} else {

				$json['status'] = "empty";
			}
		} else if ($role == 'thirdparty_admin' || $role == 'thirdparty_agent' || $role == 'mtn_dealer') {
			$sel = $con->query("SELECT m.*,d.*,(SELECT c.telephone FROM clients c WHERE c.client_id=m.client_id) AS telephone,(SELECT cc.name FROM clients cc WHERE cc.client_id=m.client_id) AS client_name FROM motor_invoices m INNER JOIN motor_invoice_details d ON d.invoice_id=m.invoice_id WHERE (m.user_id='$user_id' AND m.end_date >= DATE(NOW()) - INTERVAL 300 DAY) GROUP BY d.invoice_detail_id ") or die($con->errorInfo());

			if ($sel->rowCount() > 0) {
				while ($row = $sel->fetch()) {

					$from_number = $row['telephone'];

					if (strlen($from_number) == 12) {
						$from_number;
					} else {
						$from_number = substr_replace($from_number, "256", 0, 1);
					}

					$message = '[BRITAM MTP] Dear ' . strtoupper($row['client_name']) . ' vehicle Registration No ' . $row['vehicle_plate_no'] . ' MTP Policy is expiring on ' . $row['end_date'];


					$sel = $con->query("select * from mtp_reminders  WHERE message='$message'") or die($con->errorInfo());

					if ($sel->rowCount() > 0) {

						$json['status'] = 'duplicate';
					} else {

						$con->exec("INSERT INTO mtp_reminders SET client_name='" . strtoupper($row['client_name']) . "', message='$message',user_id='$user_id'") or die($con->errorInfo());
					}
					$project->sendMessage(array(
						'to_number' => $from_number,
						'content' => $message
					));

					$row['message'] = $message;

					$json['results'][] = $row;
				}
				//
				$json['status'] = "ok";
			} else {

				$json['status'] = "empty";
			}
		}
	} else {

		$json['status'] = "missing";
	}

	echo json_encode($json);
}


function changePassword()
{
	$con = connection();

	$json = array();

	if (isset($_REQUEST['user_id'], $_REQUEST['password'])) {
		$user_id = addslashes($_REQUEST['user_id']);
		$password = md5($_REQUEST['password']);

		$sel = $con->exec("UPDATE users SET password='$password' WHERE user_id='$user_id'") or die($con->errorInfo());

		if ($sel) {

			$json['status'] = "ok";
			$json['message'] = "Password Successfully Updated";
		} else {
			$json['status'] = "error";
			$json['message'] = "An error occurred";
		}
	} else {
		$json['status'] = "error";
		$json['message'] = "Missing fields";
	}
	echo json_encode($json);
}

function viewClient()
{
	$con = connection();

	$json = array();

	if (isset($_REQUEST['client_id'])) {
		$client_id = addslashes($_REQUEST['client_id']);
		$sel = $con->query("select * FROM clients WHERE client_id='$client_id' ORDER BY time desc") or die($con->errorInfo());

		if ($sel->rowCount() > 0) {
			while ($row = $sel->fetch()) {

				$id = $row['client_id'];
				$pic = getpicture('../images/clients', $id);
				$row['avatar'] = $pic;

				$json['results'][] = $row;
			}
			//
			$json['status'] = "ok";
		} else {

			$json['status'] = "empty";
		}
	} else {
		$json['status'] = "missing";
	}
	echo json_encode($json);
}



function clean($string)
{
	$string = str_replace('[', '', $string);
	$string = str_replace(']', '', $string);
	return $string;
}

function updateNotification()
{
	$con = connection();

	$json = array();

	if (isset($_REQUEST['id'])) {

		$id = addslashes($_REQUEST['id']);


		$query = $con->exec("UPDATE mtp_reminders SET status='read' WHERE id='$id'") or die($con->errorInfo());

		if ($query) {
			$json['status'] = "ok";
			$json['message'] = "Reminder read";
		} else {
			$json['status'] = "failed";
			$json['message'] = "Reminder failed";
		}
	} else {
		$json['status'] = "missing";
	}
	echo json_encode($json);
}


function viewAllNotifications()
{
	$con = connection();

	$json = array();

	if (isset($_REQUEST['user_id'], $_REQUEST['role'])) {

		$user_id = addslashes($_REQUEST['user_id']);
		$role = addslashes($_REQUEST['role']);

		if ($role == 'admin') {
			$sel = $con->query("select * FROM mtp_reminders ORDER BY time desc") or die($con->errorInfo());
		} else {
			$sel = $con->query("select * FROM mtp_reminders WHERE user_id='$user_id' ORDER BY time desc") or die($con->errorInfo());
		}

		if ($sel->rowCount() > 0) {
			while ($row = $sel->fetch()) {
				$row['message'] = clean($row['message']);
				$row['t_ago'] = time_elapsed_string($row['time'], 0);
				$json['results'][] = $row;
			}
			//
			$json['status'] = "ok";
		} else {

			$json['status'] = "empty";
		}
	} else {
		$json['status'] = "missing";
	}
	echo json_encode($json);
}

function time_elapsed_string($datetime, $full = false)
{
	$now = new DateTime;
	$ago = new DateTime($datetime);
	$diff = $now->diff($ago);

	$diff->w = floor($diff->d / 7);
	$diff->d -= $diff->w * 7;

	$string = array(
		'y' => 'year',
		'm' => 'month',
		'w' => 'week',
		'd' => 'day',
		'h' => 'hour',
		'i' => 'minute',
		's' => 'second',
	);
	foreach ($string as $k => &$v) {
		if ($diff->$k) {
			$v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
		} else {
			unset($string[$k]);
		}
	}

	if (!$full) $string = array_slice($string, 0, 1);
	return $string ? implode(', ', $string) . ' ago' : 'just now';
}

function viewNotifications()
{
	$con = connection();

	$json = array();

	if (isset($_REQUEST['user_id'], $_REQUEST['role'])) {

		$user_id = addslashes($_REQUEST['user_id']);
		$role = addslashes($_REQUEST['role']);

		if ($role == 'admin') {
			$sel = $con->query("select * FROM mtp_reminders ORDER BY time desc LIMIT 4") or die($con->errorInfo());
		} else {
			$sel = $con->query("select * FROM mtp_reminders WHERE user_id='$user_id' ORDER BY time desc LIMIT 4") or die($con->errorInfo());
		}

		if ($sel->rowCount() > 0) {
			while ($row = $sel->fetch()) {
				$row['t_ago'] = time_elapsed_string($row['time'], 0);

				$id = $row['user_id'];
				$row['pic'] = getpicture('../images/users', $id);

				$json['results'][] = $row;
			}
			//
			$json['status'] = "ok";
		} else {

			$json['status'] = "empty";
		}
	} else {
		$json['status'] = "missing";
	}
	echo json_encode($json);
}


function viewNotificationCount()
{
	$con = connection();

	$json = array();

	if (isset($_REQUEST['user_id'], $_REQUEST['role'])) {

		$user_id = addslashes($_REQUEST['user_id']);
		$role = addslashes($_REQUEST['role']);

		if ($role == 'admin') {
			$sel = $con->query("select count(id) as no FROM mtp_reminders WHERE status='unread' ORDER BY time desc") or die($con->errorInfo());
		} else {
			$sel = $con->query("select count(id) as no FROM mtp_reminders WHERE user_id='$user_id' AND status='unread' ORDER BY time desc") or die($con->errorInfo());
		}

		if ($sel->rowCount() > 0) {
			while ($row = $sel->fetch()) {

				$json['no'] = $row['no'];
			}
			//
			$json['status'] = "ok";
		} else {

			$json['status'] = "empty";
		}
	} else {
		$json['status'] = "missing";
	}
	echo json_encode($json);
}


function viewUsers()
{
	$con = connection();

	$json = array();



	if (isset($_REQUEST['user_id'], $_REQUEST['role'])) {

		$user_id = addslashes($_REQUEST['user_id']);
		$role = addslashes($_REQUEST['role']);

		if ($role == 'admin') {
			$sel = $con->query("select * FROM users ORDER BY time desc") or die($con->errorInfo());

			if ($sel->rowCount() > 0) {
				while ($row = $sel->fetch()) {

					$id = $row['user_id'];
					$pic = getpicture('../images/users', $id);
					$row['avatar'] = $pic;

					$json['results'][] = $row;
				}
				//
				$json['status'] = "ok";
			} else {

				$json['status'] = "empty";
			}
		} else if ($role == 'thirdparty_admin' || $role == 'mtn_dealer') {
			$sel = $con->query("select * FROM users WHERE user_id_added='$user_id' ORDER BY time desc") or die($con->errorInfo());

			if ($sel->rowCount() > 0) {
				while ($row = $sel->fetch()) {

					$id = $row['user_id'];
					$pic = getpicture('../images/users', $id);
					$row['avatar'] = $pic;

					$json['results'][] = $row;
				}
				//
				$json['status'] = "ok";
			} else {

				$json['status'] = "empty";
			}
		}
	} else {

		$json['status'] = "missing";
	}

	echo json_encode($json);
}

function viewStickerNoBal()
{
	$con = connection();

	$json = array();

	$json['motor_bike'] = 0;
	$json['motor_transit'] = 0;
	$json['motor_private'] = 0;
	$json['motor_commercial'] = 0;

	$sel = $con->query("select SUM(s.total_amount_received) as total_amount,s.category,(SELECT COUNT(t.sticker_no) FROM organ_stickers t WHERE t.category=s.category AND status='used') AS usedStickers  FROM sticker_acc s GROUP BY s.category") or die($con->errorInfo());

	if ($sel->rowCount() > 0) {
		while ($row = $sel->fetch()) {

			if ($row['category'] == 'Motor Bike') {
				$json['motor_bike'] = ($row['total_amount'] - $row['usedStickers']);
			}


			if ($row['category'] == 'Motor Transit') {
				$json['motor_transit'] = ($row['total_amount'] - $row['usedStickers']);
			}


			if ($row['category'] == 'Motor Private') {
				$json['motor_private'] = ($row['total_amount'] - $row['usedStickers']);
			}

			if ($row['category'] == 'Motor Commercial') {
				$json['motor_commercial'] = ($row['total_amount'] - $row['usedStickers']);
			}


			//$json['results'][]=$row;
		}
		//
		$json['status'] = "ok";
	} else {

		$json['status'] = "empty";
	}


	echo json_encode($json);
}


function viewUserStickers()
{
	$con = connection();

	$json = array();



	if (isset($_REQUEST['user_id'])) {

		$user_id = addslashes($_REQUEST['user_id']);
		// $date_from = (date('Y-m-d', strtotime(substr($_REQUEST['date_from'],0,10)))); 
		// $date_to = (date('Y-m-d', strtotime(substr($_REQUEST['date_to'],0,10)))); 
		// $role = addslashes($_REQUEST['role']); 



		$sel = $con->query("select q.*,p.print_status,p.sticker_no,p.vehicle_use,p.vehicle_category,p.vehicle_plate_no,p.vehicle_no_seats,p.vehicle_cc,p.basic_premium as basic_p,DATE_FORMAT(q.start_date, '%d/%m/%Y') AS start_date,DATE_FORMAT(q.end_date, '%d/%m/%Y') AS end_date,DATE_FORMAT(q.time, '%d/%m/%Y') as t,(SELECT ccc.telephone FROM clients ccc WHERE ccc.client_id=q.client_id) AS client_tel,(SELECT cc.address FROM clients cc WHERE cc.client_id=q.client_id) AS client_address,(SELECT c.name FROM clients c WHERE c.client_id=q.client_id) AS client_name,(SELECT CONCAT(u3.name) FROM users u3 WHERE u3.user_id=q.user_id) AS agent_details,(SELECT o.name FROM organisations o WHERE o.organ_id=q.organ_id) AS organ_name FROM motor_invoices q INNER JOIN motor_invoice_details p ON p.invoice_id=q.invoice_id WHERE q.user_id='$user_id' GROUP BY p.invoice_detail_id") or die($con->errorInfo());

		if ($sel->rowCount() > 0) {
			while ($row = $sel->fetch()) {

				if ($row['status'] == 'new') {
					$row['q_status'] = 'New';
				} else if ($row['status'] == 'completed') {
					$row['q_status'] = 'Completed';
				} else if ($row['status'] == 'replaced') {
					$row['q_status'] = 'Replaced';
				} else if ($row['status'] == 'renewal') {
					$row['q_status'] = 'Renewal';
				}

				$row['stamp_duty'] = STAMP_DUTY;
				$row['sticker_fees'] = STICKER_FEES;

				$training_levy = ($row['basic_premium'] * 0.005);

				$vat = (($training_levy) + ($row['basic_premium']) + ($row['sticker_fees'])) * 0.18;

				$row['total_premium'] = (($row['basic_premium']) + ($training_levy) + ($row['stamp_duty']) + ($row['sticker_fees']) + ($vat));

				$json['results'][] = $row;
			}
			//
			$json['status'] = "ok";
		} else {

			$json['status'] = "empty";
		}
	} else {

		$json['status'] = "missing";
	}

	echo json_encode($json);
}


function viewUserStickerReports()
{
	$con = connection();

	$json = array();



	if (isset($_REQUEST['user_id'])) {

		$user_id = addslashes($_REQUEST['user_id']);
		$date_from = (date('Y-m-d', strtotime(substr($_REQUEST['date_from'], 0, 10))));
		$date_to = (date('Y-m-d', strtotime(substr($_REQUEST['date_to'], 0, 10))));
		// $role = addslashes($_REQUEST['role']); 



		$sel = $con->query("select q.*,p.sticker_no,p.vehicle_use,p.vehicle_category,p.vehicle_plate_no,p.vehicle_no_seats,p.vehicle_cc,p.basic_premium as basic_p,DATE_FORMAT(q.start_date, '%d/%m/%Y') AS start_date,DATE_FORMAT(q.end_date, '%d/%m/%Y') AS end_date,DATE_FORMAT(q.time, '%d/%m/%Y') as t,(SELECT ccc.telephone FROM clients ccc WHERE ccc.client_id=q.client_id) AS client_tel,(SELECT cc.address FROM clients cc WHERE cc.client_id=q.client_id) AS client_address,(SELECT c.name FROM clients c WHERE c.client_id=q.client_id) AS client_name,(SELECT CONCAT(u3.name) FROM users u3 WHERE u3.user_id=q.user_id) AS agent_details,(SELECT o.name FROM organisations o WHERE o.organ_id=q.organ_id) AS organ_name FROM motor_invoices q INNER JOIN motor_invoice_details p ON p.invoice_id=q.invoice_id WHERE q.user_id='$user_id' AND (q.time >= '$date_from' AND q.time <= '$date_to') GROUP BY p.invoice_detail_id") or die($con->errorInfo());

		if ($sel->rowCount() > 0) {
			while ($row = $sel->fetch()) {

				if ($row['status'] == 'new') {
					$row['q_status'] = 'New';
				} else if ($row['status'] == 'completed') {
					$row['q_status'] = 'Completed';
				} else if ($row['status'] == 'replaced') {
					$row['q_status'] = 'Replaced';
				} else if ($row['status'] == 'renewal') {
					$row['q_status'] = 'Renewal';
				}

				$row['stamp_duty'] = STAMP_DUTY;
				$row['sticker_fees'] = STICKER_FEES;

				$training_levy = ($row['basic_premium'] * 0.005);

				$vat = (($training_levy) + ($row['basic_premium']) + ($row['sticker_fees'])) * 0.18;

				$row['total_premium'] = (($row['basic_premium']) + ($training_levy) + ($row['stamp_duty']) + ($row['sticker_fees']) + ($vat));

				$json['results'][] = $row;
			}
			//
			$json['status'] = "ok";
		} else {

			$json['status'] = "empty";
		}
	} else {

		$json['status'] = "missing";
	}

	echo json_encode($json);
}


function viewUserIssuedStickers()
{
	$con = connection();

	$json = array();



	if (isset($_REQUEST['user_id'])) {

		$user_id = addslashes($_REQUEST['user_id']);
		// $date_from = (date('Y-m-d', strtotime(substr($_REQUEST['date_from'],0,10)))); 
		// $date_to = (date('Y-m-d', strtotime(substr($_REQUEST['date_to'],0,10)))); 
		// $role = addslashes($_REQUEST['role']); 



		$sel = $con->query("select q.*,p.sticker_no,p.vehicle_use,p.vehicle_category,p.vehicle_plate_no,p.vehicle_no_seats,p.vehicle_cc,p.basic_premium as basic_p,DATE_FORMAT(q.start_date, '%d/%m/%Y') AS start_date,DATE_FORMAT(q.end_date, '%d/%m/%Y') AS end_date,DATE_FORMAT(q.time, '%d/%m/%Y') as t,(SELECT ccc.telephone FROM clients ccc WHERE ccc.client_id=q.client_id) AS client_tel,(SELECT cc.address FROM clients cc WHERE cc.client_id=q.client_id) AS client_address,(SELECT c.name FROM clients c WHERE c.client_id=q.client_id) AS client_name,(SELECT CONCAT(u3.name) FROM users u3 WHERE u3.user_id=q.user_id) AS agent_details,(SELECT o.name FROM organisations o WHERE o.organ_id=q.organ_id) AS organ_name FROM motor_invoices q INNER JOIN motor_invoice_details p ON p.invoice_id=q.invoice_id WHERE q.user_id='$user_id' GROUP BY p.invoice_detail_id") or die($con->errorInfo());

		if ($sel->rowCount() > 0) {
			while ($row = $sel->fetch()) {

				if ($row['status'] == 'new') {
					$row['q_status'] = 'New';
				} else if ($row['status'] == 'completed') {
					$row['q_status'] = 'Completed';
				} else if ($row['status'] == 'replaced') {
					$row['q_status'] = 'Replaced';
				} else if ($row['status'] == 'renewal') {
					$row['q_status'] = 'Renewal';
				}

				$row['stamp_duty'] = STAMP_DUTY;
				$row['sticker_fees'] = STICKER_FEES;

				$training_levy = ($row['basic_premium'] * 0.005);

				$vat = (($training_levy) + ($row['basic_premium']) + ($row['sticker_fees'])) * 0.18;

				$row['total_premium'] = (($row['basic_premium']) + ($training_levy) + ($row['stamp_duty']) + ($row['sticker_fees']) + ($vat));

				$json['results'][] = $row;
			}
			//
			$json['status'] = "ok";
		} else {

			$json['status'] = "empty";
		}
	} else {

		$json['status'] = "missing";
	}

	echo json_encode($json);
}


function viewUIAStickerNos()
{
	$con = connection();

	$json = array();



	$sel = $con->query("select * FROM sticker_acc") or die($con->errorInfo());

	if ($sel->rowCount() > 0) {
		while ($row = $sel->fetch()) {

			$json['results'][] = $row;
		}
		//
		$json['status'] = "ok";
	} else {

		$json['status'] = "empty";
	}



	echo json_encode($json);
}


function viewUserLogs()
{
	$con = connection();

	$json = array();



	if (isset($_REQUEST['user_id'])) {

		$user_id = addslashes($_REQUEST['user_id']);


		$sel = $con->query("SELECT u.*, DATE_FORMAT(l.time, '%d %b %Y %h:%i %p') as t,l.event FROM users u INNER JOIN system_logs l ON l.user_id=u.user_id WHERE (u.user_id='$user_id' OR u.user_id_added='$user_id') GROUP BY l.log_id") or die($con->errorInfo());

		if ($sel->rowCount() > 0) {
			while ($row = $sel->fetch()) {

				$id = $row['user_id'];
				$pic = getpicture('../images/users', $id);
				$row['avatar'] = $pic;

				$json['results'][] = $row;
			}
			//
			$json['status'] = "ok";
		} else {

			$json['status'] = "empty";
		}
	} else {

		$json['status'] = "missing";
	}

	echo json_encode($json);
}


function viewClients()
{
	$con = connection();

	$json = array();

	if (isset($_REQUEST['user_id'], $_REQUEST['role'])) {

		$user_id = addslashes($_REQUEST['user_id']);
		$role = addslashes($_REQUEST['role']);

		if ($role == 'admin') {

			$sel = $con->query("select * FROM clients ORDER BY time desc") or die($con->errorInfo());

			if ($sel->rowCount() > 0) {
				while ($row = $sel->fetch()) {

					$id = $row['client_id'];
					$pic = getpicture('../images/clients', $id);
					$row['avatar'] = $pic;

					$json['results'][] = $row;
				}
				//
				$json['status'] = "ok";
			} else {

				$json['status'] = "empty";
			}
		} else if ($role == 'mtn_agent' || $role == 'mtn_dealer' || $role == 'thirdparty_agent') {
			$sel = $con->query("select * FROM clients where user_id='$user_id' ORDER BY time desc") or die($con->errorInfo());

			if ($sel->rowCount() > 0) {
				while ($row = $sel->fetch()) {

					$id = $row['client_id'];
					$pic = getpicture('../images/clients', $id);
					$row['avatar'] = $pic;

					$json['results'][] = $row;
				}
				//
				$json['status'] = "ok";
			} else {

				$json['status'] = "empty";
			}
		} else if ($role == 'thirdparty_admin') {
			$sel = $con->query("select c.* FROM clients c INNER JOIN users u ON u.user_id=c.user_id where u.user_id_added='$user_id' GROUP BY c.client_id ORDER BY c.time desc") or die($con->errorInfo());

			if ($sel->rowCount() > 0) {
				while ($row = $sel->fetch()) {

					$id = $row['client_id'];
					$pic = getpicture('../images/clients', $id);
					$row['avatar'] = $pic;

					$json['results'][] = $row;
				}
				//
				$json['status'] = "ok";
			} else {

				$json['status'] = "empty";
			}

			$sel1 = $con->query("select * FROM clients where user_id='$user_id' ORDER BY time desc") or die($con->errorInfo());

			while ($row1 = $sel1->fetch()) {

				$json['results'][] = $row1;
			}
		}
	} else {
		$json['status'] = "missing";
	}

	echo json_encode($json);
}


function LoadOrganisation()
{
	$con = connection();

	$json = array();

	if (isset($_REQUEST['user_id'], $_REQUEST['role'])) {

		$user_id = addslashes($_REQUEST['user_id']);
		$role = addslashes($_REQUEST['role']);

		if ($role == 'admin') {
			$sel = $con->query("select * FROM organisations ORDER BY time desc") or die($con->errorInfo());

			if ($sel->rowCount() > 0) {
				while ($row = $sel->fetch()) {

					$id = $row['organ_id'];
					$organPic = getpicture('../images/organisations', $id);
					$row['organPic'] = $organPic;

					$json['results'][] = $row;
				}
				//
				$json['status'] = "ok";
			} else {

				$json['status'] = "empty";
			}
		} else if ($role == 'organisation') {
			$sel = $con->query("select * FROM organisations where user_id='$user_id' ORDER BY time desc") or die($con->errorInfo());

			if ($sel->rowCount() > 0) {
				while ($row = $sel->fetch()) {

					$id = $row['organ_id'];
					$organPic = getpicture('../images/organisations', $id);
					$row['organPic'] = $organPic;

					$json['results'][] = $row;
				}
				//
				$json['status'] = "ok";
			} else {

				$json['status'] = "empty";
			}
		} else if ($role == 'organ_agent') {
			$sel = $con->query("select * FROM organisations where user_id='$user_id' ORDER BY time desc") or die($con->errorInfo());

			if ($sel->rowCount() > 0) {
				while ($row = $sel->fetch()) {

					$id = $row['organ_id'];
					$organPic = getpicture('../images/organisations', $id);
					$row['organPic'] = $organPic;

					$json['results'][] = $row;
				}
				//
				$json['status'] = "ok";
			} else {

				$json['status'] = "empty";
			}
		} else if ($role == 'thirdparty_admin' || $role == 'thirdparty_agent' || $role == 'mtn_dealer' || $role == 'mtn_agent') {
			$sel = $con->query("select * FROM organisations where organ_id='3' ORDER BY time desc") or die($con->errorInfo());

			if ($sel->rowCount() > 0) {
				while ($row = $sel->fetch()) {

					$id = $row['organ_id'];
					$organPic = getpicture('../images/organisations', $id);
					$row['organPic'] = $organPic;

					$json['results'][] = $row;
				}
				//
				$json['status'] = "ok";
			} else {

				$json['status'] = "empty";
			}
		}
	} else {
		$json['status'] = "missing";
	}

	echo json_encode($json);
}

function viewOrganisation()
{
	$con = connection();

	$json = array();

	if (isset($_REQUEST['organ_id'])) {
		$organ_id = addslashes($_REQUEST['organ_id']);
		$sel = $con->query("select * FROM organisations WHERE organ_id='$organ_id' ORDER BY time desc") or die($con->errorInfo());

		if ($sel->rowCount() > 0) {
			while ($row = $sel->fetch()) {

				$id = $row['organ_id'];
				$pic = getpicture('../images/organisations', $id);
				$row['logo'] = $pic;

				$json['results'][] = $row;
			}
			//
			$json['status'] = "ok";
		} else {

			$json['status'] = "empty";
		}
	} else {
		$json['status'] = "missing";
	}
	echo json_encode($json);
}

function editOrganisation()
{
	$con = connection();

	$json = array();



	if (isset($_REQUEST['organ_id'])) {
		$organ_id = addslashes($_REQUEST['organ_id']);
		$code = addslashes($_REQUEST['code']);
		$name = addslashes($_REQUEST['name']);
		$address = addslashes($_REQUEST['address']);
		$contact_name = addslashes($_REQUEST['contact_name']);
		$contact_email = addslashes($_REQUEST['contact_email']);
		$contact_tel = addslashes($_REQUEST['contact_tel']);



		$sel3 = $con->query("select * from organisations where organ_id='$organ_id'") or die($con->errorInfo());

		if ($sel3->rowCount() > 0) {


			$sel = $con->exec("UPDATE organisations set code='$code',name='$name',address='$address',contact_name='$contact_name',contact_email='$contact_email',contact_tel='$contact_tel' WHERE organ_id='$organ_id'") or die($con->errorInfo());


			if ($sel) {

				$sel2 = $con->query("select * from organisations where organ_id='$organ_id' ") or die($con->errorInfo());

				if ($sel2->rowCount() > 0) {

					while ($row = $sel2->fetch()) {
						$json['organ_id'] = $row['organ_id'];
					}
				}

				$json['status'] = 'ok';
				$json['type'] = 'success';
				$json['message'] = 'Organisation successfully edited';
			}
		} else {
			$json['status'] = 'notfound';
			$json['type'] = 'error';
			$json['message'] = 'Organisation not found';
		}
	} else {
		$json['status'] = "missing";

		$json['type'] = 'error';
		$json['message'] = 'Missing Fields';
	}
	echo json_encode($json);
}


function upload_edited_files()
{
	$folder_id = addslashes($_REQUEST['id']);
	$folder_name = addslashes($_REQUEST['folder_name']);


	if (!empty($_FILES)) {

		$tempPath = $_FILES['file']['tmp_name'];




		if (!file_exists("../images/" . $folder_name . "/" . $folder_id)) {

			mkdir("../images/" . $folder_name . "/" . $folder_id, 0777, true);
		} else {

			EmptyDir('../images/' . $folder_name . '/' . $folder_id);
		}


		$uploadPath = dirname(__FILE__) . DIRECTORY_SEPARATOR . '../images/' . $folder_name . '/' . $folder_id . DIRECTORY_SEPARATOR . $_FILES['file']['name'];

		move_uploaded_file($tempPath, $uploadPath);

		$answer = array('answer' => 'File transfer completed' . $tempPath);
		$json = json_encode($answer);

		echo $json;
	} else {

		echo 'No files';
	}
}

function removeOrganisation()
{
	$con = connection();

	$json = array();

	if (isset($_REQUEST['organ_id'])) {
		$id = addslashes($_REQUEST['organ_id']);

		$sel = $con->exec("DELETE FROM organisations WHERE organ_id='$id'") or die($con->errorInfo());

		if ($sel) {
			deleteDirectory('../images/organisations/' . $id);

			$json['status'] = 'ok';
		} else {
			$json['status'] = 'empty';
		}
	} else {
		$json['status'] = "missing";
	}
	echo json_encode($json);
}


function LoadClients()
{
	$con = connection();

	$json = array();

	$sel = $con->query("select * FROM clients ORDER BY time desc") or die($con->errorInfo());

	if ($sel->rowCount() > 0) {
		while ($row = $sel->fetch()) {

			$id = $row['client_id'];
			$clientPic = getpicture('../images/clients', $id);
			$row['clientPic'] = $clientPic;

			$json['results'][] = $row;
		}
		//
		$json['status'] = "ok";
	} else {

		$json['status'] = "empty";
	}

	echo json_encode($json);
}


function generateBar()
{
	$con = connection();
	$json = array();
	// $sql="select * from motor_invoices";
	$start = '2019-01-01';
	$end = date('Y-m-d', strtotime("+5 days"));

	if (isset($_REQUEST['user_id'])) {
		$user_id = ($_REQUEST['user_id']);
		$role = ($_REQUEST['role']);

		if ($role == 'admin') {
			$sql = "SELECT YEAR((m.time)) as y, MONTH((m.time)) as month, COUNT(m.invoice_id) as count
FROM motor_invoices m
WHERE m.policy_no <>'' AND (m.time >= '$start' AND  m.time <='$end')
GROUP BY MONTH((m.time))
ORDER BY YEAR((m.time)), MONTH((m.time));";

			$result = $con->query($sql) or die($con->errorInfo());
			// while ($row = mysqli_fetch_array($result)) {
			while ($row = $result->fetch()) {

				if ($row['month'] == 1) {
					$row['month'] = 'Jan';
				} else if ($row['month'] == 2) {
					$row['month'] = 'Feb';
				} else if ($row['month'] == 3) {
					$row['month'] = 'Mar';
				} else if ($row['month'] == 4) {
					$row['month'] = 'Apr';
				} else if ($row['month'] == 5) {
					$row['month'] = 'May';
				} else if ($row['month'] == 6) {
					$row['month'] = 'Jun';
				} else if ($row['month'] == 7) {
					$row['month'] = 'Jul';
				} else if ($row['month'] == 8) {
					$row['month'] = 'Aug';
				} else if ($row['month'] == 9) {
					$row['month'] = 'Sep';
				} else if ($row['month'] == 10) {
					$row['month'] = 'Oct';
				} else if ($row['month'] == 11) {
					$row['month'] = 'Nov';
				} else if ($row['month'] == 12) {
					$row['month'] = 'Dec';
				}


				$month = $row['month'];
				$count = $row['count'];

				$json['month'][] = $month;
				$json['count'][] = $count;

				$json['status'] = "ok";
			}

			// $response['label'] = "Stickers"; //"label": "Quotes", 
			// $response['color']= "#b905a2";//"color": "#b905a2",
			// $response['data'] = $posts;

			// $fp = fopen('../server/chart/bargraph_stickers.json', 'w');
			// fwrite($fp, json_encode(array($response)));
			// fclose($fp);	

		} else {
			$sql = "SELECT YEAR((m.time)) as y, MONTH((m.time)) as month, COUNT(m.invoice_id) as count
FROM motor_invoices m
WHERE m.policy_no <>'' AND m.user_id='$user_id' AND (m.time >= '$start' AND  m.time <='$end')
GROUP BY MONTH((m.time))
ORDER BY YEAR((m.time)), MONTH((m.time));";

			$response = array();
			$posts = array();
			$result = $con->query($sql) or die($con->errorInfo());
			while ($row = $result->fetch()) {

				if ($row['month'] == 8) {
					$row['month'] = 'Aug';
				} else if ($row['month'] == 9) {
					$row['month'] = 'Sep';
				} else if ($row['month'] == 10) {
					$row['month'] = 'Oct';
				} else if ($row['month'] == 11) {
					$row['month'] = 'Nov';
				} else if ($row['month'] == 12) {
					$row['month'] = 'Dec';
				} else if ($row['month'] == 7) {
					$row['month'] = 'Jul';
				} else if ($row['month'] == 6) {
					$row['month'] = 'Jun';
				} else if ($row['month'] == 5) {
					$row['month'] = 'May';
				} else if ($row['month'] == 4) {
					$row['month'] = 'Apr';
				} else if ($row['month'] == 3) {
					$row['month'] = 'Mar';
				} else if ($row['month'] == 2) {
					$row['month'] = 'Feb';
				} else if ($row['month'] == 1) {
					$row['month'] = 'Jan';
				}


				$month = $row['month'];
				$count = $row['count'];

				$json['month'][] = $month;
				$json['count'][] = $count;

				$json['status'] = "ok";
			}
		}
	} else {

		$json['status'] = "empty";
	}
	echo json_encode($json);
}

function viewInvoice()
{
	$con = connection();

	$json = array();

	if (isset($_REQUEST['invoice_id'])) {

		$invoice_id = addslashes($_REQUEST['invoice_id']);

		$sel = $con->query("select q.*,DATE_FORMAT(q.start_date, '%d/%m/%Y') as start_date,DATE_FORMAT(q.end_date, '%d/%m/%Y') as end_date,DATE_FORMAT(q.time, '%d/%m/%Y %r') as t,o.code as organ_code,o.name as organ_name,o.address as organ_address,CONCAT(o.contact_name,' ',o.contact_email,' ',o.contact_tel) AS organ_contact, (SELECT ccc1.telephone FROM clients ccc1 WHERE ccc1.client_id=q.client_id) AS client_telephone,(SELECT ccc.email FROM clients ccc WHERE ccc.client_id=q.client_id) AS client_email,(SELECT cc.address FROM clients cc WHERE cc.client_id=q.client_id) AS client_address,(SELECT c.name FROM clients c WHERE c.client_id=q.client_id) AS client_name,(SELECT (u3.name) FROM users u3 WHERE u3.user_id=q.user_id) AS agent_details,(SELECT u2.license_no FROM users u2 WHERE u2.user_id=q.user_id) AS license_no FROM motor_invoices q INNER JOIN organisations o ON o.organ_id=q.organ_id WHERE q.invoice_id='$invoice_id'") or die($con->errorInfo());

		if ($sel->rowCount() > 0) {
			while ($row = $sel->fetch()) {


				$organ_id = $row['organ_id'];
				$pic = getpicture('../images/organisations', $organ_id);
				$row['logo'] = $pic;
				$row['policyNumber'] = $row['policy_no'];
				$row['sticker_fees'] = STICKER_FEES;
				$row['stamp_duty'] = STAMP_DUTY;
				$count = 0;

				$sel3 = $con->query("select d.* FROM motor_invoice_details d INNER JOIN motor_invoices t ON d.invoice_id=t.invoice_id WHERE d.invoice_id='$invoice_id'") or die($con->errorInfo());
				while ($row3 = $sel3->fetch()) {


					$count += 1;
					//$row3['terms']=explode(',',$row3['terms']);
					$row['motor_thirdparty_details'][] = $row3;
				}

				$row['sticker_fees'] = ($count * $row['sticker_fees']);

				$row['stamp_duty'] = ($count * $row['stamp_duty']);

				$training_levy = ($row['basic_premium'] * 0.005);

				$vat = (($training_levy) + ($row['basic_premium']) + ($row['sticker_fees'])) * 0.18;

				$row['total_premium'] = (($row['basic_premium']) + ($row['sticker_fees']) + ($row['stamp_duty']) + ($training_levy) + ($vat));

				$json['results'][] = $row;
			}
			//
			$json['status'] = "ok";
		} else {

			$json['status'] = "empty";
		}
	} else {
		$json['status'] = "missing";
	}
	echo json_encode($json);
}

function viewInvoiceDetails()
{
	$con = connection();

	$json = array();

	if (isset($_REQUEST['invoice_detail_id'])) {

		$invoice_detail_id = addslashes($_REQUEST['invoice_detail_id']);

		$sel = $con->query("select q.*,i.organ_id,i.policy_no,i.iclass,i.currency,DATE_FORMAT(i.start_date, '%d/%m/%Y') as start_date,DATE_FORMAT(i.end_date, '%d/%m/%Y') as end_date, (SELECT o.name FROM organisations o WHERE o.organ_id=i.organ_id) as organ_name,(SELECT c.name FROM clients c WHERE c.client_id=i.client_id) AS client_name,(SELECT u3.name FROM users u3 WHERE u3.user_id=i.user_id) AS agent_details FROM motor_invoice_details q INNER JOIN motor_invoices i ON i.invoice_id=q.invoice_id WHERE q.invoice_detail_id='$invoice_detail_id'") or die($con->errorInfo());

		if ($sel->rowCount() > 0) {
			while ($row = $sel->fetch()) {


				$row['sticker_fees'] = STICKER_FEES;
				$row['stamp_duty'] = STAMP_DUTY;

				$json['results'][] = $row;
			}
			//
			$json['status'] = "ok";
		} else {

			$json['status'] = "empty";
		}
	} else {
		$json['status'] = "missing";
	}
	echo json_encode($json);
}


function viewInvoiceFleetDetails()
{
	$con = connection();

	$json = array();

	if (isset($_REQUEST['invoice_id'])) {

		$invoice_id = addslashes($_REQUEST['invoice_id']);

		$sel = $con->query("select q.*,(SELECT t.policy_no FROM motor_invoices t WHERE t.invoice_id=q.invoice_id) AS policy_no,(SELECT y.currency FROM motor_invoices y WHERE y.invoice_id=q.invoice_id) AS currency,(SELECT DATE_FORMAT(iv.start_date, '%d/%m/%Y') FROM motor_invoices iv WHERE iv.invoice_id=q.invoice_id) as start_date,(SELECT DATE_FORMAT(v.end_date, '%d/%m/%Y') FROM motor_invoices v WHERE v.invoice_id=q.invoice_id) as end_date, (SELECT o.name FROM organisations o WHERE o.organ_id=(SELECT vi.organ_id FROM  motor_invoices vi WHERE vi.invoice_id=q.invoice_id)) as organ_name,(SELECT c.name FROM clients c WHERE c.client_id=(SELECT vii.client_id FROM  motor_invoices vii WHERE vii.invoice_id=q.invoice_id)) AS client_name,(SELECT u3.name FROM users u3 WHERE u3.user_id=q.user_id) AS agent_details FROM motor_invoice_details q WHERE q.invoice_id='$invoice_id'") or die($con->errorInfo());

		if ($sel->rowCount() > 0) {
			while ($row = $sel->fetch()) {


				// $row['sticker_fees']='6000'; 
				$row['sticker_fees'] = STICKER_FEES;
				$row['stamp_duty'] = STAMP_DUTY;
				$row['total_amount'] = ((($row['sticker_fees']) + ($row['basic_premium']) + ($row['training_levy'])) * 0.18) + (($row['sticker_fees']) + ($row['basic_premium']) + ($row['training_levy'])) + ($row['stamp_duty']);

				$json['results'][] = $row;
			}
			//
			$json['status'] = "ok";
		} else {

			$json['status'] = "empty";
		}
	} else {
		$json['status'] = "missing";
	}
	echo json_encode($json);
}


function viewInvoices()
{
	$con = connection();

	$json = array();



	if (isset($_REQUEST['user_id'], $_REQUEST['role'])) {

		$user_id = addslashes($_REQUEST['user_id']);
		$role = addslashes($_REQUEST['role']);

		if ($role == 'admin') {

			$sel = $con->query("select q.*,DATE_FORMAT(q.time, '%d/%m/%Y %r') as t,(SELECT ccc1.telephone FROM clients ccc1 WHERE ccc1.client_id=q.client_id) AS client_telephone,(SELECT ccc.email FROM clients ccc WHERE ccc.client_id=q.client_id) AS client_email,(SELECT cc.address FROM clients cc WHERE cc.client_id=q.client_id) AS client_address,(SELECT c.name FROM clients c WHERE c.client_id=q.client_id) AS client_name,(SELECT CONCAT(u3.name,' ',u3.email,' ',u3.telephone) FROM users u3 WHERE u3.user_id=q.user_id) AS agent_details,(SELECT u2.license_no FROM users u2 WHERE u2.user_id=q.user_id) AS license_no,(SELECT o2.code FROM organisations o2 WHERE o2.organ_id=p.organ_id) AS organ_code,(SELECT o22.organ_id FROM organisations o22 WHERE o22.organ_id=p.organ_id) AS organ_id,(SELECT o.name FROM organisations o WHERE o.organ_id=p.organ_id) AS organ_name,(SELECT oo.address FROM organisations oo WHERE oo.organ_id=p.organ_id) AS organ_address,(SELECT CONCAT(ooo.contact_name,' ',ooo.contact_email,' ',ooo.contact_tel) AS contact FROM organisations ooo WHERE ooo.organ_id=p.organ_id) AS organ_contact FROM motor_invoices q INNER JOIN organisations p ON p.organ_id=q.organ_id GROUP BY q.invoice_id ORDER BY q.invoice_id DESC") or die($con->errorInfo());

			if ($sel->rowCount() > 0) {
				while ($row = $sel->fetch()) {

					$invoice_id = $row['invoice_id'];
					$row['sticker_fees'] = STICKER_FEES;
					$row['stamp_duty'] = STAMP_DUTY;
					$count = 0;

					$sel3 = $con->query("select d.* FROM motor_invoice_details d INNER JOIN motor_invoices t ON d.invoice_id=t.invoice_id WHERE d.invoice_id='$invoice_id'") or die($con->errorInfo());
					while ($row3 = $sel3->fetch()) {


						$count += 1;
						//$row3['terms']=explode(',',$row3['terms']);
						$row['motor_thirdparty_details'][] = $row3;
					}

					$row['sticker_fees'] = ($count * $row['sticker_fees']);

					$row['stamp_duty'] = ($count * $row['stamp_duty']);

					$json['results'][] = $row;
				}

				//
				$json['status'] = "ok";
			} else {

				$json['status'] = "empty";
			}
		} else if ($role == 'thirdparty_agent' || $role == 'mtn_agent') {

			$sel = $con->query("select q.*,DATE_FORMAT(q.time, '%d/%m/%Y %r') as t,(SELECT ccc1.telephone FROM clients ccc1 WHERE ccc1.client_id=q.client_id) AS client_telephone,(SELECT ccc.email FROM clients ccc WHERE ccc.client_id=q.client_id) AS client_email,(SELECT cc.address FROM clients cc WHERE cc.client_id=q.client_id) AS client_address,(SELECT c.name FROM clients c WHERE c.client_id=q.client_id) AS client_name,(SELECT CONCAT(u3.name,' ',u3.email,' ',u3.telephone) FROM users u3 WHERE u3.user_id=q.user_id) AS agent_details,(SELECT u2.license_no FROM users u2 WHERE u2.user_id=q.user_id) AS license_no,(SELECT o2.code FROM organisations o2 WHERE o2.organ_id=p.organ_id) AS organ_code,(SELECT o22.organ_id FROM organisations o22 WHERE o22.organ_id=p.organ_id) AS organ_id,(SELECT o.name FROM organisations o WHERE o.organ_id=p.organ_id) AS organ_name,(SELECT oo.address FROM organisations oo WHERE oo.organ_id=p.organ_id) AS organ_address,(SELECT CONCAT(ooo.contact_name,' ',ooo.contact_email,' ',ooo.contact_tel) AS contact FROM organisations ooo WHERE ooo.organ_id=p.organ_id) AS organ_contact FROM motor_invoices q INNER JOIN organisations p ON p.organ_id=q.organ_id WHERE q.user_id='$user_id' GROUP BY q.invoice_id ORDER BY q.invoice_id DESC") or die($con->errorInfo());

			if ($sel->rowCount() > 0) {
				while ($row = $sel->fetch()) {

					$invoice_id = $row['invoice_id'];
					$row['sticker_fees'] = STICKER_FEES;
					$row['stamp_duty'] = STAMP_DUTY;
					$count = 0;

					$sel3 = $con->query("select d.* FROM motor_invoice_details d INNER JOIN motor_invoices t ON d.invoice_id=t.invoice_id WHERE d.invoice_id='$invoice_id'") or die($con->errorInfo());
					while ($row3 = $sel3->fetch()) {


						$count += 1;
						//$row3['terms']=explode(',',$row3['terms']);
						$row['motor_thirdparty_details'][] = $row3;
					}

					$row['sticker_fees'] = ($count * $row['sticker_fees']);
					$row['stamp_duty'] = ($count * $row['stamp_duty']);

					$json['results'][] = $row;
				}

				//
				$json['status'] = "ok";
			} else {

				$json['status'] = "empty";
			}
		} else if ($role == 'thirdparty_admin' || $role == 'mtn_dealer') {

			$sel = $con->query("select q.*,DATE_FORMAT(q.time, '%d/%m/%Y %r') as t,(SELECT ccc1.telephone FROM clients ccc1 WHERE ccc1.client_id=q.client_id) AS client_telephone,(SELECT ccc.email FROM clients ccc WHERE ccc.client_id=q.client_id) AS client_email,(SELECT cc.address FROM clients cc WHERE cc.client_id=q.client_id) AS client_address,(SELECT c.name FROM clients c WHERE c.client_id=q.client_id) AS client_name,(SELECT CONCAT(u3.name,' ',u3.email,' ',u3.telephone) FROM users u3 WHERE u3.user_id=q.user_id) AS agent_details,(SELECT u2.license_no FROM users u2 WHERE u2.user_id=q.user_id) AS license_no,(SELECT o2.code FROM organisations o2 WHERE o2.organ_id=p.organ_id) AS organ_code,(SELECT o22.organ_id FROM organisations o22 WHERE o22.organ_id=p.organ_id) AS organ_id,(SELECT o.name FROM organisations o WHERE o.organ_id=p.organ_id) AS organ_name,(SELECT oo.address FROM organisations oo WHERE oo.organ_id=p.organ_id) AS organ_address,(SELECT CONCAT(ooo.contact_name,' ',ooo.contact_email,' ',ooo.contact_tel) AS contact FROM organisations ooo WHERE ooo.organ_id=p.organ_id) AS organ_contact FROM motor_invoices q INNER JOIN organisations p ON p.organ_id=q.organ_id WHERE q.user_id='$user_id' GROUP BY q.invoice_id ORDER BY q.invoice_id DESC") or die($con->errorInfo());

			if ($sel->rowCount() > 0) {
				while ($row = $sel->fetch()) {

					$invoice_id = $row['invoice_id'];
					$row['sticker_fees'] = STICKER_FEES;
					$row['stamp_duty'] = STAMP_DUTY;
					$count = 0;

					$sel3 = $con->query("select d.* FROM motor_invoice_details d INNER JOIN motor_invoices t ON d.invoice_id=t.invoice_id WHERE d.invoice_id='$invoice_id'") or die($con->errorInfo());
					while ($row3 = $sel3->fetch()) {


						$count += 1;
						//$row3['terms']=explode(',',$row3['terms']);
						$row['motor_thirdparty_details'][] = $row3;
					}

					$row['sticker_fees'] = ($count * $row['sticker_fees']);
					$row['stamp_duty'] = ($count * $row['stamp_duty']);

					$json['results'][] = $row;
				}

				//
				$json['status'] = "ok";
			} else {

				$json['status'] = "empty";
			}


			$sel1 = $con->query("select q.*,DATE_FORMAT(q.time, '%d/%m/%Y %r') as t,(SELECT ccc1.telephone FROM clients ccc1 WHERE ccc1.client_id=q.client_id) AS client_telephone,(SELECT ccc.email FROM clients ccc WHERE ccc.client_id=q.client_id) AS client_email,(SELECT cc.address FROM clients cc WHERE cc.client_id=q.client_id) AS client_address,(SELECT c.name FROM clients c WHERE c.client_id=q.client_id) AS client_name,(SELECT CONCAT(u3.name,' ',u3.email,' ',u3.telephone) FROM users u3 WHERE u3.user_id=q.user_id) AS agent_details,(SELECT u2.license_no FROM users u2 WHERE u2.user_id=q.user_id) AS license_no,(SELECT o2.code FROM organisations o2 WHERE o2.user_id=p.user_id) AS organ_code,(SELECT o22.organ_id FROM organisations o22 WHERE o22.user_id=p.user_id) AS organ_id,(SELECT o.name FROM organisations o WHERE o.user_id=p.user_id) AS organ_name,(SELECT oo.address FROM organisations oo WHERE oo.user_id=p.user_id) AS organ_address,(SELECT CONCAT(ooo.contact_name,' ',ooo.contact_email,' ',ooo.contact_tel) AS contact FROM organisations ooo WHERE ooo.user_id=p.user_id) AS organ_contact FROM motor_invoices q INNER JOIN users p ON p.user_id=q.user_id WHERE p.user_id_added='$user_id' GROUP BY q.invoice_id ORDER BY q.invoice_id DESC") or die($con->errorInfo());

			if ($sel1->rowCount() > 0) {
				while ($row1 = $sel1->fetch()) {

					$invoice_id1 = $row1['invoice_id'];
					$row1['sticker_fees'] = STICKER_FEES;
					$row1['stamp_duty'] = STAMP_DUTY;
					$count1 = 0;

					$sel33 = $con->query("select d.* FROM motor_invoice_details d INNER JOIN motor_invoices t ON d.invoice_id=t.invoice_id WHERE d.invoice_id='$invoice_id1'") or die($con->errorInfo());
					while ($row33 = $sel33) {


						$count1 += 1;
						//$row3['terms']=explode(',',$row3['terms']);
						$row1['motor_thirdparty_details'][] = $row33;
					}

					$row1['sticker_fees'] = ($count1 * $row1['sticker_fees']);
					$row1['stamp_duty'] = ($count1 * $row1['stamp_duty']);

					$json['results'][] = $row1;
				}

				//
				$json['status'] = "ok";
			}
		}
	} else {
		$json['status'] = "missing";
	}

	echo json_encode($json);
}


function viewWindscreens()
{
	$con = connection();

	$json = array();



	if (isset($_REQUEST['user_id'], $_REQUEST['role'])) {

		$user_id = addslashes($_REQUEST['user_id']);
		$role = addslashes($_REQUEST['role']);

		if ($role == 'admin') {

			$sel = $con->query("select q.*,DATE_FORMAT(q.time, '%d/%m/%Y %r') as t,c.name AS client_name,(SELECT CONCAT(u3.name,' ',u3.email,' ',u3.telephone) FROM users u3 WHERE u3.user_id=q.user_id_charged) AS agent_details FROM windscreen_policy q INNER JOIN clients c ON c.client_id=q.client_id GROUP BY q.windscreen_id ORDER BY q.windscreen_id DESC") or die($con->errorInfo());

			if ($sel->rowCount() > 0) {
				while ($row = $sel->fetch()) {

					$row['stamp_duty'] = STAMP_DUTY;

					$json['results'][] = $row;
				}

				//
				$json['status'] = "ok";
			} else {

				$json['status'] = "empty";
			}
		} else if ($role == 'thirdparty_agent' || $role == 'mtn_agent') {

			$sel = $con->query("select q.*,DATE_FORMAT(q.time, '%d/%m/%Y %r') as t,c.name AS client_name,(SELECT CONCAT(u3.name,' ',u3.email,' ',u3.telephone) FROM users u3 WHERE u3.user_id=q.user_id_charged) AS agent_details FROM windscreen_policy q INNER JOIN clients c ON c.client_id=q.client_id WHERE user_id_charged='$user_id' GROUP BY q.windscreen_id ORDER BY q.windscreen_id DESC") or die($con->errorInfo());

			if ($sel->rowCount() > 0) {
				while ($row = $sel->fetch()) {

					$row['stamp_duty'] = STAMP_DUTY;

					$json['results'][] = $row;
				}

				//
				$json['status'] = "ok";
			} else {

				$json['status'] = "empty";
			}
		} else if ($role == 'thirdparty_admin' || $role == 'mtn_dealer') {

			$sel = $con->query("select q.*,DATE_FORMAT(q.time, '%d/%m/%Y %r') as t,c.name AS client_name,(SELECT CONCAT(u3.name,' ',u3.email,' ',u3.telephone) FROM users u3 WHERE u3.user_id=q.user_id_charged) AS agent_details FROM windscreen_policy q INNER JOIN clients c ON c.client_id=q.client_id WHERE user_id_charged='$user_id' GROUP BY q.windscreen_id ORDER BY q.windscreen_id DESC") or die($con->errorInfo());

			if ($sel->rowCount() > 0) {
				while ($row = $sel->fetch()) {

					$row['stamp_duty'] = STAMP_DUTY;

					$json['results'][] = $row;
				}

				//
				$json['status'] = "ok";
			} else {

				$json['status'] = "empty";
			}


			$sel1 = $con->query("select q.*,DATE_FORMAT(q.time, '%d/%m/%Y %r') as t,(SELECT c.name FROM clients c WHERE c.client_id=q.client_id) AS client_name,(SELECT CONCAT(u3.name,' ',u3.email,' ',u3.telephone) FROM users u3 WHERE u3.user_id=q.user_id_charged) AS agent_details FROM windscreen_policy q INNER JOIN users u ON u.user_id=q.user_id_added WHERE user_id_charged='$user_id' GROUP BY q.windscreen_id ORDER BY q.windscreen_id DESC") or die($con->errorInfo());

			if ($sel1->rowCount() > 0) {
				while ($row1 = $sel1->fetch()) {

					$row1['stamp_duty'] = STAMP_DUTY;

					$json['results'][] = $row1;
				}

				//
				$json['status'] = "ok";
			}
		}
	} else {
		$json['status'] = "missing";
	}

	echo json_encode($json);
}


function viewStickers()
{
	$con = connection();

	$json = array();



	if (isset($_REQUEST['user_id'], $_REQUEST['role'])) {

		$user_id = addslashes($_REQUEST['user_id']);
		$role = addslashes($_REQUEST['role']);

		if ($role == 'admin') {

			$sel = $con->query("select s.*,u.name as agent,DATE_FORMAT(s.time, '%d/%m/%Y %r') as t FROM organ_stickers s INNER JOIN users u ON u.user_id=s.agent_user_id GROUP BY s.sticker_id") or die($con->errorInfo());

			if ($sel->rowCount() > 0) {
				while ($row = $sel->fetch()) {


					$json['results'][] = $row;
				}

				//
				$json['status'] = "ok";
			} else {

				$json['status'] = "empty";
			}
		} else if ($role == 'thirdparty_admin' || $role == 'mtn_dealer') {
			// $sel=$con->query("select *,DATE_FORMAT(time, '%d/%m/%Y %r') as t FROM organ_stickers where user_id='$user_id'")or die($con->errorInfo());
			$sel = $con->query("select s.*,u.name as agent,DATE_FORMAT(s.time, '%d/%m/%Y %r') as t FROM organ_stickers s INNER JOIN users u ON u.user_id=s.agent_user_id GROUP BY s.sticker_id") or die($con->errorInfo());

			if ($sel->rowCount() > 0) {
				while ($row = $sel->fetch()) {


					$json['results'][] = $row;
				}

				//
				$json['status'] = "ok";
			} else {

				$json['status'] = "empty";
			}
		} else if ($role == 'thirdparty_agent' || $role == 'mtn_agent') {
			// $sel=$con->query("select *,DATE_FORMAT(time, '%d/%m/%Y %r') as t FROM organ_stickers where user_id='$user_id'")or die($con->errorInfo());
			$sel = $con->query("select s.*,u.name as agent,DATE_FORMAT(s.time, '%d/%m/%Y %r') as t FROM organ_stickers s INNER JOIN users u ON u.user_id=s.agent_user_id WHERE s.agent_user_id='$user_id' GROUP BY s.sticker_id") or die($con->errorInfo());

			if ($sel->rowCount() > 0) {
				while ($row = $sel->fetch()) {


					$json['results'][] = $row;
				}

				//
				$json['status'] = "ok";
			} else {

				$json['status'] = "empty";
			}
		}
	} else {
		$json['status'] = "missing";
	}

	echo json_encode($json);
}

function viewPolicy_nos()
{
	$con = connection();

	$json = array();



	if (isset($_REQUEST['user_id'], $_REQUEST['role'])) {

		$user_id = addslashes($_REQUEST['user_id']);
		$role = addslashes($_REQUEST['role']);
		// $organ_id = addslashes($_REQUEST['organ_id']); 

		if ($role == 'admin') {

			$sel = $con->query("SELECT *,DATE_FORMAT(time, '%d/%m/%Y %r') as t FROM organ_policy_no") or die($con->errorInfo());

			if ($sel->rowCount() > 0) {
				while ($row = $sel->fetch()) {


					$json['results'][] = $row;
				}

				//
				$json['status'] = "ok";
			} else {

				$json['status'] = "empty";
			}
		} else if ($role == 'thirdparty_admin' || $role == 'mtn_dealer') {
			// $sel=$con->query("select *,DATE_FORMAT(time, '%d/%m/%Y %r') as t FROM organ_stickers where user_id='$user_id'")or die($con->errorInfo());
			$sel = $con->query("SELECT *,DATE_FORMAT(time, '%d/%m/%Y %r') as t FROM organ_policy_no WHERE user_id='$user_id'") or die($con->errorInfo());

			if ($sel->rowCount() > 0) {
				while ($row = $sel->fetch()) {


					$json['results'][] = $row;
				}

				//
				$json['status'] = "ok";
			} else {

				$json['status'] = "empty";
			}
		} else if ($role == 'thirdparty_agent') {
			// $sel=$con->query("select *,DATE_FORMAT(time, '%d/%m/%Y %r') as t FROM organ_stickers where user_id='$user_id'")or die($con->errorInfo());
			$sel = $con->query("SELECT * FROM organ_policy_no WHERE status='notused'") or die($con->errorInfo());

			if ($sel->rowCount() > 0) {
				while ($row = $sel->fetch()) {


					$json['results'][] = $row;
				}

				//
				$json['status'] = "ok";
			} else {

				$json['status'] = "empty";
			}
		}
	} else {
		$json['status'] = "missing";
	}

	echo json_encode($json);
}


function viewThirdpartyUsers()
{
	$con = connection();

	$json = array();



	if (isset($_REQUEST['user_id'], $_REQUEST['role'])) {

		$user_id = addslashes($_REQUEST['user_id']);
		$role = addslashes($_REQUEST['role']);

		if ($role == 'thirdparty_admin' || $role == 'mtn_dealer' || $role == 'admin') {
			$sel = $con->query("select *,DATE_FORMAT(time, '%d/%m/%Y %r') as t FROM users where user_id_added='$user_id'") or die($con->errorInfo());

			if ($sel->rowCount() > 0) {
				while ($row = $sel->fetch()) {
					$id = $row['user_id'];
					$pic = getpicture('../images/users', $id);
					$row['avatar'] = $pic;
					$json['results'][] = $row;
				}

				//
				$json['status'] = "ok";
			} else {

				$json['status'] = "empty";
			}
		}
	} else {
		$json['status'] = "missing";
	}

	echo json_encode($json);
}


function RevenueReports()
{
	$con = connection();

	$json = array();


	// AND (q.time >= '$date_from' AND q.time <= '$date_to')
	$sel = $con->query("select q.*,(year(q.end_date) - year(q.start_date)) AS validity,p.sticker_no,p.vehicle_use,p.vehicle_plate_no,p.vehicle_no_seats,p.vehicle_make,p.vehicle_category,p.sticker_no,p.gross_weight,p.vehicle_cc,p.basic_premium as basic_p,DATE_FORMAT(q.start_date, '%d/%m/%Y') AS start_date,DATE_FORMAT(q.end_date, '%d/%m/%Y') AS end_date,DATE_FORMAT(q.time, '%d/%m/%Y') as t,(SELECT ccc.telephone FROM clients ccc WHERE ccc.client_id=q.client_id) AS client_tel,(SELECT cc.address FROM clients cc WHERE cc.client_id=q.client_id) AS client_address,(SELECT c.name FROM clients c WHERE c.client_id=q.client_id) AS client_name,(SELECT CONCAT(u3.name) FROM users u3 WHERE u3.user_id=q.user_id) AS agent_details,(SELECT (us.branch_name) FROM users us WHERE us.user_id=q.user_id) AS branch,(SELECT o.name FROM organisations o WHERE o.organ_id=q.organ_id) AS organ_name FROM motor_invoices q INNER JOIN motor_invoice_details p ON p.invoice_id=q.invoice_id WHERE (q.status='new' OR q.status='replaced' OR q.status='completed' OR q.status='paid') AND q.iclass='thirdparty' GROUP BY p.invoice_detail_id") or die($con->errorInfo());

	if ($sel->rowCount() > 0) {
		while ($row = $sel->fetch()) {

			$row['stamp_duty'] = STAMP_DUTY;
			$row['sticker_fees'] = STICKER_FEES;
			$json['results'][] = $row;
		}
		//
		$json['status'] = "ok";
	} else {

		$json['status'] = "empty";
	}

	echo json_encode($json);
}




function MonthlyRevenueReports()
{
	$con = connection();

	$json = array();

	if (isset($_REQUEST['user_id'], $_REQUEST['role'])) {

		$user_id = addslashes($_REQUEST['user_id']);
		$date_from = (date('Y-m-d', strtotime(substr($_REQUEST['date_from'], 0, 10))));
		// $date_from = addslashes($_REQUEST['date_from']); 
		// $date_to = addslashes($_REQUEST['date_to']);
		$date_to = (date('Y-m-d', strtotime(substr($_REQUEST['date_to'], 0, 10))));
		$role = addslashes($_REQUEST['role']);

		if ($role == 'admin') {
			// AND (q.time >= '$date_from' AND q.time <= '$date_to')
			$sel = $con->query("select q.*,(year(q.end_date) - year(q.start_date)) AS validity,p.sticker_no,p.vehicle_use,p.vehicle_plate_no,p.vehicle_no_seats,p.vehicle_make,p.vehicle_category,p.sticker_no,p.gross_weight,p.vehicle_cc,p.basic_premium as basic_p,DATE_FORMAT(q.start_date, '%d/%m/%Y') AS start_date,DATE_FORMAT(q.end_date, '%d/%m/%Y') AS end_date,DATE_FORMAT(q.time, '%d/%m/%Y') as t,(SELECT ccc.telephone FROM clients ccc WHERE ccc.client_id=q.client_id) AS client_tel,(SELECT cc.address FROM clients cc WHERE cc.client_id=q.client_id) AS client_address,(SELECT c.name FROM clients c WHERE c.client_id=q.client_id) AS client_name,(SELECT CONCAT(u3.name) FROM users u3 WHERE u3.user_id=q.user_id) AS agent_details,(SELECT (us.branch_name) FROM users us WHERE us.user_id=q.user_id) AS branch,(SELECT o.name FROM organisations o WHERE o.organ_id=q.organ_id) AS organ_name FROM motor_invoices q INNER JOIN motor_invoice_details p ON p.invoice_id=q.invoice_id WHERE (q.status='new' OR q.status='replaced' OR q.status='completed' OR q.status='paid') AND (q.time >= '$date_from' AND q.time <= '$date_to') GROUP BY p.invoice_detail_id") or die($con->errorInfo());

			if ($sel->rowCount() > 0) {
				while ($row = $sel->fetch()) {

					$row['stamp_duty'] = STAMP_DUTY;
					$row['sticker_fees'] = STICKER_FEES;
					$json['results'][] = $row;
				}
				//
				$json['status'] = "ok";
			} else {

				$json['status'] = "empty";
			}
		} else if ($role == 'thirdparty_admin' || $role == 'mtn_dealer') {
			$sel = $con->query("select q.*,(year(q.end_date) - year(q.start_date)) AS validity,p.sticker_no,p.vehicle_use,p.vehicle_plate_no,p.vehicle_no_seats,p.vehicle_make,p.vehicle_category,p.sticker_no,p.gross_weight,p.vehicle_cc,p.basic_premium as basic_p,DATE_FORMAT(q.start_date, '%d/%m/%Y') AS start_date,DATE_FORMAT(q.end_date, '%d/%m/%Y') AS end_date,DATE_FORMAT(q.time, '%d/%m/%Y') as t,(SELECT ccc.telephone FROM clients ccc WHERE ccc.client_id=q.client_id) AS client_tel,(SELECT cc.address FROM clients cc WHERE cc.client_id=q.client_id) AS client_address,(SELECT c.name FROM clients c WHERE c.client_id=q.client_id) AS client_name,(SELECT CONCAT(u3.name) FROM users u3 WHERE u3.user_id=q.user_id) AS agent_details,(SELECT (us.branch_name) FROM users us WHERE us.user_id=q.user_id) AS branch,(SELECT o.name FROM organisations o WHERE o.organ_id=q.organ_id) AS organ_name FROM motor_invoices q INNER JOIN motor_invoice_details p ON p.invoice_id=q.invoice_id WHERE (q.status='new' OR q.status='replaced' OR q.status='completed' OR q.status='paid') AND (q.time >= '$date_from' AND q.time <= '$date_to') AND q.user_id='$user_id' GROUP BY p.invoice_detail_id") or die($con->errorInfo());

			if ($sel->rowCount() > 0) {
				while ($row = $sel->fetch()) {

					$row['stamp_duty'] = STAMP_DUTY;
					$row['sticker_fees'] = STICKER_FEES;
					$json['results'][] = $row;
				}
				//
				$json['status'] = "ok";
			} else {

				$json['status'] = "empty";
			}
		}
	} else {

		$json['status'] = "missing";
	}
	echo json_encode($json);
}





function MtpReports()
{
	$con = connection();

	$json = array();


	if (isset($_REQUEST['user_id'], $_REQUEST['role'])) {

		$user_id = addslashes($_REQUEST['user_id']);
		// $date = addslashes($_REQUEST['date']); 
		$date = (date('Y-m-d', strtotime(substr($_REQUEST['date'], 0, 10))));
		$role = addslashes($_REQUEST['role']);

		$json['total_stamp_duty'] = 0;
		$json['total_sticker_fees'] = 0;
		$json['total_training_levy'] = 0;
		$json['total_basic_premium'] = 0;
		$json['total_gross_commission'] = 0;
		$json['total_gross_commission'] = 0;

		if ($role == 'admin') {

			$sel = $con->query("select q.*,(year(q.end_date) - year(q.start_date)) AS validity,p.sticker_no,p.vehicle_use,p.vehicle_plate_no,p.vehicle_no_seats,p.vehicle_make,p.vehicle_category,p.sticker_no,p.gross_weight,p.vehicle_cc,p.basic_premium as basic_p,DATE_FORMAT(q.start_date, '%d/%m/%Y') AS start_date,DATE_FORMAT(q.end_date, '%d/%m/%Y') AS end_date,DATE_FORMAT(q.time, '%d/%m/%Y') as t,(SELECT ccc.telephone FROM clients ccc WHERE ccc.client_id=q.client_id) AS client_tel,(SELECT cc.address FROM clients cc WHERE cc.client_id=q.client_id) AS client_address,(SELECT c.name FROM clients c WHERE c.client_id=q.client_id) AS client_name,(SELECT CONCAT(u3.name) FROM users u3 WHERE u3.user_id=q.user_id) AS agent_details,(SELECT (us.branch_name) FROM users us WHERE us.user_id=q.user_id) AS branch,(SELECT o.name FROM organisations o WHERE o.organ_id=q.organ_id) AS organ_name FROM motor_invoices q INNER JOIN motor_invoice_details p ON p.invoice_id=q.invoice_id WHERE (q.status='new' OR q.status='replaced' OR q.status='completed' OR q.status='paid') AND q.iclass='thirdparty' AND (DATE(q.time) = '$date') GROUP BY p.invoice_detail_id") or die($con->errorInfo());

			if ($sel->rowCount() > 0) {
				while ($row = $sel->fetch()) {

					$row['stamp_duty'] = STAMP_DUTY;
					$row['sticker_fees'] = STICKER_FEES;
					$json['results'][] = $row;
				}
				//
				$json['status'] = "ok";
			} else {

				$json['status'] = "empty";
			}
		} else if ($role == 'mtn_dealer') {



			$sel = $con->query("select q.*,(year(q.end_date) - year(q.start_date)) AS validity,p.sticker_no,p.vehicle_use,p.vehicle_plate_no,p.vehicle_no_seats,p.vehicle_make,p.vehicle_category,p.sticker_no,p.gross_weight,p.vehicle_cc,p.basic_premium as basic_p,DATE_FORMAT(q.start_date, '%d/%m/%Y') AS start_date,DATE_FORMAT(q.end_date, '%d/%m/%Y') AS end_date,DATE_FORMAT(q.time, '%d/%m/%Y') as t,(SELECT ccc.telephone FROM clients ccc WHERE ccc.client_id=q.client_id) AS client_tel,(SELECT cc.address FROM clients cc WHERE cc.client_id=q.client_id) AS client_address,(SELECT c.name FROM clients c WHERE c.client_id=q.client_id) AS client_name,(SELECT CONCAT(u3.name) FROM users u3 WHERE u3.user_id=q.user_id) AS agent_details,(SELECT (us.branch_name) FROM users us WHERE us.user_id=q.user_id) AS branch,(SELECT o.name FROM organisations o WHERE o.organ_id=q.organ_id) AS organ_name FROM motor_invoices q INNER JOIN motor_invoice_details p ON p.invoice_id=q.invoice_id WHERE (q.status='new' OR q.status='replaced' OR q.status='completed' OR q.status='paid') AND q.user_id='$user_id' AND q.iclass='thirdparty' AND (DATE(q.time) = '$date')  GROUP BY p.invoice_detail_id") or die($con->errorInfo());

			if ($sel->rowCount() > 0) {
				while ($row = $sel->fetch()) {


					$row['stamp_duty'] = STAMP_DUTY;
					$row['sticker_fees'] = STICKER_FEES;

					$json['results'][] = $row;
				}
				//
				$json['status'] = "ok";
			} else {

				$json['status'] = "empty";
			}
		} else if ($role == 'thirdparty_admin') {

			$sel = $con->query("select q.*,(year(q.end_date) - year(q.start_date)) AS validity,p.sticker_no,p.vehicle_use,p.vehicle_plate_no,p.vehicle_no_seats,p.vehicle_make,p.vehicle_category,p.sticker_no,p.gross_weight,p.vehicle_cc,p.basic_premium as basic_p,DATE_FORMAT(q.start_date, '%d/%m/%Y') AS start_date,DATE_FORMAT(q.end_date, '%d/%m/%Y') AS end_date,DATE_FORMAT(q.time, '%d/%m/%Y') as t,(SELECT ccc.telephone FROM clients ccc WHERE ccc.client_id=q.client_id) AS client_tel,(SELECT cc.address FROM clients cc WHERE cc.client_id=q.client_id) AS client_address,(SELECT c.name FROM clients c WHERE c.client_id=q.client_id) AS client_name,(SELECT CONCAT(u3.name) FROM users u3 WHERE u3.user_id=q.user_id) AS agent_details,(SELECT (us.branch_name) FROM users us WHERE us.user_id=q.user_id) AS branch,(SELECT o.name FROM organisations o WHERE o.organ_id=q.organ_id) AS organ_name FROM motor_invoices q INNER JOIN motor_invoice_details p ON p.invoice_id=q.invoice_id WHERE (q.status='paid' OR q.status='replaced' OR q.status='completed' OR q.status='paid') AND q.user_id='$user_id' AND q.iclass='thirdparty' AND (DATE(q.time) = '$date')  GROUP BY p.invoice_detail_id") or die($con->errorInfo());

			if ($sel->rowCount() > 0) {
				while ($row = $sel->fetch()) {

					$row['stamp_duty'] = STAMP_DUTY;
					$row['sticker_fees'] = STICKER_FEES;

					$json['results'][] = $row;
				}
				//
				$json['status'] = "ok";
			} else {

				$json['status'] = "empty";
			}
		}
	} else {

		$json['status'] = "missing";
	}
	echo json_encode($json);
}


function DailyReports()
{
	$con = connection();

	$json = array();


	if (isset($_REQUEST['user_id'], $_REQUEST['role'], $_REQUEST['iclass'])) {

		$user_id = addslashes($_REQUEST['user_id']);
		$iclass = addslashes($_REQUEST['iclass']); 
		$date = (date('Y-m-d', strtotime(substr($_REQUEST['date'], 0, 10))));
		$role = addslashes($_REQUEST['role']);

		$json['total_stamp_duty'] = 0;
		$json['total_sticker_fees'] = 0;
		$json['total_training_levy'] = 0;
		$json['total_basic_premium'] = 0;
		$json['total_gross_commission'] = 0;
		$json['total_gross_commission'] = 0;

		if ($role == 'admin') {

			$sel = $con->query("select q.*,(year(q.end_date) - year(q.start_date)) AS validity,p.sticker_no,p.vehicle_use,p.vehicle_plate_no,p.vehicle_no_seats,p.vehicle_make,p.vehicle_category,p.sticker_no,p.gross_weight,p.vehicle_cc,p.basic_premium as basic_p,DATE_FORMAT(q.start_date, '%d/%m/%Y') AS start_date,DATE_FORMAT(q.end_date, '%d/%m/%Y') AS end_date,DATE_FORMAT(q.time, '%d/%m/%Y') as t,(SELECT ccc.telephone FROM clients ccc WHERE ccc.client_id=q.client_id) AS client_tel,(SELECT cc.address FROM clients cc WHERE cc.client_id=q.client_id) AS client_address,(SELECT c.name FROM clients c WHERE c.client_id=q.client_id) AS client_name,(SELECT CONCAT(u3.name) FROM users u3 WHERE u3.user_id=q.user_id) AS agent_details,(SELECT (us.branch_name) FROM users us WHERE us.user_id=q.user_id) AS branch,(SELECT o.name FROM organisations o WHERE o.organ_id=q.organ_id) AS organ_name FROM motor_invoices q INNER JOIN motor_invoice_details p ON p.invoice_id=q.invoice_id WHERE (q.status='new' OR q.status='replaced' OR q.status='completed' OR q.status='paid') AND q.iclass='$iclass' AND (DATE(q.time) = '$date') GROUP BY p.invoice_detail_id") or die($con->errorInfo());

			if ($sel->rowCount() > 0) {
				while ($row = $sel->fetch()) {

					$row['stamp_duty'] = STAMP_DUTY;
					$row['sticker_fees'] = STICKER_FEES;
					$json['results'][] = $row;
				}
				//
				$json['status'] = "ok";
			} else {

				$json['status'] = "empty";
			}
		} else if ($role == 'mtn_dealer') {



			$sel = $con->query("select q.*,(year(q.end_date) - year(q.start_date)) AS validity,p.sticker_no,p.vehicle_use,p.vehicle_plate_no,p.vehicle_no_seats,p.vehicle_make,p.vehicle_category,p.sticker_no,p.gross_weight,p.vehicle_cc,p.basic_premium as basic_p,DATE_FORMAT(q.start_date, '%d/%m/%Y') AS start_date,DATE_FORMAT(q.end_date, '%d/%m/%Y') AS end_date,DATE_FORMAT(q.time, '%d/%m/%Y') as t,(SELECT ccc.telephone FROM clients ccc WHERE ccc.client_id=q.client_id) AS client_tel,(SELECT cc.address FROM clients cc WHERE cc.client_id=q.client_id) AS client_address,(SELECT c.name FROM clients c WHERE c.client_id=q.client_id) AS client_name,(SELECT CONCAT(u3.name) FROM users u3 WHERE u3.user_id=q.user_id) AS agent_details,(SELECT (us.branch_name) FROM users us WHERE us.user_id=q.user_id) AS branch,(SELECT o.name FROM organisations o WHERE o.organ_id=q.organ_id) AS organ_name FROM motor_invoices q INNER JOIN motor_invoice_details p ON p.invoice_id=q.invoice_id WHERE (q.status='new' OR q.status='replaced' OR q.status='completed' OR q.status='paid') AND q.user_id='$user_id' AND q.iclass='$iclass' AND (DATE(q.time) = '$date')  GROUP BY p.invoice_detail_id") or die($con->errorInfo());

			if ($sel->rowCount() > 0) {
				while ($row = $sel->fetch()) {


					$row['stamp_duty'] = STAMP_DUTY;
					$row['sticker_fees'] = STICKER_FEES;

					$json['results'][] = $row;
				}
				//
				$json['status'] = "ok";
			} else {

				$json['status'] = "empty";
			}
		} else if ($role == 'thirdparty_admin') {

			$sel = $con->query("select q.*,(year(q.end_date) - year(q.start_date)) AS validity,p.sticker_no,p.vehicle_use,p.vehicle_plate_no,p.vehicle_no_seats,p.vehicle_make,p.vehicle_category,p.sticker_no,p.gross_weight,p.vehicle_cc,p.basic_premium as basic_p,DATE_FORMAT(q.start_date, '%d/%m/%Y') AS start_date,DATE_FORMAT(q.end_date, '%d/%m/%Y') AS end_date,DATE_FORMAT(q.time, '%d/%m/%Y') as t,(SELECT ccc.telephone FROM clients ccc WHERE ccc.client_id=q.client_id) AS client_tel,(SELECT cc.address FROM clients cc WHERE cc.client_id=q.client_id) AS client_address,(SELECT c.name FROM clients c WHERE c.client_id=q.client_id) AS client_name,(SELECT CONCAT(u3.name) FROM users u3 WHERE u3.user_id=q.user_id) AS agent_details,(SELECT (us.branch_name) FROM users us WHERE us.user_id=q.user_id) AS branch,(SELECT o.name FROM organisations o WHERE o.organ_id=q.organ_id) AS organ_name FROM motor_invoices q INNER JOIN motor_invoice_details p ON p.invoice_id=q.invoice_id WHERE (q.status='paid' OR q.status='replaced' OR q.status='completed' OR q.status='paid') AND q.user_id='$user_id' AND q.iclass='$iclass' AND (DATE(q.time) = '$date')  GROUP BY p.invoice_detail_id") or die($con->errorInfo());

			if ($sel->rowCount() > 0) {
				while ($row = $sel->fetch()) {

					$row['stamp_duty'] = STAMP_DUTY;
					$row['sticker_fees'] = STICKER_FEES;

					$json['results'][] = $row;
				}
				//
				$json['status'] = "ok";
			} else {

				$json['status'] = "empty";
			}
		}
	} else {

		$json['status'] = "missing";
	}
	echo json_encode($json);
}



function DailyRevenueReports()
{
	$con = connection();

	$json = array();


	if (isset($_REQUEST['user_id'], $_REQUEST['role'])) {

		$user_id = addslashes($_REQUEST['user_id']);
		// $date = addslashes($_REQUEST['date']); 
		$date = (date('Y-m-d', strtotime(substr($_REQUEST['date'], 0, 10))));
		$role = addslashes($_REQUEST['role']);

		$json['total_stamp_duty'] = 0;
		$json['total_sticker_fees'] = 0;
		$json['total_training_levy'] = 0;
		$json['total_basic_premium'] = 0;
		$json['total_gross_commission'] = 0;
		$json['total_gross_commission'] = 0;

		if ($role == 'admin') {
			// (DATE(q.time) = '$date')
			//$sel=$con->query("select q.*,(year(q.end_date) - year(q.start_date)) AS validity,p.sticker_no,p.vehicle_use,p.vehicle_plate_no,p.vehicle_no_seats,p.vehicle_make,p.vehicle_category,p.sticker_no,p.gross_weight,p.vehicle_cc,p.basic_premium as basic_p,DATE_FORMAT(q.start_date, '%d/%m/%Y') AS start_date,DATE_FORMAT(q.end_date, '%d/%m/%Y') AS end_date,DATE_FORMAT(q.time, '%d/%m/%Y') as t,(SELECT ccc.telephone FROM clients ccc WHERE ccc.client_id=q.client_id) AS client_tel,(SELECT cc.address FROM clients cc WHERE cc.client_id=q.client_id) AS client_address,(SELECT c.name FROM clients c WHERE c.client_id=q.client_id) AS client_name,(SELECT CONCAT(u3.name) FROM users u3 WHERE u3.user_id=q.user_id) AS agent_details,(SELECT (us.branch_name) FROM users us WHERE us.user_id=q.user_id) AS branch,(SELECT o.name FROM organisations o WHERE o.organ_id=q.organ_id) AS organ_name FROM motor_invoices q INNER JOIN motor_invoice_details p ON p.invoice_id=q.invoice_id WHERE (q.status='new' OR q.status='replaced' OR q.status='completed' OR q.status='paid') AND q.iclass='thirdparty' AND (DATE(q.time) = '$date') GROUP BY p.invoice_detail_id")or die($con->errorInfo());
			$sel = $con->query("select q.*,(year(q.end_date) - year(q.start_date)) AS validity,p.sticker_no,p.vehicle_use,p.vehicle_plate_no,p.vehicle_no_seats,p.vehicle_make,p.vehicle_category,p.sticker_no,p.gross_weight,p.vehicle_cc,p.basic_premium as basic_p,DATE_FORMAT(q.start_date, '%d/%m/%Y') AS start_date,DATE_FORMAT(q.end_date, '%d/%m/%Y') AS end_date,DATE_FORMAT(q.time, '%d/%m/%Y') as t,(SELECT ccc.telephone FROM clients ccc WHERE ccc.client_id=q.client_id) AS client_tel,(SELECT cc.address FROM clients cc WHERE cc.client_id=q.client_id) AS client_address,(SELECT c.name FROM clients c WHERE c.client_id=q.client_id) AS client_name,(SELECT CONCAT(u3.name) FROM users u3 WHERE u3.user_id=q.user_id) AS agent_details,(SELECT (us.branch_name) FROM users us WHERE us.user_id=q.user_id) AS branch,(SELECT o.name FROM organisations o WHERE o.organ_id=q.organ_id) AS organ_name FROM motor_invoices q INNER JOIN motor_invoice_details p ON p.invoice_id=q.invoice_id WHERE (q.status='new' OR q.status='replaced' OR q.status='completed' OR q.status='paid') AND (DATE(q.time) = '$date') GROUP BY p.invoice_detail_id") or die($con->errorInfo());

			if ($sel->rowCount() > 0) {
				while ($row = $sel->fetch()) {

					$row['stamp_duty'] = STAMP_DUTY;
					$row['sticker_fees'] = STICKER_FEES;
					$json['results'][] = $row;
				}
				//
				$json['status'] = "ok";
			} else {

				$json['status'] = "empty";
			}
		} else if ($role == 'mtn_dealer' || $role == 'thirdparty_admin') {



			//$sel=$con->query("select q.*,(year(q.end_date) - year(q.start_date)) AS validity,p.sticker_no,p.vehicle_use,p.vehicle_plate_no,p.vehicle_no_seats,p.vehicle_make,p.vehicle_category,p.sticker_no,p.gross_weight,p.vehicle_cc,p.basic_premium as basic_p,DATE_FORMAT(q.start_date, '%d/%m/%Y') AS start_date,DATE_FORMAT(q.end_date, '%d/%m/%Y') AS end_date,DATE_FORMAT(q.time, '%d/%m/%Y') as t,(SELECT ccc.telephone FROM clients ccc WHERE ccc.client_id=q.client_id) AS client_tel,(SELECT cc.address FROM clients cc WHERE cc.client_id=q.client_id) AS client_address,(SELECT c.name FROM clients c WHERE c.client_id=q.client_id) AS client_name,(SELECT CONCAT(u3.name) FROM users u3 WHERE u3.user_id=q.user_id) AS agent_details,(SELECT (us.branch_name) FROM users us WHERE us.user_id=q.user_id) AS branch,(SELECT o.name FROM organisations o WHERE o.organ_id=q.organ_id) AS organ_name FROM motor_invoices q INNER JOIN motor_invoice_details p ON p.invoice_id=q.invoice_id WHERE (q.status='new' OR q.status='replaced' OR q.status='completed' OR q.status='paid') AND q.user_id='$user_id' AND q.iclass='thirdparty' AND (DATE(q.time) = '$date')  GROUP BY p.invoice_detail_id")or die($con->errorInfo());
			$sel = $con->query("select q.*,(year(q.end_date) - year(q.start_date)) AS validity,p.sticker_no,p.vehicle_use,p.vehicle_plate_no,p.vehicle_no_seats,p.vehicle_make,p.vehicle_category,p.sticker_no,p.gross_weight,p.vehicle_cc,p.basic_premium as basic_p,DATE_FORMAT(q.start_date, '%d/%m/%Y') AS start_date,DATE_FORMAT(q.end_date, '%d/%m/%Y') AS end_date,DATE_FORMAT(q.time, '%d/%m/%Y') as t,(SELECT ccc.telephone FROM clients ccc WHERE ccc.client_id=q.client_id) AS client_tel,(SELECT cc.address FROM clients cc WHERE cc.client_id=q.client_id) AS client_address,(SELECT c.name FROM clients c WHERE c.client_id=q.client_id) AS client_name,(SELECT CONCAT(u3.name) FROM users u3 WHERE u3.user_id=q.user_id) AS agent_details,(SELECT (us.branch_name) FROM users us WHERE us.user_id=q.user_id) AS branch,(SELECT o.name FROM organisations o WHERE o.organ_id=q.organ_id) AS organ_name FROM motor_invoices q INNER JOIN motor_invoice_details p ON p.invoice_id=q.invoice_id WHERE (q.status='new' OR q.status='replaced' OR q.status='completed' OR q.status='paid') AND (DATE(q.time) = '$date') AND q.user_id='$user_id' GROUP BY p.invoice_detail_id") or die($con->errorInfo());

			if ($sel->rowCount() > 0) {
				while ($row = $sel->fetch()) {


					$row['stamp_duty'] = STAMP_DUTY;
					$row['sticker_fees'] = STICKER_FEES;

					$json['results'][] = $row;
				}
				//
				$json['status'] = "ok";
			} else {

				$json['status'] = "empty";
			}
		}
	} else {

		$json['status'] = "missing";
	}
	echo json_encode($json);
}



function statusStickerReports()
{
	$con = connection();

	$json = array();


	if (isset($_REQUEST['user_id'], $_REQUEST['role'])) {

		$user_id = addslashes($_REQUEST['user_id']);
		$role = addslashes($_REQUEST['role']);
		$status = addslashes($_REQUEST['status']);
		$class = addslashes($_REQUEST['class']);

		if ($role == 'admin') {

			$sel = $con->query("select q.*,(year(q.end_date) - year(q.start_date)) AS validity,p.sticker_no,p.vehicle_use,p.vehicle_plate_no,p.vehicle_no_seats,p.vehicle_make,p.vehicle_category,p.sticker_no,p.gross_weight,p.vehicle_cc,p.basic_premium as basic_p,DATE_FORMAT(q.start_date, '%d/%m/%Y') AS start_date,DATE_FORMAT(q.end_date, '%d/%m/%Y') AS end_date,DATE_FORMAT(q.time, '%d/%m/%Y') as t,(SELECT ccc.telephone FROM clients ccc WHERE ccc.client_id=q.client_id) AS client_tel,(SELECT cc.address FROM clients cc WHERE cc.client_id=q.client_id) AS client_address,(SELECT c.name FROM clients c WHERE c.client_id=q.client_id) AS client_name,(SELECT CONCAT(u3.name) FROM users u3 WHERE u3.user_id=q.user_id) AS agent_details,(SELECT o.name FROM organisations o WHERE o.organ_id=q.organ_id) AS organ_name FROM motor_invoices q INNER JOIN motor_invoice_details p ON p.invoice_id=q.invoice_id WHERE (q.status='$status') AND q.iclass='$class' GROUP BY p.invoice_detail_id") or die($con->errorInfo());

			if ($sel->rowCount() > 0) {
				while ($row = $sel->fetch()) {

					$row['stamp_duty'] = STAMP_DUTY;
					$row['sticker_fees'] = STICKER_FEES;
					$json['results'][] = $row;
				}
				//
				$json['status'] = "ok";
			} else {

				$json['status'] = "empty";
			}
		} else if ($role == 'mtn_dealer') {



			$sel = $con->query("select q.*,(year(q.end_date) - year(q.start_date)) AS validity,p.sticker_no,p.vehicle_use,p.vehicle_plate_no,p.vehicle_no_seats,p.vehicle_make,p.vehicle_category,p.sticker_no,p.gross_weight,p.vehicle_cc,p.basic_premium as basic_p,DATE_FORMAT(q.start_date, '%d/%m/%Y') AS start_date,DATE_FORMAT(q.end_date, '%d/%m/%Y') AS end_date,DATE_FORMAT(q.time, '%d/%m/%Y') as t,(SELECT ccc.telephone FROM clients ccc WHERE ccc.client_id=q.client_id) AS client_tel,(SELECT cc.address FROM clients cc WHERE cc.client_id=q.client_id) AS client_address,(SELECT c.name FROM clients c WHERE c.client_id=q.client_id) AS client_name,(SELECT CONCAT(u3.name) FROM users u3 WHERE u3.user_id=q.user_id) AS agent_details,(SELECT o.name FROM organisations o WHERE o.organ_id=q.organ_id) AS organ_name FROM motor_invoices q INNER JOIN motor_invoice_details p ON p.invoice_id=q.invoice_id WHERE (q.status='$status') AND q.user_id='$user_id' AND q.iclass='$class' GROUP BY p.invoice_detail_id") or die($con->errorInfo());

			if ($sel->rowCount() > 0) {
				while ($row = $sel->fetch()) {


					$row['stamp_duty'] = STAMP_DUTY;
					$row['sticker_fees'] = STICKER_FEES;

					$json['results'][] = $row;
				}
				//
				$json['status'] = "ok";
			} else {

				$json['status'] = "empty";
			}
		} else if ($role == 'thirdparty_admin') {



			$sel = $con->query("select q.*,(year(q.end_date) - year(q.start_date)) AS validity,p.sticker_no,p.vehicle_use,p.vehicle_plate_no,p.vehicle_no_seats,p.vehicle_make,p.vehicle_category,p.sticker_no,p.gross_weight,p.vehicle_cc,p.basic_premium as basic_p,DATE_FORMAT(q.start_date, '%d/%m/%Y') AS start_date,DATE_FORMAT(q.end_date, '%d/%m/%Y') AS end_date,DATE_FORMAT(q.time, '%d/%m/%Y') as t,(SELECT ccc.telephone FROM clients ccc WHERE ccc.client_id=q.client_id) AS client_tel,(SELECT cc.address FROM clients cc WHERE cc.client_id=q.client_id) AS client_address,(SELECT c.name FROM clients c WHERE c.client_id=q.client_id) AS client_name,(SELECT CONCAT(u3.name) FROM users u3 WHERE u3.user_id=q.user_id) AS agent_details,(SELECT o.name FROM organisations o WHERE o.organ_id=q.organ_id) AS organ_name FROM motor_invoices q INNER JOIN motor_invoice_details p ON p.invoice_id=q.invoice_id WHERE (q.status='$status') AND q.user_id='$user_id' AND q.iclass='$class' GROUP BY p.invoice_detail_id") or die($con->errorInfo());

			if ($sel->rowCount() > 0) {
				while ($row = $sel->fetch()) {



					$row['stamp_duty'] = STAMP_DUTY;
					$row['sticker_fees'] = STICKER_FEES;

					$json['results'][] = $row;
				}
				//
				$json['status'] = "ok";
			} else {

				$json['status'] = "empty";
			}
		}
	} else {

		$json['status'] = "missing";
	}
	echo json_encode($json);
}



function WeeklyMtpReports()
{
	$con = connection();

	$json = array();


	if (isset($_REQUEST['user_id'], $_REQUEST['role'])) {

		$user_id = addslashes($_REQUEST['user_id']);
		$date_from = (date('Y-m-d', strtotime(substr($_REQUEST['date_from'], 0, 10))));
		$iclass = addslashes($_REQUEST['iclass']); 
		// $date_to = addslashes($_REQUEST['date_to']);
		$date_to = (date('Y-m-d', strtotime(substr($_REQUEST['date_to'], 0, 10))));
		$role = addslashes($_REQUEST['role']);

		if ($role == 'admin') {

			$sel = $con->query("select q.*,(year(q.end_date) - year(q.start_date)) AS validity,p.sticker_no,p.vehicle_use,p.vehicle_plate_no,p.vehicle_no_seats,p.vehicle_make,p.vehicle_category,p.sticker_no,p.gross_weight,p.vehicle_cc,p.basic_premium as basic_p,DATE_FORMAT(q.start_date, '%d/%m/%Y') AS start_date,DATE_FORMAT(q.end_date, '%d/%m/%Y') AS end_date,DATE_FORMAT(q.time, '%d/%m/%Y') as t,(SELECT ccc.telephone FROM clients ccc WHERE ccc.client_id=q.client_id) AS client_tel,(SELECT cc.address FROM clients cc WHERE cc.client_id=q.client_id) AS client_address,(SELECT c.name FROM clients c WHERE c.client_id=q.client_id) AS client_name,(SELECT CONCAT(u3.name) FROM users u3 WHERE u3.user_id=q.user_id) AS agent_details,(SELECT (us.branch_name) FROM users us WHERE us.user_id=q.user_id) AS branch,(SELECT o.name FROM organisations o WHERE o.organ_id=q.organ_id) AS organ_name FROM motor_invoices q INNER JOIN motor_invoice_details p ON p.invoice_id=q.invoice_id WHERE (q.status='new' OR q.status='replaced' OR q.status='completed' OR q.status='paid') AND (q.time >= '$date_from' AND q.time <= '$date_to') AND q.iclass='$iclass' GROUP BY p.invoice_detail_id") or die($con->errorInfo());

			if ($sel->rowCount() > 0) {
				while ($row = $sel->fetch()) {

					$row['stamp_duty'] = STAMP_DUTY;
					$row['sticker_fees'] = STICKER_FEES;
					$json['results'][] = $row;
				}
				//
				$json['status'] = "ok";
			} else {

				$json['status'] = "empty";
			}
		} else if ($role == 'mtn_dealer') {



			$sel = $con->query("select q.*,(year(q.end_date) - year(q.start_date)) AS validity,p.sticker_no,p.vehicle_use,p.vehicle_plate_no,p.vehicle_no_seats,p.vehicle_make,p.vehicle_category,p.sticker_no,p.gross_weight,p.vehicle_cc,p.basic_premium as basic_p,DATE_FORMAT(q.start_date, '%d/%m/%Y') AS start_date,DATE_FORMAT(q.end_date, '%d/%m/%Y') AS end_date,DATE_FORMAT(q.time, '%d/%m/%Y') as t,(SELECT ccc.telephone FROM clients ccc WHERE ccc.client_id=q.client_id) AS client_tel,(SELECT cc.address FROM clients cc WHERE cc.client_id=q.client_id) AS client_address,(SELECT c.name FROM clients c WHERE c.client_id=q.client_id) AS client_name,(SELECT CONCAT(u3.name) FROM users u3 WHERE u3.user_id=q.user_id) AS agent_details,(SELECT (us.branch_name) FROM users us WHERE us.user_id=q.user_id) AS branch,(SELECT o.name FROM organisations o WHERE o.organ_id=q.organ_id) AS organ_name FROM motor_invoices q INNER JOIN motor_invoice_details p ON p.invoice_id=q.invoice_id WHERE (q.status='new' OR q.status='replaced' OR q.status='completed' OR q.status='paid') AND (q.time >= '$date_from' AND q.time <= '$date_to') AND q.user_id='$user_id' AND q.iclass='$iclass' GROUP BY p.invoice_detail_id") or die($con->errorInfo());

			if ($sel->rowCount() > 0) {
				while ($row = $sel->fetch()) {


					$row['stamp_duty'] = STAMP_DUTY;
					$row['sticker_fees'] = STICKER_FEES;

					$json['results'][] = $row;
				}
				//
				$json['status'] = "ok";
			} else {

				$json['status'] = "empty";
			}
		} else if ($role == 'thirdparty_admin') {



			$sel = $con->query("select q.*,(year(q.end_date) - year(q.start_date)) AS validity,p.sticker_no,p.vehicle_use,p.vehicle_plate_no,p.vehicle_no_seats,p.vehicle_make,p.vehicle_category,p.sticker_no,p.gross_weight,p.vehicle_cc,p.basic_premium as basic_p,DATE_FORMAT(q.start_date, '%d/%m/%Y') AS start_date,DATE_FORMAT(q.end_date, '%d/%m/%Y') AS end_date,DATE_FORMAT(q.time, '%d/%m/%Y') as t,(SELECT ccc.telephone FROM clients ccc WHERE ccc.client_id=q.client_id) AS client_tel,(SELECT cc.address FROM clients cc WHERE cc.client_id=q.client_id) AS client_address,(SELECT c.name FROM clients c WHERE c.client_id=q.client_id) AS client_name,(SELECT CONCAT(u3.name) FROM users u3 WHERE u3.user_id=q.user_id) AS agent_details,(SELECT (us.branch_name) FROM users us WHERE us.user_id=q.user_id) AS branch,(SELECT o.name FROM organisations o WHERE o.organ_id=q.organ_id) AS organ_name FROM motor_invoices q INNER JOIN motor_invoice_details p ON p.invoice_id=q.invoice_id WHERE (q.status='paid' OR q.status='replaced' OR q.status='completed' OR q.status='paid') AND (q.time >= '$date_from' AND q.time <= '$date_to') AND q.user_id='$user_id' AND q.iclass='$iclass' GROUP BY p.invoice_detail_id") or die($con->errorInfo());

			if ($sel->rowCount() > 0) {
				while ($row = $sel->fetch()) {



					$row['stamp_duty'] = STAMP_DUTY;
					$row['sticker_fees'] = STICKER_FEES;

					$json['results'][] = $row;
				}
				//
				$json['status'] = "ok";
			} else {

				$json['status'] = "empty";
			}
		}
	} else {

		$json['status'] = "missing";
	}
	echo json_encode($json);
}


function WeeklyComprehensiveReports()
{
	$con = connection();

	$json = array();


	if (isset($_REQUEST['user_id'], $_REQUEST['role'])) {

		$user_id = addslashes($_REQUEST['user_id']);
		// $date_from = addslashes($_REQUEST['date_from']);
		$date_from = (date('Y-m-d', strtotime(substr($_REQUEST['date_from'], 0, 10))));
		$date_to = (date('Y-m-d', strtotime(substr($_REQUEST['date_to'], 0, 10))));
		// $date_to = addslashes($_REQUEST['date_to']); 
		$role = addslashes($_REQUEST['role']);

		if ($role == 'admin') {

			$sel = $con->query("select q.*,(year(q.end_date) - year(q.start_date)) AS validity,p.sticker_no,p.vehicle_use,p.vehicle_plate_no,p.vehicle_no_seats,p.vehicle_make,p.vehicle_category,p.sticker_no,p.gross_weight,p.vehicle_cc,p.basic_premium as basic_p,DATE_FORMAT(q.start_date, '%d/%m/%Y') AS start_date,DATE_FORMAT(q.end_date, '%d/%m/%Y') AS end_date,DATE_FORMAT(q.time, '%d/%m/%Y') as t,(SELECT ccc.telephone FROM clients ccc WHERE ccc.client_id=q.client_id) AS client_tel,(SELECT cc.address FROM clients cc WHERE cc.client_id=q.client_id) AS client_address,(SELECT c.name FROM clients c WHERE c.client_id=q.client_id) AS client_name,(SELECT CONCAT(u3.name) FROM users u3 WHERE u3.user_id=q.user_id) AS agent_details,(SELECT (us.branch_name) FROM users us WHERE us.user_id=q.user_id) AS branch,(SELECT o.name FROM organisations o WHERE o.organ_id=q.organ_id) AS organ_name FROM motor_invoices q INNER JOIN motor_invoice_details p ON p.invoice_id=q.invoice_id WHERE (q.status='replaced' OR q.status='completed' OR q.status='paid') AND (q.time >= '$date_from' AND q.time <= '$date_to') AND q.iclass='comprehensive' GROUP BY p.invoice_detail_id") or die($con->errorInfo());

			if ($sel->rowCount() > 0) {
				while ($row = $sel->fetch()) {

					$row['stamp_duty'] = STAMP_DUTY;
					$row['sticker_fees'] = STICKER_FEES;
					$json['results'][] = $row;
				}
				//
				$json['status'] = "ok";
			} else {

				$json['status'] = "empty";
			}
		} else if ($role == 'mtn_dealer') {



			$sel = $con->query("select q.*,(year(q.end_date) - year(q.start_date)) AS validity,p.sticker_no,p.vehicle_use,p.vehicle_plate_no,p.vehicle_no_seats,p.vehicle_make,p.vehicle_category,p.sticker_no,p.gross_weight,p.vehicle_cc,p.basic_premium as basic_p,DATE_FORMAT(q.start_date, '%d/%m/%Y') AS start_date,DATE_FORMAT(q.end_date, '%d/%m/%Y') AS end_date,DATE_FORMAT(q.time, '%d/%m/%Y') as t,(SELECT ccc.telephone FROM clients ccc WHERE ccc.client_id=q.client_id) AS client_tel,(SELECT cc.address FROM clients cc WHERE cc.client_id=q.client_id) AS client_address,(SELECT c.name FROM clients c WHERE c.client_id=q.client_id) AS client_name,(SELECT CONCAT(u3.name) FROM users u3 WHERE u3.user_id=q.user_id) AS agent_details,(SELECT (us.branch_name) FROM users us WHERE us.user_id=q.user_id) AS branch,(SELECT o.name FROM organisations o WHERE o.organ_id=q.organ_id) AS organ_name FROM motor_invoices q INNER JOIN motor_invoice_details p ON p.invoice_id=q.invoice_id WHERE (q.status='replaced' OR q.status='completed' OR q.status='paid') AND (q.time >= '$date_from' AND q.time <= '$date_to') AND q.user_id='$user_id' AND q.iclass='comprehensive' GROUP BY p.invoice_detail_id") or die($con->errorInfo());

			if ($sel->rowCount() > 0) {
				while ($row = $sel->fetch()) {


					$row['stamp_duty'] = STAMP_DUTY;
					$row['sticker_fees'] = STICKER_FEES;

					$json['results'][] = $row;
				}
				//
				$json['status'] = "ok";
			} else {

				$json['status'] = "empty";
			}
		} else if ($role == 'thirdparty_admin') {



			$sel = $con->query("select q.*,(year(q.end_date) - year(q.start_date)) AS validity,p.sticker_no,p.vehicle_use,p.vehicle_plate_no,p.vehicle_no_seats,p.vehicle_make,p.vehicle_category,p.sticker_no,p.gross_weight,p.vehicle_cc,p.basic_premium as basic_p,DATE_FORMAT(q.start_date, '%d/%m/%Y') AS start_date,DATE_FORMAT(q.end_date, '%d/%m/%Y') AS end_date,DATE_FORMAT(q.time, '%d/%m/%Y') as t,(SELECT ccc.telephone FROM clients ccc WHERE ccc.client_id=q.client_id) AS client_tel,(SELECT cc.address FROM clients cc WHERE cc.client_id=q.client_id) AS client_address,(SELECT c.name FROM clients c WHERE c.client_id=q.client_id) AS client_name,(SELECT CONCAT(u3.name) FROM users u3 WHERE u3.user_id=q.user_id) AS agent_details,(SELECT (us.branch_name) FROM users us WHERE us.user_id=q.user_id) AS branch,(SELECT o.name FROM organisations o WHERE o.organ_id=q.organ_id) AS organ_name FROM motor_invoices q INNER JOIN motor_invoice_details p ON p.invoice_id=q.invoice_id WHERE (q.status='replaced' OR q.status='completed' OR q.status='paid') AND (q.time >= '$date_from' AND q.time <= '$date_to') AND q.user_id='$user_id' AND q.iclass='comprehensive' GROUP BY p.invoice_detail_id") or die($con->errorInfo());

			if ($sel->rowCount() > 0) {
				while ($row = $sel->fetch()) {



					$row['stamp_duty'] = STAMP_DUTY;
					$row['sticker_fees'] = STICKER_FEES;

					$json['results'][] = $row;
				}
				//
				$json['status'] = "ok";
			} else {

				$json['status'] = "empty";
			}
		}
	} else {

		$json['status'] = "missing";
	}
	echo json_encode($json);
}

function ComprehensiveReports()
{
	$con = connection();

	$json = array();


	if (isset($_REQUEST['user_id'], $_REQUEST['role'])) {

		$user_id = addslashes($_REQUEST['user_id']);
		$date = addslashes($_REQUEST['date']);
		$role = addslashes($_REQUEST['role']);

		if ($role == 'admin') {

			$sel = $con->query("select q.*,(year(q.end_date) - year(q.start_date)) AS validity,p.sticker_no,p.vehicle_use,p.vehicle_plate_no,p.vehicle_no_seats,p.vehicle_make,p.vehicle_category,p.sticker_no,p.gross_weight,p.vehicle_cc,p.basic_premium as basic_p,DATE_FORMAT(q.start_date, '%d/%m/%Y') AS start_date,DATE_FORMAT(q.end_date, '%d/%m/%Y') AS end_date,DATE_FORMAT(q.time, '%d/%m/%Y') as t,(SELECT ccc.telephone FROM clients ccc WHERE ccc.client_id=q.client_id) AS client_tel,(SELECT cc.address FROM clients cc WHERE cc.client_id=q.client_id) AS client_address,(SELECT c.name FROM clients c WHERE c.client_id=q.client_id) AS client_name,(SELECT CONCAT(u3.name) FROM users u3 WHERE u3.user_id=q.user_id) AS agent_details,(SELECT (us.branch_name) FROM users us WHERE us.user_id=q.user_id) AS branch,(SELECT o.name FROM organisations o WHERE o.organ_id=q.organ_id) AS organ_name FROM motor_invoices q INNER JOIN motor_invoice_details p ON p.invoice_id=q.invoice_id WHERE (q.status='replaced' OR q.status='completed' OR q.status='paid') AND q.iclass='comprehensive' AND (DATE(q.time) = '$date') GROUP BY p.invoice_detail_id") or die($con->errorInfo());

			if ($sel->rowCount() > 0) {
				while ($row = $sel->fetch()) {

					$row['stamp_duty'] = STAMP_DUTY;
					$row['sticker_fees'] = STICKER_FEES;
					$json['results'][] = $row;
				}
				//
				$json['status'] = "ok";
			} else {

				$json['status'] = "empty";
			}
		} else if ($role == 'mtn_dealer') {



			$sel = $con->query("select q.*,(year(q.end_date) - year(q.start_date)) AS validity,p.sticker_no,p.vehicle_use,p.vehicle_plate_no,p.vehicle_no_seats,p.vehicle_make,p.vehicle_category,p.sticker_no,p.gross_weight,p.vehicle_cc,p.basic_premium as basic_p,DATE_FORMAT(q.start_date, '%d/%m/%Y') AS start_date,DATE_FORMAT(q.end_date, '%d/%m/%Y') AS end_date,DATE_FORMAT(q.time, '%d/%m/%Y') as t,(SELECT ccc.telephone FROM clients ccc WHERE ccc.client_id=q.client_id) AS client_tel,(SELECT cc.address FROM clients cc WHERE cc.client_id=q.client_id) AS client_address,(SELECT c.name FROM clients c WHERE c.client_id=q.client_id) AS client_name,(SELECT CONCAT(u3.name) FROM users u3 WHERE u3.user_id=q.user_id) AS agent_details,(SELECT (us.branch_name) FROM users us WHERE us.user_id=q.user_id) AS branch,(SELECT o.name FROM organisations o WHERE o.organ_id=q.organ_id) AS organ_name FROM motor_invoices q INNER JOIN motor_invoice_details p ON p.invoice_id=q.invoice_id WHERE (q.status='replaced' OR q.status='completed' OR q.status='paid') AND q.user_id='$user_id' AND q.iclass='comprehensive' AND (DATE(q.time) = '$date') GROUP BY p.invoice_detail_id") or die($con->errorInfo());

			if ($sel->rowCount() > 0) {
				while ($row = $sel->fetch()) {


					$row['stamp_duty'] = STAMP_DUTY;
					$row['sticker_fees'] = STICKER_FEES;

					$json['results'][] = $row;
				}
				//
				$json['status'] = "ok";
			} else {

				$json['status'] = "empty";
			}
		} else if ($role == 'thirdparty_admin') {

			$sel = $con->query("select q.*,(year(q.end_date) - year(q.start_date)) AS validity,p.sticker_no,p.vehicle_use,p.vehicle_plate_no,p.vehicle_no_seats,p.vehicle_make,p.vehicle_category,p.sticker_no,p.gross_weight,p.vehicle_cc,p.basic_premium as basic_p,DATE_FORMAT(q.start_date, '%d/%m/%Y') AS start_date,DATE_FORMAT(q.end_date, '%d/%m/%Y') AS end_date,DATE_FORMAT(q.time, '%d/%m/%Y') as t,(SELECT ccc.telephone FROM clients ccc WHERE ccc.client_id=q.client_id) AS client_tel,(SELECT cc.address FROM clients cc WHERE cc.client_id=q.client_id) AS client_address,(SELECT c.name FROM clients c WHERE c.client_id=q.client_id) AS client_name,(SELECT CONCAT(u3.name) FROM users u3 WHERE u3.user_id=q.user_id) AS agent_details,(SELECT (us.branch_name) FROM users us WHERE us.user_id=q.user_id) AS branch,(SELECT o.name FROM organisations o WHERE o.organ_id=q.organ_id) AS organ_name FROM motor_invoices q INNER JOIN motor_invoice_details p ON p.invoice_id=q.invoice_id WHERE (q.status='replaced' OR q.status='completed' OR q.status='paid') AND q.user_id='$user_id' AND q.iclass='comprehensive' AND (DATE(q.time) = '$date') GROUP BY p.invoice_detail_id") or die($con->errorInfo());

			if ($sel->rowCount() > 0) {
				while ($row = $sel->fetch()) {

					$row['stamp_duty'] = STAMP_DUTY;
					$row['sticker_fees'] = STICKER_FEES;

					$json['results'][] = $row;
				}
				//
				$json['status'] = "ok";
			} else {

				$json['status'] = "empty";
			}
		}
	} else {

		$json['status'] = "missing";
	}
	echo json_encode($json);
}

function viewMotorClaims()
{
	$con = connection();

	$json = array();


	$sel = $con->query("select q.*,CONCAT(u.name,' ',u.email,' ',u.telephone) AS agent_details FROM motor_claim_notification q INNER JOIN users u ON u.user_id=q.user_id GROUP BY q.claim_ref") or die($con->errorInfo());

	if ($sel->rowCount() > 0) {
		while ($row = $sel->fetch()) {

			$json['results'][] = $row;
		}
		//
		$json['status'] = "ok";
	} else {

		$json['status'] = "empty";
	}

	echo json_encode($json);
}


function viewUserMotorClaims()
{
	$con = connection();

	$json = array();

	if (isset($_REQUEST['user_id'])) {
		$user_id = urldecode(addslashes($_REQUEST['user_id']));

		$sel = $con->query("select q.* FROM motor_claim_notification q WHERE q.user_id='$user_id' ") or die($con->errorInfo());

		if ($sel->rowCount() > 0) {
			while ($row = $sel->fetch()) {

				$json['results'][] = $row;
			}
			//
			$json['status'] = "ok";
		} else {

			$json['status'] = "empty";
		}
	} else {
		$json['status'] = "missing";
	}

	echo json_encode($json);
}


function viewClaim()
{
	$con = connection();

	$json = array();

	if (isset($_REQUEST['claim_ref'])) {
		$claim_ref = addslashes($_REQUEST['claim_ref']);

		$sel = $con->query("select q.*,u.name FROM motor_claim_notification q INNER JOIN users u ON u.user_id=q.user_id WHERE q.claim_ref='$claim_ref' GROUP BY q.claim_ref") or die($con->errorInfo());

		if ($sel->rowCount() > 0) {
			while ($row = $sel->fetch()) {

				$json['results'][] = $row;
			}
			//
			$json['status'] = "ok";
		} else {

			$json['status'] = "empty";
		}
	} else {
		$json['status'] = "missing";
	}

	echo json_encode($json);
}


function viewThirdPartyAgents()
{
	$con = connection();

	$json = array();

	if (isset($_REQUEST['user_id'])) {
		$user_id = addslashes($_REQUEST['user_id']);

		$sel = $con->query("SELECT user_id,name,license_no FROM users") or die($con->errorInfo());

		if ($sel->rowCount() > 0) {
			while ($row = $sel->fetch()) {

				$json['results'][] = $row;
			}
			//
			$json['status'] = "ok";
		} else {

			$json['status'] = "empty";
		}
	} else {
		$json['status'] = "missing";
	}

	echo json_encode($json);
}


function addUsers()
{
	$con = connection();

	$json = array();

	$digits_needed = 5;
	$random_number = '';
	$count = 0;
	while ($count < $digits_needed) {
		$random_digit = mt_rand(0, 9);

		$random_number .= $random_digit;
		$count++;
	}

	if (isset($_REQUEST['name'], $_REQUEST['license_no'], $_REQUEST['email'], $_REQUEST['NIN'], $_REQUEST['telephone'])) {
		$license_no = addslashes($_REQUEST['license_no']);
		$name = addslashes($_REQUEST['name']);
		$gender = addslashes($_REQUEST['gender']);
		//$dob = strip_html_tags(addslashes($_REQUEST['dob'])); 
		$dob = date('Y-m-d', strtotime(substr($_REQUEST['dob'], 0, 10)));
		$NIN = addslashes($_REQUEST['NIN']);
		$email = $_REQUEST['email'];
		$telephone = addslashes($_REQUEST['telephone']);
		$address = addslashes($_REQUEST['address']);
		$role = addslashes($_REQUEST['role']);
		$user_id_added = addslashes($_REQUEST['user_id_added']);
		$branch_name = addslashes($_REQUEST['branch_name']);
		$password = addslashes($random_number);
		$status = 1;

		// Compose a simple HTML email message
		$message = '<html><body>';
		$message .= '<h3>Dear ' . $name . '!</h3>';
		$message .= '<p>Welcome to the BRITAM MTP system</p>';
		$message .= '<p>Username: ' . $email . ' or </p>';
		$message .= '<p>Username: ' . $telephone . '</p>';
		$message .= '<p>Password: ' . $password . '</p>';
		$message .= '<p>Click <a href="https://servicesug.britam.com" target="_blank">BRITAM MTP</a> to login.</p>';
		$message .= '<p>Regards</p>';
		$message .= '<p>Administration Team</p>';
		$message .= '</body></html>';

		send_email($email, 'LOGIN DETAILS ', $message);

		$password = md5($password);


		$sel2 = $con->query("select * from users where telephone='$telephone'") or die($con->errorInfo());

		if ($sel2->rowCount() > 0) {

			$json['status'] = 'duplicate';
			$json['message'] = 'Duplicate user';
		} else {

			$con->exec("INSERT INTO users set name='$name',email='$email',status='$status',dob='$dob',telephone='$telephone',branch_name='$branch_name',gender='$gender',user_id_added='$user_id_added',password='$password',address='$address',role='$role',license_no='$license_no',NIN='$NIN' ,time=NOW() ") or die($con->errorInfo());

			$id = $con->lastInsertId(); // last inserted id

			$directory = '../images/users';

			if (!file_exists($directory . "/" . $id)) {
				mkdir($directory . "/" . $id, 0777, true);
			}



			$sel = $con->query("select * from users where user_id='$id' ") or die($con->errorInfo());

			if ($sel->rowCount() > 0) {

				while ($row = $sel->fetch()) {
					$json['user_id'] = $row['user_id'];
					$json['name'] = $row['name'];
					$json['email'] = $row['email'];
					$json['telephone'] = $row['telephone'];
					$json['role'] = $row['role'];
					$json['NIN'] = $row['NIN'];
					$json['license_no'] = $row['license_no'];
					$json['branch_name'] = $row['branch_name'];
				}

				// create text QR code 


				$json['status'] = 'ok';
				$json['message'] = 'User account ' . $json['role'] . ' has been successfully created';
			}
		}
	} else {
		$json['status'] = "missing";
		$json['message'] = "missing fields";
	}
	echo json_encode($json);
}

function addWindscreenPolicy()
{
	$con = connection();

	$json = array();



	if (isset($_REQUEST['vehicle_registration_no'], $_REQUEST['client_id'], $_REQUEST['policy_no'], $_REQUEST['windscreen_valuation'], $_REQUEST['premium_charged'])) {

		$vehicle_registration_no = addslashes($_REQUEST['vehicle_registration_no']);
		$client_id = addslashes($_REQUEST['client_id']);
		$policy_no = addslashes($_REQUEST['policy_no']);
		$windscreen_valuation = addslashes($_REQUEST['windscreen_valuation']);
		$premium_charged = addslashes($_REQUEST['premium_charged']);
		$training_levy = addslashes($_REQUEST['training_levy']);
		$user_id_charged = addslashes($_REQUEST['user_id_charged']);
		$vehicle_make = addslashes($_REQUEST['vehicle_make']);
		$vehicle_chasis_no = addslashes($_REQUEST['vehicle_chasis_no']);
		$payment_ref = addslashes($_REQUEST['payment_ref']);
		$payment_mode = addslashes($_REQUEST['payment_mode']);
		$currency = addslashes($_REQUEST['currency']);
		// $start_date = strip_html_tags(addslashes($_REQUEST['start_date']));
		$start_date = date('Y-m-d', strtotime(substr($_REQUEST['start_date'], 0, 10)));
		// $start_date = explode('-',$start_date);
		// $start_date = $start_date[2].'-'.$start_date[1].'-'.$start_date[0];

		$amount_paid = addslashes($_REQUEST['amount_paid']);

		if ($payment_mode <> '') {
			$status = 'paid';
		} else {
			$status = 'notpaid';
		}






		$sel2 = $con->query("select * from windscreen_policy WHERE vehicle_registration_no='$vehicle_registration_no' AND end_date > NOW()") or die($con->errorInfo());

		if ($sel2->rowCount() > 0) {

			$json['status'] = 'duplicate';
			$json['message'] = 'Windscreen insurance for registration' . $vehicle_registration_no . ' is still running';
		} else {

			$con->exec("INSERT INTO windscreen_policy set vehicle_registration_no='$vehicle_registration_no',start_date='$start_date',end_date=DATE_ADD('$start_date', INTERVAL 364 DAY),currency='$currency',client_id='$client_id',policy_no='$policy_no',windscreen_valuation='$windscreen_valuation',premium_charged='$premium_charged',training_levy='$training_levy',user_id_charged='$user_id_charged',vehicle_make='$vehicle_make',vehicle_chasis_no='$vehicle_chasis_no',payment_mode='$payment_mode',amount_paid='$amount_paid' ,status='$status',payment_ref='$payment_ref'") or die($con->errorInfo());

			$con->exec("UPDATE organ_policy_no set status='used' WHERE policy_no='$policy_no'") or die($con->errorInfo());

			$id = $con->lastInsertId(); // last inserted id



			$sel = $con->query("select * from windscreen_policy where windscreen_id='$id' ") or die($con->errorInfo());

			if ($sel->rowCount() > 0) {

				while ($row = $sel->fetch()) {
					$json['windscreen_id'] = $row['windscreen_id'];
					$json['payment_ref'] = $row['payment_ref'];
				}

				$json['status'] = 'ok';
				$json['message'] = 'Windscreen has been successfully insured';
			}
		}
	} else {
		$json['status'] = "missing";
		$json['message'] = "missing fields";
	}
	echo json_encode($json);
}

function addWindscreenPolicyMobile()
{
	$con = connection();

	$json = array();



	if (isset($_REQUEST['vehicle_registration_no'], $_REQUEST['client_id'], $_REQUEST['policy_no'], $_REQUEST['windscreen_valuation'], $_REQUEST['premium_charged'])) {

		$vehicle_registration_no = urldecode(addslashes($_REQUEST['vehicle_registration_no']));
		$client_id = urldecode(addslashes($_REQUEST['client_id']));
		$policy_no = urldecode(addslashes($_REQUEST['policy_no']));
		$windscreen_valuation = urldecode(addslashes($_REQUEST['windscreen_valuation']));
		$premium_charged = urldecode(addslashes($_REQUEST['premium_charged']));
		$training_levy = urldecode(addslashes($_REQUEST['training_levy']));
		$user_id_charged = urldecode(addslashes($_REQUEST['user_id_charged']));
		$vehicle_make = urldecode(addslashes($_REQUEST['vehicle_make']));
		$vehicle_chasis_no = urldecode(addslashes($_REQUEST['vehicle_chasis_no']));
		$payment_ref = urldecode(addslashes($_REQUEST['payment_ref']));
		$payment_mode = urldecode(addslashes($_REQUEST['payment_mode']));
		$currency = urldecode(addslashes($_REQUEST['currency']));
		$start_date = urldecode(addslashes($_REQUEST['start_date']));
		$start_date = explode('-', $start_date);
		$start_date = $start_date[2] . '-' . $start_date[1] . '-' . $start_date[0];

		$amount_paid = urldecode(addslashes($_REQUEST['amount_paid']));

		if ($payment_mode <> '') {
			$status = 'paid';
		} else {
			$status = 'notpaid';
		}






		$sel2 = $con->query("select * from windscreen_policy WHERE vehicle_registration_no='$vehicle_registration_no' AND end_date > NOW()") or die($con->errorInfo());

		if ($sel2->rowCount() > 0) {

			$json['status'] = 'duplicate';
			$json['message'] = 'Windscreen insurance for registration' . $vehicle_registration_no . ' is still running';
		} else {

			$con->exec("INSERT INTO windscreen_policy set vehicle_registration_no='$vehicle_registration_no',start_date='$start_date',end_date=DATE_ADD('$start_date', INTERVAL 364 DAY),currency='$currency',client_id='$client_id',policy_no='$policy_no',windscreen_valuation='$windscreen_valuation',premium_charged='$premium_charged',training_levy='$training_levy',user_id_charged='$user_id_charged',vehicle_make='$vehicle_make',vehicle_chasis_no='$vehicle_chasis_no',payment_mode='$payment_mode',amount_paid='$amount_paid' ,status='$status',payment_ref='$payment_ref'") or die($con->errorInfo());

			$con->exec("UPDATE organ_policy_no set status='used' WHERE policy_no='$policy_no'") or die($con->errorInfo());

			$id = $con->lastInsertId(); // last inserted id



			$sel = $con->query("select * from windscreen_policy where windscreen_id='$id' ") or die($con->errorInfo());

			if ($sel->rowCount() > 0) {

				while ($row = $sel->fetch()) {
					$json['windscreen_id'] = $row['windscreen_id'];
					$json['payment_ref'] = $row['payment_ref'];
				}

				$json['status'] = 'ok';
				$json['message'] = 'Windscreen has been successfully insured';
			}
		}
	} else {
		$json['status'] = "missing";
		$json['message'] = "missing fields";
	}
	echo json_encode($json);
}

function addMotorClaimNotification()
{
	$con = connection();

	$json = array();

	$digits_needed = 5;
	$random_number = '';
	$count = 0;
	while ($count < $digits_needed) {
		$random_digit = mt_rand(0, 9);

		$random_number .= $random_digit;
		$count++;
	}

	if (isset($_REQUEST['claimant_name'], $_REQUEST['claimant_email'], $_REQUEST['claimant_telephone'], $_REQUEST['number_plate'])) {

		$claimant_name = urldecode(addslashes($_REQUEST['claimant_name']));
		$claimant_email = urldecode(addslashes($_REQUEST['claimant_email']));
		$claimant_telephone = urldecode(addslashes($_REQUEST['claimant_telephone']));
		$number_plate = urldecode(addslashes($_REQUEST['number_plate']));

		$sticker_no = urldecode(addslashes($_REQUEST['sticker_no']));

		// $quote_policy_id = strip_html_tags(addslashes($_REQUEST['quote_policy_id'])); 
		$policy_type = urldecode(addslashes($_REQUEST['policy_type']));
		// $date_of_incident = urldecode(strip_html_tags(addslashes($_REQUEST['date_of_incident']))); 
		$date_of_incident = date('Y-m-d', strtotime(substr($_REQUEST['date_of_incident'], 0, 10)));
		$details_of_incident = urldecode(addslashes($_REQUEST['details_of_incident']));
		$claim_estimate = urldecode(addslashes($_REQUEST['claim_estimate']));
		// $date_reported = strip_html_tags(addslashes($_REQUEST['date_reported']));
		// $date_reported = date('Y-m-d', strtotime(substr($_REQUEST['date_reported'],0,10)));     
		// $organ_id = strip_html_tags(addslashes($_REQUEST['organ_id']));
		// $bank_reference = strip_html_tags(addslashes($_REQUEST['bank_reference']));
		// $insurance_reference = strip_html_tags(addslashes($_REQUEST['insurance_reference']));
		// $policy_reference = strip_html_tags(addslashes($_REQUEST['policy_reference']));
		$user_id = urldecode(addslashes($_REQUEST['user_id']));
		$status = 'Draft';
		$claim_ref = $random_number;



		$sel2 = $con->query("select * from motor_claim_notification where status='$status' AND (claimant_email='$claimant_email' OR claimant_telephone='$claimant_telephone') AND number_plate='$number_plate'") or die($con->errorInfo());

		if ($sel2->rowCount() > 0) {

			$json['status'] = 'duplicate';
			$json['message'] = 'Duplicate claim';
		} else {

			$con->exec("INSERT INTO motor_claim_notification set claim_ref='$claim_ref',claimant_name='$claimant_name',claimant_email='$claimant_email',claimant_telephone='$claimant_telephone',number_plate='$number_plate',sticker_no='$sticker_no',policy_type='$policy_type',date_of_incident='$date_of_incident',details_of_incident='$details_of_incident',claim_estimate='$claim_estimate',user_id='$user_id',status='$status' ") or die($con->errorInfo());

			//$id = $con->lastInsertId(); // last inserted id


			$directory = '../images/motor_claims';

			if (!file_exists($directory . "/" . $claim_ref)) {
				mkdir($directory . "/" . $claim_ref, 0777, true);
			}


			$sel = $con->query("select * from motor_claim_notification where claim_ref='$claim_ref' ") or die($con->errorInfo());

			if ($sel->rowCount() > 0) {

				while ($row = $sel->fetch()) {
					$json['claim_ref'] = $row['claim_ref'];
				}

				$json['status'] = 'ok';
				$json['message'] = 'Claim notification has been successfully created';
			}
		}
	} else {
		$json['status'] = "missing";
		$json['message'] = "missing fields";
	}
	echo json_encode($json);
}

function newStickerNo()
{
	$con = connection();

	$json = array();

	if (isset($_REQUEST['category'], $_REQUEST['sticker_no_from'])) {
		$category = urldecode(addslashes($_REQUEST['category']));
		$sticker_no_from = urldecode(addslashes($_REQUEST['sticker_no_from']));
		$sticker_no_to = urldecode(addslashes($_REQUEST['sticker_no_to']));
		$total_amount_received = urldecode(addslashes($_REQUEST['total_amount_received']));
		$user_id = urldecode(addslashes($_REQUEST['user_id']));
		$status = 'notused';



		$con->exec("INSERT INTO sticker_acc set category='$category',sticker_no_from='$sticker_no_from',sticker_no_to='$sticker_no_to',total_amount_received='$total_amount_received',status='$status',user_id='$user_id',time=NOW() ") or die($con->errorInfo());

		$id = $con->lastInsertId(); // last inserted id




		$sel = $con->query("select * from sticker_acc where sticker_id='$id' ") or die($con->errorInfo());

		if ($sel->rowCount() > 0) {

			while ($row = $sel->fetch()) {
				$json['sticker_id'] = $row['sticker_id'];
				$json['category'] = $row['category'];
				$json['sticker_no_to'] = $row['sticker_no_to'];
				$json['sticker_no_from'] = $row['sticker_no_from'];
				$json['user_id'] = $row['user_id'];
			}

			// create text QR code 


			$json['status'] = 'ok';
			$json['message'] = 'Sticker No created';
		}
	} else {
		$json['status'] = "missing";
		$json['message'] = "missing fields";
	}
	echo json_encode($json);
}


function addClients()
{
	$con = connection();

	$json = array();

	if (isset($_REQUEST['name'], $_REQUEST['telephone'])) {
		$name = urldecode(addslashes($_REQUEST['name']));
		//$dob = urldecode (strip_html_tags(addslashes($_REQUEST['dob']))); 
		$dob = urldecode(date('Y-m-d', strtotime(substr($_REQUEST['dob'], 0, 10))));
		$gender = urldecode(addslashes($_REQUEST['gender']));
		$email = urldecode(addslashes($_REQUEST['email']));
		$telephone = urldecode(addslashes($_REQUEST['telephone']));
		$address = urldecode(addslashes($_REQUEST['address']));
		$user_id = urldecode(addslashes($_REQUEST['user_id']));



		$sel2 = $con->query("select * from clients where telephone='$telephone'") or die($con->errorInfo());

		if ($sel2->rowCount() > 0) {

			while ($row2 = $sel2->fetch()) {
				$json['client_id'] = $row2['client_id'];
			}

			$json['status'] = 'duplicate';
			$json['message'] = 'Duplicate client';
		} else {

			$con->exec("INSERT INTO clients set name='$name',dob='$dob',email='$email',telephone='$telephone',address='$address',gender='$gender',user_id='$user_id' ,time=NOW() ") or die($con->errorInfo());

			$id = $con->lastInsertId(); // last inserted id

			$directory = '../images/clients';

			if (!file_exists($directory . "/" . $id)) {
				mkdir($directory . "/" . $id, 0777, true);
			}



			$sel = $con->query("select * from clients where client_id='$id' ") or die($con->errorInfo());

			if ($sel->rowCount() > 0) {

				while ($row = $sel->fetch()) {
					$json['client_id'] = $row['client_id'];
					$json['name'] = $row['name'];
					$json['email'] = $row['email'];
					$json['telephone'] = $row['telephone'];
					$json['address'] = $row['address'];
				}

				// create text QR code 


				$json['status'] = 'ok';
				$json['message'] = 'Client account has been successfully created';
			}
		}
	} else {
		$json['status'] = "missing";
		$json['message'] = "missing fields";
	}
	echo json_encode($json);
}


function editClients()
{
	$con = connection();

	$json = array();

	if (isset($_REQUEST['client_id'])) {
		$client_id = urldecode(addslashes($_REQUEST['client_id']));
		$name = urldecode(addslashes($_REQUEST['name']));
		//$dob = urldecode (strip_html_tags(addslashes($_REQUEST['dob']))); 
		$dob = urldecode(date('Y-m-d', strtotime(substr($_REQUEST['dob'], 0, 10))));
		$gender = urldecode(addslashes($_REQUEST['gender']));
		$email = urldecode(addslashes($_REQUEST['email']));
		$telephone = urldecode(addslashes($_REQUEST['telephone']));
		$address = urldecode(addslashes($_REQUEST['address']));


		//$qr_code = $converter->encode($pin); 

		$sel3 = $con->query("select * from clients where client_id='$client_id'") or die($con->errorInfo());

		if ($sel3->rowCount() > 0) {


			$sel = $con->exec("UPDATE clients set name='$name',email='$email',dob='$dob',gender='$gender',telephone='$telephone',address='$address' WHERE client_id='$client_id'") or die($con->errorInfo());


			if ($sel) {

				$sel2 = $con->query("select * from clients where client_id='$client_id' ") or die($con->errorInfo());

				if ($sel2->rowCount() > 0) {

					while ($row = $sel2->fetch()) {
						$json['client_id'] = $row['client_id'];
					}
				}

				$json['status'] = 'ok';
				$json['type'] = 'success';
				$json['message'] = 'Client successfully edited';
			}
		} else {
			$json['status'] = 'notfound';
			$json['type'] = 'error';
			$json['message'] = 'Client not found';
		}
	} else {
		$json['status'] = "missing";
		$json['type'] = 'error';
		$json['message'] = 'Missing Fields';
	}
	echo json_encode($json);
}

function removeClients()
{
	$con = connection();

	$json = array();

	if (isset($_REQUEST['client_id'])) {
		$client_id = addslashes($_REQUEST['client_id']);

		$sel = $con->exec("DELETE FROM clients WHERE client_id='$client_id'") or die($con->errorInfo());

		if ($sel) {
			deleteDirectory('../images/clients/' . $client_id);

			$json['status'] = 'ok';
		} else {
			$json['status'] = 'error';
		}
	} else {
		$json['status'] = "missing";
	}
	echo json_encode($json);
}


function removeStickerNos()
{
	$con = connection();

	$json = array();

	if (isset($_REQUEST['sticker_id'])) {
		$sticker_id = addslashes($_REQUEST['sticker_id']);

		$sel = $con->exec("DELETE FROM sticker_acc WHERE sticker_id='$sticker_id'") or die($con->errorInfo());

		if ($sel) {

			$json['status'] = 'ok';
		} else {
			$json['status'] = 'error';
		}
	} else {
		$json['status'] = "missing";
	}
	echo json_encode($json);
}


function addOrganisations()
{
	$con = connection();

	$json = array();

	if (isset($_REQUEST['name'])) {
		$code = addslashes($_REQUEST['code']);
		$name = addslashes($_REQUEST['name']);
		$contact_name = addslashes($_REQUEST['contact_name']);
		$contact_email = addslashes($_REQUEST['contact_email']);
		$contact_tel = addslashes($_REQUEST['contact_tel']);
		$address = addslashes($_REQUEST['address']);
		$user_id = addslashes($_REQUEST['user_id']);



		$sel2 = $con->query("select * from organisations where contact_email='$contact_email'") or die($con->errorInfo());

		if ($sel2->rowCount() > 0) {

			$json['status'] = 'duplicate';
			$json['message'] = 'Duplicate email address';
		} else {

			$con->exec("INSERT INTO organisations set name='$name',contact_name='$contact_name',contact_email='$contact_email',contact_tel='$contact_tel
	',address='$address',user_id='$user_id' ,time=NOW() ") or die($con->errorInfo());

			$id = $con->lastInsertId(); // last inserted id

			$directory = '../images/organisations';

			if (!file_exists($directory . "/" . $id)) {
				mkdir($directory . "/" . $id, 0777, true);
			}



			$sel = $con->query("select * from organisations where organ_id='$id' ") or die($con->errorInfo());

			if ($sel->rowCount() > 0) {

				while ($row = $sel->fetch()) {
					$json['organ_id'] = $row['organ_id'];
					$json['name'] = $row['name'];
					$json['email'] = $row['email'];
					$json['address'] = $row['address'];
					// $json['contact']=$row['contact']; 

				}

				// create text QR code 


				$json['status'] = 'ok';
				$json['message'] = 'Organisation details successfully recorded';
			}
		}
	} else {
		$json['status'] = "missing";
		$json['message'] = "missing values";
	}
	echo json_encode($json);
}




function add_Sticker()
{
	$con = connection();

	$json = array();


	if (isset($_REQUEST['sticker_no'])) {
		// $organ_id = strip_html_tags(addslashes($_REQUEST['organ_id']));   
		$sticker_no = addslashes($_REQUEST['sticker_no']);
		$category = addslashes($_REQUEST['category']);
		$policy_no = addslashes($_REQUEST['policy_no']);
		$user_id = addslashes($_REQUEST['user_id']);
		$agent_user_id = addslashes($_REQUEST['agent_user_id']);
		$status_s = addslashes('notused');

		$sel2 = $con->query("select * from organ_stickers where sticker_no='$sticker_no'") or die($con->errorInfo());

		if ($sel2->rowCount() > 0) {

			$json['status'] = 'duplicate';
			$json['type'] = 'error';
			$json['message'] = 'Sticker already exists';
		} else {

			$con->exec("INSERT INTO organ_stickers set policy_no='$policy_no',category='$category',agent_user_id='$agent_user_id',user_id='$user_id',sticker_no='$sticker_no',status='$status_s'") or die($con->errorInfo());

			$id = $con->lastInsertId(); // last inserted id

			$events = "User added sticker with ID " . $id . " to Agent ID " . $agent_user_id;
			$con->exec("INSERT INTO system_logs set user_id='$user_id',event='$events' ") or die($con->errorInfo());



			$sel = $con->query("select * from organ_stickers where sticker_id='$id' ") or die($con->errorInfo());

			if ($sel->rowCount() > 0) {

				while ($row = $sel->fetch()) {
					$json['sticker_id'] = $row['sticker_id'];
					// $json['organ_id']=$row['organ_id']; 

				}

				$json['status'] = 'ok';

				$json['type'] = 'success';
				$json['message'] = 'Sticker successfully added';
			}
		}
	} else {
		$json['status'] = "missing";
		$json['type'] = 'success';
		$json['message'] = 'Missing fields';
	}
	echo json_encode($json);
}

function add_PolciyNo()
{
	$con = connection();

	$json = array();


	if (isset($_REQUEST['organ_id'], $_REQUEST['policy_no'])) {
		$organ_id = addslashes($_REQUEST['organ_id']);
		$policy_no = addslashes($_REQUEST['policy_no']);
		$user_id = addslashes($_REQUEST['user_id']);
		$status_s = addslashes('notused');

		$sel2 = $con->query("select * from organ_policy_no where organ_id='$organ_id' AND policy_no='$policy_no'") or die($con->errorInfo());

		if ($sel2->rowCount() > 0) {

			$json['status'] = 'duplicate';
			$json['type'] = 'error';
			$json['message'] = 'Polciy No already exists';
		} else {

			$con->exec("INSERT INTO organ_policy_no set organ_id='$organ_id',user_id='$user_id',policy_no='$policy_no',status='$status_s',time=NOW() ") or die($con->errorInfo());

			$id = $con->lastInsertId(); // last inserted id

			$events = "User added Policy No with ID " . $id;
			$con->exec("INSERT INTO system_logs set user_id='$user_id',event='$events' ") or die($con->errorInfo());



			$sel = $con->query("select * from organ_policy_no where policy_id='$id' ") or die($con->errorInfo());

			if ($sel->rowCount() > 0) {

				while ($row = $sel->fetch()) {
					$json['policy_id'] = $row['policy_id'];
					$json['organ_id'] = $row['organ_id'];
				}

				$json['status'] = 'ok';

				$json['type'] = 'success';
				$json['message'] = 'Policy No successfully added';
			}
		}
	} else {
		$json['status'] = "missing";
		$json['type'] = 'success';
		$json['message'] = 'Missing fields';
	}
	echo json_encode($json);
}

function update_Sticker()
{
	$con = connection();

	$json = array();


	if (isset($_REQUEST['invoice_detail_id'])) {
		$invoice_detail_id = urldecode(addslashes($_REQUEST['invoice_detail_id']));

		//choose from a list of stickers the first on in line should be chosen. 

		$user_id = urldecode(addslashes($_REQUEST['user_id']));
		$sticker_status = urldecode(addslashes('used'));

		$sticker_no = '';



		$sel = $con->query("select * from motor_invoice_details where invoice_detail_id='$invoice_detail_id'") or die($con->errorInfo());

		if ($sel->rowCount() > 0) {

			while ($row = $sel->fetch()) {

				$invoice_id = $row['invoice_id'];
				$vehicle_category = trim($row['vehicle_category']);
				$vehicle_use = $row['vehicle_use'];

				if ($vehicle_category == 'Private') {

					$sel0 = $con->query("select * from organ_stickers where category='Motor Private' AND agent_user_id='$user_id' AND status='notused' ORDER BY sticker_no ASC LIMIT 1") or die($con->errorInfo());

					if ($sel0->rowCount() > 0) {

						while ($row0 = $sel0->fetch()) {

							$sticker_no = $row0['sticker_no'];

							//before update check user available stickers
							$con->exec("UPDATE motor_invoice_details set sticker_no='$sticker_no',print_status='printed' WHERE invoice_detail_id='$invoice_detail_id' ") or die($con->errorInfo());

							$con->exec("UPDATE motor_invoices set user_id='$user_id' WHERE invoice_id='$invoice_id' ") or die($con->errorInfo());

							$con->exec("UPDATE organ_stickers set status='$sticker_status' WHERE sticker_no='$sticker_no' ") or die($con->errorInfo());



							$json['status'] = 'ok';

							$json['type'] = 'success';
							$json['message'] = 'Sticker successfully updated reading No : ' . $sticker_no;
						}
					} else {

						$json['status'] = 'error';
						$json['type'] = 'error';
						$json['message'] = 'Stickers already used up contact administrator at Britam Head office';
					}
				} else if ($vehicle_category == 'Commercial' || $vehicle_category == 'Farm vehicles' || $vehicle_category == 'Buses' || $vehicle_category == 'Ambulances' || $vehicle_category == 'Bullion Vans/ Fire fighting' || $vehicle_category == 'Construction Equipments' || $vehicle_category == 'Hauliers' || $vehicle_category == 'Oil,gas,petro tankers') {

					$sel0 = $con->query("select * from organ_stickers where category='Motor Commercial' AND agent_user_id='$user_id' AND status='notused' ORDER BY sticker_no ASC LIMIT 1") or die($con->errorInfo());

					if ($sel0->rowCount() > 0) {

						while ($row0 = $sel0->fetch()) {
							$sticker_no = $row0['sticker_no'];
							//before update check user available stickers
							$con->exec("UPDATE motor_invoice_details set sticker_no='$sticker_no',print_status='printed' WHERE invoice_detail_id='$invoice_detail_id' ") or die($con->errorInfo());

							$con->exec("UPDATE motor_invoices set user_id='$user_id' WHERE invoice_id='$invoice_id' ") or die($con->errorInfo());

							$con->exec("UPDATE organ_stickers set status='$sticker_status' WHERE sticker_no='$sticker_no' ") or die($con->errorInfo());



							$json['status'] = 'ok';

							$json['type'] = 'success';
							$json['message'] = 'Sticker successfully updated reading No : ' . $sticker_no;
						}
					} else {

						$json['status'] = 'error';
						$json['type'] = 'error';
						$json['message'] = 'Stickers already used up contact administrator at Britam Head office';
					}
				} else if ($vehicle_category == 'Motorcycles') {

					$sel0 = $con->query("select * from organ_stickers where category='Motor Bike' AND agent_user_id='$user_id' AND status='notused' ORDER BY sticker_no ASC LIMIT 1") or die($con->errorInfo());

					if ($sel0->rowCount() > 0) {

						while ($row0 = $sel0->fetch()) {
							$sticker_no = $row0['sticker_no'];
							//before update check user available stickers
							$con->exec("UPDATE motor_invoice_details set sticker_no='$sticker_no',print_status='printed' WHERE invoice_detail_id='$invoice_detail_id' ") or die($con->errorInfo());

							$con->exec("UPDATE motor_invoices set user_id='$user_id' WHERE invoice_id='$invoice_id' ") or die($con->errorInfo());

							$con->exec("UPDATE organ_stickers set status='$sticker_status' WHERE sticker_no='$sticker_no' ") or die($con->errorInfo());



							$json['status'] = 'ok';

							$json['type'] = 'success';
							$json['message'] = 'Sticker successfully updated reading No : ' . $sticker_no;
						}
					} else {

						$json['status'] = 'error';
						$json['type'] = 'error';
						$json['message'] = 'Stickers already used up contact administrator at Britam Head office';
					}
				} else if ($vehicle_category == 'Motor trade') {

					$sel0 = $con->query("select * from organ_stickers where category='Motor Transit' AND agent_user_id='$user_id' AND status='notused' ORDER BY sticker_no ASC LIMIT 1") or die($con->errorInfo());

					if ($sel0->rowCount() > 0) {

						while ($row0 = $sel0->fetch()) {
							$sticker_no = $row0['sticker_no'];
							//before update check user available stickers
							$con->exec("UPDATE motor_invoice_details set sticker_no='$sticker_no',print_status='printed' WHERE invoice_detail_id='$invoice_detail_id' ") or die($con->errorInfo());

							$con->exec("UPDATE motor_invoices set user_id='$user_id' WHERE invoice_id='$invoice_id' ") or die($con->errorInfo());

							$con->exec("UPDATE organ_stickers set status='$sticker_status' WHERE sticker_no='$sticker_no' ") or die($con->errorInfo());



							$json['status'] = 'ok';

							$json['type'] = 'success';
							$json['message'] = 'Sticker successfully updated reading No : ' . $sticker_no;
						}
					} else {

						$json['status'] = 'error';
						$json['type'] = 'error';
						$json['message'] = 'Stickers already used up contact administrator at Britam Head office';
					}
				}

				//havent chatered for categories buses,ambulances, bullion Vans/Fire fighting yet

			}
		}
	} else {
		$json['status'] = "missing";
		$json['type'] = 'success';
		$json['message'] = 'Missing fields';
	}
	echo json_encode($json);
}

function replace_Sticker()
{
	$con = connection();

	$json = array();


	if (isset($_REQUEST['invoice_detail_id'], $_REQUEST['charge'])) {
		$invoice_detail_id = urldecode(addslashes($_REQUEST['invoice_detail_id']));
		// $sticker_no = urldecode (strip_html_tags(addslashes($_REQUEST['sticker_no'])));  
		$charge = urldecode(addslashes($_REQUEST['charge']));
		$user_id = urldecode(addslashes($_REQUEST['user_id']));
		$payment_method = urldecode(addslashes($_REQUEST['payment_method']));
		$print_status = addslashes('printed');
		$sticker_status = addslashes('used');



		$se = $con->query("select * from motor_invoice_details where invoice_detail_id='$invoice_detail_id' ") or die($con->errorInfo());

		if ($se->rowCount() > 0) {

			while ($ro = $se->fetch()) {

				$old_sticker_no = $ro['sticker_no'];





				$sel = $con->query("select * from motor_invoice_details where invoice_detail_id='$invoice_detail_id'") or die($con->errorInfo());

				if ($sel->rowCount() > 0) {

					while ($row = $sel->fetch()) {

						$invoice_id = $row['invoice_id'];
						$vehicle_category = $row['vehicle_category'];

						if ($vehicle_category == 'Private') {

							$sel0 = $con->query("select * from organ_stickers where category='Motor Private' AND agent_user_id='$user_id' AND status='notused' ORDER BY sticker_no ASC LIMIT 1") or die($con->errorInfo());

							if ($sel0->rowCount() > 0) {

								while ($row0 = $sel0->fetch()) {

									$sticker_no = $row0['sticker_no'];

									//before update check user available stickers 
									$con->exec("INSERT INTO motor_invoice_sticker_payments set old_sticker_no='$old_sticker_no',sticker_no='$sticker_no', user_id='$user_id',amount='$charge',payment_method='$payment_method',time=NOW() ") or die($con->errorInfo());

									$con->exec("UPDATE motor_invoice_details set sticker_no='$sticker_no',print_status='printed' WHERE invoice_detail_id='$invoice_detail_id' ") or die($con->errorInfo());

									$con->exec("UPDATE motor_invoices set user_id='$user_id' WHERE invoice_id='$invoice_id' ") or die($con->errorInfo());

									$con->exec("UPDATE organ_stickers set status='$sticker_status' WHERE sticker_no='$sticker_no' ") or die($con->errorInfo());

									$json['status'] = 'ok';

									$json['type'] = 'success';
									$json['message'] = 'Sticker successfully replaced with No : ' . $sticker_no;
								}
							} else {

								$json['status'] = 'error';
								$json['type'] = 'error';
								$json['message'] = 'Stickers already used up contact administrator at Britam Head office';
							}
						} else if ($vehicle_category == 'Commercial' || $vehicle_category == 'Farm vehicles' || $vehicle_category == 'Buses' || $vehicle_category == 'Ambulances' || $vehicle_category == 'Bullion Vans/ Fire fighting' || $vehicle_category == 'Construction Equipments' || $vehicle_category == 'Hauliers' || $vehicle_category == 'Oil,gas,petro tankers') {

							$sel0 = $con->query("select * from organ_stickers where category='Motor Commercial' AND agent_user_id='$user_id' AND status='notused' ORDER BY sticker_no ASC LIMIT 1") or die($con->errorInfo());

							if ($sel0->rowCount() > 0) {

								while ($row0 = $sel0->fetch()) {
									$sticker_no = $row0['sticker_no'];
									//before update check user available stickers
									$con->exec("INSERT INTO motor_invoice_sticker_payments set old_sticker_no='$old_sticker_no',sticker_no='$sticker_no', user_id='$user_id',amount='$charge',payment_method='$payment_method',time=NOW() ") or die($con->errorInfo());

									$con->exec("UPDATE motor_invoice_details set sticker_no='$sticker_no',print_status='printed' WHERE invoice_detail_id='$invoice_detail_id' ") or die($con->errorInfo());

									$con->exec("UPDATE motor_invoices set user_id='$user_id' WHERE invoice_id='$invoice_id' ") or die($con->errorInfo());

									$con->exec("UPDATE organ_stickers set status='$sticker_status' WHERE sticker_no='$sticker_no' ") or die($con->errorInfo());

									$json['status'] = 'ok';

									$json['type'] = 'success';
									$json['message'] = 'Sticker successfully replaced with No : ' . $sticker_no;
								}
							} else {

								$json['status'] = 'error';
								$json['type'] = 'error';
								$json['message'] = 'Stickers already used up contact administrator at Britam Head office';
							}
						} else if ($vehicle_category == 'Motorcycles') {

							$sel0 = $con->query("select * from organ_stickers where category='Motor Bike' AND agent_user_id='$user_id' AND status='notused' ORDER BY sticker_no ASC LIMIT 1") or die($con->errorInfo());

							if ($sel0->rowCount() > 0) {

								while ($row0 = $sel0->fetch()) {
									$sticker_no = $row0['sticker_no'];
									//before update check user available stickers
									$con->exec("INSERT INTO motor_invoice_sticker_payments set old_sticker_no='$old_sticker_no',sticker_no='$sticker_no', user_id='$user_id',amount='$charge',payment_method='$payment_method',time=NOW() ") or die($con->errorInfo());

									$con->exec("UPDATE motor_invoice_details set sticker_no='$sticker_no',print_status='printed' WHERE invoice_detail_id='$invoice_detail_id' ") or die($con->errorInfo());

									$con->exec("UPDATE motor_invoices set user_id='$user_id' WHERE invoice_id='$invoice_id' ") or die($con->errorInfo());

									$con->exec("UPDATE organ_stickers set status='$sticker_status' WHERE sticker_no='$sticker_no' ") or die($con->errorInfo());

									$json['status'] = 'ok';

									$json['type'] = 'success';
									$json['message'] = 'Sticker successfully replaced with No : ' . $sticker_no;
								}
							} else {

								$json['status'] = 'error';
								$json['type'] = 'error';
								$json['message'] = 'Stickers already used up contact administrator at Britam Head office';
							}
						} else if ($vehicle_category == 'Motor trade') {

							$sel0 = $con->query("select * from organ_stickers where category='Motor Transit' AND agent_user_id='$user_id' AND status='notused' ORDER BY sticker_no ASC LIMIT 1") or die($con->errorInfo());

							if ($sel0->rowCount() > 0) {

								while ($row0 = $sel0) {
									$sticker_no = $row0['sticker_no'];
									//before update check user available stickers
									$con->exec("INSERT INTO motor_invoice_sticker_payments set old_sticker_no='$old_sticker_no',sticker_no='$sticker_no', user_id='$user_id',amount='$charge',payment_method='$payment_method',time=NOW() ") or die($con->errorInfo());

									$con->exec("UPDATE motor_invoice_details set sticker_no='$sticker_no',print_status='printed' WHERE invoice_detail_id='$invoice_detail_id' ") or die($con->errorInfo());

									$con->exec("UPDATE motor_invoices set user_id='$user_id' WHERE invoice_id='$invoice_id' ") or die($con->errorInfo());

									$con->exec("UPDATE organ_stickers set status='$sticker_status' WHERE sticker_no='$sticker_no' ") or die($con->errorInfo());

									$json['status'] = 'ok';

									$json['type'] = 'success';
									$json['message'] = 'Sticker successfully replaced with No : ' . $sticker_no;
								}
							} else {

								$json['status'] = 'error';
								$json['type'] = 'error';
								$json['message'] = 'Stickers already used up contact administrator at Britam Head office';
							}
						}

						//havent chatered for categories buses,ambulances, bullion Vans/Fire fighting yet



					}
				}


				// $con->exec("UPDATE organ_stickers set status='$sticker_status' WHERE sticker_no='$sticker_no' ")or die($con->errorInfo());

				// $con->exec("UPDATE motor_invoice_details set sticker_no='$sticker_no',print_status='$print_status' WHERE invoice_detail_id='$invoice_detail_id' ")or die($con->errorInfo());


				//$id = $con->lastInsertId(); // last inserted id

				$events = "User replaced sticker with ID " . $invoice_detail_id;
				$con->exec("INSERT INTO system_logs set user_id='$user_id',event='$events' ") or die($con->errorInfo());



				$sel = $con->query("select * from motor_invoice_details where invoice_detail_id='$invoice_detail_id' ") or die($con->errorInfo());

				if ($sel->rowCount() > 0) {

					while ($row = $sel->fetch()) {
						$json['invoice_detail_id'] = $row['invoice_detail_id'];
						$json['sticker_no'] = $row['sticker_no'];
					}
				}
			}
		} else {
			$json['status'] = "invalid fields";
			$json['type'] = 'error';
			$json['message'] = 'invalid fields';
		}
	} else {
		$json['status'] = "missing";
		$json['type'] = 'error';
		$json['message'] = 'Missing fields';
	}
	echo json_encode($json);
}


function add_MotorThird_Comesa()
{
	$con = connection();
	$json = array();

	if (isset($_REQUEST['organ_id'])) {
		$organ_id = urldecode(addslashes($_REQUEST['organ_id']));
		$policy_no = "";
		$client_id = urldecode(addslashes($_REQUEST['client_id']));
		//$motor_category = strip_html_tags(addslashes($_REQUEST['motor_category'])); 
		$iclass = urldecode(addslashes($_REQUEST['quote_type']));
		$basic_premium = urldecode(addslashes($_REQUEST['basic_premium']));
		$currency = urldecode(addslashes($_REQUEST['currency']));
		// $start_date = explode('-',$start_date);
		// $start_date = $start_date[2].'-'.$start_date[1].'-'.$start_date[0]; 
		$start_date =  (date('Y-m-d', strtotime(substr($_REQUEST['start_date'], 0, 10))));


		$country = urldecode((addslashes($_REQUEST['country'])));
		$training_levy = urldecode(addslashes($_REQUEST['training_levy']));
		$user_id = urldecode(addslashes($_REQUEST['user_id']));


		$status = urldecode(addslashes($_REQUEST['status']));


		try {
			if (isset($_REQUEST["vehicle_plate_no"])) {
				$vehicle_plate_no = urldecode(addslashes($_REQUEST['vehicle_plate_no']));
			}
		} catch (Exception $e) {

			$vehicle_plate_no = "";
		}


		//$status = strip_html_tags(addslashes('pending')); 
		$json['start_date'] = $start_date;

		if ($currency == 'undefined') {
			$currency = 'UGX';
		}

		if ($basic_premium > 0) {

			//get policy_no 
			$selpolicy = $con->query("select * from organ_stickers ORDER BY RAND() LIMIT 1") or die($con->errorInfo());

			if ($rowpolicy = $selpolicy->fetch()) {
				$policy_no = $rowpolicy['policy_no'];
			}
			$expiry_date = date('Y-m-d');
			// AND q.end_date >= '$expiry_date'

			$sel2 = $con->query("select q.* from motor_invoices q INNER JOIN motor_invoice_details d ON d.invoice_id=q.invoice_id where d.vehicle_plate_no='$vehicle_plate_no' AND (q.status='paid' OR q.status='completed') AND q.end_date >= '$expiry_date'") or die($con->errorInfo());

			if ($sel2->rowCount() >= 1) {

				$json['status'] = 'duplicate';
				$json['message'] = 'Vehicle with Registration [' . $vehicle_plate_no . '] has a running thirdparty policy, you can only replace sticker or renew';
			} else {


				if ($iclass == 'thirdparty' || $iclass == 'newimport') {

					$con->exec("INSERT INTO motor_invoices set organ_id='$organ_id',policy_no='$policy_no',status='$status',user_id='$user_id',country='$country',start_date='$start_date',end_date=DATE_ADD('$start_date', INTERVAL 365 DAY),training_levy='$training_levy',client_id='$client_id',basic_premium='$basic_premium',currency='$currency',iclass='$iclass',time=NOW() ") or die($con->errorInfo());
					
				} else if ($iclass == 'transit') {

					$basic_premium = ($basic_premium + ($basic_premium * 0.2));

					$con->exec("INSERT INTO motor_invoices set organ_id='$organ_id',policy_no='$policy_no',status='$status',user_id='$user_id',country='$country',start_date='$start_date',end_date=DATE_ADD('$start_date', INTERVAL 31 DAY),training_levy='$training_levy',client_id='$client_id',basic_premium='$basic_premium',currency='$currency',iclass='$iclass',time=NOW() ") or die($con->errorInfo());
				} else if ($iclass == 'comprehensive') {

					$debit_note = addslashes($_REQUEST['debit_note']);
					$comprehensive_paid = addslashes($_REQUEST['comprehensive_paid']);

					$con->exec("INSERT INTO motor_invoices set debit_note='$debit_note',comprehensive_paid='$comprehensive_paid',organ_id='$organ_id',policy_no='$policy_no',status='$status',user_id='$user_id',country='$country',start_date='$start_date',end_date=DATE_ADD('$start_date', INTERVAL 365 DAY),training_levy='$training_levy',client_id='$client_id',basic_premium='$basic_premium',currency='$currency',iclass='$iclass',time=NOW() ") or die($con->errorInfo());
				}


				$id = $con->lastInsertId(); // last inserted id

				$events = "User issued thirdparty with ID " . $id;
				$con->exec("INSERT INTO system_logs set user_id='$user_id',event='$events'") or die($con->errorInfo());



				$sel = $con->query("select * from motor_invoices where invoice_id='$id' ") or die($con->errorInfo());

				if ($sel->rowCount() > 0) {

					while ($row = $sel->fetch()) {
						$json['invoice_id'] = $row['invoice_id'];
						//$json['sticker_no']=$row['sticker_no'];
						$json['client_id'] = $row['client_id'];
						$json['basic_premium'] = $row['basic_premium'];
						$json['policy_no'] = $row['policy_no'];
						$json['organ_id'] = $row['organ_id'];
					}

					$json['status'] = 'ok';
					$json['message'] = 'Sticker successfully purchased';
				}
			}
		} else {
			$json['status'] = 'error';
			$json['message'] = 'Please click generate basic premium before submitting data';
		}/**/
	} else {
		$json['status'] = "missing";
		$json['message'] = 'Missing fields';
	}
	echo json_encode($json);
}

function addMotor_MotorThird_Comesa_details()
{
	$con = connection();

	$json = array();


	if (isset($_REQUEST['invoice_id'], $_REQUEST['basic_premium'])) {
		$invoice_id = addslashes($_REQUEST['invoice_id']);
		$user_id = addslashes($_REQUEST['user_id']);
		$basic_premium = addslashes($_REQUEST['basic_premium']);
		$vehicle_chasis_no = addslashes($_REQUEST['vehicle_chasis_no']);
		$vehicle_category = addslashes($_REQUEST['vehicle_category']);
		$vehicle_class = addslashes($_REQUEST['vehicle_class']);
		$gross_weight = addslashes($_REQUEST['gross_weight']);
		//$terms = strip_html_tags(addslashes($_REQUEST['terms']));  
		$vehicle_make = addslashes($_REQUEST['vehicle_make']);
		$vehicle_use = addslashes($_REQUEST['vehicle_use']);
		$vehicle_plate_no = urldecode(addslashes($_REQUEST['vehicle_plate_no']));
		$vehicle_no_seats = addslashes($_REQUEST['vehicle_no_seats']);
		$vehicle_cc = addslashes($_REQUEST['vehicle_cc']);
		$training_levy = addslashes($_REQUEST['training_levy']);


		if ($vehicle_cc == 'undefined') {
			$vehicle_cc = '';
		}

		if ($vehicle_no_seats == 'undefined') {
			$vehicle_no_seats = '';
		}

		if ($vehicle_plate_no == 'undefined') {
			$vehicle_plate_no = '';
		}

		if ($vehicle_use == 'undefined') {
			$vehicle_use = '';
		}

		if ($gross_weight == 'undefined') {
			$gross_weight = '';
		}

		if ($vehicle_make == 'undefined') {
			$vehicle_make = '';
		}

		if ($vehicle_chasis_no == 'undefined') {
			$vehicle_chasis_no = '';
		}


		//vehicle_use

		$con->exec("INSERT INTO motor_invoice_details set user_id='$user_id',invoice_id='$invoice_id',basic_premium='$basic_premium',vehicle_chasis_no='$vehicle_chasis_no',vehicle_category='$vehicle_category',vehicle_class='$vehicle_class',gross_weight='$gross_weight',vehicle_make='$vehicle_make',vehicle_use='$vehicle_use',vehicle_plate_no='$vehicle_plate_no',vehicle_cc='$vehicle_cc',training_levy='$training_levy',vehicle_no_seats='$vehicle_no_seats' ") or die($con->errorInfo());

		$id = $con->lastInsertId(); // last inserted id



		$json['status'] = 'ok';
		$json['message'] = 'Motor thirdparty details successfully recorded';
	} else {
		$json['status'] = "missing";
		$json['message'] = 'Missing fields';
	}
	echo json_encode($json);
}

function getClientID()
{
	$con = connection();

	$json = array();

	if (isset($_REQUEST['client_telephone'])) {

		$client_telephone = addslashes($_REQUEST['client_telephone']);
		$json['client_id'] = '0';
		$sel = $con->query("select * FROM clients WHERE telephone='$client_telephone'") or die($con->errorInfo());

		if ($sel->rowCount() > 0) {
			while ($row = $sel->fetch()) {


				$json['client_id'] = $row['client_id'];
			}
			//
			$json['status'] = "ok";
		} else {

			$json['status'] = "empty";
		}
	} else {
		$json['status'] = "missing";
	}
	echo json_encode($json);
}


function sendClientSMS()
{
	//$con = connection();

	/*	$json=array();
	
	if(isset($_REQUEST['client_telephone']))
	{
	$client_telephone=strip_html_tags(addslashes($_REQUEST['client_telephone'])); 
	$refNo=strip_html_tags(addslashes($_REQUEST['refNo'])); 
	
	require_once "../telerivet/telerivet.php";
	   
	$API_KEY = 'VUGupggZc1QmUl7gec6zUZ1BxukQgSmB';           // from https://telerivet.com/api/keys
	$PROJECT_ID = 'PJ7fdf8389d2066c66';

	$telerivet = new Telerivet_API($API_KEY);

	$project = $telerivet->initProjectById($PROJECT_ID);

	// Send a SMS message
	$project->sendMessage(array(
		'to_number' => $client_telephone,
		'content' => '[XYLEM Agency MGT] Approve your quotation refNo '.$refNo.' with format refNo<SPACE>approved'
	));   

	 $json['status']="ok";
	 $json['message']="SMS sent successful";
	}
	else
	{
		$json['status']="missing";
	}
	echo json_encode($json); 
	*/
}

//user dashboards
function getUserDashboard()
{

	$con = connection();

	$json = array();

	if (isset($_REQUEST['user_id'], $_REQUEST['role'])) {

		$user_id = addslashes(trim(strtolower($_REQUEST['user_id'])));
		$role = addslashes(trim(strtolower($_REQUEST['role'])));

		$json['clients'] = '0';
		$json['quotes'] = '0';
		$json['party'] = '0';
		$json['policies'] = '0';
		$json['expectrevenue'] = '0';
		$json['actualrevenue'] = '0';
		$json['revenue'] = '0';
		$json['commission'] = '0';
		$json['claim_notification'] = '0';
		$json['claim_settlement'] = '0';

		$today = date('Y-m-d');

		//checking user roles

		if ($role == 'admin') {
			//load user referral no
			$selThird = $con->query("select count(invoice_id) as party FROM motor_invoices") or die($con->errorInfo());

			while ($rowM = $selThird->fetch()) {
				$json['party'] = $rowM['party'];
			}

			$selAdverts = $con->query("select count(invoice_id) as policy_no FROM motor_invoices WHERE  (status='paid' OR status='replaced' OR status='completed')") or die($con->errorInfo());

			while ($rowA = $selAdverts->fetch()) {
				$json['policies'] = $rowA['policy_no'];
			}

			$json['count'] = 0;
			// $comm1=$con->query("select count(b.invoice_id) as comm FROM motor_invoices b INNER JOIN users u ON u.user_id=b.user_id WHERE (b.status='paid' OR b.status='replaced' OR b.status='completed') ")or die($con->errorInfo());

			// while($rowcomm1=$comm1->fetch()){   
			// $json['count'] =$rowcomm1['comm'];
			// } 

			$json['party'] = ($json['count'] + $json['party']);

			$json['policies'] = ($json['count'] + $json['policies']);

			$comm = $con->query("select SUM(basic_premium) as comm FROM motor_invoices WHERE user_id='$user_id'") or die($con->errorInfo());

			while ($rowcomm = $comm->fetch()) {
				$json['commission'] = $rowcomm['comm'];
			}


			$json['taxes'] = (STAMP_DUTY + STICKER_FEES) * $json['count'];

			// $comm1=$con->query("select SUM(b.basic_premium) as comm FROM motor_invoices b INNER JOIN users u ON u.user_id=b.user_id WHERE (b.status='paid' OR b.status='replaced' OR b.status='completed')")or die($con->errorInfo());

			// while($rowcomm1=$comm1->fetch()){   
			// $json['commission2'] =$rowcomm1['comm'];
			// } 


			$json['revenue'] = ($json['commission'] + $json['taxes']);


			$json['commission'] = (($json['revenue']) - ($json['revenue'] * 0.06) - ($json['revenue'] * 0.015)) * 0.1;

			$loadClaimsN = $con->query("select count(claim_ref) as notify_no FROM motor_claim_notification WHERE status<>'Deleted' ") or die($con->errorInfo());

			while ($rowE = $loadClaimsN->fetch()) {
				$json['claim_notification'] = $rowE['notify_no'];
			}

			$loadClaimsS = $con->query("select count(setttlement_id) as settle_no FROM motor_claim_settlement") or die($con->errorInfo());

			while ($rowS = $loadClaimsS->fetch()) {
				$json['claim_settlement'] = $rowS['settle_no'];
			}



			$json['status'] = "ok";
		} else if ($role == 'thirdparty_admin' || $role == 'thirdparty_agent' || $role == 'mtn_dealer' || $role == 'mtn_agent') {
			//load user referral no


			$selThird = $con->query("select count(invoice_id) as party FROM motor_invoices WHERE user_id='$user_id'") or die($con->errorInfo());

			while ($rowM = $selThird->fetch()) {
				$json['party'] = $rowM['party'];
			}

			$selAdverts = $con->query("select count(invoice_id) as policy_no FROM motor_invoices WHERE user_id='$user_id'") or die($con->errorInfo());

			while ($rowA = $selAdverts->fetch()) {
				$json['policies'] = $rowA['policy_no'];
			}

			$json['count'] = 0;
			// $comm1=$con->query("select count(b.invoice_id) as comm FROM motor_invoices b INNER JOIN users u ON u.user_id=b.user_id WHERE (b.status='paid' OR b.status='replaced' OR b.status='completed') ")or die($con->errorInfo());

			// while($rowcomm1=$comm1->fetch()){   
			// $json['count'] =$rowcomm1['comm'];
			// } 

			$json['party'] = ($json['count'] + $json['party']);

			$json['policies'] = ($json['count'] + $json['policies']);

			$comm = $con->query("select SUM(basic_premium) as comm FROM motor_invoices WHERE  user_id='$user_id'") or die($con->errorInfo());

			while ($rowcomm = $comm->fetch()) {
				$json['commission'] = $rowcomm['comm'];
			}


			$json['taxes'] = (STAMP_DUTY + STICKER_FEES) * $json['count'];

			// $comm1=$con->query("select SUM(b.basic_premium) as comm FROM motor_invoices b INNER JOIN users u ON u.user_id=b.user_id WHERE (b.status='paid' OR b.status='replaced' OR b.status='completed')")or die($con->errorInfo());

			// while($rowcomm1=$comm1->fetch()){   
			// $json['commission2'] =$rowcomm1['comm'];
			// } 


			$json['revenue'] = ($json['commission'] + $json['taxes']);


			$json['commission'] = (($json['revenue']) - ($json['revenue'] * 0.06) - ($json['revenue'] * 0.015)) * 0.1;

			$loadClaimsN = $con->query("select count(claim_ref) as notify_no FROM motor_claim_notification WHERE status<>'Deleted' AND user_id='$user_id' ") or die($con->errorInfo());

			while ($rowE = $loadClaimsN->fetch()) {
				$json['claim_notification'] = $rowE['notify_no'];
			}

			$loadClaimsS = $con->query("select count(setttlement_id) as settle_no FROM motor_claim_settlement") or die($con->errorInfo());

			while ($rowS = $loadClaimsS->fetch()) {
				$json['claim_settlement'] = $rowS['settle_no'];
			}



			$json['status'] = "ok";
		}
	} else {
		$json['status'] = "missing";
	}

	echo json_encode($json);
}


//user dashboards
function viewUserSummaryRevenues()
{

	$con = connection();

	$json = array();

	if (isset($_REQUEST['user_id'])) {

		$user_id = addslashes(trim(strtolower($_REQUEST['user_id'])));

		$row['agent_balance'] = '0';
		$row['no_stickers'] = '0';
		$row['total_gross_amount'] = '0';
		$row['total_commission'] = '0';
		$row['taxes_levies'] = '0';
		$row['net_commission'] = '0';
		$row['agent_paid'] = '0';

		$today = date('Y-m-d');


		$selThird = $con->query("select count(invoice_id) as party FROM motor_invoices WHERE user_id='$user_id'") or die($con->errorInfo());

		while ($rowM = $selThird->fetch()) {
			$row['no_stickers'] = $rowM['party'];
		}

		$selAdverts = $con->query("select count(invoice_id) as policy_no FROM motor_invoices WHERE user_id='$user_id'") or die($con->errorInfo());

		while ($rowA = $selAdverts->fetch()) {
			$json['policies'] = $rowA['policy_no'];
		}

		$json['count'] = 0;
		// $comm1=$con->query("select count(b.invoice_id) as comm FROM motor_invoices b INNER JOIN users u ON u.user_id=b.user_id WHERE (b.status='paid' OR b.status='replaced' OR b.status='completed') ")or die($con->errorInfo());

		// while($rowcomm1=$comm1->fetch()){   
		// $json['count'] =$rowcomm1['comm'];
		// } 

		$row['no_stickers'] = ($json['count'] + $row['no_stickers']);


		$json['results'][] = '';

		$json['summary'] = $row;

		$json['status'] = "ok";
	} else {
		$json['status'] = "missing";
	}

	echo json_encode($json);
}


function editUsers()
{
	$con = connection();

	$json = array();

	if (isset($_REQUEST['user_id'])) {
		$user_id = addslashes($_REQUEST['user_id']);
		$license_no = addslashes($_REQUEST['license_no']);
		$name = addslashes($_REQUEST['name']);
		$NIN = addslashes($_REQUEST['NIN']);
		$dob = date('Y-m-d', strtotime(substr($_REQUEST['dob'], 0, 10)));
		$gender = addslashes($_REQUEST['gender']);
		$email = ($_REQUEST['email']);
		$telephone = addslashes($_REQUEST['telephone']);
		$address = addslashes($_REQUEST['address']);
		$role = addslashes($_REQUEST['role']);
		$status = addslashes($_REQUEST['status']);
		$branch_name = addslashes($_REQUEST['branch_name']);


		//$qr_code = $converter->encode($pin); 

		$sel3 = $con->query("select user_id, name, telephone, email from users where user_id='$user_id'") or die($con->errorInfo());

		if ($sel3->rowCount() > 0) {


			$sel = $con->exec("UPDATE users set name='$name',license_no='$license_no',email='$email',NIN='$NIN',branch_name='$branch_name',dob='$dob',gender='$gender',telephone='$telephone',address='$address',role='$role',status='$status' WHERE user_id='$user_id'") or die($con->errorInfo());


			if ($sel) {

				$sel2 = $con->query("select * from users where user_id='$user_id' ") or die($con->errorInfo());

				if ($sel2->rowCount() > 0) {

					while ($row = $sel2->fetch()) {
						$json['user_id'] = $row['user_id'];
					}
				}

				$json['status'] = 'ok';
				$json['type'] = 'success';
				$json['message'] = 'User successfully edited';
			}
		} else {
			$json['status'] = 'notfound';
			$json['type'] = 'error';
			$json['message'] = 'User not found';
		}
	} else {
		$json['status'] = "missing";
		$json['type'] = 'error';
		$json['message'] = 'Missing Fields';
	}
	echo json_encode($json);
}


function editUserProfile()
{
	$con = connection();

	$json = array();

	if (isset($_REQUEST['user_id'])) {
		$user_id = addslashes($_REQUEST['user_id']);
		$license_no = addslashes($_REQUEST['license_no']);
		$name = addslashes($_REQUEST['name']);
		$NIN = addslashes($_REQUEST['NIN']);
		$dob = date('Y-m-d', strtotime(substr($_REQUEST['dob'], 0, 10)));
		$gender = addslashes($_REQUEST['gender']);
		$email = ($_REQUEST['email']);
		$telephone = addslashes($_REQUEST['telephone']);
		$address = addslashes($_REQUEST['address']);
		// $role = strip_html_tags(addslashes($_REQUEST['role']));  
		// $status = strip_html_tags(addslashes($_REQUEST['status']));
		$branch_name = addslashes($_REQUEST['branch_name']);


		//$qr_code = $converter->encode($pin); 

		$sel3 = $con->query("select user_id, name, telephone, email from users where user_id='$user_id'") or die($con->errorInfo());

		if ($sel3->rowCount() > 0) {


			$sel = $con->exec("UPDATE users set name='$name',license_no='$license_no',email='$email',NIN='$NIN',branch_name='$branch_name',dob='$dob',gender='$gender',telephone='$telephone',address='$address' WHERE user_id='$user_id'") or die($con->errorInfo());


			if ($sel) {

				$sel2 = $con->query("select * from users where user_id='$user_id' ") or die($con->errorInfo());

				if ($sel2->rowCount() > 0) {

					while ($row = $sel2->fetch()) {
						$json['user_id'] = $row['user_id'];
					}
				}

				$json['status'] = 'ok';
				$json['type'] = 'success';
				$json['message'] = 'Your profile successfully updated';
			}
		} else {
			$json['status'] = 'notfound';
			$json['type'] = 'error';
			$json['message'] = 'User not found';
		}
	} else {
		$json['status'] = "missing";
		$json['type'] = 'error';
		$json['message'] = 'Missing Fields';
	}
	echo json_encode($json);
}


function payThirdparty()
{
	$con = connection();

	$json = array();

	// $digits_needed=9; 
	// $random_number=''; 
	// $count=0; 
	// while ( $count < $digits_needed ) {
	// $random_digit = mt_rand(0, 9);

	// $random_number .= $random_digit;
	// $count++;
	// }

	if (isset($_REQUEST['invoice_id'])) {

		$invoice_id = urlencode(addslashes($_REQUEST['invoice_id']));

		// $policy_no = urlencode (strip_html_tags(addslashes($_REQUEST['policy_no']))); 
		//$policy_no = urlencode($random_number); 

		$payment_method = urlencode(addslashes($_REQUEST['payment_method']));
		$total_amount = urlencode(addslashes($_REQUEST['total_amount']));
		$user_id = urlencode(addslashes($_REQUEST['user_id']));




		$sel3 = $con->query("select * from motor_invoices where invoice_id='$invoice_id'") or die($con->errorInfo());

		if ($sel3->rowCount() > 0) {


			// $seld=$con->query("select * from motor_invoices where policy_no='$policy_no'")or die($con->errorInfo());

			// if ($seld->rowCount() > 0) {

			// $json['status']='duplicate';
			// $json['type']='error';
			// $json['message']='Policy number already exists, use a different Policy number';
			// }
			// else{

			$sel = $con->exec("UPDATE motor_invoices set status='paid',payment_method='$payment_method',amount_paid='$total_amount' WHERE invoice_id='$invoice_id'") or die($con->errorInfo());

			$sel = $con->exec("INSERT INTO motor_invoice_payments set invoice_id='$invoice_id',payment_method='$payment_method',amount='$total_amount',user_id='$user_id'") or die($con->errorInfo());


			if ($sel) {

				$sel2 = $con->query("select * from motor_invoices where invoice_id='$invoice_id' ") or die($con->errorInfo());

				if ($sel2->rowCount() > 0) {

					while ($row = $sel2->fetch()) {
						$json['invoice_id'] = $row['invoice_id'];
					}
				}

				$json['status'] = 'ok';
				$json['type'] = 'success';
				$json['message'] = 'Thirdparty successfully paid';
			}
			//close duplicates
			// }

		} else {
			$json['status'] = 'notfound';
			$json['type'] = 'error';
			$json['message'] = 'Thirdparty details not found';
		}
	} else {
		$json['status'] = "missing";
		$json['type'] = 'error';
		$json['message'] = 'Missing Fields';
	}
	echo json_encode($json);
}




function removeUsers()
{
	$con = connection();

	$json = array();

	if (isset($_REQUEST['user_id'])) {
		$user_id = addslashes($_REQUEST['user_id']);

		$sel = $con->exec("DELETE FROM users WHERE user_id='$user_id'") or die($con->errorInfo());

		if ($sel) {
			deleteDirectory('../images/users/' . $user_id);

			$json['status'] = 'ok';
		} else {
			$json['status'] = 'error';
		}
	} else {
		$json['status'] = "missing";
	}
	echo json_encode($json);
}


function cancel_invoice()
{
	$con = connection();

	$json = array();

	if (isset($_REQUEST['invoice_id'])) {
		$invoice_id = addslashes($_REQUEST['invoice_id']);

		$sel = $con->exec("UPDATE motor_invoices SET status='cancelled' WHERE invoice_id='$invoice_id'") or die($con->errorInfo());

		if ($sel) {

			$json['status'] = 'ok';
		} else {
			$json['status'] = 'error';
		}
	} else {
		$json['status'] = "missing";
	}
	echo json_encode($json);
}

function delete_invoice()
{
	$con = connection();

	$json = array();

	if (isset($_REQUEST['invoice_id'])) {
		$invoice_id = addslashes($_REQUEST['invoice_id']);

		$sel = $con->exec("UPDATE motor_invoices SET status='deleted' WHERE invoice_id='$invoice_id'") or die($con->errorInfo());

		if ($sel) {

			$json['status'] = 'ok';
		} else {
			$json['status'] = 'error';
		}
	} else {
		$json['status'] = "missing";
	}
	echo json_encode($json);
}


function send_client_email()
{
	$con = connection();
	$json = array();

	if (isset($_REQUEST['client_email'])) {
		$client_email = addslashes($_REQUEST['client_email']);
		$quote_id = addslashes($_REQUEST['quote_id']);


		$sel = $con->query("select q.*,p.stamp_duty,p.sticker_fees,p.rate,(SELECT ccc1.telephone FROM clients ccc1 WHERE ccc1.client_id=q.client_id) AS client_telephone,(SELECT ccc.email FROM clients ccc WHERE ccc.client_id=q.client_id) AS client_email,(SELECT cc.address FROM clients cc WHERE cc.client_id=q.client_id) AS client_address,(SELECT c.name FROM clients c WHERE c.client_id=q.client_id) AS client_name,(SELECT CONCAT(u3.name,' ',u3.email,' ',u3.telephone) FROM users u3 WHERE u3.user_id=q.user_id) AS agent_details,(SELECT u2.license_no FROM users u2 WHERE u2.user_id=q.user_id) AS license_no,(SELECT o2.code FROM organisations o2 WHERE o2.organ_id=p.organ_id) AS organ_code,(SELECT o22.organ_id FROM organisations o22 WHERE o22.organ_id=p.organ_id) AS organ_id,(SELECT o.name FROM organisations o WHERE o.organ_id=p.organ_id) AS organ_name,(SELECT oo.address FROM organisations oo WHERE oo.organ_id=p.organ_id) AS organ_address,(SELECT CONCAT(ooo.contact_name,' ',ooo.contact_email,' ',ooo.contact_tel) AS contact FROM organisations ooo WHERE ooo.organ_id=p.organ_id) AS organ_contact FROM motor_quotations q INNER JOIN motor_policies p ON p.policy_id=q.policy_id WHERE q.quote_id='$quote_id'") or die($con->errorInfo());

		if ($sel->rowCount() > 0) {
			while ($row = $sel->fetch()) {

				$json['agent_license'] = $row['license_no'];
				$json['organ_code'] = $row['organ_code'];
				$json['time'] = $row['time'];
				$json['organ_name'] = $row['organ_name'];
				$json['organ_address'] = $row['organ_address'];
				$json['start_date'] = $row['start_date'];
				$json['end_date'] = $row['end_date'];
				$json['basic_premium'] = $row['basic_premium'];
				$json['training_levy'] = $row['training_levy'];
				$json['sticker_fees'] = $row['sticker_fees'];
				$json['stamp_duty'] = $row['stamp_duty'];
				$json['vehicle_value'] = $row['vehicle_value'];
				$json['vehicle_use'] = $row['vehicle_use'];
				$json['vehicle_make'] = $row['vehicle_make'];
				$json['year_of_manufacture'] = $row['year_of_manufacture'];
				$json['vehicle_type'] = $row['vehicle_type'];
				$json['agent_details'] = $row['agent_details'];
				$vat = (($json['basic_premium']) + ($json['training_levy']) + ($json['sticker_fees'])) * 0.18;
				$sel2 = $con->query("select q.* FROM motor_policy_terms q INNER JOIN motor_quotation_policy_terms t ON q.term_id=t.motor_policy_term_id WHERE t.motor_quotation_id='$quote_id'") or die($con->errorInfo());
				while ($row2 = $sel2->fetch()) {

					$row['terms'][] = $row2;
				}


				$organ_id = $row['organ_id'];
				$pic = getpicture('../images/organisations', $organ_id);
				$row['logo'] = $pic;

				//$json['results'][]=$row;
			}
			//
			$json['status'] = "ok";
		} else {

			$json['status'] = "empty";
		}

		$to = $client_email;
		$subject = "XYLEM INSURANCE MGT | Quotation Approval";

		$htmlContent = '
<html>
<body>
    <h3>Quotation Approval</h3>
	<hr/>
    <p>Agent License No: ' . $json['agent_license'] . '<br/>
	Company Code No: ' . $json['organ_code'] . '<br/>
	Date: ' . $json['time'] . '
	<hr/><br/> 
	Organisation: ' . $json['organ_name'] . ' ' . $json['organ_address'] . '<br/>
	Start Date: ' . $json['start_date'] . ' End Date: ' . $json['end_date'] . ' <br/><br/>
	<h5>Vehicle Details</h5>
	<table border=1>
      <thead>
         <tr>
            <th>
               <p>Vehicle Type</p> 
            </th>
            <th>
               <p>Make</p> 
            </th>
            
            <th>
               <p>Use</p> 
            </th>
            
            <th>
               <p>Valuation</p> 
            </th>
            
           
         </tr>
      </thead>
      <tbody> 
		 <tr>
		 <td>' . $json['vehicle_type'] . '</td> 
		 <td>' . $json['vehicle_make'] . ' ' . $json['year_of_manufacture'] . '</td> 
		 <td>' . $json['vehicle_use'] . '</td>  
		<td>UGX' . number_format($json['vehicle_value'], 2) . ' </td>		 
		 </tr> 
      </tbody>
   </table>
   
	<table border=1>
      <thead>
         <tr>
            <th colspan=10><u>Cost of Insurance:</u></th> 
         </tr>
      </thead>
      <tbody>
         <tr>
            <th>
               <p>Basic Premium</p> 
            </th>
            <td>UGX' . number_format($json['basic_premium'], 2) . ' </td> 
         </tr> 
		 
         <tr>
            <th>
               <p>Training Levy(0.5%)</p> 
            </th>
            <td>UGX' . number_format($json['training_levy'], 2) . ' </td>
         </tr>
		  <tr>
            <th>
               <p>Sticker Fee</p> 
            </th> 
            <td>UGX' . number_format($json['sticker_fees'], 2) . ' </td> 
         </tr>
		 
		 
		  <tr>
            <th>
               <p>VAT(18%)</p> 
            </th>
            <td>' . number_format($vat, 2) . ' </td> 
         </tr>
          <tr>
            <th>
               <p>Stamp Duty</p> 
            </th>
            <td>UGX' . number_format($json['stamp_duty'], 2) . ' </td> 
         </tr>
          
           
      </tbody>
   </table>
Total Premium: ' . number_format(($json['basic_premium']) + ($json['stamp_duty']) + ($json['sticker_fees']) + ($vat) + ($json['training_levy']), 2) . '
	</p>
	<p>Quotation was prepared by ' . $json['agent_details'] . '<br/></p>
	<p>Approve quotation by clicking <a href="https://xylem.clearbasics.ug/api/?cmd=clientApproveQuote&quote_id=' . $quote_id . '" target="_blank">here</a></p>
</body>
</html>';

		// Set content-type header for sending HTML email
		$headers = "MIME-Version: 1.0" . "\r\n";
		$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

		// Additional headers
		$headers .= 'From: XYLEM<info@clearbasics.ug>' . "\r\n";
		//$headers .= 'Cc: mugabijohnblaiz@.com' . "\r\n";
		$headers .= 'Bcc: mugabijohnblaiz@gmail.com' . "\r\n";

		// Send email
		if (mail($to, $subject, $htmlContent, $headers)) :
			$json['message'] = 'Email has sent successfully.';
			$json['status'] = 'sent';
		else :
			$json['message'] = 'Email sending fail.';
			$json['status'] = 'notsent';
		endif;
	} else {
		$json['status'] = "missing";
	}
	echo json_encode($json);
}

function send_email($to, $subject, $txt)
{

	$to = $to;
	$subject = $subject;
	$txt = $txt;

	$headers = "From: info@clearbasics.ug" . "\r\n";
	$headers .= "Reply-To: info@clearbasics.ug" . "\r\n";
	$headers .= "BCC: rnsubuga@clearbasics.ug,mugabijohnblaiz@gmail.com\r\n";
	$headers .= "MIME-Version: 1.0\r\n";
	$headers .= "Content-Type: text/html; charset=UTF-8\r\n";


	mail($to, $subject, $txt, $headers);
}


function humanTiming($time)
{

	$time = time() - $time; // to get the time since that moment
	$time = ($time < 1) ? 1 : $time;
	$tokens = array(
		31536000 => 'year',
		2592000 => 'month',
		604800 => 'week',
		86400 => 'day',
		3600 => 'hour',
		60 => 'minute',
		1 => 'second'
	);

	foreach ($tokens as $unit => $text) {
		if ($time < $unit) continue;
		$numberOfUnits = floor($time / $unit);
		return $numberOfUnits . ' ' . $text . (($numberOfUnits > 1) ? 's' : '');
	}
}


function deleteDirectory($dirPath)
{
	if (is_dir($dirPath)) {
		$objects = scandir($dirPath);
		foreach ($objects as $object) {
			if ($object != "." && $object != "..") {
				if (filetype($dirPath . DIRECTORY_SEPARATOR . $object) == "dir") {
					deleteDirectory($dirPath . DIRECTORY_SEPARATOR . $object);
				} else {
					unlink($dirPath . DIRECTORY_SEPARATOR . $object);
				}
			}
		}
		reset($objects);
		rmdir($dirPath);
	}
}

function upload_files()
{
	$folder_id = addslashes($_REQUEST['id']);
	$folder_name = addslashes($_REQUEST['name']);


	if (!empty($_FILES)) {

		$tempPath = $_FILES['file']['tmp_name'];
		$uploadPath = dirname(__FILE__) . DIRECTORY_SEPARATOR . '../images/' . $folder_name . '/' . $folder_id . DIRECTORY_SEPARATOR . $_FILES['file']['name'];

		move_uploaded_file($tempPath, $uploadPath);

		$answer = array('answer' => 'File transfer completed');
		$json = json_encode($answer);

		echo $json;
	} else {

		echo 'No files';
	}
}


function EmptyDir($dir)
{

	$handle = opendir($dir);



	while (($file = readdir($handle)) !== false) {

		echo "$file <br>";

		@unlink($dir . '/' . $file);
	}

	closedir($handle);
}



function upload_edited_user_files()
{
	$folder_id = addslashes($_REQUEST['id']);


	if (!empty($_FILES)) {

		$tempPath = $_FILES['file']['tmp_name'];




		if (!file_exists("../images/users/" . $folder_id)) {

			mkdir("../images/users/" . $folder_id, 0777, true);
		} else {

			EmptyDir('../images/users/' . $folder_id);
		}


		$uploadPath = dirname(__FILE__) . DIRECTORY_SEPARATOR . '../images/users/' . $folder_id . DIRECTORY_SEPARATOR . $_FILES['file']['name'];

		move_uploaded_file($tempPath, $uploadPath);

		$answer = array('answer' => 'File transfer completed' . $tempPath);
		$json = json_encode($answer);

		echo $json;
	} else {

		echo 'No files';
	}
}



// Email address verification, do not edit.
function isEmail($email)
{
	return (preg_match("/^[-_.[:alnum:]]+@((([[:alnum:]]|[[:alnum:]][[:alnum:]-]*[[:alnum:]])\.)+(ad|ae|aero|af|ag|ai|al|am|an|ao|aq|ar|arpa|as|at|au|aw|az|ba|bb|bd|be|bf|bg|bh|bi|biz|bj|bm|bn|bo|br|bs|bt|bv|bw|by|bz|ca|cc|cd|cf|cg|ch|ci|ck|cl|cm|cn|co|com|coop|cr|cs|cu|cv|cx|cy|cz|de|dj|dk|dm|do|dz|ec|edu|ee|eg|eh|er|es|et|eu|fi|fj|fk|fm|fo|fr|ga|gb|gd|ge|gf|gh|gi|gl|gm|gn|gov|gp|gq|gr|gs|gt|gu|gw|gy|hk|hm|hn|hr|ht|hu|id|ie|il|in|info|int|io|iq|ir|is|it|jm|jo|jp|ke|kg|kh|ki|km|kn|kp|kr|kw|ky|kz|la|lb|lc|li|lk|lr|ls|lt|lu|lv|ly|ma|mc|md|mg|mh|mil|mk|ml|mm|mn|mo|mp|mq|mr|ms|mt|mu|museum|mv|mw|mx|my|mz|na|name|nc|ne|net|nf|ng|ni|nl|no|np|nr|nt|nu|nz|om|org|pa|pe|pf|pg|ph|pk|pl|pm|pn|pr|pro|ps|pt|pw|py|qa|re|ro|ru|rw|sa|sb|sc|sd|se|sg|sh|si|sj|sk|sl|sm|sn|so|sr|st|su|sv|sy|sz|tc|td|tf|tg|th|tj|tk|tm|tn|to|tp|tr|tt|tv|tw|tz|ua|ug|uk|um|us|uy|uz|va|vc|ve|vg|vi|vn|vu|wf|ws|ye|yt|yu|za|zm|zw)$|(([0-9][0-9]?|[0-1][0-9][0-9]|[2][0-4][0-9]|[2][5][0-5])\.){3}([0-9][0-9]?|[0-1][0-9][0-9]|[2][0-4][0-9]|[2][5][0-5]))$/i", $email));
}


function getpicture($folder, $id)
{
	$directory = $folder . '/' . $id . '/';

	$images = glob($directory . "{*.jpg,*.gif,*.png,*.*}", GLOB_BRACE);
	$listImages = array();
	foreach ($images as $image) {
		$listImages = $image;
	}

	if (!$listImages) {
		$listImages = $folder . '/' . 'user.jpg';
	}
	$str = str_replace('..', '', $listImages);

	//return 'http://localhost/new_britam/'.$str;

	return 'https://britam.clearbasics.ug' . $str;
}


function getAllpictures($folder, $id)
{ //RETURN ARRAY
	$directory = $folder . '/' . $id . '/';

	$images = glob($directory . "{*.jpg,*.gif,*.png,*.PNG,*.JPEG}", GLOB_BRACE);
	$listImages = array();
	$ImagesList = array();
	foreach ($images as $image) {
		$listImages[] = $image;
	}

	//echo count($images)."</br>";
	if (count($listImages) == 0) {
		$listImg = $folder . '/' . 'default.jpg';
		$str = str_replace('..', '', $listImg);

		$ImagesList[] = 'servicesug.britam.com' . $str;
	} else {
		foreach ($listImages as $image) {
			$str = str_replace('..', '', $image);
			$ImagesList[] = 'servicesug.britam.com' . $str;
		}
	}

	return $ImagesList;
	/*foreach($ImagesList as $image){
         echo $image."</br>";
    }*/
} //end of getAllpicture



?>