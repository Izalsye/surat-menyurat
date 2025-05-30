<?php

return [
    'account_created' => [
        'subject' => '계정이 생성되었습니다',
        'welcome_heading' => ':app에 오신 것을 환영합니다',
        'hello' => '안녕하세요 :name님',
        'account_created' => '**:app**에 대한 계정이 생성되었습니다.',
        'credentials' => '다음은 귀하의 로그인 정보입니다:',
        'email' => '이메일',
        'password' => '비밀번호',
        'login_button' => ':app 로그인',
        'security_notice' => '보안을 위해 즉시 로그인하고 비밀번호를 변경해 주세요.',
        'contact_admin' => '문의 사항이 있으시면 관리자에게 문의하세요.',
        'thanks' => '감사합니다,',
    ],
    'disposition_assigned' => [
        'subject' => '새 업무가 할당되었습니다',
        'heading' => '새 업무 할당',
        'greeting' => '안녕하세요 :name님,',
        'message' => ':from님이 주제 **:subject**의 업무를 할당했습니다.',
        'button' => '업무 보기',
        'footer' => '지정된 업무를 검토하고 후속 조치를 취해 주세요.',
        'thanks' => '감사합니다,',
        'urgency_level' => '긴급 수준: **:urgency**',
        'urgency' => [
            'normal' => '보통',
            'important' => '중요',
            'urgent' => '긴급',
            'immediate' => '즉시',
            'confidential' => '기밀',
            'top_secret' => '극비',
        ],
    ],
    'disposition_done' => [
        'subject' => '결재 완료됨',
        'heading' => '결재 완료',
        'greeting' => ':name 님, 안녕하세요.',
        'message' => ':from 님이 지정한 제목 "**:subject**"의 결재가 :by 님에 의해 **완료됨**으로 표시되었습니다.',
        'system' => '시스템',
        'button' => '결재 보기',
        'footer' => '관심 가져주셔서 감사합니다.',
        'thanks' => '감사합니다.',
    ],
    'report_exported' => [
        'subject' => '보고서를 다운로드할 수 있습니다',
        'greeting' => '안녕하세요 :name님,',
        'message' => '보고서가 성공적으로 내보내졌습니다. 이 이메일에 첨부된 보고서를 확인해 주세요.',
        'thanks' => '감사합니다,'
    ],
];
