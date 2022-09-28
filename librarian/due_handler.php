<?php
	require "../db_connect.php";
	require "verify_librarian.php";
	require "header_librarian.php";
?>

<html>
	<head>
		<title>LMS</title>
		<link rel="stylesheet" type="text/css" href="../css/global_styles.css" />
	</head>
	<body>

	<?php
		$query = "SELECT I.issue_id, M.email, B.isbn, B.title FROM book_issue_log I INNER JOIN member M on I.member = M.username INNER JOIN book B ON I.book_isbn = B.isbn WHERE DATEDIFF(CURRENT_DATE, I.due_date) >= 0  AND (I.last_reminded IS NULL OR DATEDIFF(I.last_reminded, CURRENT_DATE) <> 0)";
		$result = mysqli_query($con, $query);
		$rows = mysqli_num_rows($result);
		
		if($rows > 0)
		{
			$successfulEmails = 0;
			$idArray;
			//$header = 'From: <noreply@library.com>' . "\r\n";
			$subject = "Return your book today";
			$query = "";
		
			for($i=0; $i<$rows; $i++)
			{
				$row = mysqli_fetch_array($result);
				$email = $row[1];
				$body = "This is a reminder to return the book '".$row[3]."' with ISBN ".$row[2]." to the library.";
				if((include '../sendmail.php') == TRUE)
				{
					$idArray[$i] = $row[0];
					$successfulEmails++;
				}
			}
			
			mysqli_next_result($con);
			
			for($i=0; $i<$rows; $i++)
			{
				$query = $con->prepare("UPDATE book_issue_log SET last_reminded = CURRENT_DATE WHERE issue_id = ?;");
				$query->bind_param("d", $idArray[$i]);
				$query->execute();
				$query->get_result();
			}
			
			if($successfulEmails > 0)
				echo "<h2 align='center'>Successfully notified ".$successfulEmails." members</h2>";
			else
				echo "ERROR: Couldn't notify any member.";
		}
		else
			echo "<h2 align='center'>No Pending Reminders</h2>";
	?>
	</body>
</html>