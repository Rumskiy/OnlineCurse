<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Сертифікат</title>
    <style>
        /* Важливо! dompdf потребує, щоб шрифти були доступні. DejaVu Sans зазвичай підтримує кирилицю. */
        /* Ви можете вказати шлях до .ttf файлу, якщо потрібно: */
        /* @font-face {
            font-family: 'DejaVu Sans';
            src: url('{{ storage_path('fonts/DejaVuSans.ttf') }}') format('truetype');
            font-weight: normal;
            font-style: normal;
        } */
        body {
            font-family: 'DejaVu Sans', sans-serif; /* Використовуйте шрифт, що підтримує кирилицю */
            margin: 0;
            padding: 0;
            color: #333;
        }
        .certificate-container {
            width: 700px; /* Ширина A4 приблизно 210mm, тут для прикладу */
            height: 495px; /* Висота A4 приблизно 297mm */
            margin: 30px auto;
            padding: 30px;
            border: 10px solid #c0a062; /* Золотистий колір рамки */
            text-align: center;
            position: relative; /* Для позиціонування дати */
            background-color: #fdfdfa; /* Легкий фон */
        }
        h1 {
            font-size: 38px;
            color: #2c3e50;
            margin-bottom: 20px;
            text-transform: uppercase;
        }
        h2 {
            font-size: 20px;
            color: #34495e;
            margin-bottom: 30px;
            font-weight: normal;
        }
        .recipient-name {
            font-size: 28px;
            font-weight: bold;
            color: #bf8f3f; /* Темно-золотистий */
            margin: 20px 0;
            border-bottom: 2px solid #e0c082;
            display: inline-block;
            padding-bottom: 5px;
        }
        .course-details {
            font-size: 18px;
            margin-bottom: 15px;
        }
        .course-name {
            font-style: italic;
            font-weight: bold;
        }
        .score {
            font-size: 20px;
            font-weight: bold;
            color: #27ae60; /* Зелений для успіху */
            margin-top: 20px;
            margin-bottom: 30px;
        }
        .signature-line {
            width: 250px;
            border-bottom: 1px solid #7f8c8d;
            margin: 50px auto 5px auto;
        }
        .signature-title {
            font-size: 14px;
            color: #7f8c8d;
        }
        .issue-date {
            position: absolute;
            bottom: 25px; /* Відступ від нижнього краю рамки */
            right: 25px;  /* Відступ від правого краю рамки */
            font-size: 14px;
            color: #555;
        }
        .organization-name { /* Можете додати назву вашої організації */
            font-size: 16px;
            margin-top: 10px;
            color: #7f8c8d;
        }
    </style>
</head>
<body>
<div class="certificate-container">
    <h1>Сертифікат</h1>
    <h2>ПРО УСПІШНЕ ЗАВЕРШЕННЯ КУРСУ</h2>

    <p class="course-details">Цим засвідчується, що</p>
    <div class="recipient-name">{{ $userName }}</div>

    <p class="course-details">
        успішно завершив(ла) навчання за програмою курсу
    </p>
    <p class="course-details course-name">«{{ $courseName }}»</p>

    <div class="signature-line"></div>

    <div class="issue-date">
        Дата видачі: {{ $issueDate }}
    </div>
</div>
</body>
</html>
