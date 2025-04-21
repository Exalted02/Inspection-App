<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Customer_record_owners;
use App\Models\Resource;
use App\Models\Referral;
use App\Models\EmailManagement;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class SendCustomerNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-customer-notifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send email notifications for customer anniversaries or birthdays';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Get today's date
        $today = Carbon::today()->format('Y-m-d');

        // Fetch customers with today's anniversary or birthday
        /*$customers = Customer_record_owners::where(function ($query) use ($today) {
            $query->where('date_of_anniversary', $today)
                  ->orWhere('date_of_birth', $today);
        })->get();

        if ($customers->isEmpty()) {
            $this->info('No customers found with anniversaries or birthdays today.');
            return;
        }*/
		
		//-----------------customer birthday----------------------
		
		$birthdayCustomers = Customer_record_owners::where('date_of_birth', $today)->get();

		if ($birthdayCustomers->isEmpty()) {
            $this->info('No customers found with anniversaries or birthdays today.');
            return;
        }
		
		foreach ($birthdayCustomers as $birthday) {
            // Define the email content
			$data["email"] = $birthday->primary_email_id;
			$get_birthday_email = EmailManagement::where('id',3)->first();
			
			$birthdayEmailbody = str_replace(array("[NAME]", "[SCREEN_NAME]"), array($birthday->owner_name, ''), $get_birthday_email->message);
			$data["body"] = $birthdayEmailbody;
			$data["message_subject"] = $get_birthday_email->message_subject;
			
            // Send email
			$birthdaydata = [
				'subject' => $get_birthday_email->message_subject,
				'body' => str_replace(array("[NAME]", "[SCREEN_NAME]"), array($birthday->owner_name, ''), $get_birthday_email->message),
				'toEmails' => array($birthday->primary_email_id),
			];
			//send_email($birthdaydata);
			 try {
				send_email($birthdaydata);
				$this->info("Email sent to: {$birthday->primary_email_id}");
			} catch (\Exception $e) {
				\Log::error("Failed to send email to {$birthday->primary_email_id}: {$e->getMessage()}");
			}
			
            $this->info("Email sent to: {$birthday->primary_email_id}");
        }
		
		//-----------------customer annivarsary----------------------
		$anniversaryCustomers = Customer_record_owners::where('date_of_anniversary', $today)->get();

		if ($anniversaryCustomers->isEmpty()) {
            $this->info('No customers found with anniversaries or birthdays today.');
            return;
        }
		
		foreach ($anniversaryCustomers as $anniversary) {
            // Define the email content
            
			$data["email"] = $anniversary->primary_email_id;
			
			$get_anniversary_email = get_customer_email(4);
			$anniversaryEmailbody = str_replace(array("[NAME]", "[SCREEN_NAME]"), array($anniversary->owner_name, ''), $get_anniversary_email->message);
			$data["body"] = $anniversaryEmailbody;
			$data["message_subject"] = $get_anniversary_email->message_subject;
			
            // Send email
			
			$anivaersarydata = [
				'subject' => $get_anniversary_email->message_subject,
				'body' => str_replace(array("[NAME]", "[SCREEN_NAME]"), array($anniversary->owner_name, ''), $get_anniversary_email->message),
				'toEmails' => array($anniversary->primary_email_id),
				// 'bccEmails' => array('exaltedsol06@gmail.com','exaltedsol04@gmail.com'),
				// 'ccEmails' => array('exaltedsol04@gmail.com'),
				// 'files' => [public_path('images/logo.jpg'), public_path('css/app.css'),],
			];
			send_email($anivaersarydata);
			/*Mail::raw($data["body"], function ($message) use ($data) {
				$message->to($data["email"])
						->subject($data["message_subject"]);
			});*/
			
			 // Log or display message in console
            $this->info("Email sent to: {$anniversary->primary_email_id}");
        }
		//-----------------Resource birthday----------------------
		
		$birthdayResource = Resource::where('date_of_birth', $today)->get();

		if ($birthdayResource->isEmpty()) {
            $this->info('No customers found with anniversaries or birthdays today.');
            return;
        }
		
		foreach ($birthdayResource as $birthday) {
            // Define the email content
			$get_birthday_email = EmailManagement::where('id',3)->first();
			
            // Send email
			$birthdaydata = [
				'subject' => $get_birthday_email->message_subject,
				'body' => str_replace(array("[NAME]", "[SCREEN_NAME]"), array($birthday->name, ''), $get_birthday_email->message),
				'toEmails' => array($birthday->personal_primary_email_id),
			];
			//send_email($birthdaydata);
			 try {
				send_email($birthdaydata);
				$this->info("Email sent to: {$birthday->personal_primary_email_id}");
			} catch (\Exception $e) {
				\Log::error("Failed to send email to {$birthday->personal_primary_email_id}: {$e->getMessage()}");
			}
			
            $this->info("Email sent to: {$birthday->personal_primary_email_id}");
        }
		
		//-----------------Resource annivarsary----------------------
		$anniversaryResource = Resource::where('date_of_anniversary', $today)->get();

		if ($anniversaryResource->isEmpty()) {
            $this->info('No customers found with anniversaries or birthdays today.');
            return;
        }
		
		foreach ($anniversaryResource as $anniversary) {
            // Define the email content
			$get_anniversary_email = get_customer_email(4);
			
            // Send email			
			$anivaersarydata = [
				'subject' => $get_anniversary_email->message_subject,
				'body' => str_replace(array("[NAME]", "[SCREEN_NAME]"), array($anniversary->name, ''), $get_anniversary_email->message),
				'toEmails' => array($anniversary->personal_primary_email_id),
				// 'bccEmails' => array('exaltedsol06@gmail.com','exaltedsol04@gmail.com'),
				// 'ccEmails' => array('exaltedsol04@gmail.com'),
				// 'files' => [public_path('images/logo.jpg'), public_path('css/app.css'),],
			];
			send_email($anivaersarydata);
			
			 // Log or display message in console
            $this->info("Email sent to: {$anniversary->personal_primary_email_id}");
        }

		//-----------------Referral birthday----------------------
		
		$birthdayReferral = Referral::where('dob', $today)->get();

		if ($birthdayReferral->isEmpty()) {
            $this->info('No customers found with anniversaries or birthdays today.');
            return;
        }
		
		foreach ($birthdayReferral as $birthday) {
            // Define the email content
			$get_birthday_email = EmailManagement::where('id',3)->first();
			
            // Send email
			$birthdaydata = [
				'subject' => $get_birthday_email->message_subject,
				'body' => str_replace(array("[NAME]", "[SCREEN_NAME]"), array($birthday->name, ''), $get_birthday_email->message),
				'toEmails' => array($birthday->primary_email),
			];
			//send_email($birthdaydata);
			 try {
				send_email($birthdaydata);
				$this->info("Email sent to: {$birthday->primary_email}");
			} catch (\Exception $e) {
				\Log::error("Failed to send email to {$birthday->primary_email}: {$e->getMessage()}");
			}
			
            $this->info("Email sent to: {$birthday->primary_email}");
        }
		
		//-----------------Resource annivarsary----------------------
		$anniversaryReferral = Referral::where('date_of_anniversary', $today)->get();

		if ($anniversaryReferral->isEmpty()) {
            $this->info('No customers found with anniversaries or birthdays today.');
            return;
        }
		
		foreach ($anniversaryReferral as $anniversary) {
            // Define the email content
			$get_anniversary_email = get_customer_email(4);
			
            // Send email			
			$anivaersarydata = [
				'subject' => $get_anniversary_email->message_subject,
				'body' => str_replace(array("[NAME]", "[SCREEN_NAME]"), array($anniversary->name, ''), $get_anniversary_email->message),
				'toEmails' => array($anniversary->primary_email),
				// 'bccEmails' => array('exaltedsol06@gmail.com','exaltedsol04@gmail.com'),
				// 'ccEmails' => array('exaltedsol04@gmail.com'),
				// 'files' => [public_path('images/logo.jpg'), public_path('css/app.css'),],
			];
			send_email($anivaersarydata);
			
			 // Log or display message in console
            $this->info("Email sent to: {$anniversary->primary_email}");
        }

        $this->info('Customer notifications sent successfully.');
    }
}


?>