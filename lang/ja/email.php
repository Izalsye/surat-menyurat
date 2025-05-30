<?php

return [
    'account_created' => [
        'subject' => 'アカウントが作成されました',
        'welcome_heading' => ':appへようこそ',
        'hello' => 'こんにちは、:nameさん',
        'account_created' => '**:app**にあなたのアカウントが作成されました。',
        'credentials' => '以下はあなたのログイン情報です:',
        'email' => 'メールアドレス',
        'password' => 'パスワード',
        'login_button' => ':appにログイン',
        'security_notice' => 'セキュリティのため、すぐにログインしてパスワードを変更してください。',
        'contact_admin' => 'ご不明な点があれば、管理者にお問い合わせください。',
        'thanks' => 'ありがとうございます。',
    ],
    'disposition_assigned' => [
        'subject' => '新しい処理が割り当てられました',
        'heading' => '新しい処理の割り当て',
        'greeting' => 'こんにちは、:nameさん',
        'message' => ':fromさんより、件名「**:subject**」の処理が割り当てられました。',
        'button' => '処理を確認する',
        'footer' => '処理内容を確認し、対応をお願いします。',
        'thanks' => 'よろしくお願いいたします。',
        'urgency_level' => '緊急度: **:urgency**',
        'urgency' => [
            'normal' => '通常',
            'important' => '重要',
            'urgent' => '緊急',
            'immediate' => '至急',
            'confidential' => '機密',
            'top_secret' => '極秘',
        ],
    ],
    'disposition_done' => [
        'subject' => '処理が完了しました',
        'heading' => '処理完了',
        'greeting' => 'こんにちは :name さん、',
        'message' => ':from から割り当てられた件名「**:subject**」の処理が、:by によって**完了済み**としてマークされました。',
        'system' => 'システム',
        'button' => '処理を見る',
        'footer' => 'ご対応ありがとうございます。',
        'thanks' => 'よろしくお願いいたします。',
    ],
    'report_exported' => [
        'subject' => 'レポートのダウンロード準備ができました',
        'greeting' => 'こんにちは :name さん、',
        'message' => 'レポートのエクスポートが正常に完了しました。このメールに添付されたレポートをご確認ください。',
        'thanks' => 'ありがとうございます、'
    ],
];
