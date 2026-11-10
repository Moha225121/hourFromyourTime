<?php

namespace Database\Seeders;

use App\Models\CommunityMember;
use App\Models\CommunityStory;
use App\Models\HelpRequest;
use App\Models\ServiceOffer;
use App\Models\SkillCategory;
use App\Models\TimeActivity;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $members = [
            ['name' => 'يوسف', 'initial' => 'ي', 'email' => 'youssef@example.com', 'city' => 'طرابلس', 'skill' => 'أساسيات الحاسوب', 'category' => 'تقنية', 'description' => 'أساعدك على استخدام الحاسوب والإنترنت بثقة، خطوة بخطوة.', 'place' => 'طرابلس · عن بُعد أو حضوريًا', 'rating' => 4.9, 'hours_contributed' => 18, 'available_hours' => 1, 'bio' => 'أحب تبسيط التقنية ومساعدة الناس على استخدامها في حياتهم اليومية.', 'times' => 'الأحد والثلاثاء · 4–7 مساءً'],
            ['name' => 'مريم', 'initial' => 'م', 'email' => 'maryam@example.com', 'city' => 'طرابلس', 'skill' => 'اللغة الإنجليزية', 'category' => 'تعليم', 'description' => 'نتدرّب معًا على المحادثة اليومية بطريقة ممتعة وبسيطة.', 'place' => 'طرابلس · عن بُعد', 'rating' => 5.0, 'hours_contributed' => 24, 'available_hours' => 1, 'bio' => 'خريجة لغة إنجليزية وأؤمن أن الممارسة أفضل طريقة للتعلم.', 'times' => 'الاثنين والخميس · 5–8 مساءً'],
            ['name' => 'سالم', 'initial' => 'س', 'email' => 'salem@example.com', 'city' => 'بنغازي', 'skill' => 'صيانة كهربائية بسيطة', 'category' => 'حرف', 'description' => 'مساعدة في الفحص والإصلاحات المنزلية البسيطة والآمنة.', 'place' => 'بنغازي · حضوريًا', 'rating' => 4.8, 'hours_contributed' => 31, 'available_hours' => 1, 'bio' => 'فني صيانة أستمتع بمشاركة خبرتي.', 'times' => 'السبت · 10 صباحًا–2 ظهرًا'],
            ['name' => 'روان', 'initial' => 'ر', 'email' => 'rawan@example.com', 'city' => 'مصراتة', 'skill' => 'تصميم سيرة ذاتية', 'category' => 'إبداع', 'description' => 'نرتّب خبراتك ونصمم سيرة ذاتية تعبّر عنك بوضوح.', 'place' => 'مصراتة · عن بُعد', 'rating' => 5.0, 'hours_contributed' => 12, 'available_hours' => 1, 'bio' => 'مصممة جرافيك أساعد الخريجين على تقديم مهاراتهم.', 'times' => 'الأربعاء والجمعة · 3–6 مساءً'],
            ['name' => 'علي', 'initial' => 'ع', 'email' => 'ali@example.com', 'city' => 'طرابلس', 'skill' => 'مساعدة كبار السن', 'category' => 'خدمات مجتمعية', 'description' => 'مرافقة في مشوار قريب أو مساعدة في إنجاز بعض الأمور.', 'place' => 'طرابلس · حضوريًا', 'rating' => 4.9, 'hours_contributed' => 27, 'available_hours' => 1, 'bio' => 'متطوع مجتمعي أقدّر صحبة كبار السن.', 'times' => 'الأحد والثلاثاء · صباحًا'],
        ];
        foreach ($members as $member) {
            CommunityMember::firstOrCreate(['email' => $member['email']], $member);
        }

        foreach ([
            ['title' => 'مراجعة رياضيات للمرحلة الثانوية', 'category' => 'مساعدة دراسية', 'description' => 'أحتاج ساعة لمراجعة الجبر قبل الاختبار.', 'person' => 'سارة', 'initial' => 'س', 'place' => 'طرابلس · عن بُعد'],
            ['title' => 'مساعدة في إعداد الهاتف', 'category' => 'تقنية', 'description' => 'أحتاج مساعدة في نقل الصور وإعداد التطبيقات الأساسية.', 'person' => 'أحمد', 'initial' => 'أ', 'place' => 'مصراتة · عن بُعد'],
            ['title' => 'مرافقة إلى المكتبة', 'category' => 'رعاية كبار السن', 'description' => 'أبحث عن شخص يرافق والدتي إلى المكتبة القريبة.', 'person' => 'ليلى', 'initial' => 'ل', 'place' => 'طرابلس · حضوريًا'],
        ] as $request) {
            HelpRequest::firstOrCreate(['title' => $request['title']], $request);
        }

        foreach ([['title' => 'تعليم أساسيات الحاسوب', 'hours' => 2], ['title' => 'مساعدة مجتمعية', 'hours' => 1], ['title' => 'تصميم سيرة ذاتية', 'hours' => -1], ['title' => 'مراجعة رياضيات', 'hours' => 2]] as $activity) {
            TimeActivity::firstOrCreate(['title' => $activity['title']], $activity + ['activity_date' => now()->subDays(rand(1, 12))->toDateString()]);
        }

        foreach ([
            ['name' => 'تعليم', 'icon' => '▤', 'examples' => ['شرح أساسيات Excel', 'تدريب على المحادثة الإنجليزية', 'تعليم القراءة للأطفال']],
            ['name' => 'تقنية', 'icon' => '⌘', 'examples' => ['مساعدة في إعداد الهاتف', 'شرح أساسيات الحاسوب', 'مراجعة إعدادات الأمان']],
            ['name' => 'تصميم', 'icon' => '✎', 'examples' => ['مراجعة سيرة ذاتية', 'تصميم بطاقة لمشروع صغير', 'اختيار ألوان الهوية']],
            ['name' => 'صيانة', 'icon' => '⚒', 'examples' => ['صيانة جهاز منزلي بسيط', 'تركيب رف أو قطعة أثاث', 'فحص أعطال كهربائية بسيطة']],
            ['name' => 'أعمال وحرف', 'icon' => '✿', 'examples' => ['تعليم أساسيات الخياطة', 'صنع قطعة فخارية', 'إصلاح دراجة هوائية']],
            ['name' => 'رعاية كبار السن', 'icon' => '♡', 'examples' => ['مرافقة في مشوار قريب', 'تعليم استخدام الهاتف', 'مساعدة في ترتيب المنزل']],
            ['name' => 'مساعدة دراسية', 'icon' => '✦', 'examples' => ['دعم في الرياضيات', 'مراجعة درس العلوم', 'تدريب على القراءة']],
            ['name' => 'تصوير', 'icon' => '◉', 'examples' => ['تصوير منتجات مشروعك', 'تصوير فعالية مجتمعية', 'أساسيات التصوير بالهاتف']],
            ['name' => 'لغات', 'icon' => '文', 'examples' => ['ممارسة المحادثة الإنجليزية', 'مراجعة ترجمة قصيرة', 'تعلم كلمات إيطالية']],
            ['name' => 'استشارات مهنية', 'icon' => '↗', 'examples' => ['تجهيز لمقابلة عمل', 'مراجعة ملف لينكدإن', 'تخطيط للبحث عن وظيفة']],
        ] as $index => $category) {
            SkillCategory::updateOrCreate(['name' => $category['name']], $category + ['sort_order' => $index]);
        }

        foreach (CommunityMember::whereNotNull('skill')->get() as $member) {
            ServiceOffer::updateOrCreate(
                ['member_id' => $member->id, 'title' => $member->skill],
                ['category_id' => SkillCategory::where('name', $member->category)->value('id'), 'description' => $member->description ?: 'أشارك خبرتي مع المجتمع.', 'duration_minutes' => 60, 'delivery_mode' => str_contains((string) $member->place, 'عن بُعد') ? 'both' : 'in_person', 'city' => $member->city, 'availability' => $member->times, 'status' => 'published']
            );
        }

        foreach ([
            ['quote' => 'علّمتُ طالبًا الرياضيات لمدة ساعة، وبعد أسبوع استخدمت ساعتي للحصول على مساعدة في تصميم سيرتي الذاتية.', 'name' => 'خالد', 'detail' => 'متطوع منذ 6 أشهر', 'initial' => 'خ'],
            ['quote' => 'بدأت بساعة محادثة إنجليزية. واليوم صارت عندي صديقات أتعلم منهن وأشاركهن ما أعرفه.', 'name' => 'سلمى', 'detail' => 'عضوة في المجتمع', 'initial' => 'س'],
            ['quote' => 'ساعة بسيطة في تركيب رفوف الجمعية وفّرت علينا الكثير. بعدها تعلّمت أساسيات التصوير من نور.', 'name' => 'محمود', 'detail' => 'منسق مبادرة محلية', 'initial' => 'م'],
        ] as $story) {
            CommunityStory::updateOrCreate(['name' => $story['name']], $story);
        }
    }
}
