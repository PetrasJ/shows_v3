function stopStream() {
    $.ajax({
        type: "POST",
        url: window.baseUrl + 'stop',
        success: function (data) {
            console.log(data);
        }
    });
}
$(document).ready(function () {
    $('li.videoList').click(function () {
        stopStream();
    });
    $('a').click(function () {
        stopStream();
    });
    $('button').click(function () {
        stopStream();
    });
    var playnext = true;
    var currentvideo;
    var videotoplay = null;
    var videotoplayID = null;
    if (window.location.hash) {
        playnext = false;
        videotoplay = decodeURIComponent(window.location.hash.substring(1));
    }
    var count = 0;
    $('li.videoList').each(function () {
        count++;
        $(this).attr('id', count);
        if ($(this).data('movie-url') == videotoplay) {
            $("#video-area").attr({
                "src": window.baseUrl + 'video/' + videotoplay,
            });
            videotoplayID = count;
        }
    });
    function video(id) {
        $('li.videoList').removeClass('active');
        $('#' + id).addClass('active');
        currentvideo = id;
        $('.video').html(currentvideo + '/' + count);
    }
    if (videotoplayID != null) {
        video(videotoplayID);
    }
    $("#playlist li").on("click", function () {
        $("#video-area").attr({
            "src": window.baseUrl + 'video/' + $(this).data('movie-url'),
            "poster": "",
            "autoplay": "autoplay"
        });
        video($(this).attr('id'));
        ChangeUrl('sitas video', $('#' + currentvideo).data('movie-url'));
    });
    if (videotoplay == null) {
        $("#video-area").attr({
            "src": window.baseUrl + 'video/' + $("#playlist li").eq(0).data('movie-url'),
            "autoplay": "autoplay"
        })
        video($("#playlist li").eq(0).attr("id"));
    } else {
        $("#video-area").attr({
            "poster": "",
            "autoplay": "autoplay"
        })
    }
    $('.next').click(function () {
        next();
    });
    $('.prev').click(function () {
        prev();
    });
    $('video').on('ended', function () {
        if (playnext == true) next();
    });
    function ChangeUrl(title, url) {
        playnext = true;
        if (url == "undefined") {
            if (typeof (history.pushState) != "undefined") {
                var obj = {Title: title, Url: url};
                var urltohash = encodeURIComponent(obj.Url)
                history.pushState(obj, obj.Title, '#' + urltohash);
            }
        }
    }
    function next() {
        if (currentvideo != count) video(parseInt(currentvideo) + 1);
        else {
            video(1);
            alert('Playlist ended');
        }
        $("#video-area").attr({
            "src": window.baseUrl + 'video/' + $('#' + currentvideo).data('movie-url'),
            "poster": "",
            "autoplay": "autoplay"
        });
        ChangeUrl('sitas video', $('#' + currentvideo).data('movie-url'));
    }
    function prev() {
        if (currentvideo != 1) video(parseInt(currentvideo) - 1);
        else video(count);
        $("#video-area").attr({
            "src": window.baseUrl + 'video/' + $('#' + currentvideo).data('movie-url'),
            "poster": "",
            "autoplay": "autoplay"
        });
        ChangeUrl('sitas video', $('#' + currentvideo).data('movie-url'));
    }
    function play() {
        $("#video-area").get(0).play();
    }
    $('video').click(function () {
        if (this.paused == false) {
            this.pause();
        } else {
            this.play();
        }
    });
    $(document).keydown(function (e) {
        switch (e.which) {
            case 37:
                prev();// left
                break;
            case 38:
                prev(); // up
                break;
            case 39:
                next(); // right
                break;
            case 40:
                next(); // down
                break;
            case 32:
                play();
                break;
            default:
                return; // exit this handler for other keys
        }
        e.preventDefault(); // prevent the default action (scroll / move caret)
    });
});
