<?php

return [
    'account_created' => [
        'subject' => 'Account Created',
        'welcome_heading' => 'Welcome to :app',
        'hello' => 'Hello :name',
        'account_created' => 'An account has been created for you in **:app**.',
        'credentials' => 'Here are your login credentials:',
        'email' => 'Email',
        'password' => 'Password',
        'login_button' => 'Login to :app',
        'security_notice' => 'For security reasons, please **log in and change your password immediately**.',
        'contact_admin' => 'If you have any questions, feel free to contact your administrator.',
        'thanks' => 'Thanks,',
    ],
    'disposition_assigned' => [
        'subject' => 'You have been assigned a new disposition',
        'heading' => 'New Disposition Assignment',
        'greeting' => 'Hello :name,',
        'message' => 'You have been assigned by :from to handle a disposition with the subject: **:subject**.',
        'button' => 'View Disposition',
        'footer' => 'Please review and follow up the disposition as assigned.',
        'thanks' => 'Thank you,',
        'urgency_level' => 'Urgency Level: **:urgency**',
        'urgency' => [
            'normal' => 'Normal',
            'important' => 'Important',
            'urgent' => 'Urgent',
            'immediate' => 'Immediate',
            'confidential' => 'Confidential',
            'top_secret' => 'Top Secret',
        ],
    ],
    'disposition_done' => [
        'subject' => 'Disposition Completed',
        'heading' => 'Disposition Completed',
        'greeting' => 'Hello :name,',
        'message' => 'The disposition with the subject: **:subject** assigned by :from has been marked as **completed** by :by.',
        'system' => 'System',
        'button' => 'View Disposition',
        'footer' => 'Thank you for your attention.',
        'thanks' => 'Regards,',
    ],
    'report_exported' => [
        'subject' => 'Report ready to download',
        'greeting' => 'Hello :name,',
        'message' => 'Your report has been successfully exported. Please find the exported report attached to this email.',
        'thanks' => 'Thank you,'
    ],
];
