<!DOCTYPE html>
<html>
<head>
    <title>TeleTask</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://telegram.org/js/telegram-web-app.js"></script> <!--Подключаем скрипт от телеграм-->
    <style type="text/css">
        body {
            text-align: center;
            font-family: Verdana, Arial, sans-serif;
            font-size: 16px;
            max-width: 590px;
            margin: auto;
        }

        input {
            display: inline-block;
            margin: 20px;
            padding: 5px 20px;
            font-family: Verdana, Arial, sans-serif;
            font-size: 16px;
            outline: none;
            border-radius: 5px;
            border: transparent;
            text-align: center;
            width: calc(100% - 80px);
            background: #DEDDDD url(images/enter_icon.svg) no-repeat calc(100% - 10px) center;
        }

        ul.List {
            margin: 0;
            padding: 0;
            list-style-type: none;
        }

        /*Как будет выглядеть каждый элемент нашего списка*/
        .task {
            text-align: left;
            padding: 0 20px 0 25px;
            cursor: default;
            border-bottom: 1px solid #DEDDDD;
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 64px;
        }

        .task.show-confirm {
            position: relative;
            overflow: hidden;
        }

        .task .number {
            border-radius: 50%;
            width: 22px;
            min-width: 22px;
            height: 22px;
            padding: 4px;
            background: #30CC3F;
            color: #FFFFFF;
            text-align: center;
            font-size: 16px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .task .description {
            min-width: 150px;
            max-width: 300px;
            margin: 0 13px;
            overflow: hidden;
            position: relative;
            text-overflow: ellipsis;
            word-break: break-word;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            word-wrap: break-word;
            color: #282828;
            display: -webkit-box;
        }

        .task .info {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .task .completeness {
            background: #EEEEEE;
            border-radius: 4px;
            font-weight: 400;
            font-size: 14px;
            color: #212121;
            text-align: center;
            margin-right: 6px;
            padding: 5px 10px;
        }

        .task .user {
            vertical-align: middle;
            width: 26px;
            height: 26px;
            border-radius: 50%;
        }

        .task .action {
            width: 85px;
            text-align: center;
            padding: 7px;
        }

        .task .action.success {
            background: #30CC3F;
            margin-right: 7px;
        }

        .task .action.delete {
            background: #F44336;
        }

        .task .action.hide {
            display: none;
        }

        .task .action .circle {
            border: 1px solid #FFF;
            border-radius: 50%;
            width: 30px;
            display: block;
            height: 30px;
            margin: auto;
        }

        .task .action .action-text {
            display: block;
            font-size: 14px;
            color: #fff;
        }

        div.scrollmenu {
            overflow: auto;
            white-space: nowrap;
            width: calc(100% - 40px);
            margin: 0 auto;
            text-align: left;
        }

        div.scrollmenu a {
            display: inline-block;
            font-size: 14px;
            color: #263238;
            text-align: center;
            padding: 14px;
            text-decoration: none;
            font-weight: 400;
        }

        div.scrollmenu a.active {
            color: #007AFE;
            font-weight: 700;
            border-bottom: 3px solid;
        }

        div.scrollmenu a:hover {
            background-color: lightblue;
        }

        @media screen and (max-width: 400px) {
            .task.show-delete {
                position: relative;
                justify-content: flex-end;
            }
        }

    </style>
</head>

<body>
{{--<h2 class="todo__caption">Список задач</h2>--}}
<div id="tdlApp">
    <div id="testTg"></div>
    <input type="text" class="form-control" placeholder="Текст новой задачи">

    <div class="scrollmenu">
        <a class="active" href="#all">Все задачи</a>
        <a href="#news">Выполненные</a>
    </div>
    <div class="tdlDiv">
        <ul class="List list-unstyled">
        </ul>
    </div>
</div>


<!-- Подключаем JQuery -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js">
</script>

<script>
    var List = $('#tdlApp ul');
    var Mask = 'tdl_';
    let tg = window.Telegram.WebApp; //получаем объект webapp телеграма
    document.getElementById("testTg").innerHTML = JSON.stringify(tg)
    // function showTasks() {
    //     // Узнаём размер хранилища
    //     var Storage_size = localStorage.length;
    //     // Если в хранилище что-то есть…
    //     if (Storage_size > 0) {
    //         // то берём и добавляем это в задачи
    //         for (var i = 0; i < Storage_size; i++) {
    //             var key = localStorage.key(i);
    //             if (key.indexOf(Mask) == 0) {
    //                 // и делаем это элементами списка
    //                 $('<li></li>').addClass('tdItem')
    //                     .attr('data-itemid', key)
    //                     .text(localStorage.getItem(key))
    //                     .appendTo(List);
    //             }
    //         }
    //     }
    // }
    // // Сразу вызываем эту функцию, вдруг в памяти уже остались задачи с прошлого раза
    // showTasks();
    // Следим, когда пользователь напишет новую задачу в поле ввода и нажмёт Enter
    $('#tdlApp input').on('keydown', function (e) {
        if (e.keyCode != 13) return;
        var str = e.target.value;
        e.target.value = "";
        // Если в поле ввода было что-то написано — начинаем обрабатывать
        if (str.length > 0) {
            var number_Id = 0;
            List.children().each(function (index, el) {
                var element_Id = $(el).attr('data-itemid').slice(4);
                if (element_Id > number_Id)
                    number_Id = element_Id;
            })
            number_Id++;
            // Отправляем новую задачу сразу в память
            localStorage.setItem(Mask + number_Id, str);
            // и добавляем её в конец списка
            $('<li>' +
                '<div class="action success hide">' +
                '<span class="circle"></span>' +
                '<span class="action-text">Выполнить</span>' +
                '</div>' +
                '<span class="number">' + (List.children().length + 1) + '</span>' +
                '<span class="description">' + str + '</span>' +
                '<div class="info">' +
                '<span class="completeness">3/4</span>' +
                '<img src="images/user.png" alt="Avatar" class="user">' +
                '</div>' +
                '<div class="action delete hide">' +
                '<span class="circle"></span>' +
                '<span class="action-text">Удалить</span>' +
                '</div>' +
                '</li>').addClass('task')
                .attr('data-itemid', Mask + number_Id).prependTo(List);

            $('.task').off('touchstart touchmove touchend');

            $('.task').on('touchstart', function (e) {

                var swipe = e.originalEvent.touches,
                    start = swipe[0].pageX;

                $(this).on('touchmove', function (e) {
                    var contact = e.originalEvent.touches,
                        end = contact[0].pageX,
                        distance = end - start;

                    if (distance < -30) // swipe left
                    {
                        if ($(this).hasClass('show-confirm')) {
                            console.log('Есть класс confirm')
                            $(this).removeClass('show-confirm')
                            $(this).removeClass('show-delete')
                            $(this).find('.action.success').addClass('hide')
                            $(this).trigger('touchend');
                        } else {
                            $(this).find('.action.delete').removeClass('hide')
                            $(this).addClass('show-delete')
                            $(this).removeClass('show-confirm')
                            $(this).find('.action.success').addClass('hide')
                            $(this).trigger('touchend');
                        }
                    }
                    if (distance > 30) // swipe right
                    {
                        if ($(this).hasClass('show-delete')) {
                            console.log('Есть класс delete')
                            $(this).removeClass('show-confirm')
                            $(this).removeClass('show-delete')
                            $(this).find('.action.delete').addClass('hide')
                            $(this).trigger('touchend');
                        } else {
                            $(this).find('.action.success').removeClass('hide')
                            $(this).addClass('show-confirm')
                            $(this).removeClass('show-delete')
                            $(this).find('.action.delete').addClass('hide')
                            $(this).trigger('touchend');
                        }
                    }
                })
                    .one('touchend', function () {

                        $(this).off('touchmove touchend');
                    });
            });
        }
    });
    // По клику на удалить/выполнить — убираем её из списка
    $(document).on('click', '.action', function (e) {
        let task = $(e.target.closest('.task')).remove()
    })


</script>
<!-- Закончилось содержимое страницы-->
</body>

</html>
