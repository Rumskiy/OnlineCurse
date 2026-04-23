<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Сертифікат про завершення</title>
    <style>
        @page {
            margin: 0;
            size: a4 landscape;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8fafc;
            color: #1e293b;
        }
        .wrapper {
            width: 100%;
            height: 100%;
            padding: 20px;
            box-sizing: border-box;
        }
        .certificate-card {
            width: 100%;
            height: 550px;
            background-color: #ffffff;
            border: 15px solid #6366f1; /* Indigo border */
            position: relative;
            box-sizing: border-box;
            padding: 40px;
            text-align: center;
        }
        .inner-border {
            position: absolute;
            top: 5px;
            left: 5px;
            right: 5px;
            bottom: 5px;
            border: 1px solid #94a3b8;
        }
        .header {
            margin-bottom: 30px;
        }
        .title {
            font-size: 48px;
            font-weight: bold;
            color: #4338ca;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 4px;
        }
        .subtitle {
            font-size: 16px;
            color: #64748b;
            margin-top: 5px;
            font-weight: normal;
        }
        .content {
            margin-top: 40px;
        }
        .presented-to {
            font-size: 18px;
            color: #64748b;
            margin-bottom: 15px;
        }
        .name {
            font-size: 38px;
            font-weight: bold;
            color: #0f172a;
            margin: 15px 0;
            border-bottom: 2px solid #6366f1;
            display: inline-block;
            padding-bottom: 5px;
        }
        .course-text {
            font-size: 20px;
            margin: 20px 0;
            line-height: 1.4;
        }
        .course-name {
            font-weight: bold;
            color: #4338ca;
            font-size: 24px;
        }
        .footer {
            margin-top: 60px;
            width: 100%;
            position: absolute;
            bottom: 40px;
            left: 0;
            padding: 0 40px;
            box-sizing: border-box;
        }
        .signature-box {
            width: 250px;
            float: left;
            text-align: center;
        }
        .date-box {
            width: 250px;
            float: right;
            text-align: center;
        }
        .line {
            border-bottom: 1px solid #94a3b8;
            margin-bottom: 5px;
            min-height: 20px;
        }
        .label {
            font-size: 12px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .seal {
            position: absolute;
            bottom: 100px;
            left: 50%;
            margin-left: -50px;
            width: 100px;
            height: 100px;
            background-color: #6366f1;
            border-radius: 50%;
            color: white;
            text-align: center;
            line-height: 100px;
            font-weight: bold;
            font-size: 10px;
            text-transform: uppercase;
            transform: rotate(-15deg);
            border: 4px double white;
            opacity: 0.8;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="certificate-card">
            <div class="inner-border"></div>
            
            <div class="header">
                <h1 class="title">Сертифікат</h1>
                <p class="subtitle">ПРО УСПІШНЕ ЗАВЕРШЕННЯ НАВЧАННЯ</p>
            </div>

            <div class="content">
                <p class="presented-to">Цей сертифікат виданий</p>
                <div class="name">{{ $userName }}</div>
                
                <p class="course-text">
                    за успішне завершення онлайн-курсу<br>
                    <span class="course-name">«{{ $courseName }}»</span>
                </p>
                
                <p class="subtitle">Середній результат: {{ $averageScore }}%</p>
            </div>

            <div class="footer">
                <div class="signature-box">
                    <div class="line"></div>
                    <div class="label">Підпис інструктора</div>
                </div>
                
                <div class="date-box">
                    <div class="line" style="font-weight: bold;">{{ $issueDate }}</div>
                    <div class="label">Дата видачі</div>
                </div>
            </div>

            <div class="seal">VERIFIED</div>
        </div>
    </div>
</body>
</html>
