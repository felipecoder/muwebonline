document.addEventListener('DOMContentLoaded', function () {
  if (Notification.permission !== 'granted')
    Notification.requestPermission();
});

function notifyMe(icon, title, message, link) {
  if (!Notification) {
    alert('Seu navegador não suporta notificações, tente o Google Chrome');
    return;
  }

  if (Notification.permission !== "granted") {
    Notification.requestPermission();
  } else {
    var notification = new Notification(title, {
      icon: icon,
      body: message
    });

    notification.onclick = function () {
      window.open(link);
    };
  }
}

function getEvents(element, events, icon, title, link) {

  var onReady = [];
  var eventsTime = events;

  function toSeconds(h, m, s) {
    return h * 3600 + m * 60 + s;
  }

  function updateEventsTime() {
    var d = new Date();
    var time = toSeconds(d.getHours(), d.getMinutes(), d.getSeconds());

    var html = '';
    for (i in eventsTime) {
      var j;
      var push = -1;
      for (j = 0; j < eventsTime[i][1].length; j++) {
        var t = eventsTime[i][1][j].split(':');
        t = toSeconds(t[0], t[1], 0);
        if (t > time) break;
      }

      j = j % eventsTime[i][1].length;
      var t = eventsTime[i][1][j].split(':');

      var diff = toSeconds(t[0], t[1], 0) - time;
      if (diff < 0) diff += 3600 * 24;
      push = diff;

      var h = parseInt(diff / 3600);
      diff -= 3600 * h;
      var m = parseInt(diff / 60);
      var s = diff - m * 60;
      if (push == 60 * 5) {
        var message = 'O evento ' + eventsTime[i][0] + ' está perto de começar';
        notifyMe(icon, title, message, link);
      }

      var countdown = h + ':' + ("0" + m).slice(-2) + ':' + ("0" + s).slice(-2);

      //Eventos contando //
      html += '<li class="btnl"><b>' + eventsTime[i][0] + ':</b> <span>' + countdown + '</span></li>';

    }
    $(element).html(html);
  }

  onReady.push(function () { setInterval(updateEventsTime, 1000) });

  for (var i in onReady) onReady[i]();
}