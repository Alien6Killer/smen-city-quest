function connect()
{
    return new WebSocket("wss://" +  location.host + "/wss?chat-id-"+ userId);
}

var userId = $('[data-user]').data().user;
socket = connect();

socket.onopen = function () {
    console.log('Connection successful');
    loadMessages();
};

socket.onclose = function (event) {
    if (event.wasClean) {
        console.log('Connection closed.');
    } else {
        console.log('Connection killed:(');
    }
    socket = connect();
};

socket.onmessage = function (event) {
    var list = $('#list').last();
    var message = '<div class="card">'+event.data+'</div>';

    list.append(message)
};

socket.onerror = function (error) {
    console.log(error.message);
};

var button = document.getElementById('send');
var textarea = document.getElementById('message-box');

function sendText() {
    var text = textarea.value;
    if (text.length > 0) {
        socket.send(JSON.stringify(text));
        textarea.value = '';

        return true;
    }

    return false;
}

button.onclick = sendText;

textarea.onkeypress = function (ev) {
    if (ev.charCode === 13 && ev.shiftKey) {
        sendText();
    }
};

function loadMessages() {
    $.ajax({
       url: '/messages',
       method: 'GET'
    }).success(function (data) {
        data.forEach(function(item, i, arr) {
            var list = $('#list').last();
            var message = '<div class="card">'+item+'</div>';

            list.append(message)
        });
    });
}


