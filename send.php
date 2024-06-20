<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use TCPDF;

require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';
require 'tcpdf/tcpdf.php';

$conn = mysqli_connect("localhost", "root", "", "autoemail");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if (isset($_POST["send"])) {
    // Get the total number of rows in the student table
    $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM student");
    $row = mysqli_fetch_assoc($result);
    $totalRows = $row['total'];

    $counter = 1;

    while ($counter <= $totalRows) {
        $query = mysqli_query($conn, "SELECT * FROM student WHERE Counter = $counter");
        $num = mysqli_num_rows($query);

        if ($num > 0) {
            while ($data = mysqli_fetch_array($query)) {
                $first = $data['StudFirstName'];
                $last = $data['StudLastName'];
                $email = $data['StudEmail'];
                $subject = "Transcript Report";
                $marks = $data['StudMarks'];
                $body = "Hello $first $last, your final marks are $marks.";

                // Create PDF
                $pdf = new TCPDF();
                $pdf->AddPage();
                $pdf->SetFont('helvetica', '', 12);
                $pdf->Write(0, $body);
                $pdfFilePath = __DIR__ . "/transcript_$counter.pdf";
                $pdf->Output($pdfFilePath, 'F');

                $mail = new PHPMailer(true);

                try {
                    // Server settings
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = '';
                    $mail->Password = '';
                    $mail->SMTPSecure = 'ssl';
                    $mail->Port = 465;

                    // Recipients
                    $mail->setFrom('');
                    $mail->addAddress($email);

                    // Attach PDF
                    $mail->addAttachment($pdfFilePath);

                    // Content
                    $mail->isHTML(true);
                    $mail->Subject = $subject;
                    $mail->Body = 'Please find your transcript attached as a PDF.';

                    $mail->send();
                    echo "Email sent to $email with PDF attachment<br>";
                } catch (Exception $e) {
                    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}<br>";
                }

                // Delete the PDF file after sending the email
                unlink($pdfFilePath);
            }
        } else {
            echo "No records found for counter = $counter<br>";
        }

        $counter++;
    }

    echo "All emails sent.";
}
?>
