@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header fs-4">События</div>

                <div class="card-body d-flex flex-wrap">
                    <div style="border-right: 2px solid lightgrey; padding-right: 15px;">
                        <div id="all_events">
                            <div class="fs-5 mt-2 mb-2">Все события</div>
                            <div><i>Нет событий</i></div>
                        </div>
                        <div id="my_events" class="mt-4">
                            <div class="fs-5 mt-2 mb-2">Мои события</div>
                            <div><i>Нет событий</i></div>
                            <div class="btn btn-primary mt-2 mb-2">Создать событие</div>
                        </div>
                    </div>

                    <div id="area" class="p-2">

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
    <script>
        const origin = window.location.origin;
        const login = "{{$user->login}}";

        const headers = {
            'Content-Type': 'application/json',
            'Auth-Token': "{{$user->remember_token}}",
            'Login': "{{$user->login}}"
        };

        function getInfo() {
            fetch('/api/info', {
                headers: headers,
                method: 'GET'
            })
                .then(response => response.json())
                .then(response => {
                    console.log(response);
                    buildList(response['events_all'], false);
                    buildList(response['events_my'], true);
                })
                .catch(error => console.log(error));
        }

        function buildList(events = [], my = false) {
            const id = my ? 'my_events' : 'all_events';
            const title = my ? 'Мои события' : 'Все события';
            if (events.length === 0) {
                document.getElementById(id).innerHTML = `<div class="fs-5 mt-2 mb-2">${title}</div><div><i>Нет событий</i></div>`;
            } else {
                document.getElementById(id).innerHTML = `<div class="fs-5 mt-2 mb-2">${title}</div>`;
                for (el of events) {
                    document.getElementById(id).innerHTML += `<div style="padding: 5px; border: 1px solid lightgrey; border-radius: 5px;cursor: pointer;" data-my="${my ? 'true' : 'false'}" onclick="showEventPrepare(event, ${el['id']})">${el['Title']}</div>`;
                }
            }
            if (my)
                document.getElementById(id).innerHTML += `<div class="btn btn-primary mt-2 mb-2" id="create" onclick="createPrepareEvent()">Создать событие</div>`;
            document.getElementById('area').innerHTML = '';
        }

        function createPrepareEvent() {
            document.getElementById('area').innerHTML = `<div class="h4">Создать событие</div>
                        <div class="mt-2">
                            <label for="title">Название события</label>
                            <input type="text" name="title" id="title" class="form-control">
                        </div>
                        <div class="mt-2">
                            <label for="desc">Описание события</label>
                            <textarea name="desc" id="desc" cols="10" rows="5" class="form-control"></textarea>
                        </div>
                        <div class="btn btn-outline-success mt-4" id="save" onclick="createNewEvent()">Сохранить</div>`;
        }

        function createNewEvent() {
            const title = document.querySelector('input[name="title"]').value;
            const desc = document.querySelector('textarea[name="desc"]').value;

            if (title.length === 0 || desc.length === 0) {
                console.log("empty fields");
                return 0;
            }

            fetch('/api/create', {
                headers: headers,
                method: "POST",
                body: JSON.stringify({
                    title,
                    desc
                })
            })
                .then(response => response.json())
                .then(response => {
                    console.log(response);
                    getInfo();
                })
                .catch(error => console.log(error));
        }

        function showEventPrepare(event, id) {
            fetch(`/api/event/${id}`, {
                headers: headers,
                method: 'GET'
            })
                .then(response => response.json())
                .then(response => {
                    console.log(response);
                    showEvent(event, response);
                })
                .catch(error => console.log(error));
        }

        function showEvent(event, current) {
            const my = event.target.dataset.my === 'true';
            let contributors = '';
            let ImIn = false;
            for (let user of current.users) {
                contributors += `<div onclick="showUserPrepare('${user.login}')"><a href="#">${user.name} ${user.surname}</a></div>`;
                if (user.login === login)
                    ImIn = true;
            }
            if (contributors.length === 0)
                contributors = '<div>Нет участников</div>';

            const objectDate = new Date(current.created_at);
            let day = objectDate.getDate();
            let month = objectDate.getMonth();
            let year = objectDate.getFullYear();
            const date = day + "/" + month + "/" + year;

            let action;
            if (ImIn)
                action = `<div class="btn btn-outline-danger mt-4" onclick="removeParticipate(${current.id})">Отказаться от участия</div>`;
            else
                action = `<div class="btn btn-outline-success mt-4" onclick="addParticipate(${current.id})">Принять участие</div>`;

            document.querySelector('#area').innerHTML = `<div class="h4">${current.Title}</div>
                        <div class="mt-2">
                            ${current.Text}
                        </div>
                        <div class="mt-2">
                            ${date}
                        </div>
                        <div class="mt-4">
                            <div class="h5">Участники:</div>
                            ${contributors}
                        </div>
                        ${action}`;
        }

        function removeParticipate(event_id) {
            fetch('/api/remove', {
                headers: headers,
                method: "POST",
                body: JSON.stringify({
                    event_id,
                })
            })
                .then(response => response.json())
                .then(response => {
                    console.log(response);
                    getInfo();
                })
                .catch(error => console.log(error));
        }

        function addParticipate(event_id) {
            fetch('/api/add', {
                headers: headers,
                method: "POST",
                body: JSON.stringify({
                    event_id,
                })
            })
                .then(response => response.json())
                .then(response => {
                    console.log(response);
                    getInfo();
                })
                .catch(error => console.log(error));
        }

        function showUserPrepare(user_login) {
            fetch(`/api/user/${user_login}`, {
                headers: headers,
                method: "GET",
            })
                .then(response => response.json())
                .then(response => {
                    console.log(response);
                    showUser(response);
                })
                .catch(error => console.log(error));
        }

        function showUser(user) {
            let date = 'Не известно';
            if (user.born) {
                const objectDate = new Date(user.born);
                let day = objectDate.getDate();
                let month = objectDate.getMonth();
                let year = objectDate.getFullYear();
                date = day + "/" + month + "/" + year;
            }

            document.querySelector('#area').innerHTML = `<div class="h4">${user.name} ${user.surname}</div>
                        <div class="mt-2">
                            <b>Логин </b>${user.login}
                        </div>
                        <div class="mt-2">
                            <b>Дата рождения </b>${date}
                        </div>`;
        }

        document.addEventListener('DOMContentLoaded', () => {
            getInfo();
        });

    </script>
@endsection
