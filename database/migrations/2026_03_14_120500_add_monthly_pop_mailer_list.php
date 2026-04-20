<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddMonthlyPopMailerList extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('mailer_list')->updateOrInsert(
            ['mailer_name' => 'Monthly POP Reminder'],
            [
                'mailer_name' => 'Monthly POP Reminder',
                'message' => '<p>Dear [name],<br><br>Your monthly proof of payment is required for application #[application_number].<br><strong>Amount due:</strong> R[monthly_pop_amount]<br><strong>Due date:</strong> [monthly_pop_due_date]<br><br>Please <strong>upload your document</strong> via the portal: [application_link]Login[close_application_link]<br><br>Thank you.</p>
                                <h3>If you have any questions, please reply to this email or contact us at<br><a href="mailto:info@campusafrica.co.za" rel="noreferrer">info@campusafrica.co.za</a></h3>',
                'status' => 1,
                'color' => 2,
                'subject' => 'Monthly Proof of Payment Required - #[application_number]',
            ]
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('mailer_list')
            ->where('mailer_name', 'Monthly POP Reminder')
            ->delete();
    }
}
