function stopStream() {
    $.ajax({
        type: 'POST',
        url: window.baseUrl + 'stop',
        success: function (data) {
            console.log(data);
        }
    });
}

$(document).ready(function () {
    const listVideo = $('li.videoList');
    listVideo.click(function () {
        stopStream();
    });
    $('a').click(function () {
        stopStream();
    });
    $('button').click(function () {
        stopStream();
    });
    let playNext = true;
    let currentVideo;
    let videoToPlay = null;
    let videoToPlayID = null;
    if (window.location.hash) {
        playNext = false;
        videoToPlay = decodeURIComponent(window.location.hash.substring(1));
    }
    let count = 0;
    listVideo.each(function () {
        count++;
        $(this).attr('id', count);
        if ($(this).data('movie-url') === videoToPlay) {
            $('#video-area').attr({
                'src': window.baseUrl + 'video/' + videoToPlay,
            });
            videoToPlayID = count;
        }
    });

    function video(id) {
        listVideo.removeClass('active');
        $('#' + id).addClass('active');
        currentVideo = id;
        $('.video').html(currentVideo + '/' + count);
    }

    if (videoToPlayID != null) {
        video(videoToPlayID);
    }
    listVideo.on('click', function () {
        $('#video-area').attr({
            'src': window.baseUrl + 'video/' + $(this).data('movie-url'),
            'poster': '',
            'autoplay': 'autoplay'
        });
        video($(this).attr('id'));
        ChangeUrl('', $('#' + currentVideo).data('movie-url'));
    });
    if (videoToPlay == null) {
        $('#video-area').attr({
            'src': window.baseUrl + 'video/' + listVideo.eq(0).data('movie-url'),
            'autoplay': 'autoplay'
        });
        video($('#playlist li').eq(0).attr('id'));
    } else {
        $('#video-area').attr({
            'poster': '',
            'autoplay': 'autoplay'
        })
    }
    $('.next').click(function () {
        next();
    });
    $('.prev').click(function () {
        prev();
    });
    const videoBlock = $('video');
    videoBlock.on('ended', function () {
        if (playNext === true) next();
    });

    function ChangeUrl(title, url) {
        playNext = true;
        if (url === 'undefined') {
            if (typeof (history.pushState) != 'undefined') {
                let obj = {Title: title, Url: url};
                let urlToHash = encodeURIComponent(obj.Url);
                history.pushState(obj, obj.Title, '#' + urlToHash);
            }
        }
    }

    function next() {
        if (currentVideo !== count) video(parseInt(currentVideo) + 1);
        else {
            video(1);
            alert('Playlist ended');
        }
        let currentVideoLink = $('#' + currentVideo);

        $('#video-area').attr({
            'src': window.baseUrl + 'video/' + currentVideoLink.data('movie-url'),
            'poster': '',
            'autoplay': 'autoplay'
        });
        ChangeUrl('', currentVideoLink.data('movie-url'));
    }

    function prev() {
        if (currentVideo !== 1) video(parseInt(currentVideo) - 1);
        else {
            video(count);
        }
        let currentVideoLink = $('#' + currentVideo);
        $('#video-area').attr({
            'src': window.baseUrl + 'video/' + currentVideoLink.data('movie-url'),
            'poster': '',
            'autoplay': 'autoplay'
        });
        ChangeUrl('', currentVideoLink.data('movie-url'));
    }

    function play() {
        const videoArea = $('#video-area')[0];
        if (videoArea.paused === true) {
            videoArea.play();
        } else {
            videoArea.pause();
        }
    }

    videoBlock.click(function () {
        if (this.paused === false) {
            this.pause();
        } else {
            this.play();
        }
    });
    $(document).keydown(function (e) {
        switch (e.which) {
            case 37: // left
                prev();
                break;
            case 38: // up
                prev();
                break;
            case 39: // right
                next();
                break;
            case 40: // down
                next();
                break;
            case 32: // space
                play();
                break;
            default:
                return; // exit this handler for other keys
        }
        e.preventDefault(); // prevent the default action (scroll / move caret)
    });
});
