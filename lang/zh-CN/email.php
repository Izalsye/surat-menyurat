<?php

return [
    'account_created' => [
        'subject' => '账户已创建',
        'welcome_heading' => '欢迎使用 :app',
        'hello' => '你好，:name',
        'account_created' => '您的**:app**账户已创建。',
        'credentials' => '以下是您的登录信息：',
        'email' => '邮箱',
        'password' => '密码',
        'login_button' => '登录 :app',
        'security_notice' => '出于安全考虑，请立即登录并更改密码。',
        'contact_admin' => '如果您有任何问题，请联系管理员。',
        'thanks' => '谢谢，',
    ],
    'disposition_assigned' => [
        'subject' => '您被指派了一个新的处理事项',
        'heading' => '新事项指派通知',
        'greeting' => '你好，:name，',
        'message' => '您被 :from 指派处理事项，主题为：**:subject**。',
        'button' => '查看事项',
        'footer' => '请查阅并按要求跟进该事项。',
        'thanks' => '谢谢，',
        'urgency_level' => '紧急程度：**:urgency**',
        'urgency' => [
            'normal' => '普通',
            'important' => '重要',
            'urgent' => '紧急',
            'immediate' => '立刻',
            'confidential' => '机密',
            'top_secret' => '绝密',
        ],
    ],
    'disposition_done' => [
        'subject' => '处理已完成',
        'heading' => '处理已完成',
        'greeting' => '你好，:name：',
        'message' => '由 :from 分派的主题为 "**:subject**" 的处理已由 :by 标记为**已完成**。',
        'system' => '系统',
        'button' => '查看处理',
        'footer' => '感谢您的关注。',
        'thanks' => '此致，',
    ],
    'report_exported' => [
        'subject' => '报告已准备好下载',
        'greeting' => '你好，:name，',
        'message' => '您的报告已成功导出。请查看此邮件中的附件。',
        'thanks' => '谢谢，'
    ],
];
