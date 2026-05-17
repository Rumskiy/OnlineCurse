<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Course;
use App\Models\Section;
use App\Models\Test;
use App\Models\Question;
use App\Models\Option;
use App\Models\MatchPair;
use App\Models\User;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Get or Create Author (Admin User)
        $author = User::where('email', 'admin@admin.com')->first();
        if (!$author) {
            $author = User::create([
                'firstName' => 'Admin',
                'lastName' => 'User',
                'email' => 'admin@admin.com',
                'password' => \Illuminate\Support\Facades\Hash::make('121212'),
                'role' => '2',
                'status' => 'active',
            ]);
        }

        // 2. Create Categories
        $categoryDev = Category::firstOrCreate(['name' => 'Програмування']);
        $categoryDesign = Category::firstOrCreate(['name' => 'Дизайн']);

        // ==========================================
        // COURSE 1: "Основи програмування на PHP"
        // ==========================================
        $course1 = Course::create([
            'title' => 'Основи програмування на PHP',
            'description' => 'Цей курс розроблений для початківців, які хочуть освоїти мову програмування PHP з нуля. Ви вивчите базовий синтаксис, керуючі конструкції, роботу зі змінними, масивами, формами та базами даних, а також навчитеся створювати динамічні веб-сайти.',
            'category_id' => $categoryDev->id,
            'author_id' => $author->id,
        ]);

        // Section 1.1: Вступ до PHP
        $section1_1 = Section::create([
            'course_id' => $course1->id,
            'title' => 'Вступ до PHP та базовий синтаксис',
            'description' => 'Дізнайтеся історію розвитку мови, встановіть локальне середовище розробки та напишіть свій перший PHP-скрипт.',
            'contentSection' => 'PHP (Hypertext Preprocessor) — це скриптова мова програмування широкого використання, що виконується на стороні сервера. Вона була створена Расмусом Лердорфом у 1994 році. PHP-код вбудовується безпосередньо в HTML. Код на PHP починається з відкриваючого тегу <?php і закінчується закриваючим ?>. Кожна інструкція в PHP повинна завершуватися крапкою з комою (;).',
        ]);

        // Test for Section 1.1
        $test1_1 = Test::create([
            'section_id' => $section1_1->id,
            'title' => 'Тест: Синтаксис та змінні PHP',
            'total_time_limit' => 600, // 10 minutes
            'time_per_question' => 60, // 1 minute per question
        ]);

        // Question 1: Single Choice
        $q1_1 = Question::create([
            'test_id' => $test1_1->id,
            'type' => 'single_choice',
            'text' => 'Який символ використовується для позначення змінних в PHP?',
            'order' => 1,
            'points' => 1,
        ]);
        Option::create(['question_id' => $q1_1->id, 'text' => '$', 'is_correct' => true, 'order' => 1]);
        Option::create(['question_id' => $q1_1->id, 'text' => '#', 'is_correct' => false, 'order' => 2]);
        Option::create(['question_id' => $q1_1->id, 'text' => '@', 'is_correct' => false, 'order' => 3]);
        Option::create(['question_id' => $q1_1->id, 'text' => '&', 'is_correct' => false, 'order' => 4]);

        // Question 2: Multiple Choice
        $q1_2 = Question::create([
            'test_id' => $test1_1->id,
            'type' => 'multiple_choice',
            'text' => 'Оберіть усі суперглобальні масиви в PHP (декілька правильних відповідей):',
            'order' => 2,
            'points' => 2,
        ]);
        Option::create(['question_id' => $q1_2->id, 'text' => '$_GET', 'is_correct' => true, 'order' => 1]);
        Option::create(['question_id' => $q1_2->id, 'text' => '$_POST', 'is_correct' => true, 'order' => 2]);
        Option::create(['question_id' => $q1_2->id, 'text' => '$_SERVER', 'is_correct' => true, 'order' => 3]);
        Option::create(['question_id' => $q1_2->id, 'text' => '$_SUPER', 'is_correct' => false, 'order' => 4]);

        // Section 1.2: Масиви та Цикли
        $section1_2 = Section::create([
            'course_id' => $course1->id,
            'title' => 'Робота з масивами та циклічні конструкції',
            'description' => 'Вивчіть індексовані та асоціативні масиви, а також цикли for, while, do-while та foreach.',
            'contentSection' => 'Масив в PHP — це впорядкована карта (map), яка асоціює значення з ключами. В PHP є три типи масивів: індексовані (з числовими індексами), асоціативні (з іменованими ключами-рядками) та багатовимірні масиви. Для обходу масивів найчастіше використовується спеціальний цикл foreach, який послідовно перебирає кожен елемент.',
        ]);

        // ==========================================
        // COURSE 2: "Основи UX/UI Дизайну"
        // ==========================================
        $course2 = Course::create([
            'title' => 'Основи UX/UI Дизайну',
            'description' => 'Практичний курс про проектування інтерфейсів користувача. Ви навчитеся досліджувати аудиторію, створювати варфрейми, працювати з кольором, сітками та типографікою, а також створювати інтерактивні прототипи сайтів та мобільних додатків.',
            'category_id' => $categoryDesign->id,
            'author_id' => $author->id,
        ]);

        // Section 2.1: Що таке UX та UI?
        $section2_1 = Section::create([
            'course_id' => $course2->id,
            'title' => 'Вступ до UX/UI: різниця між досвідом та інтерфейсом',
            'description' => 'Зрозумійте основні поняття, цілі та задачі дизайнерів інтерфейсів.',
            'contentSection' => 'UX (User Experience) — це досвід користувача, тобто те, як він взаємодіє з інтерфейсом, наскільки легко йому досягти своєї мети (наприклад, купити товар чи зареєструватися). UI (User Interface) — це користувацький інтерфейс, тобто те, як цей продукт виглядає візуально (кольори, кнопки, шрифти, іконки, відступи). UX — це скелет і логіка роботи, UI — це краса та емоції.',
        ]);

        // Test for Section 2.1
        $test2_1 = Test::create([
            'section_id' => $section2_1->id,
            'title' => 'Тест: UX проти UI',
            'total_time_limit' => 300,
            'time_per_question' => 60,
        ]);

        // Question 1: Single Choice
        $q2_1 = Question::create([
            'test_id' => $test2_1->id,
            'type' => 'single_choice',
            'text' => 'Який етап дизайну відповідає за логіку переходів та структуру продукту?',
            'order' => 1,
            'points' => 1,
        ]);
        Option::create(['question_id' => $q2_1->id, 'text' => 'UX-дизайн', 'is_correct' => true, 'order' => 1]);
        Option::create(['question_id' => $q2_1->id, 'text' => 'UI-дизайн', 'is_correct' => false, 'order' => 2]);
        Option::create(['question_id' => $q2_1->id, 'text' => '3D-моделювання', 'is_correct' => false, 'order' => 3]);

        // Question 2: Match Type
        $q2_2 = Question::create([
            'test_id' => $test2_1->id,
            'type' => 'match',
            'text' => 'Знайдіть правильну відповідність між терміном та його визначенням:',
            'order' => 2,
            'points' => 2,
        ]);
        MatchPair::create([
            'question_id' => $q2_2->id,
            'left_text' => 'UX (User Experience)',
            'right_text' => 'Досвід користувача від взаємодії, зручність навігації та логіка.',
            'order' => 1,
        ]);
        MatchPair::create([
            'question_id' => $q2_2->id,
            'left_text' => 'UI (User Interface)',
            'right_text' => 'Візуальне оформлення інтерфейсу: кольори, шрифти, форми кнопок.',
            'order' => 2,
        ]);
        MatchPair::create([
            'question_id' => $q2_2->id,
            'left_text' => 'Wireframe (Варфрейм)',
            'right_text' => 'Низькодеталізований чорно-білий ескіз макету сторінки.',
            'order' => 3,
        ]);

        $this->command->info('Успішно створено 2 тестові курси з секціями, тестами та питаннями!');
    }
}
