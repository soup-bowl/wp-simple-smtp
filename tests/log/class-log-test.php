<?php
use PHPUnit\Framework\TestCase;
use wpsimplesmtp\Log;

class LogTest extends TestCase {

    public function testGettersAndSetters() {
        $log = new Log();

        // Set values
        $id = 1;
        $subject = 'Test Subject';
        $body = 'Test Body';
        $recipients = ['recipient1@example.com', 'recipient2@example.com'];
        $headers = ['x-test: WP SMTP', 'Content-Type: text/plain'];
        $headers_unified = 'From: sender@example.com' . PHP_EOL . 'CC: cc@example.com' . PHP_EOL . 'BCC: bcc@example.com';
        $attachments = [];
        $error = 'Test Error';
        $timestamp = '2023-07-28 12:34:56';

        $log->set_id($id)
            ->set_subject($subject)
            ->set_body($body)
            ->set_recipients($recipients)
            ->set_headers($headers)
            ->set_headers_unified($headers_unified)
            ->set_attachments($attachments)
            ->set_error($error)
            ->set_timestamp($timestamp);

        // Check values using getters
        $this->assertEquals($id, $log->get_id());
        $this->assertEquals($subject, $log->get_subject());
        $this->assertEquals($body, $log->get_body());
        $this->assertEquals($recipients, $log->get_recipients());
        $this->assertEquals($headers, $log->get_headers());
        $this->assertEquals($headers_unified, $log->get_headers_unified());
        $this->assertEquals($attachments, $log->get_attachments());
        $this->assertEquals($error, $log->get_error());
        $this->assertEquals($timestamp, $log->get_timestamp());
    }

    public function testFindInHeaders() {
        $log = new Log();

        $headers = [
            'From: sender@example.com',
            'CC: cc@example.com',
			'CC: cc2@example.com',
            'Subject: Test Subject'
        ];
        $log->set_headers($headers);

        // Test finding 'From' header
        $from = $log->get_from();
        $this->assertEquals([' sender@example.com'], $from);

        // Test finding 'CC' header
        $cc = $log->get_cc();
        $this->assertEquals([' cc@example.com', ' cc2@example.com'], $cc);

        // Test finding non-existent header
        $bcc = $log->get_bcc();
        $this->assertEquals([], $bcc);
    }
}
