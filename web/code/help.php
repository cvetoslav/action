<?php
require_once('common.php');
require_once('page.php');

class HelpPage extends Page {
    public function getTitle() {
        return 'O(N)::Help';
    }
    
    public function getContent() {
        $version = '
            <div class="help-version">
                Версия на системата: <a href="https://github.com/espr1t/action/commit/6bd27e1f611c0683f8f4d5733b9b85fab1303a36" target="_blank">v.170124.6bd27e1</a>
            </div>
        ';

        $content = inBox('
            <h1>Упътване</h1>
            Тук е описано как работи системата и какво да очаквате, когато направите някое действие.
        ') . $version
           . inBox('
            <h2 id="report-a-problem"><a href="#report-a-problem">Съобщаване за проблеми</a></h2>
            В менюто отдолу има линк "report a bug", чрез който регистрираните потребители могат да се свържат с админ и да съобщят за проблем (било то със системата или с някоя от
            задачите). Не се притеснявайте да го ползвате дори за малки проблеми като правописни грешки.
        ') . inBox('
            <h2 id="submit-a-solution"><a href="#submit-a-solution">Предаване на решение</a></h2>
            За да предадете решение, трябва да сте се регистрирали и влезли в системата. След като отворите някоя задача
            ще видите син бутон "Предай Решение". Цъкайки на него ще се отвори форма, в която да копирате кода си.
            За разлика от други системи, езикът ще бъде разпознат автоматично, така че остава единствено да натиснете
            "Изпрати" и решението ви ще бъде изпратено за тестване.
        ') . inBox('
            <h2 id="status-codes"><a href="#status-codes">Статус кодове</a></h2>
            След като сте изпратили решение е възможно системата да върне няколко различни статуса.
            <ul>
                <li><b>Waiting:</b> Решението чака други предадени преди него решения да се изтестват.</li>
                <li><b>Running:</b> Решението се тества в момента.</li>
                <li><b>Compilation Error:</b> Решението не се е компилирало успешно.</li>
                <li><b>Memory Limit:</b> Решението е използвало повече памет, от допустимата за задачата.</li>
                <li><b>Time Limit:</b> Решението е вървяло повече време, от допустимото за задачата.</li>
                <li><b>Runtime Error:</b> Решението е крашнало по време на изпълнение (например в следствие на индексиране извън масив или делене на нула).</li>
                <li><b>Wrong Answer:</b> Решението е завършило изпълнението си успешно, но полученият от него отговор не е верен.</li>
                <li><b>Internal Error:</b> Нещо се е случило със системата по време на тестване. В този случай е препоръчително да известите админ чрез линка
                "report a bug" в менюто най-долу.</li>
                <li><b>Accepted:</b> Разбира се, в най-добрия случай предаденото решение е вярно и не е нарушило нито едно от ограниченията на задачата.</li>
            </ul>
            Заедно със статус кода ще получите и допълнителна информация - например на кой тест се е получена грешка или колко точки сте получили на всеки от тестовете.
            Информацията за всеки от Вашите събмити се пази на системата и може да бъде достъпена (само от вас) по всяко време през линка "Предишни решения" под условието на всяка задача.
        ') . inBox('
            <h2 id="float-comparison"><a href="#float-comparison">Сравнение на числа с плаваща запетая</a></h2>
            Системата имплементира сравнение на числа с плаваща запетая, базирано на абсолютна или релативна разлика. Това сравнение е такова, че да работи както за много малки, така и за много големи числа.
            <br><br>
            Често възникват проблеми, когато за сравнение се ползва само абсолютната разлика на числата. Например <code>1234567890.123456717</code> и <code>1234567890.123456955</code> имат разлика от 0.000000238 - малко над 10<sup>-7</sup>. При изисквана точност от 10<sup>-9</sup> това би било грешен отговор. Ако пък ползваме точност от 10<sup>-6</sup>, например, то числа като <code>0.0000001</code> и <code>0.0000009</code> биха били счетени за еднакви, като всъщност разликата им е 9 пъти!
            <br></br>

            За да се справи с това, системата прави две проверки:
            <ol>
            <li>Ако числата имат абсолютна разлика, по-малка от 10<sup>-9</sup>, отговорът се счита за верен (абсолютна разлика).</li>
            <li>Ако отношението на числата е много близо до 1 (попада в интервала [1-10<sup>-9</sup>, 1+10<sup>-9</sup>]), отговорът се счита за верен (релативна разлика).</li>
            </ol>
            Ако нито една от двете проверки не покаже, че отговорът е верен, решението се счита за грешно.
        ') . inBox('
            <h2 id="compilation"><a href="#compilation">Компилатори и командни флагове</a></h2>
            Изпратените решения се компилират ползвайки следните командни флагове:
            <ul>
                <li><b>C++ (GCC 5.4.0):</b> <pre>g++ -O2 -std=c++14 -w -o &lt;source&gt; &lt;source&gt;.cpp</pre></li>
                <!--
                <li><b>Java (OpenJDK 8):</b> <pre>-Xmx=ML</pre></li>
                <li><b>Python (3.5):</b> <pre>python &lt;source&gt;</pre></li>
                -->
            </ul>
            Максималното време за компилация е 3 секунди.<br>
            Максималният размер на стека е 64 мегабайта.
        ') . inBox('
            <h2 id="grader"><a href="#grader">Тестова машина</a></h2>
            Тестовата машина има следната хардуерна конфигурация:
            <ul>
                <li><b>Процесор:</b> Core2 Quad :: 3.0GHz</li>
                <li><b>Рам:</b> DDR2 8GB :: 800MHz</li>
                <li><b>Харддиск:</b> Corsair Force SSD :: 520MB/s</li>
            </ul>
            Ползваната операционна система на тестовата машина е Ubuntu 16.04.
        ') . inBox('
            <h2 id="system-status"><a href="#system-status">Опашка</a></h2>
            Чрез бутона "Опашка" в главното меню можете да видите текущите решения, които системата все още не е обработила (както и последните няколко, които е обработила).
            Когато системата е по-натоварена (например по време на състезание) там е мястото да проверите кога вашето решение ще бъде тествано.
        ') . inBox('
            <h2 id="training"><a href="#training">Подготовка</a></h2>
            Цялата система беше създадена като средство за подготовка за различни състезания или курсове. Чрез бутона "Подготовка" в главното меню можете да стигнете до систематично
            подбрани задачи в нарастваща сложност, застъпващи някои от най-разпространените теми в състезателната информатика. Минавайки през цялата подготовка ще Ви научи на мнозинството
            от основните алгоритми и структури данни, а също така ще Ви помогне да потренирате писането на по-сложен код, от това, което обикновено се застъпва в училище и университета.
            <br><br>
            Първите няколко теми от подготовката застъпват по-основните алгоритми и са подходящи както за ученици, подготвящи се за републикански или международни състезания, така и за студенти
            от университета или просто хора, които искат да се научат да пишат код.

            <br></br>
            Към края подготовката засяга значително по-сложни теми и включва съвсем нетривиални задачи. Тя е по-скоро насочена към хората, подготвящи се за състезания.
        ') . inBox('
            <h2 id="achievements"><a href="#achievements">Постижения</a></h2>
            Всеки регистриран потребител може да "отключи" така наречените <em>постижения</em>. Това са важни събития в подготовката (и ползването на системата),
            като например регистрация, първо предадено решение, първа изцяло решена задача и много други (над петдесет, за момента). Някои от тях са логични и очаквани
            (например завършването на дадена тема от подготовката), докато други са по-нестандартни и се присъждат на значително по-малък брой потребители. =)
        ') . inBox('
            <h2 id="ranking"><a href="#ranking">Класиране и точки</a></h2>
            Чрез бутона "Класиране" можете да достъпите текущо класиране на потребителите (както и да намерите линк към профила на всеки от тях). Класирането по подразбиране
            се прави на базата на точки, които потребителите събират решавайки задачи (по-трудни задачи носят повече точки), както и от постижения.
            <br><br>
            Системата предоставя възможност класирането да бъде подредено и по брой решени задачи или пък брой постижения.
        ') . inBox('
            <h2 id="problem-types"><a href="#problem-types">Видове задачи</a></h2>
            Системата поддържа няколко вида задачи:
            <ul>
                <li><b>IOI-стил:</b> Тестовете са разделени на групи, като за всяка група има определен брой точки. В най-честия случай всеки тест е отделна група.
                За да се вземат точки за групата, решението трябва да се е справило с всички тестове в групата.</li>
                <li><b>ACM-стил:</b> За да се вземат точки за задачата, решението трябва да се е справило с <em>всички</em> тестове.</li>
                <li><b>Релативна:</b> За всеки тест се получават точки, пропорционално спрямо най-добрия резултат от всички участници за съответния тест.</li>
                <li><b>Игра:</b> Всеки тест е "игра" между двама (или всички) участници, предали решение на задачата.</li>
            </ul>
        ');

        return $content;
    }
    
}

?>